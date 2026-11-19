<?php

namespace App\Notifications;

use App\Models\Pedido;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class PedidoEstadoActualizado extends Notification
{
    use Queueable;

    public function __construct(private readonly Pedido $pedido) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $estado = match ($this->pedido->estado) {
            'pendiente' => Lang::get('ui.order_status_pending', [], $notifiable->idioma_preferido),
            'preparacion', 'en_preparacion' => Lang::get('ui.order_status_preparing', [], $notifiable->idioma_preferido),
            'entregado' => Lang::get('ui.order_status_delivered', [], $notifiable->idioma_preferido),
            'cancelado' => Lang::get('ui.order_status_cancelled', [], $notifiable->idioma_preferido),
            default => ucfirst($this->pedido->estado),
        };

        return [
            'message' => Lang::get('ui.order_status_notification', [
                'order' => str_pad((string) $this->pedido->id, 4, '0', STR_PAD_LEFT),
                'status' => $estado,
            ], $notifiable->idioma_preferido),
            'order_id' => $this->pedido->id,
            'status' => $this->pedido->estado,
        ];
    }
}
