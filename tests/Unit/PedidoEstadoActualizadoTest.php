<?php

namespace Tests\Unit;

use App\Models\Pedido;
use App\Models\User;
use App\Notifications\PedidoEstadoActualizado;
use Tests\TestCase;

class PedidoEstadoActualizadoTest extends TestCase
{
    public function test_order_status_notification_uses_the_users_preferred_language(): void
    {
        $pedido = new Pedido(['estado' => 'entregado']);
        $pedido->id = 42;
        $usuario = new User(['idioma_preferido' => 'en']);

        $notification = new PedidoEstadoActualizado($pedido);

        $this->assertSame(
            'Your order #FP-0042 is now delivered.',
            $notification->toDatabase($usuario)['message']
        );
    }
}
