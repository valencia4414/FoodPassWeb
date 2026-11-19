<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Credencial QR FoodPass SENA</title>
</head>
<body style="margin: 0; padding: 0; background-color: #F8F9FA; font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1E293B;">

    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F8F9FA; padding: 30px 15px;">
        <tr>
            <td align="center">
                <!-- Tarjeta Principal -->
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 560px; background-color: #FFFFFF; border-radius: 24px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.06); border: 1px solid #E2E8F0;">
                    
                    <!-- Header SENA FoodPass -->
                    <tr>
                        <td style="background-color: #283618; padding: 28px 32px; text-align: center;">
                            <div style="display: inline-block; padding: 4px 12px; background-color: rgba(255,255,255,0.15); border-radius: 20px; color: #FFFFFF; font-size: 11px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 8px;">
                                SENA CEAI • Apoyo Alimentario
                            </div>
                            <h1 style="margin: 0; color: #FFFFFF; font-size: 22px; font-weight: 800; letter-spacing: -0.5px;">
                                FoodPass Cafetería
                            </h1>
                            <p style="margin: 4px 0 0 0; color: #E2F4C8; font-size: 13px;">
                                Tu credencial digital de ración alimentaria
                            </p>
                        </td>
                    </tr>

                    <!-- Cuerpo Principal -->
                    <tr>
                        <td style="padding: 32px 32px 20px 32px;">
                            <p style="margin: 0 0 16px 0; font-size: 16px; font-weight: 700; color: #1E293B;">
                                ¡Hola, {{ $user->name }}!
                            </p>
                            <p style="margin: 0 0 20px 0; font-size: 13px; line-height: 1.6; color: #475569;">
                                Aquí tienes tu credencial QR oficial para reclamar tu ración alimentaria en la cafetería del SENA CEAI. También la encontrarás como <strong>archivo adjunto en formato PNG</strong> lista para descargar o guardar en tu galería.
                            </p>

                            <!-- Contenedor del Código QR -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #F8F9FA; border-radius: 20px; border: 2px dashed #CBD5E1; margin-bottom: 24px;">
                                <tr>
                                    <td align="center" style="padding: 24px;">
                                        <!-- Imagen QR centrada con zona de silencio -->
                                        <div style="background-color: #FFFFFF; padding: 14px; border-radius: 16px; display: inline-block; box-shadow: 0 4px 12px rgba(0,0,0,0.06);">
                                            <img src="data:image/png;base64,{{ $qrBase64 }}" alt="Código QR FoodPass" width="220" height="220" style="display: block; width: 220px; height: 220px; border: 0;" />
                                        </div>
                                        
                                        <div style="margin-top: 14px;">
                                            <span style="display: inline-block; background-color: #EBF7EE; color: #2F9B34; font-size: 12px; font-weight: 700; padding: 4px 14px; border-radius: 12px;">
                                                ● QR personal y de un solo uso
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <!-- Ficha de Datos del Beneficio -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #FFFFFF; border-radius: 16px; border: 1px solid #E2E8F0; margin-bottom: 20px; font-size: 12px;">
                                <tr>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #F1F5F9; color: #64748B; font-weight: 600; width: 40%;">
                                        Franja de Servicio:
                                    </td>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #F1F5F9; color: #1E293B; font-weight: 700;">
                                        {{ $franjaInfo['nombre'] ?? 'Apoyo Alimentario' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #F1F5F9; color: #64748B; font-weight: 600;">
                                        Horario de Atención:
                                    </td>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #F1F5F9; color: #1E293B; font-weight: 700;">
                                        {{ $franjaInfo['ventana'] ?? 'Horario Oficial' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #F1F5F9; color: #64748B; font-weight: 600;">
                                        Fecha de Vigencia:
                                    </td>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #F1F5F9; color: #1E293B; font-weight: 700;">
                                        {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #F1F5F9; color: #64748B; font-weight: 600;">
                                        Hora de Vencimiento:
                                    </td>
                                    <td style="padding: 12px 16px; border-bottom: 1px solid #F1F5F9; color: #C45A1B; font-weight: 800;">
                                        {{ $expiraAt }} (Máximo)
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 12px 16px; color: #64748B; font-weight: 600;">
                                        Documento Aprendiz:
                                    </td>
                                    <td style="padding: 12px 16px; color: #1E293B; font-weight: 700;">
                                        {{ $user->numero_documento ?: 'Validado en Padrón' }}
                                    </td>
                                </tr>
                            </table>

                            <!-- Recomendaciones de Uso -->
                            <div style="background-color: #FFF7ED; border-left: 4px solid #F97F2D; padding: 14px 16px; border-radius: 8px; margin-bottom: 24px;">
                                <p style="margin: 0 0 6px 0; font-size: 12px; font-weight: 700; color: #9A3412;">
                                    📌 Instrucciones importantes para reclamar:
                                </p>
                                <ul style="margin: 0; padding-left: 18px; font-size: 11px; line-height: 1.5; color: #7C2D12;">
                                    <li>Presenta este código en la <strong>Estación 1 del POS</strong> en la cafetería del CEAI.</li>
                                    <li>Asegúrate de tener el brillo de tu pantalla al máximo para facilitar la lectura del escáner.</li>
                                    <li>Esta credencial es <strong>personal e intransferible</strong>. Vence al finalizar la franja horaria.</li>
                                </ul>
                            </div>

                            <!-- Botón de apertura rápida en app web -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td align="center">
                                        <a href="{{ route('canje') }}" style="display: inline-block; background-color: #F97F2D; color: #FFFFFF; font-size: 13px; font-weight: 700; text-decoration: none; padding: 12px 28px; border-radius: 12px; box-shadow: 0 4px 10px rgba(249,127,45,0.3);">
                                            Abrir FoodPass Web
                                        </a>
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>

                    <!-- Footer del Correo -->
                    <tr>
                        <td style="background-color: #F8F9FA; padding: 20px 32px; text-align: center; border-top: 1px solid #E2E8F0;">
                            <p style="margin: 0; font-size: 11px; color: #94A3B8;">
                                FoodPass SENA • Centro de Electricidad y Automatización Industrial (CEAI)
                            </p>
                            <p style="margin: 4px 0 0 0; font-size: 10px; color: #CBD5E1;">
                                Este mensaje se generó automáticamente porque solicitaste el envío de tu credencial a {{ $user->email }}.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>
