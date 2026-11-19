<?php

namespace Tests\Unit;

use App\Mail\CredencialQrMailable;
use App\Models\Canje;
use App\Models\User;
use App\Services\SrsFoodPassService;
use Tests\TestCase;
use RuntimeException;

class QrCredentialTest extends TestCase
{
    public function test_email_qr_generator_returns_a_png_image(): void
    {
        $png = SrsFoodPassService::generarQrPngBytes('FP-'.str_repeat('a', 64));

        $this->assertStringStartsWith("\x89PNG\r\n\x1a\n", $png);
    }

    public function test_email_contains_the_qr_inline_and_as_a_downloadable_attachment(): void
    {
        $png = SrsFoodPassService::generarQrPngBytes('FP-'.str_repeat('b', 64));
        $mail = new CredencialQrMailable(
            new User(['nombre' => 'Aprendiz', 'email' => 'aprendiz@example.test']),
            new Canje(['fecha_canje' => today()]),
            ['nombre' => 'Apoyo Alimentario', 'codigo' => 'ALMUERZO', 'hora_limite' => '11:59 PM'],
            $png
        );

        $this->assertStringContainsString('data:image/png;base64,', $mail->render());
        $this->assertCount(1, $mail->attachments());
    }

    public function test_signed_qr_credentials_reject_tampered_data(): void
    {
        config(['app.key' => 'test-signing-key']);

        $credential = SrsFoodPassService::firmarCredencialSocial(42, '123456789', 'almuerzo', today()->toDateString());
        $tampered = str_replace('123456789', '987654321', $credential['token']);

        $this->assertTrue(SrsFoodPassService::verificarCredencialSocial($credential['token'])['valido']);
        $this->assertFalse(SrsFoodPassService::verificarCredencialSocial($tampered)['valido']);
    }

    public function test_qr_signing_requires_the_application_key(): void
    {
        config(['app.key' => null]);

        $this->expectException(RuntimeException::class);
        SrsFoodPassService::firmarCredencialSocial(42, '123456789', 'almuerzo', today()->toDateString());
    }
}
