<?php

namespace App\Http\Controllers;

use App\Models\BeneficiarioSena;
use App\Models\Canje;
use App\Models\Restaurante;
use Illuminate\Http\Request;
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
        $beneficiario = BeneficiarioSena::where(function ($query) use ($user) {
            $query->where('email', $user->email);
            if ($user->documento) {
                $query->orWhere('numero_documento', $user->documento);
            }
        })
            ->where('activo', true)
            ->first();

        $esBeneficiario = (bool) $beneficiario || $user->isAdmin();

        // Obtener historial de QR generados hoy (sin límite de generación)
        $canjesHoy = Canje::where('usuario_id', $user->id)
            ->whereDate('fecha_canje', today())
            ->orderBy('id', 'desc')
            ->get();

        // Obtener el QR activo más reciente de hoy, o el recién generado
        $ultimoQr = $canjesHoy
            ->first(fn (Canje $canje) => $canje->estado === 'pendiente' && $canje->estaVigente())
            ?->codigo_qr;

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
        $beneficiario = BeneficiarioSena::where(function ($query) use ($user) {
            $query->where('email', $user->email);
            if ($user->documento) {
                $query->orWhere('numero_documento', $user->documento);
            }
        })
            ->where('activo', true)
            ->first();

        $esBeneficiario = (bool) $beneficiario || $user->isAdmin();

        if (!$esBeneficiario) {
            return back()->with('error', __('ui.benefit_not_active'));
        }

        // Obtener restaurante activo o por defecto
        $restauranteId = Restaurante::value('id') ?? 1;

        // Generar código QR único y seguro
        $codigoQr = 'FP-' . bin2hex(random_bytes(32));

        // Regla estricta de vigencia: 12:00 AM (medianoche de hoy)
        $expiraEn = Carbon::tomorrow()->startOfDay();

        // Registrar canje (sin restricciones de unicidad diaria)
        $canje = Canje::create([
            'usuario_id'           => $user->id,
            'beneficiario_sena_id' => $beneficiario?->id,
            'restaurante_id'       => $restauranteId,
            'monto'                => 0,
            'fecha_canje'          => today(),
            'periodo'              => now()->format('Y-m-d H:i:s'),
            'estado'               => 'pendiente',
            'detalle'              => 'Beneficio Almuerzo SENA',
            'codigo_qr'            => $codigoQr,
            'expira_en'            => $expiraEn,
        ]);

        return back()
            ->with('success', __('ui.qr_generated'))
            ->with('nuevoQr', $codigoQr);
    }

    /**
     * Valida y redime un código QR presentado en el restaurante.
     * Aplica la regla estricta de vigencia (12:00 AM / medianoche).
     */
    public function validar(Request $request)
    {
        $request->validate([
            'codigo_qr' => 'required|string|max:100',
        ]);

        $canje = Canje::where('codigo_qr', $request->string('codigo_qr')->trim()->value())->first();

        if (!$canje) {
            return response()->json([
                'valido'  => false,
                'mensaje' => __('ui.qr_not_found'),
            ], 404);
        }

        if ($canje->estado !== 'pendiente') {
            return response()->json([
                'valido'  => false,
                'mensaje' => __('ui.qr_unavailable'),
                'estado'  => $canje->estado,
            ], 409);
        }

        // Regla estricta de vigencia: si ya pasaron las 12:00 AM (medianoche) del día
        if (!$canje->expira_en || Carbon::now()->greaterThanOrEqualTo($canje->expira_en) || !Carbon::parse($canje->fecha_canje)->isToday()) {
            if ($canje->estado !== 'expirado') {
                $canje->update(['estado' => 'expirado']);
            }

            return response()->json([
                'valido'   => false,
                'mensaje'  => __('ui.qr_expired'),
                'estado'   => 'expirado',
                'expirado' => true,
            ], 410);
        }

        $beneficiario = $canje->user;
        if (!$beneficiario || (!$beneficiario->isBeneficiario() && !$beneficiario->isAdmin())) {
            return response()->json([
                'valido' => false,
                'mensaje' => __('ui.benefit_not_active'),
                'estado' => 'inactivo',
            ], 403);
        }

        // A claim on the pending state prevents the same QR being redeemed twice concurrently.
        $actualizado = Canje::query()
            ->whereKey($canje->id)
            ->where('estado', 'pendiente')
            ->where('expira_en', '>', now())
            ->update(['estado' => 'exitoso']);

        if (!$actualizado) {
            return response()->json([
                'valido' => false,
                'mensaje' => __('ui.qr_already_used_or_expired'),
                'estado' => $canje->fresh()->estado,
            ], 409);
        }

        $canje->refresh();

        return response()->json([
            'valido'      => true,
            'mensaje'     => __('ui.qr_validated'),
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
            return back()->with('error', __('ui.benefit_not_active'));
        }

        // 2. Buscar el QR activo de hoy
        $canje = Canje::where('usuario_id', $user->id)
            ->whereDate('fecha_canje', today())
            ->where('estado', 'pendiente')
            ->where('expira_en', '>', now())
            ->latest('id')
            ->first();

        if (!$canje || empty($canje->codigo_qr)) {
            return back()->with('error', __('ui.no_active_qr_to_email'));
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
                "Credencial QR del canje #{$canje->id} enviada satisfactoriamente.",
                [
                    'canje_id'    => $canje->id,
                    'fecha_canje' => $canje->fecha_canje,
                ]
            );

            return back()->with('success', __('ui.qr_sent_to_email', ['email' => $user->email]));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('QR email delivery failed.', [
                'user_id' => $user->id,
                'canje_id' => $canje->id,
                'exception' => $e,
            ]);

            return back()->with('error', __('ui.qr_email_failed'));
        }
    }
}
