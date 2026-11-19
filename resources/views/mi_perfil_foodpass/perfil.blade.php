<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodPass - Mi Perfil</title>
    <script>
        (function() {
            try {
                const theme = localStorage.getItem('theme');
                if (theme === 'dark' || (!theme && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            } catch (e) {}
        })();
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        fp: {
                            dark: '#273517',
                            light: '#f0ffd8',
                            orange: '#F97F2D',
                            orangeHover: '#e06d20'
                        }
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <style>
        body { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="antialiased bg-[#f0ffd8] dark:bg-[#121f05] text-[#121f05] dark:text-[#f0ffd8] transition-colors duration-300 min-h-screen" x-data="{ modalNotifsCuenta: false }">
    @include('partials.sidebar')

    <!-- Main Content -->
    <main class="ml-64 min-h-screen p-4 sm:p-8 transition-colors duration-300">
        <div class="max-w-5xl mx-auto">

            <!-- ── HEADER PERFIL (RF06 y RF15) ── -->
            <div class="flex flex-col sm:flex-row items-start gap-5 sm:gap-7 mb-10">
                <div class="relative shrink-0">
                    <div class="w-28 h-28 rounded-2xl overflow-hidden shadow-md border-2 border-white dark:border-white/20 bg-[#273517]">
                        @if(auth()->user()->foto_perfil)
                            <img id="avatar-preview" src="{{ asset('storage/' . auth()->user()->foto_perfil) }}" class="w-full h-full object-cover" alt="Avatar"/>
                        @else
                            <img id="avatar-preview" src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=F97F2D&color=fff" class="w-full h-full object-cover" alt="Avatar"/>
                        @endif
                    </div>
                    <!-- Botón para subir imagen (RF15) -->
                    <button type="button" onclick="document.getElementById('foto_perfil').click()" class="absolute -bottom-2 -right-2 w-8 h-8 bg-[#F97F2D] rounded-full flex items-center justify-center text-white shadow-md border-2 border-[#f0ffd8] dark:border-[#121f05] hover:bg-[#e06d20] transition-colors cursor-pointer">
                        <span class="material-symbols-outlined text-[15px]">photo_camera</span>
                    </button>
                </div>
                <div class="flex-1 pt-1">
                    <h2 class="text-3xl font-extrabold text-[#121f05] dark:text-white mb-1 transition-colors">{{ auth()->user()->name }}</h2>
                    <p class="text-[#574237] dark:text-emerald-200/80 text-sm mb-2 transition-colors">{{ auth()->user()->email }}</p>
                    <div class="flex items-center gap-3 mb-2 flex-wrap">
                        <span class="bg-[#e2f4c8] dark:bg-emerald-950/70 text-[#006e16] dark:text-emerald-300 text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wider border border-emerald-500/20">
                            {{ auth()->user()->nombreRolVisible() }}
                        </span>
                        <span class="{{ auth()->user()->isBeneficiario() ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-300' : 'bg-gray-100 text-gray-700 dark:bg-white/10 dark:text-gray-300' }} text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wider">
                            {{ __('ui.beneficiary_status') }}: {{ auth()->user()->isBeneficiario() ? __('ui.active') : __('ui.inactive') }}
                        </span>
                        <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">
                            Miembro desde: {{ auth()->user()->created_at ? ucfirst(auth()->user()->created_at->translatedFormat('F Y')) : 'Reciente' }}
                        </span>
                    </div>
                    @if(auth()->user()->fecha_renovacion_membresia)
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">Renovación: {{ \Carbon\Carbon::parse(auth()->user()->fecha_renovacion_membresia)->format('d/m/Y') }}</p>
                    @endif
                </div>
            </div>

            <!-- ── GRID CONTENIDO ── -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- COLUMNA FORMULARIO (RF15) -->
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-[#1b2711] rounded-2xl p-7 shadow-sm border border-gray-100 dark:border-white/10 transition-colors">
                        <h3 class="text-lg font-bold text-[#121f05] dark:text-white mb-6 flex items-center gap-2">
                            <span class="material-symbols-outlined text-[#F97F2D] text-xl">manage_accounts</span>
                            <span>                            {{ __('ui.profile_settings') }}</span>
                        </h3>

                        <form action="{{ route('perfil.update') }}" method="POST" id="form-perfil" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            
                            <!-- Input oculto para la foto -->
                            <input type="file" name="foto_perfil" id="foto_perfil" class="hidden" accept="image/*" onchange="previewImage(event)">

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <!-- Nombre -->
                                <div class="space-y-1">
                                    <label for="name" class="block text-[11px] font-bold uppercase tracking-widest text-[#574237] dark:text-emerald-200/90">{{ __('ui.full_name') }}</label>
                                    <input type="text" name="name" id="name" value="{{ old('name', auth()->user()->name) }}" placeholder="Ej: Juan Pérez" 
                                           class="w-full bg-gray-50 dark:bg-[#253418] border-gray-200 dark:border-white/15 text-gray-900 dark:text-white rounded-xl text-sm focus:ring-[#F97F2D] focus:border-[#F97F2D] transition-colors @error('name') border-red-500 @enderror">
                                    <p id="error-name" class="text-red-500 text-[10px] mt-1 hidden">El nombre no puede estar vacío.</p>
                                    @error('name') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                                </div>

                                <!-- Email -->
                                <div class="space-y-1">
                                    <label for="email" class="block text-[11px] font-bold uppercase tracking-widest text-[#574237] dark:text-emerald-200/90">{{ __('ui.email') }}</label>
                                    <input type="email" name="email" id="email" value="{{ old('email', auth()->user()->email) }}" placeholder="usuario@foodpass.com" 
                                           class="w-full bg-gray-50 dark:bg-[#253418] border-gray-200 dark:border-white/15 text-gray-900 dark:text-white rounded-xl text-sm focus:ring-[#F97F2D] focus:border-[#F97F2D] transition-colors @error('email') border-red-500 @enderror">
                                    <p id="error-email" class="text-red-500 text-[10px] mt-1 hidden">Ingresa un correo válido.</p>
                                    @error('email') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                                </div>

                                <!-- Teléfono -->
                                <div class="space-y-1">
                                    <label for="telefono" class="block text-[11px] font-bold uppercase tracking-widest text-[#574237] dark:text-emerald-200/90">{{ __('ui.phone') }}</label>
                                    <input type="text" name="telefono" id="telefono" value="{{ old('telefono', auth()->user()->telefono) }}" placeholder="+57 300 000 0000" 
                                           class="w-full bg-gray-50 dark:bg-[#253418] border-gray-200 dark:border-white/15 text-gray-900 dark:text-white rounded-xl text-sm focus:ring-[#F97F2D] focus:border-[#F97F2D] transition-colors">
                                </div>

                                <!-- Idioma Preferido -->
                                <div class="space-y-1">
                                    <label for="idioma_preferido" class="block text-[11px] font-bold uppercase tracking-widest text-[#574237] dark:text-emerald-200/90">{{ __('ui.preferred_language') }}</label>
                                    <select name="idioma_preferido" id="idioma_preferido" class="w-full bg-gray-50 dark:bg-[#253418] border-gray-200 dark:border-white/15 text-gray-900 dark:text-white rounded-xl text-sm focus:ring-[#F97F2D] focus:border-[#F97F2D] transition-colors">
                                        <option value="es" {{ auth()->user()->idioma_preferido == 'es' ? 'selected' : '' }}>Español</option>
                                        <option value="en" {{ auth()->user()->idioma_preferido == 'en' ? 'selected' : '' }}>English</option>
                                    </select>
                                </div>

                                <!-- Dirección -->
                                <div class="space-y-1 md:col-span-2">
                                    <label for="direccion" class="block text-[11px] font-bold uppercase tracking-widest text-[#574237] dark:text-emerald-200/90">{{ __('ui.address') }}</label>
                                    <input type="text" name="direccion" id="direccion" value="{{ old('direccion', auth()->user()->direccion) }}" placeholder="Ej: Calle 123 #45-67" 
                                           class="w-full bg-gray-50 dark:bg-[#253418] border-gray-200 dark:border-white/15 text-gray-900 dark:text-white rounded-xl text-sm focus:ring-[#F97F2D] focus:border-[#F97F2D] transition-colors">
                                </div>

                                <!-- Contraseña -->
                                <div class="space-y-1 md:col-span-2">
                                    <label for="password" class="block text-[11px] font-bold uppercase tracking-widest text-[#574237] dark:text-emerald-200/90">Nueva Contraseña (Opcional)</label>
                                    <input type="password" name="password" id="password" placeholder="Mínimo 8 caracteres para cambiar" 
                                           class="w-full bg-gray-50 dark:bg-[#253418] border-gray-200 dark:border-white/15 text-gray-900 dark:text-white rounded-xl text-sm focus:ring-[#F97F2D] focus:border-[#F97F2D] transition-colors @error('password') border-red-500 @enderror">
                                    @error('password') <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div class="mt-8">
                                <button type="submit" id="btn-save" class="w-full bg-[#F97F2D] hover:bg-[#e06d20] text-white font-bold text-sm py-3.5 rounded-xl flex items-center justify-center gap-2 shadow-lg shadow-[#F97F2D]/20 transition-all cursor-pointer">
                                    <span id="btn-text">{{ __('ui.save_changes') }}</span>
                                    <div id="btn-spinner" class="hidden w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- COLUMNA DERECHA -->
                <div class="space-y-5">
                    
                    <!-- ── SELECTOR DE TEMA / MODO OSCURO (Requerimiento) ── -->
                    <div class="bg-white dark:bg-[#1b2711] rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-white/10 transition-colors"
                         x-data="{
                            isDark: document.documentElement.classList.contains('dark'),
                            toggleTheme() {
                                this.isDark = !this.isDark;
                                if (this.isDark) {
                                    document.documentElement.classList.add('dark');
                                    document.documentElement.classList.remove('light');
                                    localStorage.setItem('theme', 'dark');
                                } else {
                                    document.documentElement.classList.remove('dark');
                                    document.documentElement.classList.add('light');
                                    localStorage.setItem('theme', 'light');
                                }
                                window.dispatchEvent(new CustomEvent('theme-changed', { detail: { dark: this.isDark } }));
                            }
                         }"
                         @theme-changed.window="isDark = $event.detail.dark">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-[10px] font-bold text-[#574237] dark:text-emerald-300 uppercase tracking-widest block">APARIENCIA DEL SISTEMA</span>
                            <span class="material-symbols-outlined text-lg text-amber-500" x-show="!isDark">light_mode</span>
                            <span class="material-symbols-outlined text-lg text-amber-300" x-show="isDark" x-cloak>dark_mode</span>
                        </div>
                        
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <h4 class="text-sm font-bold text-[#121f05] dark:text-white" x-text="isDark ? 'Modo Oscuro Activo' : 'Modo Claro Activo'"></h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Alterna el tema visual de la aplicación.</p>
                            </div>
                            
                            <!-- Interruptor Toggle Switch -->
                            <button type="button" @click="toggleTheme()"
                                    class="relative inline-flex h-7 w-14 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                                    :class="isDark ? 'bg-[#F97F2D]' : 'bg-gray-300 dark:bg-gray-700'"
                                    title="Alternar Modo Oscuro / Claro">
                                <span class="sr-only">Cambiar modo oscuro</span>
                                <span aria-hidden="true" 
                                      class="pointer-events-none inline-flex items-center justify-center h-6 w-6 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                      :class="isDark ? 'translate-x-7' : 'translate-x-0'">
                                    <span class="material-symbols-outlined text-[14px]" :class="isDark ? 'text-[#F97F2D]' : 'text-amber-500'" x-text="isDark ? 'dark_mode' : 'light_mode'"></span>
                                </span>
                            </button>
                        </div>

                        <!-- Botones de selección rápida -->
                        <div class="grid grid-cols-2 gap-2 mt-4 pt-3 border-t border-gray-100 dark:border-white/10">
                            <button type="button" @click="if (isDark) toggleTheme()"
                                    class="flex items-center justify-center gap-1.5 py-2 px-3 rounded-xl text-xs font-bold transition-all border cursor-pointer"
                                    :class="!isDark ? 'bg-amber-50 text-amber-900 border-amber-300 shadow-sm' : 'bg-gray-50 dark:bg-white/5 text-gray-600 dark:text-gray-400 border-transparent hover:bg-gray-100 dark:hover:bg-white/10'">
                                <span class="material-symbols-outlined text-[16px]">wb_sunny</span>
                                <span>Claro</span>
                            </button>
                            <button type="button" @click="if (!isDark) toggleTheme()"
                                    class="flex items-center justify-center gap-1.5 py-2 px-3 rounded-xl text-xs font-bold transition-all border cursor-pointer"
                                    :class="isDark ? 'bg-[#273517] text-white border-[#F97F2D] shadow-sm' : 'bg-gray-50 dark:bg-white/5 text-gray-600 dark:text-gray-400 border-transparent hover:bg-gray-100 dark:hover:bg-white/10'">
                                <span class="material-symbols-outlined text-[16px]">dark_mode</span>
                                <span>Oscuro</span>
                            </button>
                        </div>
                    </div>

                    <!-- Pedidos Totales -->
                    <div class="bg-white dark:bg-[#1b2711] rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-white/10 transition-colors">
                        <span class="text-[10px] font-bold text-[#574237] dark:text-emerald-300 uppercase tracking-widest">{{ __('ui.total_orders') }}</span>
                        <p class="text-4xl font-extrabold text-[#121f05] dark:text-white mt-1">{{ auth()->user()->pedidos()->count() }}</p>
                    </div>
                    
                    <!-- Notificaciones de Cuenta -->
                    <div class="bg-white dark:bg-[#1b2711] rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-white/10 transition-colors">
                        <span class="text-[10px] font-bold text-[#574237] dark:text-emerald-300 uppercase tracking-widest block mb-1">{{ __('ui.account_notifications') }}</span>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">{{ auth()->user()->unreadNotifications()->count() }} {{ __('ui.new_notifications') }}</p>
                        <button @click="modalNotifsCuenta = true" type="button" class="w-full bg-[#F97F2D] hover:bg-[#e06d20] text-white font-bold py-3 px-4 rounded-xl text-xs flex items-center justify-center gap-2 shadow-md transition-all cursor-pointer">
                            <span class="material-symbols-outlined text-[18px]">notifications</span>
                            <span>{{ __('ui.view_notifications') }}</span>
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <!-- ── POP-UP MODAL: NOTIFICACIONES DE CUENTA ── -->
    <div x-show="modalNotifsCuenta" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm p-4" x-transition>
        <div class="bg-white dark:bg-[#1b2711] text-gray-900 dark:text-white rounded-3xl max-w-lg w-full p-6 shadow-2xl relative max-h-[85vh] overflow-y-auto border border-gray-100 dark:border-white/10" @click.away="modalNotifsCuenta = false">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-100 dark:border-white/10">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-orange-100 dark:bg-orange-950/60 text-[#F97F2D] rounded-xl flex items-center justify-center">
                        <span class="material-symbols-outlined">notifications_active</span>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg text-gray-900 dark:text-white">{{ __('ui.account_notifications') }}</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('ui.notifications') }}</p>
                    </div>
                </div>
                <button @click="modalNotifsCuenta = false" class="text-gray-400 hover:text-black dark:hover:text-white cursor-pointer">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <div class="divide-y divide-gray-100 dark:divide-white/10">
                @forelse(auth()->user()->notifications()->latest()->limit(10)->get() as $notification)
                    <div class="py-3.5 flex items-start gap-3 p-2 rounded-xl {{ empty($notification->read_at) ? 'bg-orange-50/30 dark:bg-white/5' : '' }}">
                        <span class="material-symbols-outlined text-[#F97F2D] text-[18px] mt-0.5">notifications</span>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs text-gray-700 dark:text-gray-300">{{ $notification->data['message'] ?? __('ui.notifications') }}</p>
                            <span class="text-[10px] text-gray-400 mt-1 block">{{ $notification->created_at->diffForHumans() }}</span>
                            @if(empty($notification->read_at))
                                <form method="POST" action="{{ route('notifications.read', $notification->id) }}" class="mt-2">
                                    @csrf
                                    <button type="submit" class="text-[11px] font-semibold text-[#F97F2D] hover:underline">{{ __('ui.mark_read') }}</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="py-6 text-center text-xs text-gray-500 dark:text-gray-400">{{ __('ui.no_account_notifications') }}</p>
                @endforelse
            </div>

            <div class="pt-4 border-t border-gray-100 dark:border-white/10 text-right mt-2">
                <button @click="modalNotifsCuenta = false" class="px-6 py-2.5 bg-gray-100 dark:bg-white/10 hover:bg-gray-200 dark:hover:bg-white/20 text-gray-700 dark:text-white font-bold rounded-xl text-xs transition-colors cursor-pointer">
                    {{ __('ui.close') }}
                </button>
            </div>
        </div>
    </div>

    <!-- Script de Preview de Imagen y Validación -->
    <script>
        function previewImage(event) {
            const input = event.target;
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('avatar-preview').src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        document.getElementById('form-perfil').addEventListener('submit', function(e) {
            let hasErrors = false;
            const nameInput = document.getElementById('name');
            const emailInput = document.getElementById('email');
            
            const errorName = document.getElementById('error-name');
            const errorEmail = document.getElementById('error-email');

            nameInput.classList.remove('border-red-500');
            emailInput.classList.remove('border-red-500');
            errorName.classList.add('hidden');
            errorEmail.classList.add('hidden');

            if(nameInput.value.trim() === '') {
                nameInput.classList.add('border-red-500');
                errorName.classList.remove('hidden');
                hasErrors = true;
            }

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if(!emailRegex.test(emailInput.value.trim())) {
                emailInput.classList.add('border-red-500');
                errorEmail.classList.remove('hidden');
                hasErrors = true;
            }

            if(hasErrors) {
                e.preventDefault();
                return;
            }

            const btn = document.getElementById('btn-save');
            const text = document.getElementById('btn-text');
            const spinner = document.getElementById('btn-spinner');

            btn.disabled = true;
            btn.classList.add('opacity-70', 'cursor-not-allowed');
            text.innerText = 'Procesando...';
            spinner.classList.remove('hidden');
        });
    </script>
</body>
</html>
