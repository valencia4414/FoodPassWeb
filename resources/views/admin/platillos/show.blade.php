<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $platillo->nombre }} - Platillo del Día</title>
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
        
        <!-- Header -->
        <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-8 shrink-0 z-10 shadow-sm">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.platillos.index') }}" class="p-1.5 rounded-xl text-gray-500 hover:text-black hover:bg-gray-100 transition-colors" title="Volver a Platillos del Día">
                    <span class="material-symbols-outlined text-[20px]">arrow_back</span>
                </a>
                <h1 class="text-base font-bold text-[#283618]">Detalle del Platillo del Día</h1>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.platillos.edit', $platillo->id) }}" class="px-4 py-2 rounded-xl bg-[#F97F2D] text-white text-xs font-bold hover:bg-[#e06d20] shadow-sm flex items-center gap-1.5 transition-all active:scale-95">
                    <span class="material-symbols-outlined text-[18px]">edit</span>
                    <span>Editar Platillo</span>
                </a>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto p-6 lg:p-8">
            <div class="max-w-2xl mx-auto bg-white rounded-3xl p-8 shadow-sm border border-gray-100 space-y-6">
                @php
                    $imgUrl = $platillo->imagen ? (str_starts_with($platillo->imagen, 'http') ? $platillo->imagen : asset('storage/' . $platillo->imagen)) : null;
                @endphp

                <!-- Foto del platillo -->
                @if($imgUrl)
                    <img src="{{ $imgUrl }}" alt="{{ $platillo->nombre }}" class="w-full h-72 object-cover rounded-2xl shadow-sm border border-gray-200">
                @else
                    <div class="w-full h-48 rounded-2xl bg-gray-50 border-2 border-dashed border-gray-200 flex flex-col items-center justify-center text-gray-400">
                        <span class="material-symbols-outlined text-4xl">restaurant</span>
                        <p class="text-xs font-semibold mt-1">Este platillo aún no tiene foto asignada</p>
                    </div>
                @endif

                <div class="space-y-4">
                    <!-- Estado de disponibilidad -->
                    <div>
                        @if($platillo->disponible)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                                Disponible para ordenar
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-300">
                                <span class="w-2 h-2 rounded-full bg-rose-600"></span>
                                Agotado temporalmente
                            </span>
                        @endif
                    </div>

                    <!-- Nombre -->
                    <h2 class="text-2xl font-bold text-gray-900">{{ $platillo->nombre }}</h2>

                    <!-- Descripción -->
                    <div class="p-5 rounded-2xl bg-gray-50 border border-gray-100">
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Descripción</p>
                        <p class="text-xs text-gray-700 leading-relaxed">{{ $platillo->descripcion }}</p>
                    </div>
                </div>

                <div class="pt-4 flex items-center justify-between border-t border-gray-100">
                    <a href="{{ route('admin.platillos.index') }}" class="px-4 py-2 text-xs font-bold text-gray-600 hover:text-gray-900 flex items-center gap-1">
                        <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                        <span>Regresar a la lista</span>
                    </a>

                    <a href="{{ route('admin.platillos.edit', $platillo->id) }}" class="px-5 py-2.5 rounded-xl bg-[#283618] hover:bg-black text-white text-xs font-bold shadow-sm flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[18px]">edit</span>
                        <span>Editar</span>
                    </a>
                </div>
            </div>
        </main>
    </div>

</body>
</html>
