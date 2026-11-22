<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pedido;

class PedidoController extends Controller
{
    // Obtener historial paginado con filtros
    public function index(Request $request)
    {
        $query = Pedido::where('usuario_id', auth()->id());

        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->has('fecha')) {
            $query->whereDate('creado_en', $request->fecha);
        }

        return response()->json($query->orderBy('creado_en', 'desc')->paginate(10));
    }

    // Detalle completo de un pedido específico
    public function show($id)
    {
        $pedido = Pedido::with(['detalles.platillo', 'user'])->findOrFail($id);
        return response()->json($pedido);
    }

    // Cambiar estado aplicando reglas de roles
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'estado' => 'required|in:pendiente,preparacion,en_preparacion,entregado,cancelado',
        ]);

        $estado = $validated['estado'] === 'en_preparacion'
            ? 'preparacion'
            : $validated['estado'];
        $pedido = Pedido::findOrFail($id);
        $user = auth()->user();

        // Usuario cancela su propia orden solo si está en 'pendiente'
        if ($user->id === $pedido->usuario_id && $estado === 'cancelado' && $pedido->estado === 'pendiente') {
            $pedido->update(['estado' => 'cancelado']);
            return response()->json($pedido);
        }

        // Admin u Operador avanzan estados
        if ($user->isAdmin() || $user->isRestaurante()) {
            if (in_array($estado, ['preparacion', 'entregado', 'cancelado'])) {
                $pedido->update(['estado' => $estado]);
                return response()->json($pedido);
            }
        }

        return response()->json(['error' => 'No tienes permisos para este cambio.'], 403);
    }
}
