<!DOCTYPE html>

<html class="light" lang="{{ app()->getLocale() }}"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&amp;family=Inter:wght@400;500;600&amp;family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script id="tailwind-config">
      tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            "colors": {
                    "on-tertiary-fixed": "#002203",
                    "on-tertiary": "#ffffff",
                    "surface-container-high": "#dcefc3",
                    "surface": "#f0ffd8",
                    "surface-container-low": "#e8facd",
                    "on-primary-container": "#5f2700",
                    "primary": "#9b4500",
                    "on-primary": "#ffffff",
                    "surface-container": "#e2f4c8",
                    "primary-fixed": "#ffdbc9",
                    "background": "#f0ffd8",
                    "on-secondary-container": "#682800",
                    "on-background": "#121f05",
                    "tertiary-fixed-dim": "#73dd6d",
                    "tertiary": "#006e16",
                    "error": "#ba1a1a",
                    "on-secondary-fixed": "#341000",
                    "error-container": "#ffdad6",
                    "secondary-fixed": "#ffdbcb",
                    "primary-fixed-dim": "#ffb68e",
                    "primary-container": "#f97f2d",
                    "tertiary-fixed": "#8ffb86",
                    "on-error-container": "#93000a",
                    "inverse-on-surface": "#e5f7cb",
                    "surface-container-highest": "#d7e9bd",
                    "inverse-surface": "#273517",
                    "surface-dim": "#cee0b5",
                    "secondary-fixed-dim": "#ffb693",
                    "on-secondary": "#ffffff",
                    "outline": "#8b7265",
                    "on-secondary-fixed-variant": "#7a3000",
                    "surface-container-lowest": "#ffffff",
                    "on-tertiary-fixed-variant": "#00530e",
                    "on-error": "#ffffff",
                    "on-primary-fixed-variant": "#763300",
                    "inverse-primary": "#ffb68e",
                    "surface-bright": "#f0ffd8",
                    "secondary-container": "#fd8544",
                    "surface-tint": "#9b4500",
                    "on-surface-variant": "#574237",
                    "tertiary-container": "#4cb64b",
                    "outline-variant": "#dec1b2",
                    "secondary": "#a04100",
                    "surface-variant": "#d7e9bd",
                    "on-surface": "#121f05",
                    "on-tertiary-container": "#004209",
                    "on-primary-fixed": "#331200"
            },
            "borderRadius": {
                    "DEFAULT": "0.25rem",
                    "lg": "0.5rem",
                    "xl": "0.75rem",
                    "full": "9999px"
            },
            "fontFamily": {
                    "headline": ["Plus Jakarta Sans"],
                    "body": ["Inter"],
                    "label": ["Inter"]
            }
          },
        },
      }
    </script>
