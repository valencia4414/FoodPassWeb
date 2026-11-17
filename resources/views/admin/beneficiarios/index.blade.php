<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Padrón de Beneficiarios - Administración FoodPass SENA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #F8F9FA; color: #1E293B; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="flex h-screen overflow-hidden" x-data="{ modalNuevo: false, modalSubir: false, modalInfoColumnas: false }">

    @include('partials.sidebar')

    <div class="ml-64 flex-1 flex flex-col h-screen overflow-hidden">
        
        <!-- Header -->
        <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-8 shrink-0 z-10 shadow-sm">
            <div class="flex items-center gap-3">
                <span class="px-3 py-1 rounded-full bg-[#283618] text-white text-xs font-bold uppercase tracking-wider">Gestión SENA</span>
                <h1 class="text-base font-bold text-[#283618]">Padrón Oficial de Beneficiarios (Apoyo Alimentario)</h1>
            </div>

            <div class="flex items-center gap-3">
                <button type="button" @click="modalInfoColumnas = true"
                        class="px-3.5 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                    <span class="material-symbols-outlined text-[18px]">help</span>
                    <span>Guía de Archivo</span>
                </button>

                <button type="button" @click="modalSubir = true"
                        class="px-4 py-2 rounded-xl bg-white border border-[#283618] text-[#283618] hover:bg-green-50 text-xs font-bold flex items-center gap-1.5 transition-colors shadow-sm">
                    <span class="material-symbols-outlined text-[18px]">upload_file</span>
                    <span>Cargar Listado (.xlsx / .ods)</span>
                </button>

                <button type="button" @click="modalNuevo = true"
                        class="px-4 py-2 rounded-xl bg-[#F97F2D] hover:bg-[#e06d20] text-white text-xs font-bold flex items-center gap-1.5 shadow-sm transition-colors">
                    <span class="material-symbols-outlined text-[18px]">person_add</span>
                    <span>Registrar Aprendiz</span>
                </button>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto p-6 lg:p-8">
            <div class="max-w-7xl mx-auto space-y-6 pb-16">

                <!-- Mensajes Flash -->
                @if(session('success'))
                    <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-300 text-emerald-900 text-xs font-semibold flex items-center gap-2.5 shadow-sm">
                        <span class="material-symbols-outlined text-emerald-600">check_circle</span>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="p-4 rounded-2xl bg-rose-50 border border-rose-300 text-rose-900 text-xs font-semibold flex items-center gap-2.5 shadow-sm">
                        <span class="material-symbols-outlined text-rose-600">error</span>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                <!-- Tarjetas de Resumen -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-white rounded-3xl p-5 shadow-sm border border-gray-100 flex items-center justify-between">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wider text-gray-500">Total en Padrón</p>
                            <p class="text-3xl font-extrabold text-[#283618] mt-1">{{ $totalBeneficiarios }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">Aprendices en base de datos</p>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-gray-100 flex items-center justify-center text-gray-600">
                            <span class="material-symbols-outlined text-2xl">groups</span>
                        </div>
                    </div>

                    <div class="bg-white rounded-3xl p-5 shadow-sm border border-gray-100 flex items-center justify-between">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wider text-emerald-700">Beneficiarios Activos</p>
                            <p class="text-3xl font-extrabold text-emerald-600 mt-1">{{ $totalActivos }}</p>
                            <p class="text-xs text-emerald-700 font-medium mt-0.5">Autorizados con credencial QR</p>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                            <span class="material-symbols-outlined text-2xl">how_to_reg</span>
                        </div>
                    </div>

                    <div class="bg-white rounded-3xl p-5 shadow-sm border border-gray-100 flex items-center justify-between">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wider text-amber-700">Inactivos / Fuera de Lista</p>
                            <p class="text-3xl font-extrabold text-amber-600 mt-1">{{ $totalInactivos }}</p>
                            <p class="text-xs text-amber-700 font-medium mt-0.5">Historial protegido, sin QR</p>
                        </div>
                        <div class="w-12 h-12 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-600">
                            <span class="material-symbols-outlined text-2xl">person_off</span>
                        </div>
                    </div>
                </div>

                <!-- Historial de Cargas Masivas -->
                @if(isset($historialCargas) && $historialCargas->count() > 0)
                    <div class="bg-white rounded-3xl p-5 shadow-sm border border-gray-100">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-[#283618] text-[20px]">history_edu</span>
                                <h3 class="text-xs font-bold uppercase tracking-wider text-[#283618]">Historial de Cargas de Padrón (.xlsx / .ods)</h3>
                            </div>
                            <span class="text-[11px] text-gray-400">Últimas 10 cargas registradas</span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse text-xs">
                                <thead>
                                    <tr class="bg-gray-50 text-gray-500 uppercase border-b border-gray-200 text-[10px]">
                                        <th class="py-2.5 px-4 font-bold">Fecha / Hora</th>
                                        <th class="py-2.5 px-4 font-bold">Archivo Cargado</th>
                                        <th class="py-2.5 px-4 font-bold text-center">Formato</th>
                                        <th class="py-2.5 px-4 font-bold text-center">Filas</th>
                                        <th class="py-2.5 px-4 font-bold text-center">Activados</th>
                                        <th class="py-2.5 px-4 font-bold text-center">Inactivados</th>
                                        <th class="py-2.5 px-4 font-bold text-center">Inconsistencias</th>
                                        <th class="py-2.5 px-4 font-bold">Ejecutado Por</th>
                                        <th class="py-2.5 px-4 font-bold text-right">Copia</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($historialCargas as $c)
                                        <tr class="hover:bg-gray-50/70 transition-colors">
                                            <td class="py-2.5 px-4 text-gray-700 whitespace-nowrap">
                                                {{ $c->created_at ? $c->created_at->format('d/m/Y H:i') : 'N/A' }}
                                            </td>
                                            <td class="py-2.5 px-4 font-semibold text-gray-900">
                                                <span class="inline-flex items-center gap-1.5">
                                                    <span class="material-symbols-outlined text-[16px] text-[#283618]">table_chart</span>
                                                    <span>{{ $c->nombre_archivo_original }}</span>
                                                </span>
                                            </td>
                                            <td class="py-2.5 px-4 text-center">
                                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase bg-gray-100 text-gray-700">
                                                    {{ $c->formato }}
                                                </span>
                                            </td>
                                            <td class="py-2.5 px-4 text-center font-bold text-gray-800">
                                                {{ $c->total_filas_leidas }}
                                            </td>
                                            <td class="py-2.5 px-4 text-center">
                                                <span class="inline-flex items-center gap-1 font-bold text-emerald-700">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                    +{{ $c->aprendices_activados }}
                                                </span>
                                            </td>
                                            <td class="py-2.5 px-4 text-center">
                                                <span class="inline-flex items-center gap-1 font-semibold text-amber-700">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                    {{ $c->aprendices_inactivados }}
                                                </span>
                                            </td>
                                            <td class="py-2.5 px-4 text-center">
                                                @if($c->errores_count > 0)
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700"
                                                          title="{{ json_encode($c->errores_detalle) }}">
                                                        {{ $c->errores_count }} omitidas
                                                    </span>
                                                @else
                                                    <span class="text-gray-400 font-medium">0</span>
                                                @endif
                                            </td>
                                            <td class="py-2.5 px-4 text-gray-600 text-[11px]">
                                                {{ $c->ejecutado_por }}
                                            </td>
                                            <td class="py-2.5 px-4 text-right">
                                                <a href="{{ route('admin.beneficiarios.cargas.descargar', $c->id) }}"
                                                   title="Descargar copia del archivo original"
                                                   class="inline-flex items-center gap-1 text-[11px] font-bold text-[#283618] hover:text-[#F97F2D] transition-colors">
                                                    <span class="material-symbols-outlined text-[16px]">download</span>
                                                    <span>Bpal</span>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <!-- Barra de Búsqueda y Filtros -->
                <div class="bg-white rounded-3xl p-4 shadow-sm border border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-3">
                    <form action="{{ route('admin.beneficiarios') }}" method="GET" class="flex-1 flex items-center gap-2">
                        <div class="relative flex-1">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-[20px]">search</span>
                            <input type="text" name="buscar" value="{{ $busqueda }}" placeholder="Buscar por nombre, correo, documento o ficha..."
                                   class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 text-xs focus:outline-none focus:border-[#F97F2D]">
                        </div>

                        <select name="estado" onchange="this.form.submit()" class="px-3 py-2.5 rounded-xl border border-gray-200 text-xs bg-white text-gray-700">
                            <option value="todos" {{ $filtroEstado === 'todos' ? 'selected' : '' }}>Todos los estados</option>
                            <option value="activos" {{ $filtroEstado === 'activos' ? 'selected' : '' }}>Solo Activos</option>
                            <option value="inactivos" {{ $filtroEstado === 'inactivos' ? 'selected' : '' }}>Solo Inactivos</option>
                        </select>

                        <button type="submit" class="px-4 py-2.5 rounded-xl bg-[#283618] text-white text-xs font-bold hover:bg-black transition-colors">
                            Buscar
                        </button>

                        @if($busqueda || $filtroEstado)
                            <a href="{{ route('admin.beneficiarios') }}" class="px-3 py-2.5 rounded-xl border border-gray-200 text-gray-500 hover:text-gray-800 text-xs font-bold">
                                Limpiar
                            </a>
                        @endif
                    </form>
                </div>

                <!-- Tabla de Beneficiarios -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-gray-50 text-gray-500 uppercase border-b border-gray-200 text-[11px]">
                                    <th class="py-3.5 px-6 font-bold">Aprendiz / Documento</th>
                                    <th class="py-3.5 px-6 font-bold">Correo Institucional</th>
                                    <th class="py-3.5 px-6 font-bold">Ficha / Programa</th>
                                    <th class="py-3.5 px-6 font-bold text-center">Estado (Beneficio QR)</th>
                                    <th class="py-3.5 px-6 font-bold text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($beneficiarios as $b)
                                    <tr class="hover:bg-gray-50/70 transition-colors">
                                        <td class="py-3.5 px-6">
                                            <div class="font-bold text-gray-900 text-sm">{{ $b->nombre_completo }}</div>
                                            <div class="font-mono text-gray-500 text-[11px]">Doc: {{ $b->numero_documento ?: 'No registrado' }}</div>
                                        </td>
                                        <td class="py-3.5 px-6 text-gray-700 font-medium">
                                            {{ $b->email }}
                                        </td>
                                        <td class="py-3.5 px-6 text-gray-600">
                                            <span class="font-bold text-[#283618]">Ficha {{ $b->ficha ?: '3173467' }}</span>
                                            <div class="text-[10px] text-gray-400">{{ $b->programa ?: 'ADSO - CEAI' }}</div>
                                        </td>
                                        <td class="py-3.5 px-6 text-center">
                                            @if($b->activo)
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">
                                                    <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                                                    ACTIVO (Tiene QR)
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-gray-100 text-gray-600">
                                                    <span class="w-2 h-2 rounded-full bg-gray-400"></span>
                                                    INACTIVO (Sin QR)
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-6 text-right space-x-2">
                                            <!-- Botón Activar / Inactivar -->
                                            <form action="{{ route('admin.beneficiarios.toggle', $b->id) }}" method="POST" class="inline-block">
                                                @csrf
                                                @if($b->activo)
                                                    <button type="submit" title="Desactivar beneficio (oculta el QR)"
                                                            class="px-3 py-1.5 rounded-xl border border-amber-300 bg-amber-50 hover:bg-amber-100 text-amber-800 text-xs font-bold transition-colors">
                                                        Inactivar
                                                    </button>
                                                @else
                                                    <button type="submit" title="Activar beneficio (habilita el QR)"
                                                            class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-colors shadow-sm">
                                                        Activar
                                                    </button>
                                                @endif
                                            </form>

                                            <!-- Botón Eliminar -->
                                            <form action="{{ route('admin.beneficiarios.destroy', $b->id) }}" method="POST" class="inline-block"
                                                  onsubmit="return confirm('¿Seguro que deseas eliminar a {{ addslashes($b->nombre_completo) }} de la lista de beneficiarios?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Eliminar de la lista"
                                                        class="p-1.5 text-gray-400 hover:text-rose-600 transition-colors">
                                                    <span class="material-symbols-outlined text-[18px]">delete</span>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-12 text-center text-gray-400 text-sm">
                                            No se encontraron aprendices con los filtros seleccionados.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($beneficiarios->hasPages())
                        <div class="p-4 border-t border-gray-100">
                            {{ $beneficiarios->withQueryString()->links() }}
                        </div>
                    @endif
                </div>

            </div>
        </main>
    </div>

    <!-- MODAL 1: REGISTRAR BENEFICIARIO MANUAL -->
    <div x-show="modalNuevo" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4" @click.away="modalNuevo = false">
            <div class="flex items-center justify-between border-b pb-3">
                <h3 class="text-base font-extrabold text-[#283618]">Registrar Aprendiz Beneficiario</h3>
                <button type="button" @click="modalNuevo = false" class="text-gray-400 hover:text-gray-700">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form action="{{ route('admin.beneficiarios.store') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Nombre completo del aprendiz</label>
                    <input type="text" name="nombre_completo" required placeholder="Ej: Santiago Valencia"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs focus:outline-none focus:border-[#F97F2D]">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Correo institucional (@soy.sena.edu.co o Gmail)</label>
                    <input type="email" name="email" required placeholder="aprendiz@soy.sena.edu.co"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs focus:outline-none focus:border-[#F97F2D]">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">No. Documento</label>
                        <input type="text" name="numero_documento" required placeholder="1005894321"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs focus:outline-none focus:border-[#F97F2D]">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Ficha de Formación</label>
                        <input type="text" name="ficha" value="3173467" placeholder="3173467"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs focus:outline-none focus:border-[#F97F2D]">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Estado inicial del beneficio</label>
                    <select name="activo" class="w-full px-3.5 py-2.5 rounded-xl border border-gray-300 text-xs focus:outline-none focus:border-[#F97F2D] bg-white">
                        <option value="1">Activo (Habilitar código QR inmediatamente)</option>
                        <option value="0">Inactivo (Guardar sin acceso al QR)</option>
                    </select>
                </div>

                <div class="pt-3 flex gap-2">
                    <button type="button" @click="modalNuevo = false" class="flex-1 py-2.5 border rounded-xl text-xs font-bold text-gray-600 hover:bg-gray-50">
                        Cancelar
                    </button>
                    <button type="submit" class="flex-1 py-2.5 bg-[#F97F2D] hover:bg-[#e06d20] text-white rounded-xl text-xs font-bold shadow-md">
                        Guardar Aprendiz
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: CARGA AUTOMÁTICA DEL PADRÓN (.XLSX / .ODS / .CSV) -->
    <div x-show="modalSubir" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4" @click.away="modalSubir = false">
            <div class="flex items-center justify-between border-b pb-3">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#283618]">upload_file</span>
                    <h3 class="text-base font-extrabold text-[#283618]">Cargar Padrón Oficial de Beneficiarios</h3>
                </div>
                <button type="button" @click="modalSubir = false" class="text-gray-400 hover:text-gray-700">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form action="{{ route('admin.beneficiarios.subir') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div class="border-2 border-dashed border-[#283618]/30 rounded-2xl p-6 text-center hover:border-[#F97F2D] transition-colors bg-gray-50">
                    <span class="material-symbols-outlined text-4xl text-[#F97F2D] mb-1">description</span>
                    <p class="text-xs font-bold text-gray-800">Selecciona el archivo oficial de beneficiarios</p>
                    <p class="text-[11px] text-gray-500 mb-3">Formatos admitidos: Excel (.xlsx), OpenDocument (.ods) o (.csv)</p>
                    <input type="file" name="archivo" required accept=".xlsx,.ods,.csv,.txt"
                           class="w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#283618] file:text-white hover:file:bg-black cursor-pointer">
                </div>

                <div class="rounded-xl bg-amber-50 border border-amber-200 p-3 text-xs text-amber-900 space-y-1">
                    <p class="font-bold flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-amber-700 text-[18px]">info</span>
                        ¿Cómo funciona la sincronización automática?
                    </p>
                    <ul class="list-disc pl-5 space-y-0.5 text-[11px] text-amber-800">
                        <li><strong>Activación:</strong> Los aprendices del archivo se activan con acceso a su código QR.</li>
                        <li><strong>Inactivación segura:</strong> Los aprendices que ya no figuren en este listado se desactivan sin borrar su historial ni sus canjes previos.</li>
                        <li><strong>Almacenamiento privado:</strong> El archivo se almacena cifrado y aislado de rutas web públicas para cumplir con la protección de datos.</li>
                    </ul>
                </div>

                <div class="pt-2 flex gap-2">
                    <button type="button" @click="modalSubir = false" class="flex-1 py-2.5 border rounded-xl text-xs font-bold text-gray-600 hover:bg-gray-50">
                        Cancelar
                    </button>
                    <button type="submit" class="flex-1 py-2.5 bg-[#283618] hover:bg-black text-white rounded-xl text-xs font-bold shadow-md flex items-center justify-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">sync</span>
                        <span>Sincronizar Padrón</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 3: GUÍA DE FORMATO Y COLUMNAS -->
    <div x-show="modalInfoColumnas" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4" @click.away="modalInfoColumnas = false">
            <div class="flex items-center justify-between border-b pb-3">
                <h3 class="text-base font-extrabold text-[#283618]">Columnas esperadas en el archivo (.xlsx / .ods)</h3>
                <button type="button" @click="modalInfoColumnas = false" class="text-gray-400 hover:text-gray-700">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <div class="text-xs text-gray-700 space-y-3">
                <p>El sistema detecta automáticamente los encabezados de forma flexible. Se recomiendan las siguientes columnas:</p>
                <div class="bg-gray-50 rounded-xl p-3 border border-gray-200 font-mono text-[11px] space-y-1">
                    <p><span class="text-[#283618] font-bold">1. Documento:</span> documento, numero_documento, cedula, identificacion</p>
                    <p><span class="text-[#283618] font-bold">2. Nombre:</span> nombre, nombre_completo, aprendiz</p>
                    <p><span class="text-[#283618] font-bold">3. Correo:</span> email, correo, correo_institucional, correo_sena</p>
                    <p><span class="text-[#283618] font-bold">4. Ficha:</span> ficha, ficha_adso, codigo_ficha (Opcional, defecto 3173467)</p>
                    <p><span class="text-[#283618] font-bold">5. Programa:</span> programa (Opcional, defecto ADSO)</p>
                    <p><span class="text-[#283618] font-bold">6. Centro:</span> centro (Opcional, defecto CEAI - SENA)</p>
                </div>

                <div class="space-y-1">
                    <h4 class="font-bold text-[#F97F2D]">Manejo de errores e inconsistencias:</h4>
                    <p class="text-[11px] text-gray-600">
                        Si una fila carece de correo o documento válido, se omitirá y se registrará en el registro de inconsistencias sin interrumpir la carga de las demás filas válidas.
                    </p>
                </div>
            </div>

            <div class="pt-3">
                <button type="button" @click="modalInfoColumnas = false" class="w-full py-2.5 bg-[#283618] text-white rounded-xl text-xs font-bold hover:bg-black">
                    Entendido
                </button>
            </div>
        </div>
    </div>

</body>
</html>
