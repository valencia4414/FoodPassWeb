<!DOCTYPE html>
<html lang="es">
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
<body class="bg-fp-light font-sans text-slate-800 antialiased flex h-screen overflow-hidden">

    @include('partials.sidebar')

    <!-- Main Content -->
    <div class="ml-64 flex-1 flex flex-col overflow-hidden">
        <main class="flex-1 overflow-y-auto p-6 lg:p-10 relative">
            <div class="max-w-6xl mx-auto" x-data="qrApp()">
                <div class="mb-8">
                    <h1 class="text-3xl lg:text-4xl font-extrabold text-fp-dark tracking-tight mb-2">Canje y Validación QR</h1>
                    <p class="text-gray-600 max-w-2xl text-base leading-relaxed">
                        Genera tu código QR para redimir tu beneficio alimentario SENA. 
                        Puedes generar los códigos que necesites en el mismo día; todos tienen <strong>vigencia estricta hasta las 12:00 AM (medianoche)</strong>.
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
                                        <span class="w-2 h-2 bg-red-500 rounded-full mr-2"></span> No asignado como beneficiario SENA
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-4 py-2 rounded-full bg-green-100 text-green-700 text-sm font-bold border border-green-200">
                                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-ping"></span> Beneficio Activo — Generación Ilimitada
                                    </span>
                                @endif
                            </div>

                            <!-- Código QR Renderizado -->
                            @php
                                $qrData = $ultimoQr ?? ('FP-USER-' . $user->id . '-' . now()->format('Ymd'));
                            @endphp
                            <div class="bg-fp-qr p-7 rounded-3xl mb-5 relative z-10 border border-green-200/60 shadow-inner flex flex-col items-center">
                                <div class="bg-white p-4 rounded-2xl shadow-md border border-gray-100">
                                    {!! QrCode::size(210)->generate($qrData) !!}
                                </div>
                                <div class="mt-3 text-center">
                                    <span class="text-[11px] font-mono font-bold text-fp-dark bg-white/70 px-3 py-1 rounded-full border border-green-200">
                                        {{ $qrData }}
                                    </span>
                                </div>
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
                                        <span class="text-xs font-bold uppercase tracking-wider text-white/80">Vigencia Estricta</span>
                                    </div>
                                    <span class="text-xs font-semibold text-white/70">Expira 12:00 AM</span>
                                </div>
                                <div class="flex items-center justify-center gap-2 py-1">
                                    <span class="text-2xl font-black font-mono tracking-widest text-[#F97F2D]" x-text="countdown"></span>
                                    <span class="text-xs text-white/60">restantes hoy</span>
                                </div>
                                <p class="text-[11px] text-white/60 mt-1">A medianoche el código deja de ser válido automáticamente.</p>
                            </div>

                            <!-- Botón Generar Nuevo QR (Sin límite) -->
                            <form id="form-canje" action="{{ route('canje.store') }}" method="POST" class="w-full max-w-md">
                                @csrf
                                <button type="button" id="btnCanjear" 
                                    class="w-full font-bold text-base py-4 px-8 rounded-2xl shadow-lg transition-all transform flex items-center justify-center gap-2
                                    {{ !$esBeneficiario ? 'bg-gray-300 text-gray-500 cursor-not-allowed' : 'bg-fp-orange hover:bg-fp-orangeHover text-white hover:-translate-y-0.5 active:translate-y-0 shadow-orange-500/20 cursor-pointer' }}"
                                    {{ !$esBeneficiario ? 'disabled' : '' }}>
                                    <span class="material-symbols-outlined text-[22px]">qr_code_scanner</span>
                                    <span>Generar Nuevo Código QR</span>
                                </button>
                                <p class="text-center text-xs text-gray-500 mt-2 font-medium">
                                    Puedes generar tantos códigos QR como requieras en el mismo día.
                                </p>
                            </form>

                            <!-- Botón Enviar QR al Correo (Requisito 3) -->
                            @php
                                $tieneQrGenerado = $canjesHoy->where('estado', 'pendiente')->isNotEmpty() || !empty($ultimoQr);
                            @endphp
                            @if($esBeneficiario && $tieneQrGenerado)
                                <form id="form-enviar-correo" action="{{ route('canje.enviar-correo') }}" method="POST" class="w-full max-w-md mt-4">
                                    @csrf
                                    <button type="button" id="btnEnviarCorreo"
                                        class="w-full font-bold text-base py-3.5 px-8 rounded-2xl shadow-md transition-all flex items-center justify-center gap-2.5 bg-[#283618] hover:bg-[#1e2a11] text-white hover:-translate-y-0.5 active:translate-y-0 cursor-pointer border border-[#2F9B34]/30">
                                        <span class="material-symbols-outlined text-[22px] text-[#2F9B34]">mail</span>
                                        <span>Enviar QR al correo</span>
                                    </button>
                                    <p class="text-center text-[11px] text-gray-500 mt-2 font-medium">
                                        Destino: <strong class="text-gray-700">{{ $user->email }}</strong> (adjunto PNG e incrustado en el cuerpo).
                                    </p>
                                </form>
                            @elseif($esBeneficiario)
                                <div class="w-full max-w-md mt-4 p-3 bg-amber-50 border border-amber-200 rounded-2xl text-amber-800 text-xs text-center flex items-center justify-center gap-2">
                                    <span class="material-symbols-outlined text-amber-600 text-base">info</span>
                                    <span>Genera tu primer código QR para habilitar el envío a tu correo.</span>
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
                                {{ $esBeneficiario ? 'SENA BENEFICIARIO ACTIVO' : 'SIN BENEFICIO ACTIVO' }}
                            </h2>
                            <p class="text-xs text-white/70 leading-relaxed mb-4">
                                Acceso garantizado al almuerzo diario mediante validación en restaurante aliado.
                            </p>
                            <div class="pt-3 border-t border-white/10 flex items-center justify-between text-xs text-white/60">
                                <span>Fecha actual:</span>
                                <span class="font-bold text-white">{{ now()->format('d/m/Y') }}</span>
                            </div>
                        </div>

                        <!-- Lista de QR generados hoy -->
                        <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-gray-100">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                                    <span class="material-symbols-outlined text-fp-orange text-[20px]">history</span>
                                    QR Generados Hoy ({{ $canjesHoy->count() }})
                                </h3>
                                <span class="text-[11px] font-bold text-gray-400 uppercase">Sin límite diario</span>
                            </div>

                            <div class="space-y-3 max-h-56 overflow-y-auto pr-1">
                                @forelse($canjesHoy as $canje)
                                    <div class="p-3 bg-gray-50 hover:bg-gray-100/80 rounded-xl transition-colors border border-gray-100 flex items-center justify-between">
                                        <div class="overflow-hidden mr-2">
                                            <p class="font-mono font-bold text-gray-800 text-xs truncate">
                                                {{ $canje->codigo_qr ?? ('QR-' . $canje->id) }}
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
                                            {{ $canje->estado }}
                                        </span>
                                    </div>
                                @empty
                                    <div class="text-center py-6 text-gray-400">
                                        <span class="material-symbols-outlined text-3xl mb-1 text-gray-300">qr_code_2</span>
                                        <p class="text-xs">Aún no has generado ningún código QR hoy.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <!-- Reglas de Funcionamiento -->
                        <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-gray-100">
                            <h3 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
                                <span class="material-symbols-outlined text-blue-600 text-[20px]">info</span>
                                Reglas de Canje y Vigencia
                            </h3>
                            <div class="space-y-3 text-xs text-gray-600">
                                <div class="flex items-start gap-2.5">
                                    <span class="w-5 h-5 rounded-full bg-fp-light text-fp-dark flex items-center justify-center font-bold text-[11px] shrink-0 mt-0.5">1</span>
                                    <p>Genera tu código QR en cualquier momento del día cuantas veces lo requieras.</p>
                                </div>
                                <div class="flex items-start gap-2.5">
                                    <span class="w-5 h-5 rounded-full bg-fp-light text-fp-dark flex items-center justify-center font-bold text-[11px] shrink-0 mt-0.5">2</span>
                                    <p>Presenta el código en la cafetería SENA para su validación.</p>
                                </div>
                                <div class="flex items-start gap-2.5">
                                    <span class="w-5 h-5 rounded-full bg-fp-light text-fp-dark flex items-center justify-center font-bold text-[11px] shrink-0 mt-0.5">3</span>
                                    <p><strong>Expiración estricta a las 12:00 AM (medianoche)</strong> de cada día. Al iniciar el nuevo día, deberás generar un nuevo código.</p>
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
                title: '¿Generar nuevo código QR?',
                text: "Se generará un código QR válido hasta las 12:00 AM (medianoche) de hoy. Puedes generar cuantos requieras.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#f97316',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sí, generar QR',
                cancelButtonText: 'Cancelar',
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
                title: '¿Enviar QR al correo?',
                html: 'Tu credencial QR activa será enviada a:<br><strong class="text-[#2F9B34] font-mono text-sm">{{ $user->email }}</strong><br><br><small class="text-gray-500">Se adjuntará imagen PNG y se incrustará en el cuerpo del mensaje. No se descuenta ración extra.</small>',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#283618',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sí, enviar ahora',
                cancelButtonText: 'Cancelar',
                reverseButtons: true,
                customClass: {
                    popup: 'rounded-[2rem]'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Enviando credencial...',
                        text: 'Procesando el envío de tu código QR...',
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
