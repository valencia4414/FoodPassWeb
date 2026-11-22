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

        // Los administradores no tienen flujo de código QR; redirigir a platillos del día
        if ($user->isAdmin()) {
            return redirect()->route('admin.platillos.index');
        }

        // Verificar elegibilidad (beneficiario SENA)
        $beneficiario = BeneficiarioSena::where('email', $user->email)
            ->where('activo', true)
            ->first();

        $esBeneficiario = (bool) $beneficiario;

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

        if ($user->isAdmin()) {
            return redirect()->route('admin.platillos.index');
        }

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

    /**
     * Envía la credencial QR actual del aprendiz a su correo electrónico registrado.
     * Requisitos:
     * - Aprendiz debe ser beneficiario activo.
     * - Debe tener un QR vigente generado hoy (estado 'pendiente').
     * - No genera nuevo QR ni descuenta ración adicional.
     * - Adjunta el PNG y lo incluye inline.
     * - Registra evento en audit_logs.
     */
    public function enviarQrCorreo(Request $request)
    {
        $user = auth()->user();

        // 1. Validar que sea beneficiario activo o administrador
        if (!$user->isBeneficiario() && !$user->isAdmin()) {
            return back()->with('error', 'Solo los beneficiarios activos del programa alimentario SENA pueden recibir credenciales QR.');
        }

        // 2. Buscar el QR activo de hoy
        $canje = Canje::where('usuario_id', $user->id)
            ->whereDate('fecha_canje', today())
            ->where('estado', 'pendiente')
            ->latest('id')
            ->first();

        if (!$canje || empty($canje->codigo_qr)) {
            return back()->with('error', 'No tienes una credencial QR activa generada hoy. Por favor genera tu código QR primero antes de enviarlo por correo.');
        }

        try {
            // 3. Generar imagen PNG del código QR
            $qrPngBytes = \App\Services\SrsFoodPassService::generarQrPngBytes($canje->codigo_qr, 8, 4);

            $franjaInfo = [
                'nombre'      => 'Apoyo Alimentario SENA',
                'codigo'      => 'ALMUERZO',
                'hora_limite' => '11:59 PM (Medianoche)',
            ];

            // 4. Enviar correo electrónico con Mailable (inline + adjunto)
            \Illuminate\Support\Facades\Mail::to($user->email)->send(
                new \App\Mail\CredencialQrMailable($user, $canje, $franjaInfo, $qrPngBytes)
            );

            // 5. Registrar evento en bitácora inmutable de auditoría
            \App\Services\SrsFoodPassService::registrarAuditoria(
                'QR_ENVIADO_CORREO',
                'Beneficios SENA',
                "Credencial QR {$canje->codigo_qr} enviada satisfactoriamente al correo {$user->email}.",
                [
                    'canje_id'    => $canje->id,
                    'codigo_qr'   => $canje->codigo_qr,
                    'email'       => $user->email,
                    'fecha_canje' => $canje->fecha_canje,
                ]
            );

            return back()->with('success', "¡Código QR enviado exitosamente a tu correo ({$user->email})! Incluye imagen y archivo adjunto.");
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Error al enviar QR por correo a {$user->email}: " . $e->getMessage());
            return back()->with('error', 'Ocurrió un error al enviar el correo con el código QR. Inténtalo de nuevo.');
        }
    }
}
