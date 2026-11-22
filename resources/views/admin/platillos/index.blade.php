<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Platillos del Día - Panel Administrador</title>
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
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="flex h-screen overflow-hidden">

    @include('partials.sidebar')

    <div class="ml-64 flex-1 flex flex-col h-screen overflow-hidden">
        
        <!-- Header Superior de la Administración -->
        <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-8 shrink-0 z-10 shadow-sm">
            <div class="flex items-center gap-3">
                <span class="px-3 py-1 rounded-full bg-[#283618] text-white text-[11px] font-bold uppercase tracking-wider">
                    Administración
                </span>
                <h1 class="text-base font-bold text-[#283618]">Gestión Exclusiva: Platillos del Día</h1>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('admin.platillos.create') }}"
                   class="px-4 py-2 rounded-xl bg-[#F97F2D] hover:bg-[#e06d20] text-white text-xs font-bold flex items-center gap-1.5 shadow-sm transition-all active:scale-95">
                    <span class="material-symbols-outlined text-[18px]">add_circle</span>
                    <span>Nuevo Platillo del Día</span>
                </a>
            </div>
        </header>

        <!-- Contenido Principal -->
        <main class="flex-1 overflow-y-auto p-6 lg:p-8">
            <div class="max-w-7xl mx-auto space-y-6 pb-16">

                <!-- Mensajes Flash de Éxito o Error -->
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

                <!-- Tarjetas de Resumen del Menú del Día -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-white rounded-3xl p-5 shadow-sm border border-gray-100 flex items-center justify-between">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wider text-gray-500">Platillos del Día</p>
                            <p class="text-3xl font-extrabold text-[#283618] mt-1">{{ $totalCount }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">En el menú activo</p>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-gray-100 flex items-center justify-center text-gray-600">
                            <span class="material-symbols-outlined text-2xl">restaurant</span>
                        </div>
                    </div>

                    <div class="bg-white rounded-3xl p-5 shadow-sm border border-gray-100 flex items-center justify-between">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wider text-emerald-700">Disponibles</p>
                            <p class="text-3xl font-extrabold text-emerald-600 mt-1">{{ $dispCount }}</p>
                            <p class="text-xs text-emerald-700 font-medium mt-0.5">Listos para el comensal</p>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                            <span class="material-symbols-outlined text-2xl">check_circle</span>
                        </div>
                    </div>

                    <div class="bg-white rounded-3xl p-5 shadow-sm border border-gray-100 flex items-center justify-between">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wider text-rose-700">Agotados</p>
                            <p class="text-3xl font-extrabold text-rose-600 mt-1">{{ $agotCount }}</p>
                            <p class="text-xs text-rose-700 font-medium mt-0.5">Marcados fuera de servicio</p>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-rose-50 flex items-center justify-center text-rose-600">
                            <span class="material-symbols-outlined text-2xl">block</span>
                        </div>
                    </div>
                </div>

                <!-- Barra de Filtros y Búsqueda -->
                <div class="bg-white rounded-3xl p-4 shadow-sm border border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-3">
                    <form action="{{ route('admin.platillos.index') }}" method="GET" class="flex-1 flex flex-wrap items-center gap-3">
                        <div class="relative flex-1 min-w-[220px]">
                            <span class="material-symbols-outlined absolute left-3 top-2.5 text-gray-400 text-sm">search</span>
                            <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar platillo por nombre..."
                                   class="w-full pl-9 pr-3.5 py-2 rounded-xl border border-gray-200 text-xs text-gray-800 placeholder-gray-400 focus:outline-none focus:border-[#F97F2D]">
                        </div>

                        <select name="disponible" onchange="this.form.submit()" class="px-3.5 py-2 rounded-xl border border-gray-200 text-xs bg-white text-gray-700 font-medium cursor-pointer">
                            <option value="">Todos los estados</option>
                            <option value="1" {{ request('disponible') === '1' ? 'selected' : '' }}>Solo Disponibles</option>
                            <option value="0" {{ request('disponible') === '0' ? 'selected' : '' }}>Solo Agotados</option>
                        </select>

                        <button type="submit" class="px-3.5 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold">
                            Filtrar
                        </button>

                        @if(request('buscar') || request('disponible') !== null)
                            <a href="{{ route('admin.platillos.index') }}" class="px-3 py-2 rounded-xl border border-gray-200 text-gray-500 hover:text-gray-800 text-xs font-bold">
                                Limpiar filtros
                            </a>
                        @endif
                    </form>
                </div>

                <!-- Listado de Platillos del Día -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-gray-50 text-gray-500 uppercase border-b border-gray-200 text-[11px]">
                                    <th class="py-3.5 px-6 font-bold">Foto</th>
                                    <th class="py-3.5 px-6 font-bold">Nombre del Platillo</th>
                                    <th class="py-3.5 px-6 font-bold">Descripción</th>
                                    <th class="py-3.5 px-6 font-bold text-center">Disponibilidad (Agotado / Disponible)</th>
                                    <th class="py-3.5 px-6 font-bold text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($platillos as $p)
                                    @php
                                        $imgUrl = $p->imagen ? (str_starts_with($p->imagen, 'http') ? $p->imagen : asset('storage/' . $p->imagen)) : null;
                                    @endphp
                                    <tr class="hover:bg-gray-50/70 transition-colors">
                                        <!-- Foto del platillo -->
                                        <td class="py-3.5 px-6 shrink-0 w-20">
                                            @if($imgUrl)
                                                <img src="{{ $imgUrl }}" alt="{{ $p->nombre }}" class="w-14 h-14 rounded-2xl object-cover border border-gray-200 shadow-sm">
                                            @else
                                                <div class="w-14 h-14 rounded-2xl bg-amber-50 border border-amber-200/60 flex flex-col items-center justify-center text-amber-700 shadow-sm" title="Sin foto asignada">
                                                    <span class="material-symbols-outlined text-[20px]">restaurant</span>
                                                    <span class="text-[9px] font-bold uppercase mt-0.5">Sin foto</span>
                                                </div>
                                            @endif
                                        </td>

                                        <!-- Nombre del platillo -->
                                        <td class="py-3.5 px-6">
                                            <p class="font-bold text-gray-900 text-sm">{{ $p->nombre }}</p>
                                        </td>

                                        <!-- Descripción del platillo -->
                                        <td class="py-3.5 px-6">
                                            <p class="text-gray-600 text-xs line-clamp-2 max-w-md">{{ $p->descripcion }}</p>
                                        </td>

                                        <!-- Disponibilidad con Switch / Selector claro y persistente -->
                                        <td class="py-3.5 px-6 text-center">
                                            <form action="{{ route('admin.platillos.disponibilidad', $p->id) }}" method="POST" class="inline-flex items-center">
                                                @csrf
                                                @method('PATCH')
                                                
                                                @if($p->disponible)
                                                    <button type="submit" 
                                                            name="disponible" 
                                                            value="0"
                                                            title="Haz clic para marcar como AGOTADO"
                                                            class="group inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-bold bg-emerald-100 hover:bg-emerald-200 text-emerald-800 border border-emerald-300 shadow-sm transition-all active:scale-95 cursor-pointer">
                                                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-600 animate-pulse"></span>
                                                        <span>Disponible</span>
                                                        <span class="material-symbols-outlined text-[16px] text-emerald-600 group-hover:rotate-180 transition-transform">sync</span>
                                                    </button>
                                                @else
                                                    <button type="submit" 
                                                            name="disponible" 
                                                            value="1"
                                                            title="Haz clic para marcar como DISPONIBLE"
                                                            class="group inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-bold bg-rose-100 hover:bg-rose-200 text-rose-800 border border-rose-300 shadow-sm transition-all active:scale-95 cursor-pointer">
                                                        <span class="w-2.5 h-2.5 rounded-full bg-rose-600"></span>
                                                        <span>Agotado</span>
                                                        <span class="material-symbols-outlined text-[16px] text-rose-600 group-hover:rotate-180 transition-transform">sync</span>
                                                    </button>
                                                @endif
                                            </form>
                                        </td>

                                        <!-- Acciones permitidas -->
                                        <td class="py-3.5 px-6 text-right space-x-2">
                                            <!-- Editar -->
                                            <a href="{{ route('admin.platillos.edit', $p->id) }}"
                                               class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-gray-100 hover:bg-[#283618] hover:text-white text-gray-700 text-xs font-bold transition-all shadow-sm"
                                               title="Editar foto, nombre, descripción y disponibilidad">
                                                <span class="material-symbols-outlined text-[16px]">edit</span>
                                                <span>Editar</span>
                                            </a>

                                            <!-- Eliminar -->
                                            <form action="{{ route('admin.platillos.destroy', $p->id) }}" method="POST" class="inline-block"
                                                  onsubmit="return confirm('¿Seguro que deseas eliminar el platillo \'{{ addslashes($p->nombre) }}\' del menú del día?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1.5 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-colors" title="Eliminar platillo">
                                                    <span class="material-symbols-outlined text-[18px]">delete</span>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-12 text-center text-gray-400">
                                            <span class="material-symbols-outlined text-4xl mb-2 text-gray-300">restaurant_menu</span>
                                            <p class="text-sm font-semibold text-gray-500">No se encontraron platillos del día.</p>
                                            <p class="text-xs text-gray-400 mt-1">Usa el botón "Nuevo Platillo del Día" para agregar el primero.</p>
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
