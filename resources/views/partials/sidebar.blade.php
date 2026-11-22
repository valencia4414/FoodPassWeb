<!-- SideNavBar Shell Reutilizable Oficial FoodPass -->
<style>
    [x-cloak] { display: none !important; }

    /* Estilos globales de Modo Oscuro para toda la app */
    html.dark body,
    html.dark main,
    html.dark .bg-\[\#f0ffd8\],
    html.dark .bg-\[\#F8F9FA\],
    html.dark .bg-fp-light,
    html.dark .bg-fp-bg,
    html.dark .bg-background {
        background-color: #121f05 !important;
        color: #e8facd !important;
    }
    html.dark .bg-white,
    html.dark .bg-surface-container-lowest,
    html.dark .bg-surface-bright {
        background-color: #1b2610 !important;
        color: #ffffff !important;
        border-color: rgba(255, 255, 255, 0.1) !important;
    }
    html.dark .bg-gray-50,
    html.dark .bg-gray-100,
    html.dark .bg-gray-200,
    html.dark .bg-surface-container-low,
    html.dark .bg-surface-container {
        background-color: #223015 !important;
        color: #e8facd !important;
        border-color: rgba(255, 255, 255, 0.08) !important;
    }
    html.dark .bg-surface-container-high,
    html.dark .bg-surface-container-highest {
        background-color: #2a3a1b !important;
        color: #f0ffd8 !important;
        border-color: rgba(255, 255, 255, 0.12) !important;
    }
    html.dark .text-gray-900,
    html.dark .text-gray-800,
    html.dark .text-slate-900,
    html.dark .text-slate-800,
    html.dark .text-slate-700,
    html.dark .text-\[\#121f05\],
    html.dark .text-\[\#283618\],
    html.dark .text-on-background,
    html.dark .text-on-surface,
    html.dark .text-black {
        color: #ffffff !important;
    }
    html.dark .text-gray-700,
    html.dark .text-gray-600,
    html.dark .text-gray-500,
    html.dark .text-gray-400,
    html.dark .text-slate-500,
    html.dark .text-\[\#574237\],
    html.dark .text-on-surface-variant {
        color: #c5dbb0 !important;
    }
    html.dark .text-fp-dark,
    html.dark .text-fp-darkgreen {
        color: #e8facd !important;
    }
    html.dark header.bg-white,
    html.dark header.bg-white\/90,
    html.dark header.bg-white\/80,
    html.dark header {
        background-color: rgba(18, 31, 5, 0.95) !important;
        border-color: rgba(255, 255, 255, 0.1) !important;
    }
    html.dark .border-gray-100,
    html.dark .border-gray-200,
    html.dark .border-gray-300,
    html.dark .border-slate-100,
    html.dark .border-slate-200,
    html.dark .divide-gray-100 > :not([hidden]) ~ :not([hidden]),
    html.dark .divide-gray-200 > :not([hidden]) ~ :not([hidden]) {
        border-color: rgba(255, 255, 255, 0.1) !important;
    }
    html.dark input:not([type="checkbox"]):not([type="radio"]),
    html.dark select,
    html.dark textarea {
        background-color: #263618 !important;
        color: #ffffff !important;
        border-color: rgba(255, 255, 255, 0.15) !important;
    }
    html.dark input::placeholder,
    html.dark textarea::placeholder {
        color: rgba(255, 255, 255, 0.35) !important;
    }
    html.dark table thead th {
        background-color: #202d14 !important;
        color: #e8facd !important;
        border-color: rgba(255, 255, 255, 0.1) !important;
    }
    html.dark table tbody tr {
        border-color: rgba(255, 255, 255, 0.08) !important;
    }
    html.dark tr.hover\:bg-gray-50\/70:hover,
    html.dark tr.hover\:bg-gray-50:hover,
    html.dark .hover\:bg-gray-50:hover,
    html.dark .hover\:bg-gray-100:hover {
        background-color: rgba(255, 255, 255, 0.06) !important;
    }
</style>
<script>
    (function() {
        try {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark' || (!savedTheme && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
                document.documentElement.classList.remove('light');
            } else {
                document.documentElement.classList.remove('dark');
                document.documentElement.classList.add('light');
            }
        } catch (e) {}
    })();
</script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<button id="btn-sidebar-open" type="button" class="fixed left-4 top-4 z-50 rounded-xl bg-[#273517] p-2 text-white shadow-lg lg:hidden" aria-label="Abrir menú">
    <span class="material-symbols-outlined">menu</span>
</button>
<div id="sidebar-overlay" class="fixed inset-0 z-30 hidden bg-black/50 lg:hidden"></div>
<aside id="sidebar"
    class="flex flex-col z-40 h-screen w-64 fixed left-0 top-0 overflow-y-auto bg-[#273517] shadow-2xl font-['Plus_Jakarta_Sans',sans-serif] tracking-wide shrink-0 -translate-x-full lg:translate-x-0 transition-transform duration-200">
    <div class="p-8">
        <h1 class="text-2xl font-bold text-white mb-1">FoodPass</h1>
        <p class="text-white/50 text-xs uppercase tracking-widest font-bold">The Artisanal Ledger</p>
    </div>

    <nav class="flex-1 px-4 space-y-2">
        <!-- 1. Inicio -->
        <a class="flex items-center gap-3 rounded-full px-4 py-3 mx-2 transition-all duration-200 {{ request()->routeIs('dashboard*') ? 'bg-[#F97F2D] text-white font-semibold shadow-lg shadow-[#F97F2D]/20 active:scale-95' : 'text-white/70 hover:text-white hover:bg-white/10 font-semibold' }}"
            href="{{ route('dashboard') }}">
            <span class="material-symbols-outlined" @if(request()->routeIs('dashboard*'))
            style="font-variation-settings: 'FILL' 1;" @endif>home</span>
            <span class="text-sm">{{ __('Inicio') }}</span>
        </a>

        <!-- 2. Menú -->
        <a class="flex items-center gap-3 rounded-full px-4 py-3 mx-2 transition-all duration-200 {{ request()->routeIs('menu-digital*') ? 'bg-[#F97F2D] text-white font-semibold shadow-lg shadow-[#F97F2D]/20 active:scale-95' : 'text-white/70 hover:text-white hover:bg-white/10 font-semibold' }}"
            href="{{ route('menu-digital') }}">
            <span class="material-symbols-outlined" @if(request()->routeIs('menu-digital*'))
            style="font-variation-settings: 'FILL' 1;" @endif>restaurant_menu</span>
            <span class="text-sm">{{ __('Menú') }}</span>
        </a>

        <!-- 3. Historial -->
        <a class="flex items-center gap-3 rounded-full px-4 py-3 mx-2 transition-all duration-200 {{ request()->routeIs('historial*') ? 'bg-[#F97F2D] text-white font-semibold shadow-lg shadow-[#F97F2D]/20 active:scale-95' : 'text-white/70 hover:text-white hover:bg-white/10 font-semibold' }}"
            href="{{ route('historial') }}">
            <span class="material-symbols-outlined" @if(request()->routeIs('historial*'))
            style="font-variation-settings: 'FILL' 1;" @endif>history</span>
            <span class="text-sm">{{ __('Historial') }}</span>
        </a>

        <!-- 4. Canje QR -->
        <a class="flex items-center gap-3 rounded-full px-4 py-3 mx-2 transition-all duration-200 {{ request()->routeIs('canje*') ? 'bg-[#F97F2D] text-white font-semibold shadow-lg shadow-[#F97F2D]/20 active:scale-95' : 'text-white/70 hover:text-white hover:bg-white/10 font-semibold' }}"
            href="{{ route('canje') }}">
            <span class="material-symbols-outlined" @if(request()->routeIs('canje*'))
            style="font-variation-settings: 'FILL' 1;" @endif>qr_code_scanner</span>
            <span class="text-sm">{{ __('Canje QR') }}</span>
        </a>

        <!-- 5. Perfil -->
        <a class="flex items-center gap-3 rounded-full px-4 py-3 mx-2 transition-all duration-200 {{ request()->routeIs('perfil*') ? 'bg-[#F97F2D] text-white font-semibold shadow-lg shadow-[#F97F2D]/20 active:scale-95' : 'text-white/70 hover:text-white hover:bg-white/10 font-semibold' }}"
            href="{{ route('perfil') }}">
            <span class="material-symbols-outlined" @if(request()->routeIs('perfil*'))
            style="font-variation-settings: 'FILL' 1;" @endif>person</span>
            <span class="text-sm">{{ __('Perfil') }}</span>
        </a>

        @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isRestaurante()))
            <div class="pt-4 pb-1 px-4">
                <p class="text-[10px] font-bold tracking-widest uppercase text-white/40">{{ __('Administración') }}</p>
            </div>

            @if(auth()->user()->isAdmin())
            <!-- Padrón Beneficiarios -->
            <a class="flex items-center gap-3 rounded-full px-4 py-3 mx-2 transition-all duration-200 {{ request()->routeIs('admin.beneficiarios*') ? 'bg-[#F97F2D] text-white font-semibold shadow-lg shadow-[#F97F2D]/20 active:scale-95' : 'text-white/70 hover:text-white hover:bg-white/10 font-semibold' }}"
                href="{{ route('admin.beneficiarios') }}">
                <span class="material-symbols-outlined" @if(request()->routeIs('admin.beneficiarios*')) style="font-variation-settings: 'FILL' 1;" @endif>badge</span>
                <span class="text-sm">{{ __('Beneficiarios SENA') }}</span>
            </a>
            @endif

            <!-- Pedidos Cafetería -->
            <a class="flex items-center gap-3 rounded-full px-4 py-3 mx-2 transition-all duration-200 {{ request()->routeIs('admin.pedidos*') ? 'bg-[#F97F2D] text-white font-semibold shadow-lg shadow-[#F97F2D]/20 active:scale-95' : 'text-white/70 hover:text-white hover:bg-white/10 font-semibold' }}"
                href="{{ route('admin.pedidos') }}">
                <span class="material-symbols-outlined" @if(request()->routeIs('admin.pedidos*')) style="font-variation-settings: 'FILL' 1;" @endif>receipt_long</span>
                <span class="text-sm">{{ __('Pedidos Cafetería') }}</span>
            </a>

            <!-- Gestión Platillos -->
            <a class="flex items-center gap-3 rounded-full px-4 py-3 mx-2 transition-all duration-200 {{ request()->routeIs('admin.platillos*') ? 'bg-[#F97F2D] text-white font-semibold shadow-lg shadow-[#F97F2D]/20 active:scale-95' : 'text-white/70 hover:text-white hover:bg-white/10 font-semibold' }}"
                href="{{ route('admin.platillos.index') }}">
                <span class="material-symbols-outlined" @if(request()->routeIs('admin.platillos*')) style="font-variation-settings: 'FILL' 1;" @endif>lunch_dining</span>
                <span class="text-sm">{{ __('Gestión Platillos') }}</span>
            </a>
        @endif
    </nav>

    <!-- Interruptor Rápido de Modo Oscuro Global en Sidebar -->
    <div class="px-6 py-2.5 border-t border-white/10 flex items-center justify-between"
         x-data="{
             isDark: document.documentElement.classList.contains('dark'),
             toggleSidebarTheme() {
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
         }">
        <div class="flex items-center gap-2 text-white/80">
            <span class="material-symbols-outlined text-[17px] text-amber-300" x-show="!isDark">light_mode</span>
            <span class="material-symbols-outlined text-[17px] text-[#F97F2D]" x-show="isDark" x-cloak>dark_mode</span>
            <span class="text-xs font-semibold" x-text="isDark ? 'Modo Oscuro' : 'Modo Claro'"></span>
        </div>
        <button type="button" @click="toggleSidebarTheme()"
                class="relative inline-flex h-5 w-10 shrink-0 cursor-pointer rounded-full border border-white/20 transition-colors duration-200 ease-in-out focus:outline-none"
                :class="isDark ? 'bg-[#F97F2D]' : 'bg-white/15'"
                title="Cambiar Modo Claro / Oscuro">
            <span class="sr-only">Cambiar tema</span>
            <span class="pointer-events-none inline-block h-3.5 w-3.5 transform rounded-full bg-white shadow transition duration-200 ease-in-out mt-[2px] ml-[2px]"
                  :class="isDark ? 'translate-x-5' : 'translate-x-0'"></span>
        </button>
    </div>

    <!-- Usuario autenticado al pie -->
    <div class="mt-auto p-6 border-t border-white/5 relative"
        x-data="{ openUserMenu: false, modalLogoutConfirm: false }">
        <!-- Botón del usuario -->
        <button @click="openUserMenu = !openUserMenu" @click.away="openUserMenu = false" type="button"
            class="w-full flex items-center justify-between gap-3 px-3 py-3 rounded-xl bg-white/5 hover:bg-white/10 transition-all text-left focus:outline-none group cursor-pointer">
            <div class="flex items-center gap-3 overflow-hidden">
                @if(auth()->check() && auth()->user()->foto_perfil)
                    <img alt="User avatar" class="w-10 h-10 rounded-full object-cover border border-white/20 shrink-0"
                        src="{{ asset('storage/' . auth()->user()->foto_perfil) }}" />
                @else
                    <div
                        class="w-10 h-10 rounded-full bg-[#F97F2D] text-white flex items-center justify-center font-bold text-sm shadow-sm shrink-0">
                        {{ auth()->check() ? substr(auth()->user()->name, 0, 1) : 'U' }}
                    </div>
                @endif
                <div class="overflow-hidden">
                    <p class="text-white font-bold truncate text-sm">
                        {{ auth()->check() ? auth()->user()->name : 'Usuario SENA' }}</p>
                    <p class="text-white/40 text-xs truncate">{{ auth()->check() ? auth()->user()->nombreRolVisible() : 'Aprendiz SENA' }}</p>
                </div>
            </div>
            <span
                class="material-symbols-outlined text-white/40 group-hover:text-white transition-colors text-[20px]">more_vert</span>
        </button>

        <!-- Menú desplegable: Única opción Cerrar sesión -->
        <div x-show="openUserMenu" x-transition x-cloak
            class="absolute bottom-20 left-6 right-6 bg-[#1b2610] rounded-2xl shadow-2xl border border-white/10 p-2 z-50">
            <button type="button" @click="modalLogoutConfirm = true; openUserMenu = false;"
                class="w-full flex items-center gap-3 px-4 py-3 text-sm font-bold text-red-400 hover:bg-red-500/20 hover:text-red-300 rounded-xl transition-colors text-left">
                <span class="material-symbols-outlined text-[20px]">logout</span>
                <span>Cerrar Sesión</span>
            </button>
        </div>

        <!-- Pop-up Modal de Confirmación para Cerrar Sesión -->
        <div x-show="modalLogoutConfirm" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm p-4"
            @click="modalLogoutConfirm = false" x-transition>
            <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl relative text-center" @click.stop>
                <div
                    class="w-14 h-14 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <span class="material-symbols-outlined text-[30px]">logout</span>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">¿Cerrar Sesión?</h3>
                <p class="text-xs text-gray-500 mb-6">¿Estás seguro de que deseas salir de tu cuenta de FoodPass?</p>

                <form method="POST" action="{{ route('logout') }}" class="flex gap-3">
                    @csrf
                    <button type="button" @click="modalLogoutConfirm = false"
                        class="flex-1 border border-gray-300 text-gray-700 py-3 rounded-xl font-bold text-xs hover:bg-gray-50 transition-colors">
                        Cancelar
                    </button>
                    <button type="submit"
                        class="flex-1 bg-red-600 hover:bg-red-700 text-white py-3 rounded-xl font-bold text-xs shadow-md transition-colors">
                        Sí, Salir
                    </button>
                </form>
            </div>
        </div>
    </div>
</aside>
<script>
    (() => {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        const openButton = document.getElementById('btn-sidebar-open');
        const closeSidebar = () => {
            sidebar?.classList.add('-translate-x-full');
            overlay?.classList.add('hidden');
        };
        const openSidebar = () => {
            sidebar?.classList.remove('-translate-x-full');
            overlay?.classList.remove('hidden');
        };
        openButton?.addEventListener('click', openSidebar);
        document.addEventListener('click', event => {
            if (event.target.closest('#btn-menu')) openSidebar();
        });
        overlay?.addEventListener('click', closeSidebar);
    })();
</script>
