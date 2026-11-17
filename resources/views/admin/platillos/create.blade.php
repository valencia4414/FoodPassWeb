<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Platillo - Cafetería SENA</title>
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
                <h1 class="text-base font-bold text-[#283618]">Agregar Nuevo Platillo al Menú</h1>
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
                    <form action="{{ route('admin.platillos.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Nombre del Platillo *</label>
                                <input type="text" name="nombre" value="{{ old('nombre') }}" required placeholder="Ej: Lentejas Caseras con Arroz"
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs focus:outline-none focus:border-[#F97F2D]">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Categoría *</label>
                                <select name="categoria" required class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs focus:outline-none focus:border-[#F97F2D] bg-white">
                                    <option value="plato_fuerte" {{ old('categoria') === 'plato_fuerte' ? 'selected' : '' }}>Plato Fuerte</option>
                                    <option value="entrada" {{ old('categoria') === 'entrada' ? 'selected' : '' }}>Entrada / Sopa</option>
                                    <option value="postre" {{ old('categoria') === 'postre' ? 'selected' : '' }}>Postre</option>
                                    <option value="bebida" {{ old('categoria') === 'bebida' ? 'selected' : '' }}>Bebida / Jugo</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Precio (COP) *</label>
                                <input type="number" name="precio" value="{{ old('precio', 10000) }}" min="0" step="100" required
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs focus:outline-none focus:border-[#F97F2D]">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Stock Disponible</label>
                                <input type="number" name="stock" value="{{ old('stock', 50) }}" min="0"
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs focus:outline-none focus:border-[#F97F2D]">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Restaurante / Cafetería *</label>
                                <select name="restaurante_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs focus:outline-none focus:border-[#F97F2D] bg-white">
                                    @foreach($restaurantes as $r)
                                        <option value="{{ $r->id }}" {{ old('restaurante_id') == $r->id ? 'selected' : '' }}>{{ $r->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Descripción del Platillo *</label>
                            <textarea name="descripcion" rows="3" required placeholder="Describe los componentes del platillo para los aprendices..."
                                      class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs focus:outline-none focus:border-[#F97F2D]">{{ old('descripcion') }}</textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Ingredientes (Opcional)</label>
                            <input type="text" name="ingredientes" value="{{ old('ingredientes') }}" placeholder="Ej: Lenteja, arroz, plátano maduro, ensalada fresca"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs focus:outline-none focus:border-[#F97F2D]">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Imagen del Platillo</label>
                            <input type="file" name="imagen" accept="image/*"
                                   class="w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#283618] file:text-white hover:file:bg-black">
                        </div>

                        <div class="flex items-center gap-2 pt-2">
                            <input type="checkbox" name="disponible" id="disponible" value="1" {{ old('disponible', true) ? 'checked' : '' }}
                                   class="w-4 h-4 rounded text-[#283618] focus:ring-[#F97F2D]">
                            <label for="disponible" class="text-xs font-bold text-gray-700">Habilitar inmediatamente en el Menú Digital</label>
                        </div>

                        <div class="pt-4 flex gap-3 border-t">
                            <a href="{{ route('admin.platillos.index') }}" class="px-5 py-2.5 border rounded-xl text-xs font-bold text-gray-600 hover:bg-gray-50">
                                Cancelar
                            </a>
                            <button type="submit" class="px-6 py-2.5 bg-[#F97F2D] hover:bg-[#e06d20] text-white rounded-xl text-xs font-bold shadow-md">
                                Guardar Platillo
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </main>
    </div>

</body>
</html>
