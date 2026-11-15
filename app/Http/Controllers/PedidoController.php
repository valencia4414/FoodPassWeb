<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pedido;
use App\Models\Platillo;
use App\Models\DetallePedido;
use App\Models\Restaurante;
use Illuminate\Support\Facades\DB;

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

    // Crear pedido validando disponibilidad y calculando total (Sin pagos)
    public function store(Request $request)
    {
        $request->validate([
            'restaurante_id'      => 'required|exists:restaurantes,id',
            'items'               => 'required|array|min:1',
            'items.*.platillo_id' => 'required|exists:platillos,id',
            'items.*.cantidad'    => 'required|integer|min:1',
        ]);

        return DB::transaction(function () use ($request) {
            $total = 0;
            $itemsAInsertar = [];

            foreach ($request->items as $item) {
                $platillo = Platillo::findOrFail($item['platillo_id']);

                if (!$platillo->disponible) {
                    return response()->json(['error' => "El platillo {$platillo->nombre} no está disponible."], 400);
                }

                $total += $platillo->precio * $item['cantidad'];

                $itemsAInsertar[] = [
                    'platillo_id'     => $platillo->id,
                    'cantidad'        => $item['cantidad'],
                    'precio_unitario' => $platillo->precio,
                ];
            }

            $pedido = Pedido::create([
                'usuario_id'     => auth()->id(),
                'restaurante_id' => $request->restaurante_id,
                'total'          => $total,
                'estado'         => 'pendiente',
                'fecha_pedido'   => now(),
            ]);

            foreach ($itemsAInsertar as $detalle) {
                $pedido->detalles()->create($detalle);
            }

            request()->session()->flash('success', '¡Tu pedido fue realizado con éxito!');

            return response()->json($pedido->load('detalles'), 201);
        });
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
        $request->validate([
            'estado' => 'required|in:pendiente,preparacion,entregado,cancelado',
        ]);

        $pedido = Pedido::findOrFail($id);
        $user = auth()->user();

        // Usuario cancela su propia orden solo si está en 'pendiente'
        if ($user->id === $pedido->usuario_id && $request->estado === 'cancelado' && $pedido->estado === 'pendiente') {
            $pedido->update(['estado' => 'cancelado']);
            return response()->json($pedido);
        }

        // Admin u Operador avanzan estados
        if ($user->isAdmin() || $user->isRestaurante()) {
            if (in_array($request->estado, ['preparacion', 'entregado', 'cancelado'])) {
                $pedido->update(['estado' => $request->estado]);
                return response()->json($pedido);
            }
        }

        return response()->json(['error' => 'No tienes permisos para este cambio.'], 403);
    }

    // Guardar pedidos desde el menú digital (web)
    public function storeWeb(Request $request)
    {
        $request->validate([
            'items'               => 'required|array|min:1',
            'items.*.platillo_id' => 'required|integer',
            'items.*.cantidad'    => 'required|integer|min:1',
            'items.*.precio'      => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($request) {
            $total = 0;

            foreach ($request->items as $item) {
                $total += $item['precio'] * $item['cantidad'];
            }

            $restauranteId = Restaurante::value('id') ?? 1;

            $pedido = Pedido::create([
                'usuario_id'     => auth()->id(),
                'restaurante_id' => $restauranteId,
                'total'          => $total,
                'estado'         => 'pendiente',
                'fecha_pedido'   => now(),
            ]);

            foreach ($request->items as $item) {
                DetallePedido::create([
                    'pedido_id'       => $pedido->id,
                    'platillo_id'     => $item['platillo_id'],
                    'cantidad'        => $item['cantidad'],
                    'precio_unitario' => $item['precio'],
                ]);
            }

            return response()->json(['success' => true, 'pedido_id' => $pedido->id]);
        });
    }
}
