<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Platillo - Cafetería SENA</title>
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
                <h1 class="text-base font-bold text-[#283618]">Editar platillo del menú</h1>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto p-6 lg:p-8">
            <div class="max-w-3xl mx-auto space-y-6 pb-16">

                @if(isset($errors) && $errors->any())
                    <div class="p-4 rounded-2xl bg-rose-50 border border-rose-300 text-rose-900 text-xs font-semibold space-y-1 shadow-sm">
                        <p class="font-bold">Por favor corrige los siguientes errores:</p>
                        <ul class="list-disc pl-5">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100">
                    <form action="{{ route('admin.platillos.update', $platillo->id) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                        @csrf
                        @method('PUT')

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Nombre del Platillo *</label>
                            <input type="text" name="nombre" value="{{ old('nombre', $platillo->nombre) }}" required
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs focus:outline-none focus:border-[#F97F2D]">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Descripción del Platillo *</label>
                            <textarea name="descripcion" rows="3" required
                                      class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs focus:outline-none focus:border-[#F97F2D]">{{ old('descripcion', $platillo->descripcion) }}</textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Cambiar Imagen (Dejar vacío para conservar la actual)</label>
                            @if($platillo->imagen)
                                <div class="mb-2 flex items-center gap-2">
                                    <img src="{{ str_starts_with($platillo->imagen, 'http') ? $platillo->imagen : asset('storage/' . $platillo->imagen) }}"
                                         alt="Imagen actual" class="w-12 h-12 rounded-xl object-cover border">
                                    <span class="text-[11px] text-gray-500">Imagen actual en uso</span>
                                </div>
                            @endif
                            <input type="file" name="imagen" accept="image/*"
                                   class="w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#283618] file:text-white hover:file:bg-black">
                        </div>

                        <div>
                            <label for="disponible" class="block text-xs font-bold text-gray-700 mb-1">Disponibilidad *</label>
                            <select name="disponible" id="disponible" required class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs focus:outline-none focus:border-[#F97F2D] bg-white">
                                <option value="1" {{ (string) old('disponible', (int) $platillo->disponible) === '1' ? 'selected' : '' }}>Disponible</option>
                                <option value="0" {{ (string) old('disponible', (int) $platillo->disponible) === '0' ? 'selected' : '' }}>Agotado</option>
                            </select>
                        </div>

                        <div class="pt-4 flex gap-3 border-t">
                            <a href="{{ route('admin.platillos.index') }}" class="px-5 py-2.5 border rounded-xl text-xs font-bold text-gray-600 hover:bg-gray-50">
                                Cancelar
                            </a>
                            <button type="submit" class="px-6 py-2.5 bg-[#F97F2D] hover:bg-[#e06d20] text-white rounded-xl text-xs font-bold shadow-md">
                                Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </main>
    </div>

</body>
</html>
