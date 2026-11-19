<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CredencialQrMailable extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public object $canje;
    public array $franjaInfo;
    public string $qrPngBytes;
    public string $qrBase64;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, object $canje, array $franjaInfo, string $qrPngBytes)
    {
        $this->user = $user;
        $this->canje = $canje;
        $this->franjaInfo = $franjaInfo;
        $this->qrPngBytes = $qrPngBytes;
        $this->qrBase64 = base64_encode($qrPngBytes);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Tu Credencial QR de Apoyo Alimentario - FoodPass SENA ({$this->franjaInfo['nombre']})",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.credencial_qr',
            with: [
                'user'        => $this->user,
                'canje'       => $this->canje,
                'franjaInfo'  => $this->franjaInfo,
                'qrBase64'    => $this->qrBase64,
                'fecha'       => $this->canje->fecha_canje ?? now()->format('Y-m-d'),
                'expiraAt'    => $this->canje->expira_en?->format('h:i A') ?? $this->franjaInfo['hora_limite'],
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $codigo = $this->franjaInfo['codigo'] ?? 'franja';
        $fecha = date('Ymd');
        $nombreAdjunto = "Credencial_QR_{$codigo}_{$fecha}.png";

        return [
            Attachment::fromData(fn () => $this->qrPngBytes, $nombreAdjunto)
                ->withMime('image/png'),
        ];
    }
}
