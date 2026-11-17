<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Pedidos - Cafetería SENA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: "class"
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #F8F9FA; color: #1E293B; }
    </style>
</head>
<body class="flex h-screen overflow-hidden">

    @include('partials.sidebar')

    <div class="ml-64 flex-1 flex flex-col h-screen overflow-hidden">
        
        <!-- Header -->
        <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-8 shrink-0 z-10 shadow-sm">
            <div class="flex items-center gap-3">
                <span class="px-3 py-1 rounded-full bg-[#283618] text-white text-xs font-bold uppercase tracking-wider">Administración</span>
                <h1 class="text-base font-bold text-[#283618]">Pedidos Registrados en la Cafetería</h1>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.pedidos') }}" class="px-3 py-1.5 rounded-xl text-xs font-bold {{ !$estado ? 'bg-[#283618] text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">Todos</a>
                <a href="{{ route('admin.pedidos', ['estado' => 'pendiente']) }}" class="px-3 py-1.5 rounded-xl text-xs font-bold {{ strtolower((string)$estado) === 'pendiente' ? 'bg-[#F97F2D] text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">Pendientes</a>
                <a href="{{ route('admin.pedidos', ['estado' => 'preparacion']) }}" class="px-3 py-1.5 rounded-xl text-xs font-bold {{ in_array(strtolower((string)$estado), ['preparacion', 'en_preparacion']) ? 'bg-amber-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">En preparación</a>
                <a href="{{ route('admin.pedidos', ['estado' => 'entregado']) }}" class="px-3 py-1.5 rounded-xl text-xs font-bold {{ strtolower((string)$estado) === 'entregado' ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">Entregados</a>
                <a href="{{ route('admin.pedidos', ['estado' => 'cancelado']) }}" class="px-3 py-1.5 rounded-xl text-xs font-bold {{ strtolower((string)$estado) === 'cancelado' ? 'bg-rose-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">Cancelados</a>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto p-6 lg:p-8">
            <div class="max-w-7xl mx-auto space-y-6 pb-16">

                @if(session('success'))
                    <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-300 text-emerald-900 text-xs font-bold flex items-center gap-2 shadow-sm">
                        <span class="material-symbols-outlined text-emerald-600">check_circle</span>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-gray-50 text-gray-500 uppercase border-b border-gray-200 text-[11px]">
                                    <th class="py-3.5 px-6 font-bold">No. Pedido</th>
                                    <th class="py-3.5 px-6 font-bold">Cliente / Correo</th>
                                    <th class="py-3.5 px-6 font-bold">Fecha / Hora</th>
                                    <th class="py-3.5 px-6 font-bold">Total</th>
                                    <th class="py-3.5 px-6 font-bold">Medio de Pago</th>
                                    <th class="py-3.5 px-6 font-bold">Estado Actual</th>
                                    <th class="py-3.5 px-6 font-bold text-right">Cambiar Estado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($pedidos as $p)
                                    @php
                                        $estadoNormalizado = strtolower(str_replace(' ', '_', (string)$p->estado));
                                        $badges = [
                                            'pendiente'      => 'bg-amber-100 text-amber-800',
                                            'en_preparacion' => 'bg-orange-100 text-orange-800',
                                            'listo'          => 'bg-blue-100 text-blue-800',
                                            'entregado'      => 'bg-emerald-100 text-emerald-800',
                                            'cancelado'      => 'bg-rose-100 text-rose-800',
                                        ];
                                    @endphp
                                    <tr class="hover:bg-gray-50/70 transition-colors">
                                        <td class="py-3.5 px-6 font-bold text-gray-900">
                                            #FP-{{ str_pad($p->id, 4, '0', STR_PAD_LEFT) }}
                                        </td>
                                        <td class="py-3.5 px-6">
                                            <p class="font-bold text-gray-900">{{ $p->usuario->name ?? ($p->user->name ?? 'Usuario') }}</p>
                                            <p class="text-gray-400 text-[11px]">{{ $p->usuario->email ?? ($p->user->email ?? '') }}</p>
                                        </td>
                                        <td class="py-3.5 px-6 text-gray-600">
                                            {{ $p->created_at ? \Carbon\Carbon::parse($p->created_at)->format('d/m/Y H:i') : ($p->fecha_pedido ? \Carbon\Carbon::parse($p->fecha_pedido)->format('d/m/Y H:i') : 'N/A') }}
                                        </td>
                                        <td class="py-3.5 px-6 font-extrabold text-[#F97F2D] text-sm">
                                            ${{ number_format($p->total, 0, ',', '.') }} COP
                                        </td>
                                        <td class="py-3.5 px-6 uppercase font-bold text-gray-700">
                                            {{ $p->metodo_pago ?? 'Nequi' }}
                                        </td>
                                        <td class="py-3.5 px-6">
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase {{ $badges[$estadoNormalizado] ?? 'bg-gray-100 text-gray-800' }}">
                                                {{ $p->estado }}
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-6 text-right">
                                            <form action="{{ route('admin.pedidos.estado', $p->id) }}" method="POST" class="inline-flex items-center gap-1.5">
                                                @csrf
                                                <select name="estado" onchange="this.form.submit()" class="text-xs px-2.5 py-1.5 rounded-xl border border-gray-300 bg-white font-medium focus:outline-none focus:border-[#F97F2D] shadow-sm">
                                                    <option value="pendiente" {{ $estadoNormalizado === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                                    <option value="preparacion" {{ in_array($estadoNormalizado, ['preparacion', 'en_preparacion']) ? 'selected' : '' }}>En preparación</option>
                                                    <option value="entregado" {{ $estadoNormalizado === 'entregado' ? 'selected' : '' }}>Entregado</option>
                                                    <option value="cancelado" {{ $estadoNormalizado === 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                                                </select>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="py-12 text-center text-gray-400">
                                            No hay pedidos registrados en este momento.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($pedidos->hasPages())
                        <div class="p-4 border-t border-gray-100">
                            {{ $pedidos->withQueryString()->links() }}
                        </div>
                    @endif
                </div>

            </div>
        </main>
    </div>

</body>
</html>
