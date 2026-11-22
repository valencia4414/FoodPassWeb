<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BeneficiarioSena;
use App\Models\CargaBeneficiario;
use App\Models\Pedido;
use App\Models\User;
use App\Services\BeneficiariosImportService;
use App\Services\SrsFoodPassService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AdminBeneficiariosController extends Controller
{
    /**
     * Lista de Beneficiarios administrables y registro histórico de cargas.
     */
    public function index(Request $request)
    {
        $busqueda = $request->input('buscar');
        $filtroEstado = $request->input('estado'); // 'activos', 'inactivos', 'todos'

        $query = BeneficiarioSena::query();

        if ($busqueda) {
            $query->where(function ($q) use ($busqueda) {
                $q->where('nombre_completo', 'like', "%{$busqueda}%")
                  ->orWhere('email', 'like', "%{$busqueda}%")
                  ->orWhere('numero_documento', 'like', "%{$busqueda}%");
            });
        }

        if ($filtroEstado === 'activos') {
            $query->where('activo', 1);
        } elseif ($filtroEstado === 'inactivos') {
            $query->where('activo', 0);
        }

        $beneficiarios = $query->orderByDesc('activo')
                               ->orderBy('nombre_completo')
                               ->paginate(20);

        $totalBeneficiarios = BeneficiarioSena::count();
        $totalActivos = BeneficiarioSena::where('activo', 1)->count();
        $totalInactivos = BeneficiarioSena::where('activo', 0)->count();

        // Historial de cargas automáticas y manuales del padrón
        $historialCargas = CargaBeneficiario::orderByDesc('id')->take(10)->get();

        return view('admin.beneficiarios.index', compact(
            'beneficiarios',
            'totalBeneficiarios',
            'totalActivos',
            'totalInactivos',
            'busqueda',
            'filtroEstado',
            'historialCargas'
        ));
    }

    /**
     * Registrar un nuevo aprendiz en la lista de beneficiarios manualmente.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre_completo'  => 'required|string|max:150',
            'email'            => 'required|email|max:150',
            'numero_documento' => 'required|string|max:30',
            'activo'           => 'required|in:0,1',
        ]);

        $email = strtolower(trim($request->email));
        $activo = (bool) $request->activo;

        $beneficiario = BeneficiarioSena::updateOrCreate(
            ['email' => $email],
            [
                'nombre_completo'  => trim($request->nombre_completo),
                'numero_documento' => trim($request->numero_documento),
                'activo'           => $activo,
                'sincronizado_at'  => now(),
            ]
        );

        SrsFoodPassService::registrarAuditoria(
            'ADMIN_BENEFICIARIO_REGISTRADO',
            'Administración',
            "Aprendiz {$beneficiario->nombre_completo} registrado en la lista de beneficiarios como " . ($activo ? 'ACTIVO' : 'INACTIVO') . " por el Administrador.",
            ['email' => $email, 'activo' => $activo]
        );

        return back()->with('success', "Aprendiz {$beneficiario->nombre_completo} guardado correctamente (" . ($activo ? 'Activo con acceso a QR' : 'Inactivo sin acceso a QR') . ').');
    }

    /**
     * Activar o Inactivar un beneficiario (con 1 clic).
     */
    public function toggleActivo($id)
    {
        $beneficiario = BeneficiarioSena::findOrFail($id);
        $nuevoEstado = !$beneficiario->activo;

        $beneficiario->activo = $nuevoEstado;
        $beneficiario->sincronizado_at = now();
        $beneficiario->save();

        SrsFoodPassService::registrarAuditoria(
            'ADMIN_BENEFICIARIO_TOGGLE',
            'Administración',
            "Estado de beneficiario para {$beneficiario->nombre_completo} ({$beneficiario->email}) cambiado a " . ($nuevoEstado ? 'ACTIVO' : 'INACTIVO') . ".",
            ['id' => $id, 'activo' => $nuevoEstado]
        );

        $msg = $nuevoEstado
            ? "¡{$beneficiario->nombre_completo} ha sido ACTIVADO en el padrón! Ahora tiene acceso al código QR."
            : "{$beneficiario->nombre_completo} ha sido INACTIVADO en el padrón. Ya no podrá generar el código QR de beneficio.";

        return back()->with('success', $msg);
    }

    /**
     * Carga de archivo Excel (.xlsx), OpenDocument (.ods) o CSV para sincronización automática del padrón.
     */
    public function subirArchivo(Request $request, BeneficiariosImportService $importService)
    {
        $request->validate([
            'archivo' => 'required|file|max:10240',
        ], [
            'archivo.required' => 'Seleccione un archivo en formato Excel (.xlsx), OpenDocument (.ods) o CSV.',
            'archivo.max'      => 'El archivo no debe exceder los 10 megabytes.',
        ]);

        $archivo = $request->file('archivo');
        $extension = strtolower($archivo->getClientOriginalExtension());
        $formatosValidos = ['xlsx', 'ods', 'csv', 'txt'];

        if (!in_array($extension, $formatosValidos)) {
            return back()->with('error', "El formato .{$extension} no es válido. Solo se admiten archivos .xlsx, .ods y .csv.");
        }

        try {
            $usuarioEjecutor = auth()->user() ? auth()->user()->nombre . " (" . auth()->user()->email . ")" : 'Administrador';
            $res = $importService->procesar($archivo, $usuarioEjecutor);

            $mensaje = "Sincronización finalizada: {$res['aprendices_activados']} aprendices activados en el padrón y {$res['aprendices_inactivados']} inactivados.";
            if ($res['errores_count'] > 0) {
                $mensaje .= " Se omitieron {$res['errores_count']} fila(s) con inconsistencias (consulte el historial de cargas).";
            }

            return back()->with('success', $mensaje);
        } catch (\Throwable $e) {
            return back()->with('error', 'Error al procesar el archivo: ' . $e->getMessage());
        }
    }

    /**
     * Descargar copia de seguridad de un archivo de padrón cargado previamente.
     */
    public function descargarArchivoCarga($id)
    {
        $carga = CargaBeneficiario::findOrFail($id);
        if (!Storage::disk('local')->exists($carga->ruta_archivo_privado)) {
            return back()->with('error', 'El archivo privado ya no se encuentra disponible en el almacenamiento del servidor.');
        }

        return Storage::disk('local')->download($carga->ruta_archivo_privado, $carga->nombre_archivo_original);
    }

    /**
     * Eliminar un aprendiz de la lista de beneficiarios.
     */
    public function destroy($id)
    {
        $beneficiario = BeneficiarioSena::findOrFail($id);
        $email = $beneficiario->email;
        $nombre = $beneficiario->nombre_completo;

        $beneficiario->delete();

        SrsFoodPassService::registrarAuditoria(
            'ADMIN_BENEFICIARIO_ELIMINADO',
            'Administración',
            "Aprendiz {$nombre} ({$email}) eliminado de la lista de beneficiarios por el Administrador.",
            ['email' => $email]
        );

        return back()->with('success', "{$nombre} eliminado de la lista de beneficiarios.");
    }

    /**
     * Gestión de Pedidos de la Cafetería para Administradores.
     */
    public function pedidos(Request $request)
    {
        $estado = $request->input('estado');
        if ($estado === 'en_preparacion') {
            $estado = 'preparacion';
        }

        $query = Pedido::with('usuario')->orderByDesc('id');

        if ($estado) {
            $query->where('estado', $estado);
        }

        $pedidos = $query->paginate(25);

        return view('admin.pedidos.index', compact('pedidos', 'estado'));
    }

    /**
     * Actualizar estado de un pedido (en preparación, listo, entregado, cancelado).
     */
    public function actualizarEstadoPedido(Request $request, $id)
    {
        $validated = $request->validate([
            'estado' => 'required|in:pendiente,preparacion,entregado,cancelado',
        ]);

        $pedido = Pedido::findOrFail($id);
        $pedido->estado = $validated['estado'];
        $pedido->save();

        return back()->with('success', "Pedido #FP-" . str_pad($pedido->id, 4, '0', STR_PAD_LEFT) . " actualizado a estado '" . ucfirst(str_replace('_', ' ', $pedido->estado)) . "'.");
    }
}
