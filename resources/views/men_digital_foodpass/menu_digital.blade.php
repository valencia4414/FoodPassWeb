<!DOCTYPE html>
<html class="light" lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Menú Digital - FoodPass</title>

    <!-- Tipografía Oficial: Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />

    <!-- Alpine.js para búsqueda y filtros del menú -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Tailwind CSS con Paleta Oficial FoodPass -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        'fp-orange': '#F97F2D',      /* Naranja Otoñal */
                        'fp-green': '#2F9B34',       /* Verde Jungla Medio */
                        'fp-bronze': '#C45A1B',      /* Bronce Especiado */
                        'fp-darkgreen': '#283618',   /* Verde Selva Negra */
                        'fp-sidebar': '#283618',
                        'fp-bg': '#F9FBF7',
                        'fp-card-light': '#EBF7E7'
                    },
                    fontFamily: {
                        'poppins': ['Poppins', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="bg-fp-bg text-fp-darkgreen antialiased h-screen overflow-hidden flex" x-data="menuApp('{{ $categoriaActiva }}')">
    @include('partials.sidebar')

    <div class="ml-0 lg:ml-64 flex-1 flex flex-col h-screen w-full relative">

        <!-- Buscador del menú -->
        <header
            class="min-h-16 bg-white/90 backdrop-blur-md border-b border-gray-200/60 flex items-center justify-between gap-3 px-4 sm:px-8 pl-16 lg:pl-8 sticky top-0 z-10 w-full">

            <!-- Tarea 11: Buscador de Platillos en Tiempo Real -->
            <div class="relative w-full sm:w-96">
                <span
                    class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-[20px]">search</span>
                <input type="text" x-model="busqueda" placeholder="Buscar platillos o ingredientes..."
                    class="w-full bg-fp-bg border border-gray-200 text-sm rounded-full py-2 pl-10 pr-4 focus:outline-none focus:ring-2 focus:ring-fp-orange/50 transition-shadow">
            </div>

        </header>

        <main class="flex-1 overflow-y-auto p-4 sm:p-8 pb-20">

            <section>
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-fp-darkgreen mb-1">Menú Digital</h2>
                        <p class="text-gray-500 text-xs">Consulta los platillos y sus descripciones.</p>
                    </div>

                    <div
                        class="flex items-center gap-2 p-1.5 bg-white rounded-xl shadow-sm border border-gray-200 overflow-x-auto">
                        <button @click="categoriaFiltro = 'todos'"
                            :class="categoriaFiltro === 'todos' ? 'bg-fp-green text-white font-semibold' : 'text-gray-600 hover:text-fp-darkgreen'"
                            class="px-4 py-1.5 text-xs rounded-lg transition-colors">Todos</button>
                        @foreach($categorias as $categoria)
                            <button @click="categoriaFiltro = '{{ $categoria->id }}'"
                                :class="categoriaFiltro === '{{ $categoria->id }}' ? 'bg-fp-green text-white font-semibold' : 'text-gray-600 hover:text-fp-darkgreen'"
                                class="px-4 py-1.5 text-xs rounded-lg transition-colors">{{ $categoria->nombre }}</button>
                        @endforeach
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

                    @forelse($platillos as $platillo)
                        @php
                            // Usa las 12 imágenes descargadas en public/img antes de repetirlas.
                            $imgIndex = (($loop->iteration - 1) % 12) + 1;
                            $imgPath = asset("img/imagen ($imgIndex).jpg");
                        @endphp
                        <div x-show="cumpleFiltro('{{ $platillo->categoria_id }}', '{{ strtolower($platillo->nombre) }}', '{{ strtolower($platillo->descripcion) }}')"
                            class="bg-white rounded-3xl p-4 shadow-sm border border-gray-100 hover:shadow-md transition-all flex flex-col justify-between relative group">

                            <div class="relative w-full h-40 rounded-2xl overflow-hidden mb-3">
                                <img src="{{ $imgPath }}"
                                    alt="{{ $platillo->nombre }}" loading="lazy"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 {{ !$platillo->disponible ? 'grayscale opacity-60' : '' }}">

                                <!-- Badge de Categoría -->
                                <span
                                    class="absolute top-3 left-3 px-2 py-0.5 rounded-md text-[10px] font-bold uppercase bg-fp-darkgreen text-white">
                                    {{ str_replace('_', ' ', $platillo->categoria) }}
                                </span>

                                <!-- Tarea 9: Badge Visual "AGOTADO" si disponible = false -->
                                @if(!$platillo->disponible)
                                    <div class="absolute inset-0 bg-black/50 flex items-center justify-center">
                                        <span
                                            class="px-3 py-1 bg-red-600 text-white text-xs font-bold uppercase tracking-wider rounded-lg shadow-md">AGOTADO</span>
                                    </div>
                                @endif
                            </div>

                            <div class="flex flex-1 flex-col">
                                <h3 class="font-bold text-fp-darkgreen mb-1 line-clamp-1">
                                    {{ $platillo->nombre }}
                                </h3>
                                <p class="text-xs text-gray-500 mb-4">{{ $platillo->descripcion }}</p>
                                @if(!$platillo->disponible)
                                    <span class="mt-auto self-start px-2 py-1 bg-red-100 text-red-700 text-[10px] font-bold uppercase tracking-wider rounded-lg">Agotado</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full py-12 text-center text-gray-400">No hay platillos registrados en el
                            sistema.</div>
                    @endforelse

                </div>
            </section>
        </main>
    </div>

    <!-- Filtros del menú -->
    <script>
        function menuApp(categoriaInicial) {
            return {
                busqueda: '',
                categoriaFiltro: categoriaInicial,
                cumpleFiltro(cat, nombre, desc) {
                    const coincideCat = this.categoriaFiltro === 'todos' || String(cat) === this.categoriaFiltro;
                    const coincideTexto = nombre.includes(this.busqueda.toLowerCase()) || desc.includes(this.busqueda.toLowerCase());
                    return coincideCat && coincideTexto;
                }
            }
        }
    </script>
</body>

</html>
