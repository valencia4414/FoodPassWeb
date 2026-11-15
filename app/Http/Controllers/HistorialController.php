<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Canje;
use App\Models\Pedido;

class HistorialController extends Controller
{
    public function index(Request $request)
    {
        $userId = auth()->id();

        // 1. Canjes QR
        $canjes = DB::table('canjes')
            ->select(
                'id',
                'creado_en as created_at',
                'estado',
                DB::raw("COALESCE(detalle, 'Canje QR Beneficio SENA') as detalle"),
                DB::raw("0.00 as total"),
                DB::raw("'canje' as tipo")
            )
            ->where('usuario_id', $userId);

        // 2. Pedidos Menú Digital
        $pedidosQuery = DB::table('pedidos')
            ->select(
                'id',
                'creado_en as created_at',
                'estado',
                DB::raw("'Pedido Menú Digital' as detalle"),
                'total',
                DB::raw("'pedido' as tipo")
            )
            ->where('usuario_id', $userId);

        // Unión de ambas consultas
        $unionQuery = $canjes->union($pedidosQuery);
        $query = DB::query()->fromSub($unionQuery, 'historial');

        if ($request->filter == 'ultimos_30') {
            $query->where('created_at', '>=', now()->subDays(30));
        } elseif ($request->filter == 'pendientes') {
            $query->where('estado', 'pendiente');
        }

        $pedidos = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('historial_foodpass.index', compact('pedidos'));
    }

    public function show(Request $request, $id)
    {
        $tipo = $request->query('tipo', 'canje');

        if ($tipo == 'pedido') {
            $pedido = Pedido::where('usuario_id', auth()->id())->findOrFail($id);
            $pedido->tipo = 'pedido';
            $pedido->detalle = 'Pedido Menú Digital';
        } else {
            $pedido = Canje::where('usuario_id', auth()->id())->findOrFail($id);
            $pedido->tipo = 'canje';
            $pedido->total = $pedido->monto ?? 0;
            $pedido->detalle = $pedido->detalle ?? 'Canje QR Beneficio SENA';
        }

        return view('historial_foodpass.show', compact('pedido'));
    }

    // Historial de canjes QR API
    public function canjes(Request $request)
    {
        $canjes = Canje::with('restaurante:id,nombre')
            ->where('usuario_id', auth()->id())
            ->orderBy('id', 'desc')
            ->paginate(10);

        return response()->json($canjes);
    }
}
