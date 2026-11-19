<header class="h-16 bg-white border-b flex items-center justify-between px-6">
    <!-- Botón Hamburguesa para móvil (RNF03) -->
    <button id="btn-menu" class="hidden">
        <span class="material-symbols-outlined">menu</span>
    </button>




    <div class="flex items-center gap-5">
        @php
            $unreadNotificationCount = auth()->user()->unreadNotifications()->count();
            $notifications = auth()->user()->notifications()->latest()->limit(10)->get();
        @endphp
        <!-- Dropdown de Notificaciones -->
        <div class="relative x-dropdown">
            <button type="button" aria-controls="notif-menu" aria-label="{{ __('ui.notifications') }}" aria-expanded="false" class="relative p-2 text-gray-500 hover:text-[#F97F2D] transition-colors rounded-full hover:bg-orange-50 focus:outline-none" onclick="const menu=document.getElementById('notif-menu'); menu.classList.toggle('hidden'); this.setAttribute('aria-expanded', String(!menu.classList.contains('hidden')));">
                <span class="material-symbols-outlined text-[24px]">notifications</span>
                @if($unreadNotificationCount > 0)
                <span class="absolute top-1 right-1 min-w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-white" aria-label="{{ $unreadNotificationCount }} {{ __('ui.new_notifications') }}"></span>
                @endif
            </button>
            <div id="notif-menu" class="hidden absolute right-0 mt-2 w-[min(20rem,calc(100vw-2rem))] bg-white rounded-xl shadow-xl border border-gray-100 z-50 overflow-hidden">
                <div class="p-3 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                    <span class="text-xs font-bold text-gray-700 uppercase tracking-wider">{{ __('ui.notifications') }}</span>
                    @if($unreadNotificationCount > 0)
                    <form method="POST" action="{{ route('notifications.read-all') }}">
                        @csrf
                        <button type="submit" class="text-[10px] font-semibold text-[#F97F2D] hover:underline">{{ __('ui.mark_all_read') }} ({{ $unreadNotificationCount }})</button>
                    </form>
                    @endif
                </div>
                <div class="max-h-80 overflow-y-auto">
                    @forelse($notifications as $notification)
                        <div class="p-4 border-b border-gray-50 hover:bg-gray-50 flex gap-3 transition-colors {{ empty($notification->read_at) ? 'bg-orange-50/30' : '' }}">
                            <span class="material-symbols-outlined text-blue-600 text-[18px] mt-0.5" aria-hidden="true">info</span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm text-gray-800">{{ $notification->data['message'] ?? 'Tienes una nueva actualización.' }}</p>
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
                        <div class="p-6 text-center text-gray-400">
                            <span class="material-symbols-outlined text-[32px] mb-2 opacity-50">notifications_off</span>
                            <p class="text-sm font-medium">{{ __('ui.no_notifications') }}</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Dropdown de Usuario / Logout -->
        <div class="relative x-dropdown">
            <button class="flex items-center gap-3 focus:outline-none" onclick="document.getElementById('user-menu').classList.toggle('hidden')">
                <span class="text-sm font-medium hidden sm:block">{{ auth()->user()->name }}</span>
                @if(auth()->user()->foto_perfil)
                    <img src="{{ asset('storage/' . auth()->user()->foto_perfil) }}" class="w-9 h-9 rounded-full object-cover border-2 border-white shadow-sm hover:border-[#F97F2D] transition-colors">
                @else
                    <img src="https://ui-avatars.com/api/?name={{ auth()->user()->name }}&background=273517&color=fff" class="w-9 h-9 rounded-full object-cover border-2 border-white shadow-sm hover:border-[#F97F2D] transition-colors">
                @endif
            </button>
            <div id="user-menu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-xl border border-gray-100 z-50 overflow-hidden py-2">
                <a href="{{ route('perfil') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 hover:bg-orange-50 hover:text-[#F97F2D] transition-colors">
                    <span class="material-symbols-outlined text-[18px]">person</span> Mi Perfil
                </a>
                <div class="h-px bg-gray-100 my-1"></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-2 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
                        <span class="material-symbols-outlined text-[18px]">logout</span> Cerrar Sesión
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>

<script>
    // Cerrar dropdowns al hacer clic fuera
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.x-dropdown')) {
            const menus = [document.getElementById('notif-menu'), document.getElementById('user-menu')];
            menus.forEach(menu => {
                if (menu && !menu.classList.contains('hidden')) {
                    menu.classList.add('hidden');
                }
            });
            document.querySelectorAll('[aria-controls="notif-menu"]').forEach(button => button.setAttribute('aria-expanded', 'false'));
        }
    });
</script>