<style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0ffd8;
            color: #121f05;
        }
        h1, h2, h3, .font-headline {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-surface text-on-background" x-data="{ showNotifs: false }">
@include('partials.sidebar')
<!-- TopNavBar Shell -->
<header class="fixed top-0 right-0 w-full lg:w-[calc(100%-16rem)] h-16 z-30 bg-white/80 dark:bg-[#121f05]/80 backdrop-blur-md shadow-[0px_20px_40px_rgba(18,31,5,0.06)] flex justify-between items-center px-6 transition-colors duration-300">
<div class="flex items-center gap-4 flex-1 pl-16 lg:pl-0">
<div class="lg:hidden text-lg font-bold text-[#273517] dark:text-white">FoodPass</div>
</div>

<div class="flex items-center gap-6 relative">
<!-- Campana de Notificaciones -->
<div class="relative">
<button @click="showNotifs = !showNotifs" @click.away="showNotifs = false" class="relative p-2 text-on-background hover:bg-gray-100 dark:hover:bg-white/10 rounded-full transition-colors focus:outline-none">
<span class="material-symbols-outlined" data-icon="notifications">notifications</span>
@if(auth()->user()->unreadNotifications->count())
<span class="absolute top-2 right-2 w-2.5 h-2.5 bg-primary-container rounded-full ring-2 ring-white"></span>
@endif
</button>

<!-- Popover de notificaciones -->
<div x-show="showNotifs" x-transition x-cloak class="absolute right-0 mt-2 w-[min(20rem,calc(100vw-2rem))] bg-white dark:bg-[#1f2d12] rounded-2xl shadow-xl border border-gray-100 dark:border-white/10 py-3 z-50">
<div class="px-4 pb-2 border-b border-gray-100 dark:border-white/10 flex items-center justify-between gap-3">
<span class="font-headline font-bold text-sm text-on-background dark:text-white">{{ __('Notificaciones') }}</span>
@if(auth()->user()->unreadNotifications->count())
<form method="POST" action="{{ route('notifications.read-all') }}">
    @csrf
    <button type="submit" class="text-[10px] font-bold text-primary">{{ __('Marcar todas como leídas') }}</button>
</form>
@endif
</div>
<div class="divide-y divide-gray-50 dark:divide-white/5 max-h-72 overflow-y-auto">
@forelse(auth()->user()->notifications()->latest()->take(8)->get() as $notification)
<div class="p-3.5 hover:bg-surface-container-low/50 transition-colors flex items-start gap-3 {{ empty($notification->read_at) ? 'bg-orange-50/30' : '' }}">
    <div class="w-8 h-8 rounded-full bg-green-100 text-green-700 flex items-center justify-center shrink-0 text-sm mt-0.5">
        <span class="material-symbols-outlined text-[18px]">notifications</span>
    </div>
    <div class="flex-1">
        <p class="text-xs text-on-surface-variant dark:text-gray-300 font-medium">{{ $notification->data['message'] ?? 'Tienes una nueva actualización.' }}</p>
        <span class="text-[10px] text-gray-400 mt-1 block">{{ $notification->created_at->diffForHumans() }}</span>
    </div>
    @if(empty($notification->read_at))
    <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
        @csrf
        <button type="submit" class="text-[10px] font-bold text-primary">{{ __('Leída') }}</button>
    </form>
    @endif
</div>
@empty
<div class="p-6 text-center text-gray-400">
    <span class="material-symbols-outlined text-[28px] mb-2">notifications_off</span>
    <p class="text-xs">{{ __('No tienes notificaciones') }}</p>
</div>
@endforelse
</div>
</div>
</div>

</div>
</header>
<!-- Main Content Canvas -->
<main class="lg:ml-64 pt-24 px-4 sm:px-6 pb-12 min-h-screen">
<!-- Welcome Section -->
<section class="mb-8">
<h2 class="text-3xl sm:text-4xl font-headline font-extrabold text-on-background tracking-tight mb-2">¡Hola, {{ explode(' ', auth()->user()->name)[0] }}!</h2>
<p class="text-on-surface-variant font-body">Bienvenido a tu panel artesanal de hoy.</p>
</section>

<!-- Banner Estado de Beneficiario (RF01 - SRS FoodPass) -->
<div class="mb-10">
    @if(auth()->user()->isBeneficiario())
        <div class="bg-gradient-to-r from-[#283618] to-[#3a4f23] rounded-3xl p-6 sm:p-7 text-white shadow-xl flex flex-col md:flex-row items-start md:items-center justify-between gap-6 relative overflow-hidden border border-[#2F9B34]/30">
            <div class="flex items-center gap-5 relative z-10">
                <div class="w-14 h-14 rounded-2xl bg-[#2F9B34]/20 border border-[#2F9B34]/40 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[#2F9B34] text-3xl" style="font-variation-settings: 'FILL' 1;">verified</span>
                </div>
                <div>
                    <div class="inline-flex items-center gap-2 bg-[#2F9B34]/20 text-emerald-300 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider mb-2">
                        <span class="w-2 h-2 rounded-full bg-[#2F9B34] animate-pulse"></span>
                        Padrón Oficial Verificado
                    </div>
                    <h3 class="text-2xl font-headline font-bold text-white tracking-tight">Eres beneficiario activo</h3>
                    <p class="text-emerald-100/80 text-sm mt-1 max-w-xl">
                        Tu apoyo alimentario SENA se encuentra habilitado para la jornada de hoy. Puedes generar tu credencial QR y enviarla directamente a tu correo electrónico.
                    </p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-3 relative z-10 w-full md:w-auto shrink-0">
                <a href="{{ route('canje') }}" class="w-full sm:w-auto text-center px-6 py-3.5 bg-[#F97F2D] hover:bg-[#e06d1f] text-white font-bold rounded-2xl shadow-lg shadow-[#F97F2D]/30 transition-all flex items-center justify-center gap-2 text-sm active:scale-95">
                    <span class="material-symbols-outlined text-lg">qr_code</span>
                    <span>Ver / Generar QR</span>
                </a>
            </div>
            <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-[#2F9B34]/10 rounded-full blur-2xl"></div>
        </div>
    @else
        <div class="bg-gradient-to-r from-amber-950/80 to-amber-900/60 rounded-3xl p-6 sm:p-7 text-white shadow-xl flex flex-col md:flex-row items-start md:items-center justify-between gap-6 relative overflow-hidden border border-amber-500/30">
            <div class="flex items-center gap-5 relative z-10">
                <div class="w-14 h-14 rounded-2xl bg-amber-500/20 border border-amber-500/40 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-amber-400 text-3xl">info</span>
                </div>
                <div>
                    <div class="inline-flex items-center gap-2 bg-amber-500/20 text-amber-300 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider mb-2">
                        Padrón Oficial SENA
                    </div>
                    <h3 class="text-2xl font-headline font-bold text-white tracking-tight">No eres beneficiario activo</h3>
                    <p class="text-amber-100/80 text-sm mt-1 max-w-xl">
                        Tu documento no figura en el padrón activo de apoyo alimentario para el ciclo actual. Puedes ordenar tus comidas desde el Menú Digital o consultar con Bienestar al Aprendiz.
                    </p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-3 relative z-10 w-full md:w-auto shrink-0">
                <a href="{{ route('menu-digital') }}" class="w-full sm:w-auto text-center px-6 py-3.5 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-2xl shadow-lg transition-all flex items-center justify-center gap-2 text-sm active:scale-95">
                    <span class="material-symbols-outlined text-lg">restaurant_menu</span>
                    <span>Ver Menú Digital</span>
                </a>
            </div>
            <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-amber-500/10 rounded-full blur-2xl"></div>
        </div>
    @endif
</div>
<!-- Bento Grid Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-12 gap-6 mb-12">
<!-- Principal Card: Almuerzos Disponibles -->
<div class="md:col-span-8 bg-surface-container-lowest rounded-[2rem] p-8 shadow-[0px_20px_40px_rgba(18,31,5,0.04)] relative overflow-hidden group">
<div class="relative z-10 flex flex-col h-full justify-between">
<div>
<span class="bg-tertiary-container text-on-tertiary-container text-[10px] font-bold uppercase tracking-widest px-3 py-1 rounded-full mb-6 inline-block">Beneficio SENA</span>
<h3 class="text-on-background text-2xl font-headline font-bold mb-1">Canjes QR Disponibles</h3>
<p class="text-on-surface-variant mb-8">Genera tu código QR para redimir tu beneficio alimentario</p>
</div>
<div class="flex items-end gap-4">
<span class="text-7xl font-headline font-extrabold text-primary-container tracking-tighter">∞</span>
<span class="text-on-surface-variant text-xl font-headline font-semibold pb-2">sin límite diario</span>
</div>
</div>
<!-- Abstract Decorative Element -->
<div class="absolute -right-16 -bottom-16 w-64 h-64 bg-surface-container-high rounded-full opacity-20 group-hover:scale-110 transition-transform duration-500"></div>
<div class="absolute right-8 top-8">
<span class="material-symbols-outlined text-primary-container text-6xl opacity-20" data-icon="qr_code_2" style="font-variation-settings: 'FILL' 1;">qr_code_2</span>
</div>
</div>
<!-- Stats Side Cards -->
<div class="md:col-span-4 flex flex-col gap-6">
<!-- Pedidos Realizados -->
<div class="bg-surface-container-low rounded-[2rem] p-6 flex flex-col justify-between h-full">
<div class="flex justify-between items-start">
<div class="w-12 h-12 bg-surface-container-lowest rounded-2xl flex items-center justify-center">
<span class="material-symbols-outlined text-primary" data-icon="shopping_bag">shopping_bag</span>
</div>
<span class="text-xs font-bold text-tertiary">+2 esta semana</span>
</div>
<div>
<p class="text-on-surface-variant text-sm font-semibold uppercase tracking-wider mb-1">Pedidos realizados</p>
<p class="text-3xl font-headline font-extrabold text-on-background">48</p>
</div>
</div>
<!-- Botón Canje QR -->
<a href="{{ route('canje') }}" class="bg-inverse-surface rounded-[2rem] p-6 text-white flex flex-col justify-between h-full hover:opacity-95 hover:scale-[1.01] transition-all group cursor-pointer">
<div class="flex justify-between items-start">
<div class="w-12 h-12 bg-white/10 rounded-2xl flex items-center justify-center">
<span class="material-symbols-outlined text-primary-container" data-icon="qr_code_scanner" style="font-variation-settings: 'FILL' 1;">qr_code_scanner</span>
</div>
<div class="text-white/40 group-hover:text-white transition-colors">
<span class="material-symbols-outlined" data-icon="arrow_forward_ios">arrow_forward_ios</span>
</div>
</div>
<div>
<p class="text-white/60 text-sm font-semibold uppercase tracking-wider mb-1">Canje QR</p>
<p class="text-2xl font-headline font-bold">Generar QR</p>
</div>
</a>
</div>
</div>
<!-- Asymmetric Section: Recent Activity & Featured Menu -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
<!-- Activity Ledger (2/3 width) -->
<div class="lg:col-span-2">
<div class="flex items-center justify-between mb-6 px-2">
<h4 class="text-xl font-headline font-bold text-on-background">Historial de Consumo</h4>
<a class="text-primary font-semibold text-sm hover:underline" href="{{ route('historial') }}">Ver todo</a>
</div>
<div class="bg-surface-container-lowest rounded-[2rem] overflow-hidden">
<div class="p-6 flex items-center gap-4 hover:bg-surface-container-low transition-colors duration-200">
<div class="w-14 h-14 rounded-2xl bg-surface-container flex items-center justify-center flex-shrink-0">
<span class="material-symbols-outlined text-on-surface" data-icon="skillet">skillet</span>
</div>
<div class="flex-1">
<p class="font-headline font-bold text-on-background">Lasaña de Berenjenas</p>
<p class="text-sm text-on-surface-variant">Restaurante "La Toscana"</p>
</div>
<div class="text-right">
<p class="font-headline font-bold text-on-background">-1</p>
<p class="text-xs text-on-surface-variant">Hoy, 12:45 PM</p>
</div>
</div>
<div class="mx-6 h-0.5 bg-surface-container-high/50"></div>
<div class="p-6 flex items-center gap-4 hover:bg-surface-container-low transition-colors duration-200">
<div class="w-14 h-14 rounded-2xl bg-surface-container flex items-center justify-center flex-shrink-0">
<span class="material-symbols-outlined text-on-surface" data-icon="bakery_dining">bakery_dining</span>
</div>
<div class="flex-1">
<p class="font-headline font-bold text-on-background">Combo Desayuno Artesanal</p>
<p class="text-sm text-on-surface-variant">Café del Parque</p>
</div>
<div class="text-right">
<p class="font-headline font-bold text-on-background">-1</p>
<p class="text-xs text-on-surface-variant">Ayer, 09:15 AM</p>
</div>
</div>
</div>
</div>
<!-- Featured "Freshness" Section (1/3 width) -->
<div class="lg:col-span-1">
<div class="mb-6 px-2">
<h4 class="text-xl font-headline font-bold text-on-background">Sugerencia del día</h4>
</div>
<div class="bg-surface-container-low rounded-[2rem] p-2 flex flex-col gap-4">
<div class="relative h-48 w-full rounded-[1.8rem] overflow-hidden">
<img alt="Salad plate" class="w-full h-full object-cover" data-alt="vibrant gourmet salad bowl with fresh greens, grilled salmon, and citrus dressing on a rustic wooden table" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDRWCPCR71-81hVJoJQNPsDw8JWjVlr3C0npYS9FYvAqt4ZG4W8uaeugKrVmWwH07UgP49TZAhXI5mQ-vrtjgkcJFd9i3cp241m6tCMe1JeCNdCglkT-8APsxY1cwe9p2_I1YjIyqFa9bBKxZ1YoKhO8JuaucCI6spc3JvlMn6XzcqDYAdcEXlR3KQOuZLa_8HFoVWmMXJ9mPsD2rOh8eIKwnnmkAXJJ522njLHGM9YFDtRjT2A2bADxIRYbDOS_X7UKLKMIGVCYkg"/>
<div class="absolute top-4 left-4">
<span class="bg-tertiary-container text-on-tertiary-container text-[10px] font-bold uppercase tracking-widest px-3 py-1 rounded-full">En Stock</span>
</div>
</div>
<div class="px-4 pb-6 pt-2">
<h5 class="text-lg font-headline font-bold text-on-background mb-1">Poke Bowl de Salmón</h5>
<p class="text-sm text-on-surface-variant mb-6 leading-relaxed">Arroz integral, aguacate fresco y edamame con aderezo cítrico.</p>
<a href="{{ route('menu-digital') }}" class="w-full bg-primary-container text-on-primary-container font-headline font-bold py-4 rounded-2xl hover:scale-[1.02] active:scale-95 transition-all shadow-lg shadow-primary-container/10 flex items-center justify-center">
    Pedir ahora
</a>
</div>
</div>
</div>
</div>
</main>
<!-- Botón flotante QR de Canje -->
<a href="{{ route('canje') }}" class="fixed bottom-8 right-8 w-16 h-16 bg-primary-container text-on-primary-container rounded-full shadow-[0px_20px_40px_rgba(18,31,5,0.2)] flex items-center justify-center group hover:scale-110 active:scale-95 transition-all z-50">
<span class="material-symbols-outlined text-3xl" data-icon="qr_code_2">qr_code_2</span>
<div class="absolute right-full mr-4 bg-inverse-surface text-white text-sm font-bold py-2 px-4 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none">
    Generar QR de Canje
</div>
</a>
</body></html>
