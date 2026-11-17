<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión del Menú - Cafetería SENA</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
                <h1 class="text-base font-bold text-[#283618]">Catálogo y Menú de la Cafetería (Platillos)</h1>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('menu-digital') }}" target="_blank"
                   class="px-3.5 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                    <span class="material-symbols-outlined text-[18px]">visibility</span>
                    <span>Ver Menú Digital</span>
                </a>

                <a href="{{ route('admin.platillos.create') }}"
                   class="px-4 py-2 rounded-xl bg-[#F97F2D] hover:bg-[#e06d20] text-white text-xs font-bold flex items-center gap-1.5 shadow-sm transition-colors">
                    <span class="material-symbols-outlined text-[18px]">add_circle</span>
                    <span>Nuevo Platillo</span>
                </a>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto p-6 lg:p-8">
            <div class="max-w-7xl mx-auto space-y-6 pb-16">

                <!-- Mensajes Flash -->
                @if(session('success'))
                    <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-300 text-emerald-900 text-xs font-bold flex items-center gap-2.5 shadow-sm">
                        <span class="material-symbols-outlined text-emerald-600">check_circle</span>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="p-4 rounded-2xl bg-rose-50 border border-rose-300 text-rose-900 text-xs font-bold flex items-center gap-2.5 shadow-sm">
                        <span class="material-symbols-outlined text-rose-600">error</span>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                <!-- Tarjetas de Resumen -->
                @php
                    $totalCount = \App\Models\Platillo::count();
                    $dispCount = \App\Models\Platillo::where('disponible', 1)->count();
                    $agotCount = \App\Models\Platillo::where('disponible', 0)->count();
                @endphp
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-white rounded-3xl p-5 shadow-sm border border-gray-100 flex items-center justify-between">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wider text-gray-500">Total en Catálogo</p>
                            <p class="text-3xl font-extrabold text-[#283618] mt-1">{{ $totalCount }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">Platillos registrados</p>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-gray-100 flex items-center justify-center text-gray-600">
                            <span class="material-symbols-outlined text-2xl">restaurant</span>
                        </div>
                    </div>

                    <div class="bg-white rounded-3xl p-5 shadow-sm border border-gray-100 flex items-center justify-between">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wider text-emerald-700">Disponibles</p>
                            <p class="text-3xl font-extrabold text-emerald-600 mt-1">{{ $dispCount }}</p>
                            <p class="text-xs text-emerald-700 font-medium mt-0.5">Visibles en Menú Digital</p>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                            <span class="material-symbols-outlined text-2xl">check_circle</span>
                        </div>
                    </div>

                    <div class="bg-white rounded-3xl p-5 shadow-sm border border-gray-100 flex items-center justify-between">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wider text-amber-700">Agotados / Pausados</p>
                            <p class="text-3xl font-extrabold text-amber-600 mt-1">{{ $agotCount }}</p>
                            <p class="text-xs text-amber-700 font-medium mt-0.5">Desactivados temporalmente</p>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-600">
                            <span class="material-symbols-outlined text-2xl">pause_circle</span>
                        </div>
                    </div>
                </div>

                <!-- Barra de Filtros -->
                <div class="bg-white rounded-3xl p-4 shadow-sm border border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-3">
                    <form action="{{ route('admin.platillos.index') }}" method="GET" class="flex-1 flex flex-wrap items-center gap-2">
                        <select name="categoria" onchange="this.form.submit()" class="px-3.5 py-2 rounded-xl border border-gray-200 text-xs bg-white text-gray-700 font-medium">
                            <option value="">Todas las categorías</option>
                            <option value="plato_fuerte" {{ request('categoria') === 'plato_fuerte' ? 'selected' : '' }}>Platos Fuertes</option>
                            <option value="entrada" {{ request('categoria') === 'entrada' ? 'selected' : '' }}>Entradas</option>
                            <option value="postre" {{ request('categoria') === 'postre' ? 'selected' : '' }}>Postres</option>
                            <option value="bebida" {{ request('categoria') === 'bebida' ? 'selected' : '' }}>Bebidas</option>
                        </select>

                        <select name="disponible" onchange="this.form.submit()" class="px-3.5 py-2 rounded-xl border border-gray-200 text-xs bg-white text-gray-700 font-medium">
                            <option value="">Todos los estados</option>
                            <option value="1" {{ request('disponible') === '1' ? 'selected' : '' }}>Solo Disponibles</option>
                            <option value="0" {{ request('disponible') === '0' ? 'selected' : '' }}>Solo Agotados</option>
                        </select>

                        @if(request('categoria') || request('disponible') !== null)
                            <a href="{{ route('admin.platillos.index') }}" class="px-3 py-2 rounded-xl border border-gray-200 text-gray-500 hover:text-gray-800 text-xs font-bold">
                                Limpiar filtros
                            </a>
                        @endif
                    </form>
                </div>

                <!-- Tabla de Platillos -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-gray-50 text-gray-500 uppercase border-b border-gray-200 text-[11px]">
                                    <th class="py-3.5 px-6 font-bold">Platillo / Imagen</th>
                                    <th class="py-3.5 px-6 font-bold">Categoría</th>
                                    <th class="py-3.5 px-6 font-bold">Precio</th>
                                    <th class="py-3.5 px-6 font-bold text-center">Stock</th>
                                    <th class="py-3.5 px-6 font-bold text-center">Disponibilidad</th>
                                    <th class="py-3.5 px-6 font-bold text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($platillos as $p)
                                    @php
                                        $catBadges = [
                                            'plato_fuerte' => 'bg-emerald-100 text-emerald-800',
                                            'entrada'      => 'bg-amber-100 text-amber-800',
                                            'postre'       => 'bg-purple-100 text-purple-800',
                                            'bebida'       => 'bg-sky-100 text-sky-800',
                                        ];
                                        $catLabels = [
                                            'plato_fuerte' => 'Plato Fuerte',
                                            'entrada'      => 'Entrada',
                                            'postre'       => 'Postre',
                                            'bebida'       => 'Bebida',
                                        ];
                                        $imgUrl = $p->imagen ? (str_starts_with($p->imagen, 'http') ? $p->imagen : asset('storage/' . $p->imagen)) : 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=150&auto=format&fit=crop&q=80';
                                    @endphp
                                    <tr class="hover:bg-gray-50/70 transition-colors">
                                        <td class="py-3.5 px-6">
                                            <div class="flex items-center gap-3">
                                                <img src="{{ $imgUrl }}" alt="{{ $p->nombre }}" class="w-12 h-12 rounded-2xl object-cover border border-gray-100 shadow-sm shrink-0">
                                                <div class="min-w-0">
                                                    <p class="font-bold text-gray-900 text-sm truncate">{{ $p->nombre }}</p>
                                                    <p class="text-gray-400 text-[11px] truncate max-w-xs">{{ $p->descripcion }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-6">
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase {{ $catBadges[$p->categoria] ?? 'bg-gray-100 text-gray-700' }}">
                                                {{ $catLabels[$p->categoria] ?? $p->categoria }}
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-6 font-extrabold text-[#F97F2D] text-sm">
                                            ${{ number_format($p->precio, 0, ',', '.') }} COP
                                        </td>
                                        <td class="py-3.5 px-6 text-center font-bold text-gray-700">
                                            {{ $p->stock !== null ? $p->stock : 'Ilimitado' }}
                                        </td>
                                        <td class="py-3.5 px-6 text-center">
                                            <form action="{{ route('admin.platillos.disponibilidad', $p->id) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                @if($p->disponible)
                                                    <button type="submit" title="Hacer clic para marcar como agotado"
                                                            class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 hover:bg-emerald-200 transition-colors">
                                                        <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                                                        Disponible
                                                    </button>
                                                @else
                                                    <button type="submit" title="Hacer clic para marcar como disponible"
                                                            class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800 hover:bg-rose-200 transition-colors">
                                                        <span class="w-2 h-2 rounded-full bg-rose-600"></span>
                                                        Agotado
                                                    </button>
                                                @endif
                                            </form>
                                        </td>
                                        <td class="py-3.5 px-6 text-right space-x-2">
                                            <!-- Editar -->
                                            <a href="{{ route('admin.platillos.edit', $p->id) }}"
                                               class="p-2 text-gray-500 hover:text-[#283618] inline-block transition-colors" title="Editar platillo">
                                                <span class="material-symbols-outlined text-[18px]">edit</span>
                                            </a>

                                            <!-- Eliminar -->
                                            <form action="{{ route('admin.platillos.destroy', $p->id) }}" method="POST" class="inline-block"
                                                  onsubmit="return confirm('¿Seguro que deseas eliminar el platillo \'{{ addslashes($p->nombre) }}\'?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-2 text-gray-400 hover:text-rose-600 transition-colors" title="Eliminar platillo">
                                                    <span class="material-symbols-outlined text-[18px]">delete</span>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-12 text-center text-gray-400">
                                            No se encontraron platillos con los filtros seleccionados.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($platillos->hasPages())
                        <div class="p-4 border-t border-gray-100">
                            {{ $platillos->withQueryString()->links() }}
                        </div>
                    @endif
                </div>

            </div>
        </main>
    </div>

</body>
</html>
