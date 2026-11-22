<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Platillo del Día - Panel Administrador</title>
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
                <h1 class="text-base font-bold text-[#283618]">Editar Platillo del Día: {{ $platillo->nombre }}</h1>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto p-6 lg:p-8">
            <div class="max-w-2xl mx-auto space-y-6 pb-16">

                <!-- Alertas de Validación -->
                @if(isset($errors) && $errors->any())
                    <div class="p-4 rounded-2xl bg-rose-50 border border-rose-300 text-rose-900 text-xs font-semibold space-y-1 shadow-sm">
                        <p class="font-bold flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-rose-600 text-sm">error</span>
                            Por favor corrige los siguientes campos:
                        </p>
                        <ul class="list-disc pl-5 space-y-0.5">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100">
                    <form action="{{ route('admin.platillos.update', $platillo->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6" id="form-edit-platillo">
                        @csrf
                        @method('PUT')

                        <!-- 1. FOTO DEL PLATILLO CON VISTA PREVIA Y VALIDACIÓN -->
                        <div class="space-y-3">
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Foto del Platillo
                            </label>

                            @php
                                $imgActual = $platillo->imagen ? (str_starts_with($platillo->imagen, 'http') ? $platillo->imagen : asset('storage/' . $platillo->imagen)) : null;
                            @endphp

                            <div class="flex flex-col sm:flex-row items-center gap-5 p-4 rounded-2xl bg-gray-50 border border-gray-200/80">
                                <!-- Vista previa actual / nueva -->
                                <div class="relative w-32 h-32 rounded-2xl overflow-hidden bg-gray-200 border border-gray-300 shadow-inner flex items-center justify-center shrink-0">
                                    <img id="preview-img" 
                                         src="{{ $imgActual ?? '' }}" 
                                         alt="Vista previa del platillo" 
                                         class="w-full h-full object-cover {{ $imgActual ? '' : 'hidden' }}">
                                    
                                    <div id="preview-placeholder" class="{{ $imgActual ? 'hidden' : 'flex' }} flex-col items-center justify-center text-gray-400 text-center p-2">
                                        <span class="material-symbols-outlined text-3xl">image</span>
                                        <span class="text-[10px] font-bold mt-1">Sin Foto</span>
                                    </div>

                                    <span id="badge-nueva-foto" class="hidden absolute top-1.5 right-1.5 px-2 py-0.5 bg-[#F97F2D] text-white text-[9px] font-extrabold rounded-full uppercase shadow">
                                        Nueva
                                    </span>
                                </div>

                                <!-- Controles de carga -->
                                <div class="flex-1 space-y-2 text-center sm:text-left">
                                    <p class="text-xs font-semibold text-gray-800">
                                        {{ $imgActual ? 'Cambiar o actualizar la imagen del platillo' : 'Subir foto del platillo' }}
                                    </p>
                                    <p class="text-[11px] text-gray-500">
                                        Formatos válidos: JPG, PNG, WEBP. Tamaño máximo permitido: 2MB.
                                    </p>

                                    <div class="pt-1">
                                        <input type="file" 
                                               name="imagen" 
                                               id="imagen" 
                                               accept="image/jpeg,image/png,image/webp,image/jpg"
                                               class="w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#283618] file:text-white hover:file:bg-black file:cursor-pointer cursor-pointer">
                                    </div>
                                    <p id="error-peso-imagen" class="text-xs text-rose-600 font-bold hidden flex items-center gap-1">
                                        <span class="material-symbols-outlined text-sm">warning</span>
                                        La imagen seleccionada supera el límite de 2MB. Por favor elige una imagen más ligera.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- 2. NOMBRE DEL PLATILLO -->
                        <div>
                            <label for="nombre" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                Nombre del Platillo *
                            </label>
                            <input type="text" 
                                   id="nombre" 
                                   name="nombre" 
                                   value="{{ old('nombre', $platillo->nombre) }}" 
                                   required 
                                   placeholder="Ej: Bandeja Paisa Especial, Sopa de Lentejas Casera"
                                   class="w-full px-4 py-3 rounded-xl border border-gray-300 text-xs font-medium text-gray-800 placeholder-gray-400 focus:outline-none focus:border-[#F97F2D] focus:ring-1 focus:ring-[#F97F2D]">
                        </div>

                        <!-- 3. DESCRIPCIÓN DEL PLATILLO -->
                        <div>
                            <label for="descripcion" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                                Descripción del Platillo *
                            </label>
                            <textarea id="descripcion" 
                                      name="descripcion" 
                                      rows="4" 
                                      required 
                                      placeholder="Describe los componentes, ingredientes o preparación del platillo del día..."
                                      class="w-full px-4 py-3 rounded-xl border border-gray-300 text-xs font-medium text-gray-800 placeholder-gray-400 focus:outline-none focus:border-[#F97F2D] focus:ring-1 focus:ring-[#F97F2D]">{{ old('descripcion', $platillo->descripcion) }}</textarea>
                        </div>

                        <!-- 4. DISPONIBILIDAD (DISPONIBLE / AGOTADO) -->
                        <div class="space-y-2 pt-1">
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
                                Estado de Disponibilidad *
                            </label>
                            
                            @php
                                $dispActual = old('disponible', $platillo->disponible ? '1' : '0');
                            @endphp

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" x-data="{ estado: '{{ $dispActual }}' }">
                                <!-- Opción Disponible -->
                                <label class="relative flex items-center gap-3 p-4 rounded-2xl border-2 cursor-pointer transition-all"
                                       :class="estado === '1' ? 'border-emerald-500 bg-emerald-50/70 shadow-sm' : 'border-gray-200 bg-white hover:bg-gray-50'">
                                    <input type="radio" 
                                           name="disponible" 
                                           value="1" 
                                           x-model="estado" 
                                           class="sr-only">
                                    <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center text-emerald-700 shrink-0">
                                        <span class="material-symbols-outlined text-xl">check_circle</span>
                                    </div>
                                    <div>
                                        <p class="font-bold text-xs text-emerald-950">Disponible</p>
                                        <p class="text-[11px] text-emerald-700 mt-0.5">Listo para ordenar en el Menú Digital</p>
                                    </div>
                                    <div class="ml-auto w-4 h-4 rounded-full border-2 flex items-center justify-center"
                                         :class="estado === '1' ? 'border-emerald-600 bg-emerald-600' : 'border-gray-300'">
                                        <span class="w-1.5 h-1.5 rounded-full bg-white" x-show="estado === '1'"></span>
                                    </div>
                                </label>

                                <!-- Opción Agotado -->
                                <label class="relative flex items-center gap-3 p-4 rounded-2xl border-2 cursor-pointer transition-all"
                                       :class="estado === '0' ? 'border-rose-500 bg-rose-50/70 shadow-sm' : 'border-gray-200 bg-white hover:bg-gray-50'">
                                    <input type="radio" 
                                           name="disponible" 
                                           value="0" 
                                           x-model="estado" 
                                           class="sr-only">
                                    <div class="w-10 h-10 rounded-xl bg-rose-100 flex items-center justify-center text-rose-700 shrink-0">
                                        <span class="material-symbols-outlined text-xl">block</span>
                                    </div>
                                    <div>
                                        <p class="font-bold text-xs text-rose-950">Agotado</p>
                                        <p class="text-[11px] text-rose-700 mt-0.5">Marcado fuera de servicio temporalmente</p>
                                    </div>
                                    <div class="ml-auto w-4 h-4 rounded-full border-2 flex items-center justify-center"
                                         :class="estado === '0' ? 'border-rose-600 bg-rose-600' : 'border-gray-300'">
                                        <span class="w-1.5 h-1.5 rounded-full bg-white" x-show="estado === '0'"></span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="pt-5 flex items-center justify-end gap-3 border-t border-gray-100">
                            <a href="{{ route('admin.platillos.index') }}" 
                               class="px-5 py-2.5 border border-gray-300 rounded-xl text-xs font-bold text-gray-700 hover:bg-gray-50 transition-colors">
                                Cancelar
                            </a>
                            <button type="submit" 
                                    id="btn-submit"
                                    class="px-6 py-2.5 bg-[#F97F2D] hover:bg-[#e06d20] text-white rounded-xl text-xs font-bold shadow-md shadow-[#F97F2D]/20 transition-all flex items-center gap-1.5 active:scale-95">
                                <span class="material-symbols-outlined text-[18px]">save</span>
                                <span>Guardar Cambios</span>
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </main>
    </div>

    <!-- Script de Vista Previa y Validación de Tamaño de Foto -->
    <script>
        const inputImg = document.getElementById('imagen');
        const previewImg = document.getElementById('preview-img');
        const placeholder = document.getElementById('preview-placeholder');
        const badgeNueva = document.getElementById('badge-nueva-foto');
        const errorPeso = document.getElementById('error-peso-imagen');
        const btnSubmit = document.getElementById('btn-submit');

        inputImg.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;

            // Validar peso máx 2MB (2 * 1024 * 1024 bytes)
            if (file.size > 2 * 1024 * 1024) {
                errorPeso.classList.remove('hidden');
                btnSubmit.disabled = true;
                btnSubmit.classList.add('opacity-50', 'cursor-not-allowed');
                return;
            } else {
                errorPeso.classList.add('hidden');
                btnSubmit.disabled = false;
                btnSubmit.classList.remove('opacity-50', 'cursor-not-allowed');
            }

            // Vista previa con FileReader
            const reader = new FileReader();
            reader.onload = function(evt) {
                previewImg.src = evt.target.result;
                previewImg.classList.remove('hidden');
                placeholder.classList.add('hidden');
                badgeNueva.classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        });
    </script>

</body>
</html>
