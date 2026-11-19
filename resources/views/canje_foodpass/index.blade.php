<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Canje QR - FoodPass</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        fp: {
                            dark: '#273517',
                            light: '#f0ffd8',
                            qr: '#e8facd',
                            orange: '#f97316',
                            orangeHover: '#ea580c'
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="bg-fp-light font-sans text-slate-800 antialiased flex h-screen min-h-screen overflow-hidden">

    @include('partials.sidebar')

    <!-- Main Content -->
    <div class="ml-64 flex-1 min-w-0 flex flex-col overflow-hidden">
        <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-10 relative">
            <div class="max-w-6xl mx-auto" x-data="qrApp()">
                <div class="mb-8">
                    <h1 class="text-3xl lg:text-4xl font-extrabold text-fp-dark tracking-tight mb-2">{{ __('ui.qr_page_title') }}</h1>
                    <p class="text-gray-600 max-w-2xl text-base leading-relaxed">
                        {{ __('ui.qr_page_intro') }}
                    </p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
                    
                    <!-- Columna Principal: QR Actual y Botón de Generación -->
                    <div class="lg:col-span-3">
                        <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100 flex flex-col items-center justify-center relative overflow-hidden">
                            
                            <!-- Indicador de Elegibilidad -->
                            <div class="mb-5 relative z-10">
                                @if(!$esBeneficiario)
                                    <span class="inline-flex items-center px-4 py-2 rounded-full bg-red-100 text-red-700 text-sm font-bold border border-red-200">
                                        <span class="w-2 h-2 bg-red-500 rounded-full mr-2"></span> {{ __('ui.not_assigned_beneficiary') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-4 py-2 rounded-full bg-green-100 text-green-700 text-sm font-bold border border-green-200">
                                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-ping"></span> {{ __('ui.active_qr_status') }}
                                    </span>
                                @endif
                            </div>

                            <!-- Código QR Renderizado -->
                            @php
                                $qrData = $ultimoQr;
                            @endphp
                            <div class="bg-fp-qr p-4 sm:p-7 rounded-3xl mb-5 relative z-10 border border-green-200/60 shadow-inner flex flex-col items-center">
                                @if($qrData)
                                    <div class="bg-white p-3 sm:p-4 rounded-2xl shadow-md border border-gray-100" aria-label="{{ __('ui.active_qr_code') }}">
                                        {!! QrCode::size(210)->margin(2)->generate($qrData) !!}
                                    </div>
                                    <p class="mt-3 text-center text-xs text-fp-dark font-semibold">{{ __('ui.single_use_qr') }}</p>
                                @else
                                    <div class="w-[210px] h-[210px] bg-white rounded-2xl shadow-md border border-gray-100 flex flex-col items-center justify-center text-gray-400 text-center p-5">
                                        <span class="material-symbols-outlined text-5xl mb-2">qr_code_2</span>
                                        <span class="text-sm font-semibold">{{ __('ui.generate_qr_prompt') }}</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Información de Usuario -->
                            <div class="inline-flex items-center bg-gray-50 border border-gray-100 rounded-full px-5 py-1.5 mb-5">
                                <span class="font-bold text-gray-700 tracking-wider text-sm font-mono">
                                    APRENDIZ: {{ strtoupper($user->nombre ?? $user->name) }} (ID: #{{ str_pad($user->id, 4, '0', STR_PAD_LEFT) }})
                                </span>
                            </div>

                            <!-- Temporizador de Vigencia (12:00 AM) -->
                            <div class="mb-6 text-center w-full max-w-md bg-gradient-to-r from-[#273517] to-[#3d5224] text-white p-4 rounded-2xl shadow-md">
                                <div class="flex items-center justify-between mb-1">
                                    <div class="flex items-center gap-2">
                                        <span class="material-symbols-outlined text-[#F97F2D] text-[20px]">schedule</span>
                                        <span class="text-xs font-bold uppercase tracking-wider text-white/80">{{ __('ui.validity') }}</span>
                                    </div>
                                    <span class="text-xs font-semibold text-white/70">{{ __('ui.expires_midnight') }}</span>
                                </div>
                                <div class="flex items-center justify-center gap-2 py-1">
                                    <span class="text-2xl font-black font-mono tracking-widest text-[#F97F2D]" x-text="countdown"></span>
                                    <span class="text-xs text-white/60">{{ __('ui.remaining_today') }}</span>
                                </div>
                                <p class="text-[11px] text-white/60 mt-1">{{ __('ui.midnight_expiration') }}</p>
                            </div>

                            <!-- Botón Generar Nuevo QR (Sin límite) -->
                            <form id="form-canje" action="{{ route('canje.store') }}" method="POST" class="w-full max-w-md">
                                @csrf
                                <button type="button" id="btnCanjear" 
                                    class="w-full font-bold text-base py-4 px-8 rounded-2xl shadow-lg transition-all transform flex items-center justify-center gap-2
                                    {{ !$esBeneficiario ? 'bg-gray-300 text-gray-500 cursor-not-allowed' : 'bg-fp-orange hover:bg-fp-orangeHover text-white hover:-translate-y-0.5 active:translate-y-0 shadow-orange-500/20 cursor-pointer' }}"
                                    {{ !$esBeneficiario ? 'disabled' : '' }}>
                                    <span class="material-symbols-outlined text-[22px]">qr_code_scanner</span>
                                    <span>{{ __('ui.generate_new_qr') }}</span>
                                </button>
                                <p class="text-center text-xs text-gray-500 mt-2 font-medium">
                                    {{ __('ui.unlimited_qr_note') }}
                                </p>
                            </form>

                            <!-- Botón Enviar QR al Correo (Requisito 3) -->
                            @php
                                $tieneQrGenerado = !empty($ultimoQr);
                            @endphp
                            @if($esBeneficiario && $tieneQrGenerado)
                                <form id="form-enviar-correo" action="{{ route('canje.enviar-correo') }}" method="POST" class="w-full max-w-md mt-4">
                                    @csrf
                                    <button type="button" id="btnEnviarCorreo"
                                        class="w-full font-bold text-base py-3.5 px-8 rounded-2xl shadow-md transition-all flex items-center justify-center gap-2.5 bg-[#283618] hover:bg-[#1e2a11] text-white hover:-translate-y-0.5 active:translate-y-0 cursor-pointer border border-[#2F9B34]/30">
                                        <span class="material-symbols-outlined text-[22px] text-[#2F9B34]">mail</span>
                                        <span>{{ __('ui.send_qr_email') }}</span>
                                    </button>
                                    <p class="text-center text-[11px] text-gray-500 mt-2 font-medium">
                                        {{ __('ui.email_destination') }} <strong class="text-gray-700">{{ $user->email }}</strong> {{ __('ui.email_qr_attachment') }}
                                    </p>
                                </form>
                            @elseif($esBeneficiario)
                                <div class="w-full max-w-md mt-4 p-3 bg-amber-50 border border-amber-200 rounded-2xl text-amber-800 text-xs text-center flex items-center justify-center gap-2">
                                    <span class="material-symbols-outlined text-amber-600 text-base">info</span>
                                    <span>{{ __('ui.generate_qr_before_email') }}</span>
                                </div>
                            @endif

                            @if(session('success'))
                                <div class="w-full max-w-md mt-4 p-3 bg-green-50 border border-green-200 rounded-xl text-green-700 text-xs font-semibold flex items-center gap-2">
                                    <span class="material-symbols-outlined text-green-600 text-[18px]">check_circle</span>
                                    <span>{{ session('success') }}</span>
                                </div>
                            @endif

                            @if(session('error'))
                                <div class="w-full max-w-md mt-4 p-3 bg-red-50 border border-red-200 rounded-xl text-red-700 text-xs font-semibold flex items-center gap-2">
                                    <span class="material-symbols-outlined text-red-600 text-[18px]">error</span>
                                    <span>{{ session('error') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Columna Lateral: Estado e Historial de QR del Día -->
                    <div class="lg:col-span-2 space-y-6">
                        
                        <!-- Tarjeta Estado SENA -->
                        <div class="bg-fp-dark rounded-[2rem] p-7 shadow-xl relative overflow-hidden text-white">
                            <div class="flex items-center justify-between mb-3">
                                <p class="text-white/60 text-xs font-bold tracking-widest uppercase">Programa Alimentario</p>
                                <span class="material-symbols-outlined text-[#F97F2D] text-[24px]">verified</span>
                            </div>
                            <h2 class="text-2xl font-extrabold tracking-tight mb-2">
                                {{ $esBeneficiario ? 'BENEFICIARIO ACTIVO' : 'INACTIVO' }}
                            </h2>
                            <p class="text-xs text-white/70 leading-relaxed mb-4">
                                {{ $esBeneficiario ? __('ui.active_benefit_description') : __('ui.benefit_not_active') }}
                            </p>
                            <div class="pt-3 border-t border-white/10 flex items-center justify-between text-xs text-white/60">
                                <span>{{ __('ui.current_date') }}</span>
                                <span class="font-bold text-white">{{ now()->format('d/m/Y') }}</span>
                            </div>
                        </div>

                        <!-- Lista de QR generados hoy -->
                        <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-gray-100">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                                    <span class="material-symbols-outlined text-fp-orange text-[20px]">history</span>
                                    {{ __('ui.qr_generated_today', ['count' => $canjesHoy->count()]) }}
                                </h3>
                                <span class="text-[11px] font-bold text-gray-400 uppercase">{{ __('ui.no_daily_limit') }}</span>
                            </div>

                            <div class="space-y-3 max-h-56 overflow-y-auto pr-1">
                                @forelse($canjesHoy as $canje)
                                    <div class="p-3 bg-gray-50 hover:bg-gray-100/80 rounded-xl transition-colors border border-gray-100 flex items-center justify-between">
                                        <div class="overflow-hidden mr-2">
                                            <p class="font-mono font-bold text-gray-800 text-xs truncate">
                                                QR #{{ str_pad($canje->id, 4, '0', STR_PAD_LEFT) }}
                                            </p>
                                            <p class="text-gray-400 text-[10px] flex items-center gap-1 mt-0.5">
                                                <span class="material-symbols-outlined text-[12px]">schedule</span>
                                                Generado: {{ $canje->creado_en ? $canje->creado_en->format('h:i A') : now()->format('h:i A') }}
                                            </p>
                                        </div>
                                        <span class="px-2.5 py-1 text-[10px] font-bold rounded-lg shrink-0 uppercase
                                            @if($canje->estado == 'exitoso' || $canje->estado == 'entregado') bg-green-100 text-green-700
                                            @elseif($canje->estado == 'expirado') bg-red-100 text-red-700
                                            @else bg-yellow-100 text-yellow-700 @endif">
                                            {{ __('ui.status_' . $canje->estado) }}
                                        </span>
                                    </div>
                                @empty
                                    <div class="text-center py-6 text-gray-400">
                                        <span class="material-symbols-outlined text-3xl mb-1 text-gray-300">qr_code_2</span>
                                        <p class="text-xs">{{ __('ui.no_qr_today') }}</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <!-- Reglas de Funcionamiento -->
                        <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-gray-100">
                            <h3 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
                                <span class="material-symbols-outlined text-blue-600 text-[20px]">info</span>
                                {{ __('ui.qr_rules_title') }}
                            </h3>
                            <div class="space-y-3 text-xs text-gray-600">
                                <div class="flex items-start gap-2.5">
                                    <span class="w-5 h-5 rounded-full bg-fp-light text-fp-dark flex items-center justify-center font-bold text-[11px] shrink-0 mt-0.5">1</span>
                                    <p>{{ __('ui.qr_rule_generate') }}</p>
                                </div>
                                <div class="flex items-start gap-2.5">
                                    <span class="w-5 h-5 rounded-full bg-fp-light text-fp-dark flex items-center justify-center font-bold text-[11px] shrink-0 mt-0.5">2</span>
                                    <p>{{ __('ui.qr_rule_present') }}</p>
                                </div>
                                <div class="flex items-start gap-2.5">
                                    <span class="w-5 h-5 rounded-full bg-fp-light text-fp-dark flex items-center justify-center font-bold text-[11px] shrink-0 mt-0.5">3</span>
                                    <p>{{ __('ui.qr_rule_expire') }}</p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Script de Confirmación y Cuenta Regresiva -->
    <script>
        document.getElementById('btnCanjear')?.addEventListener('click', function() {
            if (this.disabled) return;
            Swal.fire({
                title: @json(__('ui.generate_qr_question')),
                text: @json(__('ui.generate_qr_confirmation')),
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#f97316',
                cancelButtonColor: '#64748b',
                confirmButtonText: @json(__('ui.confirm_generate_qr')),
                cancelButtonText: @json(__('ui.cancel')),
                reverseButtons: true,
                customClass: {
                    popup: 'rounded-[2rem]'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('form-canje').submit();
                }
            });
        });

        document.getElementById('btnEnviarCorreo')?.addEventListener('click', function() {
            Swal.fire({
                title: @json(__('ui.send_qr_question')),
                html: @json(__('ui.send_qr_confirmation', ['email' => $user->email])),
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#283618',
                cancelButtonColor: '#64748b',
                confirmButtonText: @json(__('ui.confirm_send_qr')),
                cancelButtonText: @json(__('ui.cancel')),
                reverseButtons: true,
                customClass: {
                    popup: 'rounded-[2rem]'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: @json(__('ui.sending_qr')),
                        text: @json(__('ui.processing_qr_email')),
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    document.getElementById('form-enviar-correo').submit();
                }
            });
        });

        function qrApp() {
            return {
                countdown: '00:00:00',
                init() {
                    this.calcular();
                    setInterval(() => this.calcular(), 1000);
                },
                calcular() {
                    const ahora = new Date();
                    const medianoche = new Date(ahora);
                    medianoche.setHours(24, 0, 0, 0); // 12:00 AM del día siguiente
                    const diferencia = medianoche - ahora;

                    if (diferencia <= 0) {
                        this.countdown = '00:00:00';
                        location.reload();
                        return;
                    }

                    const horas = Math.floor(diferencia / (1000 * 60 * 60));
                    const minutos = Math.floor((diferencia % (1000 * 60 * 60)) / (1000 * 60));
                    const segundos = Math.floor((diferencia % (1000 * 60)) / 1000);

                    this.countdown = 
                        String(horas).padStart(2, '0') + ':' +
                        String(minutos).padStart(2, '0') + ':' +
                        String(segundos).padStart(2, '0');
                }
            }
        }
    </script>
</body>
</html>
