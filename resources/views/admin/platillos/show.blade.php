<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle del Platillo - Cafetería SENA</title>
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
                <a href="{{ route('admin.platillos.index') }}" class="p-1.5 rounded-lg text-gray-500 hover:text-black hover:bg-gray-100 transition-colors">
                    <span class="material-symbols-outlined text-[20px]">arrow_back</span>
                </a>
                <h1 class="text-base font-bold text-[#283618]">{{ $platillo->nombre }}</h1>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.platillos.edit', $platillo->id) }}" class="px-4 py-2 rounded-xl bg-[#F97F2D] text-white text-xs font-bold hover:bg-[#e06d20]">
                    Editar Platillo
                </a>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto p-6 lg:p-8">
            <div class="max-w-2xl mx-auto bg-white rounded-3xl p-8 shadow-sm border border-gray-100 space-y-6">
                @php
                    $imgUrl = $platillo->imagen ? (str_starts_with($platillo->imagen, 'http') ? $platillo->imagen : asset('storage/' . $platillo->imagen)) : 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=400&auto=format&fit=crop&q=80';
                @endphp
                <img src="{{ $imgUrl }}" alt="{{ $platillo->nombre }}" class="w-full h-64 object-cover rounded-2xl shadow-sm border">

                <div class="space-y-2">
                    <span class="px-3 py-1 rounded-full text-xs font-extrabold uppercase bg-emerald-100 text-emerald-800">
                        {{ str_replace('_', ' ', $platillo->categoria) }}
                    </span>
                    <h2 class="text-2xl font-bold text-gray-900">{{ $platillo->nombre }}</h2>
                    <p class="text-sm text-gray-600 leading-relaxed">{{ $platillo->descripcion }}</p>
                </div>

                @if($platillo->ingredientes)
                    <div class="p-4 rounded-2xl bg-gray-50 border border-gray-100 text-xs">
                        <p class="font-bold text-gray-700 mb-1">Ingredientes:</p>
                        <p class="text-gray-500">{{ $platillo->ingredientes }}</p>
                    </div>
                @endif
            </div>
        </main>
    </div>

</body>
</html>
