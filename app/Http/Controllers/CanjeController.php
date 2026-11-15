<?php

namespace App\Http\Controllers;

use App\Models\BeneficiarioSena;
use App\Models\Canje;
use App\Models\Restaurante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CanjeController extends Controller
{
    /**
     * Muestra la vista de canje.
     * El usuario puede generar códigos QR cuantas veces quiera en el mismo día.
     * Regla estricta: Todos los códigos expiran a las 12:00 AM (medianoche) de cada día.
     */
    public function index()
    {
        $user = auth()->user();

        // Verificar elegibilidad (beneficiario SENA o administrador)
        $beneficiario = BeneficiarioSena::where('email', $user->email)
            ->where('activo', true)
            ->first();

        $esBeneficiario = (bool) $beneficiario || $user->isAdmin();

        // Obtener historial de QR generados hoy (sin límite de generación)
        $canjesHoy = Canje::where('usuario_id', $user->id)
            ->whereDate('fecha_canje', today())
            ->orderBy('id', 'desc')
            ->get();

        // Obtener el QR activo más reciente de hoy, o el recién generado
        $ultimoQr = session('nuevoQr') 
            ?? $canjesHoy->firstWhere('estado', 'pendiente')?->codigo_qr 
            ?? $canjesHoy->first()?->codigo_qr;

        // Hora estricta de expiración: 12:00 AM del día siguiente (medianoche de hoy)
        $expiraEn = Carbon::tomorrow()->startOfDay();

        return view('canje_foodpass.index', compact('user', 'esBeneficiario', 'canjesHoy', 'expiraEn', 'ultimoQr'));
    }

    /**
     * Genera un nuevo código QR de canje.
     * El usuario puede generar cuantos códigos QR quiera en el mismo día.
     * Todos los códigos expiran a las 12:00 AM (medianoche).
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        // Validar elegibilidad
        $beneficiario = BeneficiarioSena::where('email', $user->email)
            ->where('activo', true)
            ->first();

        $esBeneficiario = (bool) $beneficiario || $user->isAdmin();

        if (!$esBeneficiario) {
            return back()->with('error', 'No eres beneficiario del programa alimentario SENA.');
        }

        // Obtener restaurante activo o por defecto
        $restauranteId = $request->restaurante_id 
            ?? Restaurante::value('id') 
            ?? 1;

        // Generar código QR único y seguro
        $tokenUnico = strtoupper(Str::random(10));
        $codigoQr = "FP-QR-{$user->id}-" . now()->format('YmdHis') . "-{$tokenUnico}";

        // Regla estricta de vigencia: 12:00 AM (medianoche de hoy)
        $expiraEn = Carbon::tomorrow()->startOfDay();

        // Registrar canje (sin restricciones de unicidad diaria)
        $canje = Canje::create([
            'usuario_id'           => $user->id,
            'beneficiario_sena_id' => $beneficiario?->id,
            'restaurante_id'       => $restauranteId,
            'monto'                => $request->monto ?? 0,
            'fecha_canje'          => today(),
            'periodo'              => now()->format('Y-m-d H:i:s'),
            'estado'               => 'pendiente',
            'detalle'              => 'Beneficio Almuerzo SENA',
            'codigo_qr'            => $codigoQr,
            'expira_en'            => $expiraEn,
        ]);

        return back()
            ->with('success', '¡Nuevo código QR generado exitosamente!')
            ->with('nuevoQr', $codigoQr);
    }

    /**
     * Valida y redime un código QR presentado en el restaurante.
     * Aplica la regla estricta de vigencia (12:00 AM / medianoche).
     */
    public function validar(Request $request)
    {
        $request->validate([
            'codigo_qr' => 'required|string',
        ]);

        $canje = Canje::where('codigo_qr', $request->codigo_qr)->first();

        if (!$canje) {
            return response()->json([
                'valido'  => false,
                'mensaje' => 'Código QR no encontrado en el sistema.',
            ], 404);
        }

        // Regla estricta de vigencia: si ya pasaron las 12:00 AM (medianoche) del día
        if (Carbon::now()->greaterThanOrEqualTo($canje->expira_en) || !Carbon::parse($canje->fecha_canje)->isToday()) {
            if ($canje->estado !== 'expirado') {
                $canje->update(['estado' => 'expirado']);
            }

            return response()->json([
                'valido'   => false,
                'mensaje'  => 'El código QR ha expirado. Todos los códigos expiran automáticamente a las 12:00 AM (medianoche).',
                'estado'   => 'expirado',
                'expirado' => true,
            ], 410);
        }

        // Verificar si ya fue utilizado
        if (in_array($canje->estado, ['exitoso', 'entregado'])) {
            return response()->json([
                'valido'  => false,
                'mensaje' => 'Este código QR ya fue redimido previamente.',
                'estado'  => $canje->estado,
            ], 409);
        }

        // Marcar como exitoso
        $canje->update([
            'estado' => 'exitoso',
        ]);

        return response()->json([
            'valido'      => true,
            'mensaje'     => '¡Beneficio validado y canjeado exitosamente!',
            'canje'       => $canje->load('user:id,nombre,email', 'restaurante:id,nombre'),
            'hora_canje'  => now()->format('h:i:s A'),
            'beneficiario'=> $canje->user?->nombre,
        ]);
    }
}
