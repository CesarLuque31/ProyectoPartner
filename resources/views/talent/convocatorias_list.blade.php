@if(auth()->check() && auth()->user()->rol === 'jefe')
    @php
        // Cargar datos necesarios para el modal (tipos de contrato, horarios y ubigeos)
        $TalentoController = app(\App\Http\Controllers\TalentoController::class);
        $tiposContrato = collect([]);
        $horariosBase = collect([]);
        $ubigeos = [];
        $ubigeoPath = public_path('data');
        try {
            $tiposContrato = \Illuminate\Support\Facades\DB::table('raz_tipocontratos')
                ->where('estado', 1)
                ->select('tipo_contrato')
                ->orderBy('tipo_contrato')
                ->get();
        } catch (\Exception $e) {
            \Log::warning('Error cargando tipos_contrato: ' . $e->getMessage());
        }
        try {
            $horariosBase = $TalentoController->getHorariosBase();
        } catch (\Exception $e) {
            \Log::warning('Error cargando horarios_base: ' . $e->getMessage());
        }
        try {
            $departamentosFile = $ubigeoPath . '/ubigeo_peru_2016_departamentos.json';
            $provinciasFile = $ubigeoPath . '/ubigeo_peru_2016_provincias.json';
            $distritosFile = $ubigeoPath . '/ubigeo_peru_2016_distritos.json';
            if (file_exists($departamentosFile) && file_exists($provinciasFile) && file_exists($distritosFile)) {
                $ubigeos['departamentos'] = json_decode(file_get_contents($departamentosFile), true) ?? [];
                $ubigeos['provincias'] = json_decode(file_get_contents($provinciasFile), true) ?? [];
                $ubigeos['distritos'] = json_decode(file_get_contents($distritosFile), true) ?? [];
            }
        } catch (\Exception $e) {
            \Log::warning('Error cargando archivos ubigeos: ' . $e->getMessage());
        }
        $departamentosJson = json_encode($ubigeos['departamentos'] ?? []);
        $provinciasJson = json_encode($ubigeos['provincias'] ?? []);
        $distritosJson = json_encode($ubigeos['distritos'] ?? []);
        // Crear mapa de horarios (HorarioID => HorarioCompleto)
        $horariosMap = $horariosBase->keyBy('HorarioID')->map(function ($h) {
            return $h->HorarioCompleto;
        })->toArray();
        $horariosJson = json_encode($horariosMap);
    @endphp
    <div class="p-6">
        <!-- Header -->
        <div class="bg-white shadow-xl rounded-xl border-2 border-azul-noche border-opacity-20 overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-azul-noche to-azul-noche px-6 py-4">
                <h2 class="text-2xl font-bold text-white flex items-center">
                    <i class="fas fa-list mr-2"></i>
                    Listado de Convocatorias
                </h2>
            </div>
        </div>

        <!-- Filtros -->
        <div class="bg-white shadow-xl rounded-xl border-2 border-azul-noche border-opacity-20 overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-celeste to-celeste px-6 py-4 border-b border-azul-noche border-opacity-20">
                <h3 class="text-lg font-bold text-azul-noche flex items-center">
                    <i class="fas fa-filter mr-2 text-naranja"></i>
                    Filtros de Búsqueda
                </h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <!-- Filtro por Campaña -->
                        <div>
                            <label class="block text-sm font-semibold text-azul-noche mb-2">Campaña</label>
                            <div class="relative">
                                <i
                                    class="fas fa-building absolute left-3 top-1/2 transform -translate-y-1/2 text-naranja z-10"></i>
                                <select id="filtro-campana"
                                    class="w-full pl-10 pr-3 py-2 border-2 border-azul-noche border-opacity-30 focus:border-naranja focus:ring-2 focus:ring-naranja focus:ring-opacity-30 rounded-lg transition-all appearance-none bg-white text-azul-noche">
                                    <option value="">Todas</option>
                                    @foreach($campanias as $id => $nombre)
                                        <option value="{{ $id }}">{{ $nombre }}</option>
                                    @endforeach
                                </select>
                                <i
                                    class="fas fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-azul-noche pointer-events-none"></i>
                            </div>
                        </div>

                        <!-- Filtro por Cargo -->
                        <div>
                            <label class="block text-sm font-semibold text-azul-noche mb-2">Cargo</label>
                            <div class="relative">
                                <i
                                    class="fas fa-user-tie absolute left-3 top-1/2 transform -translate-y-1/2 text-naranja z-10"></i>
                                <select id="filtro-cargo"
                                    class="w-full pl-10 pr-3 py-2 border-2 border-azul-noche border-opacity-30 focus:border-naranja focus:ring-2 focus:ring-naranja focus:ring-opacity-30 rounded-lg transition-all appearance-none bg-white text-azul-noche">
                                    <option value="">Todos</option>
                                    @foreach($cargos as $id => $nombre)
                                        <option value="{{ $id }}">{{ $nombre }}</option>
                                    @endforeach
                                </select>
                                <i
                                    class="fas fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-azul-noche pointer-events-none"></i>
                            </div>
                        </div>

                        <!-- Filtro por Estado -->
                        <div>
                            <label class="block text-sm font-semibold text-azul-noche mb-2">Estado</label>
                            <div class="relative">
                                <i
                                    class="fas fa-info-circle absolute left-3 top-1/2 transform -translate-y-1/2 text-naranja z-10"></i>
                                <select id="filtro-estado"
                                    class="w-full pl-10 pr-3 py-2 border-2 border-azul-noche border-opacity-30 focus:border-naranja focus:ring-2 focus:ring-naranja focus:ring-opacity-30 rounded-lg transition-all appearance-none bg-white text-azul-noche">
                                    <option value="">Todos</option>
                                    <option value="Abierta">Abierta</option>
                                    <option value="Cerrada">Cerrada</option>
                                </select>
                                <i
                                    class="fas fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-azul-noche pointer-events-none"></i>
                            </div>
                        </div>

                        <!-- Filtro por Fecha Inicio -->
                        <div>
                            <label class="block text-sm font-semibold text-azul-noche mb-2">Desde Fecha Inicio</label>
                            <div class="relative">
                                <i
                                    class="fas fa-calendar-check absolute left-3 top-1/2 transform -translate-y-1/2 text-naranja z-10"></i>
                                <input type="date" id="filtro-fecha-inicio"
                                    class="w-full pl-10 pr-3 py-2 border-2 border-azul-noche border-opacity-30 focus:border-naranja focus:ring-2 focus:ring-naranja focus:ring-opacity-30 rounded-lg transition-all text-azul-noche" />
                            </div>
                        </div>

                        <!-- Filtro por Fecha Fin -->
                        <div>
                            <label class="block text-sm font-semibold text-azul-noche mb-2">Hasta Fecha Fin</label>
                            <div class="relative">
                                <i
                                    class="fas fa-calendar-times absolute left-3 top-1/2 transform -translate-y-1/2 text-naranja z-10"></i>
                                <input type="date" id="filtro-fecha-fin"
                                    class="w-full pl-10 pr-3 py-2 border-2 border-azul-noche border-opacity-30 focus:border-naranja focus:ring-2 focus:ring-naranja focus:ring-opacity-30 rounded-lg transition-all text-azul-noche" />
                            </div>
                        </div>

                        <!-- Filtro por Reclutador Asignado -->
                        <div>
                            <label class="block text-sm font-semibold text-azul-noche mb-2">Reclutador Asignado</label>
                            <div class="relative">
                                <i
                                    class="fas fa-user-friends absolute left-3 top-1/2 transform -translate-y-1/2 text-naranja z-10"></i>
                                <select id="filtro-reclutador"
                                    class="w-full pl-10 pr-3 py-2 border-2 border-azul-noche border-opacity-30 focus:border-naranja focus:ring-2 focus:ring-naranja focus:ring-opacity-30 rounded-lg transition-all appearance-none bg-white text-azul-noche">
                                    <option value="">Cualquiera</option>
                                    <option value="sin-asignar">Sin Asignar</option>
                                    @foreach($reclutadores_disponibles as $id => $nombre)
                                        <option value="{{ $id }}">{{ $nombre }}</option>
                                    @endforeach
                                </select>
                                <i
                                    class="fas fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-azul-noche pointer-events-none"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de Filtro -->
                    <div class="flex gap-3 pt-2">
                        <button type="button" id="btn-aplicar-filtros"
                            class="bg-gradient-to-r from-azul-noche to-azul-noche hover:from-azul-noche hover:to-azul-noche hover:bg-opacity-90 text-white px-6 py-2 rounded-lg font-semibold text-sm shadow-md hover:shadow-lg transition-all flex items-center">
                            <i class="fas fa-search mr-2"></i>
                            Aplicar Filtros
                        </button>
                        <button type="button" id="btn-limpiar-filtros"
                            class="bg-gray-400 hover:bg-gray-500 text-white px-6 py-2 rounded-lg font-semibold text-sm transition-all flex items-center">
                            <i class="fas fa-redo mr-2"></i>
                            Limpiar Filtros
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenedor de Convocatorias -->
        <div id="convocatorias-container" class="grid grid-cols-1 md:grid-cols-2 gap-6 hidden"></div>
        <div id="convocatorias-empty"
            class="hidden bg-white shadow-xl rounded-xl border-2 border-azul-noche border-opacity-20 p-8 text-center">
            <i class="fas fa-inbox text-4xl text-azul-noche text-opacity-50 mb-4"></i>
            <p class="text-lg font-semibold text-azul-noche">No hay convocatorias que coincidan con los filtros
                seleccionados.</p>
        </div>
        <!-- Modal Insertar Postulante (incluye pestañas: Postulantes / Insertar) -->
        <div id="modal-insertar-postulante"
            class="fixed inset-0 z-50 flex items-center justify-center bg-azul-noche bg-opacity-70 backdrop-blur-sm hidden">
            <div
                class="bg-white rounded-xl shadow-2xl w-full max-w-5xl relative h-[90vh] overflow-hidden flex flex-col border-2 border-azul-noche">
                <!-- Header del Modal -->
                <div
                    class="bg-gradient-to-r from-azul-noche to-azul-noche px-6 py-4 flex items-center justify-between shrink-0">
                    <h3 id="modal-conv-title" class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-users mr-2"></i>
                        <span>Gestión de Postulantes</span>
                    </h3>
                    <button id="cerrar-modal-postulante"
                        class="text-white hover:text-amarillo hover:bg-azul-noche hover:bg-opacity-80 rounded-full p-2 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Tabs Navigation -->
                <div class="border-b border-celeste bg-celeste bg-opacity-30 shrink-0">
                    <nav class="flex" aria-label="Tabs">
                        <button id="tab-postulantes"
                            class="flex-1 py-4 px-6 text-center border-b-2 border-naranja text-sm font-semibold text-azul-noche bg-white transition-colors">
                            <i class="fas fa-list mr-2"></i>Lista de Postulantes
                        </button>
                        <button id="tab-insertar"
                            class="flex-1 py-4 px-6 text-center border-b-2 border-transparent text-sm font-semibold text-azul-noche text-opacity-60 hover:text-azul-noche hover:bg-celeste transition-colors">
                            <i class="fas fa-user-plus mr-2"></i>Insertar Nuevo
                        </button>
                        <button id="tab-importar"
                            class="flex-1 py-4 px-6 text-center border-b-2 border-transparent text-sm font-semibold text-azul-noche text-opacity-60 hover:text-azul-noche hover:bg-celeste transition-colors">
                            <i class="fas fa-file-excel mr-2"></i>Importación Masiva
                        </button>
                    </nav>
                </div>

                <!-- Content Area -->
                <div class="flex-1 overflow-y-auto min-h-0 w-full bg-white relative">
                    <div id="tab-panel-postulantes" class="tab-panel p-4">
                        <div id="modal-postulantes-list" class="mb-4">
                            <div class="flex items-center justify-center py-8">
                                <div class="text-center">
                                    <i class="fas fa-spinner fa-spin text-naranja text-2xl mb-2"></i>
                                    <p class="text-sm text-gray-600">Cargando postulantes...</p>
                                </div>
                            </div>
                        </div>
                        <div id="modal-postulante-detalle"
                            class="mb-4 p-4 bg-celeste border border-azul-noche border-opacity-20 rounded-lg hidden"></div>
                    </div>
                    <div id="tab-panel-insertar" class="tab-panel hidden p-4">
                        {{-- Incluir el partial del formulario de insertar postulante --}}
                        @include('talent.insertar_postulante')
                    </div>
                    <div id="tab-panel-importar" class="tab-panel hidden pt-2 px-4 pb-4"
                        style="overflow-y: auto; max-height: calc(90vh - 180px);">
                        {{-- Incluir el partial de importación masiva --}}
                        @include('talent.importar_postulantes')
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Detalles Convocatoria -->
        <div id="modal-detalles-convocatoria"
            class="fixed inset-0 z-50 flex items-center justify-center bg-azul-noche bg-opacity-70 backdrop-blur-sm hidden">
            <div
                class="bg-white rounded-xl shadow-2xl w-full max-w-6xl relative max-h-[95vh] overflow-hidden flex flex-col border-2 border-azul-noche">
                <!-- Header del Modal -->
                <div class="bg-gradient-to-r from-azul-noche to-azul-noche px-6 py-4 flex items-center justify-between">
                    <h3 class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-info-circle mr-2"></i>
                        <span>Detalles de la Convocatoria</span>
                    </h3>
                    <button id="cerrar-modal-detalles"
                        class="text-white hover:text-amarillo hover:bg-azul-noche hover:bg-opacity-80 rounded-full p-2 transition-colors">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Content Area -->
                <div class="flex-1 overflow-y-auto p-6">
                    <div id="detalles-loading" class="text-center py-8">
                        <i class="fas fa-spinner fa-spin text-naranja text-2xl mb-2"></i>
                        <p class="text-sm text-azul-noche">Cargando detalles...</p>
                    </div>

                    <div id="detalles-content" class="hidden">
                        <!-- Información de la Convocatoria (Solo lectura) -->
                        <div
                            class="mb-6 bg-celeste bg-opacity-30 rounded-lg p-5 border-2 border-azul-noche border-opacity-10">
                            <h4 class="text-lg font-semibold text-azul-noche mb-4 flex items-center">
                                <i class="fas fa-file-alt mr-2 text-naranja"></i>
                                Información de la Convocatoria
                            </h4>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label
                                        class="block text-sm font-medium text-azul-noche text-opacity-70 mb-1">Solicitante</label>
                                    <input type="text" id="detalle-solicitante"
                                        class="w-full p-2 rounded-lg bg-white border-2 border-azul-noche border-opacity-20 text-azul-noche cursor-not-allowed"
                                        readonly />
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-azul-noche text-opacity-70 mb-1">Campaña</label>
                                    <input type="text" id="detalle-campana"
                                        class="w-full p-2 rounded-lg bg-white border-2 border-azul-noche border-opacity-20 text-azul-noche cursor-not-allowed"
                                        readonly />
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-azul-noche text-opacity-70 mb-1">Cargo</label>
                                    <input type="text" id="detalle-cargo"
                                        class="w-full p-2 rounded-lg bg-white border-2 border-azul-noche border-opacity-20 text-azul-noche cursor-not-allowed"
                                        readonly />
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-azul-noche text-opacity-70 mb-1">Vacantes</label>
                                    <input type="text" id="detalle-vacantes"
                                        class="w-full p-2 rounded-lg bg-white border-2 border-azul-noche border-opacity-20 text-azul-noche cursor-not-allowed"
                                        readonly />
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-azul-noche text-opacity-70 mb-1">Experiencia</label>
                                    <input type="text" id="detalle-experiencia"
                                        class="w-full p-2 rounded-lg bg-white border-2 border-azul-noche border-opacity-20 text-azul-noche cursor-not-allowed"
                                        readonly />
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-azul-noche text-opacity-70 mb-1">Estado</label>
                                    <input type="text" id="detalle-estado"
                                        class="w-full p-2 rounded-lg bg-white border-2 border-azul-noche border-opacity-20 text-azul-noche cursor-not-allowed"
                                        readonly />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-azul-noche text-opacity-70 mb-1">Inicio
                                        Capacitación</label>
                                    <input type="text" id="detalle-fecha-inicio"
                                        class="w-full p-2 rounded-lg bg-white border-2 border-azul-noche border-opacity-20 text-azul-noche cursor-not-allowed"
                                        readonly />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-azul-noche text-opacity-70 mb-1">Fin
                                        Capacitación</label>
                                    <input type="text" id="detalle-fecha-fin"
                                        class="w-full p-2 rounded-lg bg-white border-2 border-azul-noche border-opacity-20 text-azul-noche cursor-not-allowed"
                                        readonly />
                                </div>
                            </div>
                        </div>

                        <!-- Formulario de Detalles (Editable) -->
                        <form id="form-detalles-convocatoria">
                            @csrf
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-azul-noche mb-2">Razón Social
                                        Interna</label>
                                    <input type="text" name="razon_social_interna" id="razon_social_interna"
                                        class="border-2 border-azul-noche border-opacity-30 focus:border-naranja focus:ring-2 focus:ring-naranja focus:ring-opacity-30 p-3 w-full rounded-lg transition-all outline-none" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-azul-noche mb-2">Jefe Inmediato</label>
                                    <input type="text" name="jefe_inmediato" id="jefe_inmediato"
                                        class="border-2 border-azul-noche border-opacity-30 focus:border-naranja focus:ring-2 focus:ring-naranja focus:ring-opacity-30 p-3 w-full rounded-lg transition-all outline-none" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-azul-noche mb-2">Cliente</label>
                                    <input type="text" name="cliente" id="cliente"
                                        class="border-2 border-azul-noche border-opacity-30 focus:border-naranja focus:ring-2 focus:ring-naranja focus:ring-opacity-30 p-3 w-full rounded-lg transition-all outline-none" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-azul-noche mb-2">Servicio Asociado</label>
                                    <input type="text" name="servicio_asociado" id="servicio_asociado"
                                        class="border-2 border-azul-noche border-opacity-30 focus:border-naranja focus:ring-2 focus:ring-naranja focus:ring-opacity-30 p-3 w-full rounded-lg transition-all outline-none" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-azul-noche mb-2">Centro de Costo</label>
                                    <input type="text" name="centro_costo" id="centro_costo"
                                        class="border-2 border-azul-noche border-opacity-30 focus:border-naranja focus:ring-2 focus:ring-naranja focus:ring-opacity-30 p-3 w-full rounded-lg transition-all outline-none" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-azul-noche mb-2">Modalidad de
                                        Trabajo</label>
                                    <select name="modalidad_trabajo" id="modalidad_trabajo_det"
                                        class="border-2 border-azul-noche border-opacity-30 focus:border-naranja focus:ring-2 focus:ring-naranja focus:ring-opacity-30 p-3 w-full rounded-lg transition-all outline-none">
                                        <option value="">Seleccionar</option>
                                        <option value="presencial">Presencial</option>
                                        <option value="remoto">Remoto</option>
                                        <option value="hibrido">Híbrido</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-azul-noche mb-2">Lugar de Trabajo</label>
                                    <input type="text" name="lugar_trabajo" id="lugar_trabajo"
                                        class="border-2 border-azul-noche border-opacity-30 focus:border-naranja focus:ring-2 focus:ring-naranja focus:ring-opacity-30 p-3 w-full rounded-lg transition-all outline-none" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-azul-noche mb-2">Región Presencial</label>
                                    <input type="text" name="region_presencial" id="region_presencial"
                                        class="border-2 border-azul-noche border-opacity-30 focus:border-naranja focus:ring-2 focus:ring-naranja focus:ring-opacity-30 p-3 w-full rounded-lg transition-all outline-none" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-azul-noche mb-2">Tipo de Contrato</label>
                                    <select name="tipo_contrato" id="tipo_contrato_det"
                                        class="border-2 border-azul-noche border-opacity-30 focus:border-naranja focus:ring-2 focus:ring-naranja focus:ring-opacity-30 p-3 w-full rounded-lg transition-all outline-none">
                                        <option value="">Seleccionar</option>
                                        @foreach($tiposContrato as $tc)
                                            <option value="{{ $tc->tipo_contrato }}">{{ $tc->tipo_contrato }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-azul-noche mb-2">Días Laborables</label>
                                    <button type="button" id="btn-seleccionar-dias"
                                        class="w-full border-2 border-azul-noche border-opacity-30 focus:border-naranja focus:ring-2 focus:ring-naranja focus:ring-opacity-30 p-3 rounded-lg transition-all outline-none text-left bg-white">
                                        <span id="dias-seleccionados-text">Seleccionar días</span>
                                    </button>
                                    <input type="hidden" name="dias_laborables" id="dias_laborables" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-azul-noche mb-2">Hora Inicio</label>
                                    <input type="time" name="hora_inicio" id="hora_inicio"
                                        class="border-2 border-azul-noche border-opacity-30 focus:border-naranja focus:ring-2 focus:ring-naranja focus:ring-opacity-30 p-3 w-full rounded-lg transition-all outline-none" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-azul-noche mb-2">Hora Fin</label>
                                    <input type="time" name="hora_fin" id="hora_fin"
                                        class="border-2 border-azul-noche border-opacity-30 focus:border-naranja focus:ring-2 focus:ring-naranja focus:ring-opacity-30 p-3 w-full rounded-lg transition-all outline-none" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-azul-noche mb-2">Remuneración</label>
                                    <input type="number" step="0.01" name="remuneracion" id="remuneracion"
                                        class="border-2 border-azul-noche border-opacity-30 focus:border-naranja focus:ring-2 focus:ring-naranja focus:ring-opacity-30 p-3 w-full rounded-lg transition-all outline-none" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-azul-noche mb-2">Variable</label>
                                    <input type="number" step="0.01" name="variable" id="variable"
                                        class="border-2 border-azul-noche border-opacity-30 focus:border-naranja focus:ring-2 focus:ring-naranja focus:ring-opacity-30 p-3 w-full rounded-lg transition-all outline-none" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-azul-noche mb-2">Movilidad</label>
                                    <input type="number" step="0.01" name="movilidad" id="movilidad"
                                        class="border-2 border-azul-noche border-opacity-30 focus:border-naranja focus:ring-2 focus:ring-naranja focus:ring-opacity-30 p-3 w-full rounded-lg transition-all outline-none" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-azul-noche mb-2">Bono Permanencia</label>
                                    <select name="bono_permanencia" id="bono_permanencia"
                                        class="border-2 border-azul-noche border-opacity-30 focus:border-naranja focus:ring-2 focus:ring-naranja focus:ring-opacity-30 p-3 w-full rounded-lg transition-all outline-none">
                                        <option value="">Seleccionar</option>
                                        <option value="si">Sí</option>
                                        <option value="no">No</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-azul-noche mb-2">Tipo de
                                        Requerimiento</label>
                                    <input type="text" name="tipo_requerimiento" id="tipo_requerimiento"
                                        class="border-2 border-azul-noche border-opacity-30 focus:border-naranja focus:ring-2 focus:ring-naranja focus:ring-opacity-30 p-3 w-full rounded-lg transition-all outline-none" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-azul-noche mb-2">Motivo
                                        Requerimiento</label>
                                    <textarea name="motivo_requerimiento" id="motivo_requerimiento" rows="3"
                                        class="border-2 border-azul-noche border-opacity-30 focus:border-naranja focus:ring-2 focus:ring-naranja focus:ring-opacity-30 p-3 w-full rounded-lg transition-all outline-none"></textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-azul-noche mb-2">Fecha SLA</label>
                                    <input type="date" name="fecha_sla" id="fecha_sla"
                                        class="border-2 border-azul-noche border-opacity-30 focus:border-naranja focus:ring-2 focus:ring-naranja focus:ring-opacity-30 p-3 w-full rounded-lg transition-all outline-none" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-azul-noche mb-2">Fecha Objetivo</label>
                                    <input type="date" name="fecha_objetivo" id="fecha_objetivo"
                                        class="border-2 border-azul-noche border-opacity-30 focus:border-naranja focus:ring-2 focus:ring-naranja focus:ring-opacity-30 p-3 w-full rounded-lg transition-all outline-none" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-azul-noche mb-2">Tipo de Proceso</label>
                                    <input type="text" name="tipo_proceso" id="tipo_proceso"
                                        class="border-2 border-azul-noche border-opacity-30 focus:border-naranja focus:ring-2 focus:ring-naranja focus:ring-opacity-30 p-3 w-full rounded-lg transition-all outline-none" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-azul-noche mb-2">Horario</label>
                                    <select name="tipo_gestion" id="tipo_gestion_det"
                                        class="border-2 border-azul-noche border-opacity-30 focus:border-naranja focus:ring-2 focus:ring-naranja focus:ring-opacity-30 p-3 w-full rounded-lg transition-all outline-none">
                                        <option value="">Seleccionar</option>
                                        @foreach($horariosBase as $h)
                                            <option value="{{ $h->HorarioID }}">{{ $h->HorarioCompleto }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="mt-6 pt-6 border-t border-celeste flex gap-3">
                                <button type="button" id="btn-guardar-detalles"
                                    class="flex-1 bg-gradient-to-r from-verde to-verde hover:from-verde hover:to-verde hover:bg-opacity-90 text-white px-6 py-3 rounded-lg font-semibold transition-all shadow-md hover:shadow-lg flex items-center justify-center">
                                    <i class="fas fa-save mr-2"></i>Guardar Detalles
                                </button>
                                <button type="button" id="btn-cerrar-detalles"
                                    class="bg-azul-noche bg-opacity-60 hover:bg-opacity-80 text-white px-6 py-3 rounded-lg font-semibold transition-all shadow-md hover:shadow-lg">
                                    <i class="fas fa-times mr-2"></i>Cerrar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detalles Postulante -->
    <div id="modal-detalles-postulante"
        class="fixed inset-0 z-50 flex items-center justify-center bg-azul-noche bg-opacity-70 backdrop-blur-sm hidden">
        <div
            class="bg-white rounded-xl shadow-2xl w-full max-w-3xl relative max-h-[90vh] overflow-hidden flex flex-col border-2 border-azul-noche">
            <!-- Header -->
            <div class="bg-gradient-to-r from-azul-noche to-azul-noche px-6 py-4 flex items-center justify-between">
                <h3 class="text-xl font-bold text-white flex items-center">
                    <i class="fas fa-user-circle mr-2"></i>
                    <span>Detalles del Postulante</span>
                </h3>
                <button id="cerrar-modal-detalle-postulante"
                    class="text-white hover:text-amarillo hover:bg-azul-noche hover:bg-opacity-80 rounded-full p-2 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Content -->
            <div class="flex-1 overflow-y-auto p-6">
                <div id="detalle-postulante-content" class="space-y-4">
                    <div class="flex items-center justify-center py-8">
                        <div class="text-center">
                            <i class="fas fa-spinner fa-spin text-naranja text-2xl mb-2"></i>
                            <p class="text-sm text-gray-600">Cargando detalles...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
@if(auth()->check() && auth()->user()->rol === 'jefe')
    @push('scripts')
        <script>         // Mapa de horarios (HorarioID => HorarioCompleto) para convertir valores numéricos a t         exto
            window.horariosMap = {!! $horariosJson !!};
        </script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Ubigeo y selects gestionados en el partial `insertar_postulante`; modal eliminado.
                const filtros = {
                    campana: document.getElementById('filtro-campana'),
                    cargo: document.getElementById('filtro-cargo'),
                    estado: document.getElementById('filtro-estado'),
                    fechaInicio: document.getElementById('filtro-fecha-inicio'),
                    fechaFin: document.getElementById('filtro-fecha-fin'),
                    reclutador: document.getElementById('filtro-reclutador')
                };
                const btnAplicar = document.getElementById('btn-aplicar-filtros');
                const btnLimpiar = document.getElementById('btn-limpiar-filtros');
                const container = document.getElementById('convocatorias-container');
                const emptyMsg = document.getElementById('convocatorias-empty');

                function renderConvocatoriaCard(c, campanias, cargos, horarios, reclutadores_disponibles) {
                    // Construir HTML de la tarjeta (solo los datos necesarios)
                    let turnos = '';
                    if (Array.isArray(c.turnos_labels) && c.turnos_labels.length > 0) {
                        c.turnos_labels.forEach(lbl => {
                            turnos += `<span class="inline-flex items-center px-2 py-0.5 rounded bg-celeste bg-opacity-50 text-azul-noche text-xs font-medium">${lbl}</span>`;
                        });
                    }

                    let recAsignados = '';
                    if (Array.isArray(c.reclutadores_asignados_labels) && c.reclutadores_asignados_labels.length > 0) {
                        c.reclutadores_asignados_labels.forEach(rec => {
                            recAsignados += `<span class="inline-flex items-center px-2 py-0.5 rounded bg-verde bg-opacity-20 text-verde text-xs font-medium">${rec}</span>`;
                        });
                    }

                    return `
                                                                                                                    <div class="bg-white shadow-lg rounded-lg border border-azul-noche border-opacity-20 overflow-hidden convocatoria-item hover:shadow-xl transition-all">
                                                                                                                        <div class="bg-gradient-to-r from-azul-noche to-azul-noche px-4 py-3">
                                                                                                                            <div class="flex justify-between items-center">
                                                                                                                                <div class="flex items-center gap-3">
                                                                                                                                    <h3 class="text-lg font-bold text-white">${c.campania_nombre}</h3>
                                                                                                                                    <span class="px-2 py-0.5 rounded text-xs font-semibold ${c.estado === 'Abierta' ? 'bg-verde text-white' : 'bg-amarillo text-azul-noche'}">
                                                                                                                                        ${c.estado}
                                                                                                                                    </span>
                                                                                                                                </div>
                                                                                                                                <p class="text-xs text-white text-opacity-80">
                                                                                                                                    ${c.created_at}
                                                                                                                                </p>
                                                                                                                            </div>
                                                                                                                        </div>
                                                                                                                        <div class="p-4">
                                                                                                                            <div class="grid grid-cols-3 gap-3 mb-3">
                                                                                                                                <div>
                                                                                                                                    <p class="text-xs text-azul-noche text-opacity-60 mb-1">Cargo</p>
                                                                                                                                    <p class="text-sm font-semibold text-azul-noche">${c.cargo_nombre}</p>
                                                                                                                                </div>
                                                                                                                                <div>
                                                                                                                                    <p class="text-xs text-azul-noche text-opacity-60 mb-1">Vacantes</p>
                                                                                                                                    <p class="text-sm font-semibold text-azul-noche">${c.requerimiento_personal}</p>
                                                                                                                                </div>
                                                                                                                                <div>
                                                                                                                                    <p class="text-xs text-azul-noche text-opacity-60 mb-1">Experiencia</p>
                                                                                                                                    <p class="text-sm font-semibold text-azul-noche">${c.experiencia_label}</p>
                                                                                                                                </div>
                                                                                                                            </div>
                                                                                                                            <div class="grid grid-cols-2 gap-3 mb-3 text-xs">
                                                                                                                                <div>
                                                                                                                                    <p class="text-azul-noche text-opacity-60 mb-1">Inicio Capacitación</p>
                                                                                                                                    <p class="text-azul-noche font-medium">${c.fecha_inicio_capacitacion}</p>
                                                                                                                                </div>
                                                                                                                                <div>
                                                                                                                                    <p class="text-azul-noche text-opacity-60 mb-1">Fin Capacitación</p>
                                                                                                                                    <p class="text-azul-noche font-medium">${c.fecha_fin_capacitacion}</p>
                                                                                                                                </div>
                                                                                                                            </div>
                                                                                                                            ${turnos ? `<div class="mb-3">
                                                                                                                                <p class="text-xs text-azul-noche text-opacity-60 mb-1">Turnos</p>
                                                                                                                                <div class="flex flex-wrap gap-1.5">${turnos}</div>
                                                                                                                            </div>` : ''}
                                                                                                                            ${recAsignados && recAsignados.includes('Sin reclutadores') === false ? `<div class="mb-3">
                                                                                                                                <p class="text-xs text-azul-noche text-opacity-60 mb-1">Reclutadores</p>
                                                                                                                                <div class="flex flex-wrap gap-1.5">${recAsignados}</div>
                                                                                                                            </div>` : ''}
                                                                                                                            <div class="pt-3 border-t border-azul-noche border-opacity-10 flex items-center gap-2">
                                                                                                                                <button type="button" class="detalles-conv-btn flex-1 bg-azul-noche hover:bg-azul-noche hover:bg-opacity-90 text-white px-3 py-2 rounded text-xs font-semibold transition-all flex items-center justify-center" data-conv-id="${c.id}">
                                                                                                                                    <i class="fas fa-info-circle mr-1.5"></i> Detalles
                                                                                                                                </button>
                                                                                                                                <button type="button" class="swal-assign-btn flex-1 bg-celeste hover:bg-celeste hover:bg-opacity-80 text-azul-noche px-3 py-2 rounded text-xs font-semibold transition-all flex items-center justify-center" data-conv-id="${c.id}" data-conv-name="${c.campania_nombre}">
                                                                                                                                    <i class="fas fa-users mr-1.5"></i> Asignar
                                                                                                                                </button>
                                                                                                                                <button type="button" class="insertar-postulante-btn flex-1 bg-verde hover:bg-verde hover:bg-opacity-90 text-white px-3 py-2 rounded text-xs font-semibold transition-all flex items-center justify-center" data-conv-id="${c.id}" data-conv-name="${c.campania_nombre}">
                                                                                                                                    <i class="fas fa-user-plus mr-1.5"></i> Postulante
                                                                                                                                </button>
                                                                                                                                <form action="/convocatorias/${c.id}" method="POST" class="inline delete-conv-form">
                                                                                                                                    <input type="hidden" name="_token" value="${c.csrf_token}">
                                                                                                                                    <input type="hidden" name="_method" value="DELETE">
                                                                                                                                    <button type="button" class="swal-delete-btn bg-naranja hover:bg-naranja hover:bg-opacity-90 text-white px-3 py-2 rounded text-xs font-semibold transition-all flex items-center justify-center">
                                                                                                                                        <i class="fas fa-trash"></i>
                                                                                                                                    </button>
                                                                                                                                                                                                                    </form>
                                                                                                                            </div>
                                                                                                                        </div>
                                                                                                                    </div>
                                                                                                                    `;
                        }

                        function aplicarFiltros() {
                            // Construir objeto de filtros
                            const data = {
                                campana: filtros.campana.value,
                                cargo: filtros.cargo.value,
                                estado: filtros.estado.value,
                                fecha_inicio: filtros.fechaInicio.value,
                                fecha_fin: filtros.fechaFin.value,
                                reclutador: filtros.reclutador.value
                            };
                            container.innerHTML = '';
                            container.classList.add('hidden');
                            emptyMsg.classList.add('hidden');
                            fetch('/convocatorias/filtrar', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                },
                                body: JSON.stringify(data)
                            })
                                .then(res => res.json())
                                .then(resp => {
                                    if (Array.isArray(resp.convocatorias) && resp.convocatorias.length > 0) {
                                        container.classList.remove('hidden');
                                        emptyMsg.classList.add('hidden');
                                        resp.convocatorias.forEach(c => {
                                            container.innerHTML += renderConvocatoriaCard(c, resp.campanias, resp.cargos, resp.horarios, resp.reclutadores_disponibles);
                                        });
                                    } else {
                                        container.classList.add('hidden');
                                        emptyMsg.classList.remove('hidden');
                                    }
                                })
                                .catch(() => {
                                    container.classList.add('hidden');
                                    emptyMsg.classList.remove('hidden');
                                });
                        }

                        function limpiarFiltros() {
                            filtros.campana.value = '';
                            filtros.cargo.value = '';
                            filtros.estado.value = '';
                            filtros.fechaInicio.value = '';
                            filtros.fechaFin.value = '';
                            filtros.reclutador.value = '';
                            container.innerHTML = '';
                            container.classList.add('hidden');
                            emptyMsg.classList.add('hidden');
                        }

                        if (btnAplicar) btnAplicar.addEventListener('click', aplicarFiltros);
                        if (btnLimpiar) btnLimpiar.addEventListener('click', limpiarFiltros);

                        // Las convocatorias solo se cargan cuando se aplican los filtros

                        // Aquí puedes agregar los listeners para los botones de eliminar y asignar reclutadores después de renderizar
                        // (puedes usar event delegation si lo prefieres)
                        // Listener global para botones 'Insertar Postulante' generados dinámicamente
                        document.addEventListener('click', function (e) {
                            const btn = e.target.closest && e.target.closest('.insertar-postulante-btn');
                            if (!btn) return;
                            const convId = btn.getAttribute('data-conv-id');
                            const convName = btn.getAttribute('data-conv-name');
                            const convTitle = btn.getAttribute('data-conv-title') || convName || '';

                            // Abrir modal y pasar datos
                            openPostulanteModal(convId, convTitle);
                        });

                        // Delegación para botones de eliminar y asignar reclutadores
                        document.addEventListener('click', function (e) {
                            const delBtn = e.target.closest && e.target.closest('.swal-delete-btn');
                            if (delBtn) {
                                e.preventDefault();
                                const form = delBtn.closest('.delete-conv-form');
                                if (!form) return;
                                const formData = new FormData(form);
                                const url = form.getAttribute('action');

                                Swal.fire({
                                    title: '¿Eliminar convocatoria?',
                                    text: 'Esta acción no se puede deshacer.',
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonColor: '#d33',
                                    cancelButtonColor: '#3085d6',
                                    confirmButtonText: 'Sí, eliminar',
                                    cancelButtonText: 'Cancelar'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        fetch(url, {
                                            method: 'POST',
                                            headers: {
                                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                                'X-HTTP-Method-Override': 'DELETE'
                                            },
                                            body: formData
                                        })
                                            .then(response => response.text())
                                            .then(html => {
                                                Swal.fire({
                                                    icon: 'success',
                                                    title: '¡Eliminada!',
                                                    text: 'La convocatoria ha sido eliminada exitosamente.',
                                                    showConfirmButton: false,
                                                    timer: 2000,
                                                    toast: true,
                                                    position: 'top-end'
                                                }).then(() => { location.reload(); });
                                            })
                                            .catch(error => {
                                                Swal.fire('Error', 'No se pudo eliminar la convocatoria', 'error');
                                                console.error(error);
                                            });
                                    }
                                });
                                return;
                            }

                            const assignBtn = e.target.closest && e.target.closest('.swal-assign-btn');
                            if (assignBtn) {
                                e.preventDefault();
                                const convID = assignBtn.getAttribute('data-conv-id');
                                const convName = assignBtn.getAttribute('data-conv-name');
                                const reclutadoresDisp = {!! json_encode($reclutadores_disponibles) !!};
                                let currentReclutadoresRaw = {!! json_encode($convocatorias->pluck('reclutadores_asignados', 'id')) !!}[convID] || [];

                                // Normalizar a array de strings/ints
                                let currentReclutadores = [];
                                try {
                                    if (typeof currentReclutadoresRaw === 'string') {
                                        // puede ser JSON string
                                        const parsed = JSON.parse(currentReclutadoresRaw);
                                        if (Array.isArray(parsed)) currentReclutadores = parsed;
                                    } else if (Array.isArray(currentReclutadoresRaw)) {
                                        currentReclutadores = currentReclutadoresRaw;
                                    } else if (currentReclutadoresRaw && typeof currentReclutadoresRaw === 'object') {
                                        // en algunos casos puede llegar como objeto indexado
                                        currentReclutadores = Object.values(currentReclutadoresRaw);
                                    }
                                } catch (err) {
                                    currentReclutadores = [];
                                }

                                // Convertir todos a strings para comparación simple
                                currentReclutadores = currentReclutadores.map(v => String(v));

                                // Crear opciones para Select2
                                let options = '';
                                for (let id in reclutadoresDisp) {
                                    const idStr = String(id);
                                    const selected = currentReclutadores.includes(idStr) ? 'selected' : '';
                                    options += `<option value="${id}" ${selected}>${reclutadoresDisp[id]}</option>`;
                                }

                                Swal.fire({
                                    title: `Asignar Reclutadores`,
                                    html: `
                                                                                                                                    <div class="text-left">
                                                                                                                                        <p class="mb-4 text-sm text-gray-600"><strong>Convocatoria:</strong> ${convName}</p>
                                                                                                                                        <select id="swal-reclutadores-select" class="w-full" multiple="multiple">
                                                                                                                                            ${options}
                                                                                                                                        </select>
                                                                                                                                    </div>
                                                                                                                                `,
                                    showCancelButton: true,
                                    confirmButtonText: 'Guardar',
                                    cancelButtonText: 'Cancelar',
                                    didOpen: () => {
                                        // Inicializar Select2 después de que el HTML se renderize
                                        if (window.jQuery && $.fn.select2) {
                                            const $sel = $('#swal-reclutadores-select');
                                            $sel.select2({
                                                dropdownParent: $('.swal2-container'),
                                                placeholder: 'Selecciona reclutadores',
                                                allowClear: true,
                                                width: '100%'
                                            });
                                            // Forzar valores seleccionados (asegura compatibilidad)
                                            $sel.val(currentReclutadores).trigger('change');
                                        } else {
                                            // Si no hay select2, nada más: los <option selected> ya marcan selección nativa
                                        }
                                    },
                                    preConfirm: () => {
                                        const selected = window.jQuery && $.fn.select2 ? $('#swal-reclutadores-select').val() || [] : Array.from(document.getElementById('swal-reclutadores-select').selectedOptions).map(o => o.value);
                                        return selected;
                                    }
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        const selectedReclutadores = result.value;
                                        fetch(`/convocatorias/${convID}/assign-reclutadores`, {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                            },
                                            body: JSON.stringify({ reclutadores: selectedReclutadores })
                                        })
                                            .then(response => response.json())
                                            .then(data => {
                                                Swal.fire('Éxito', 'Reclutadores asignados correctamente', 'success')
                                                    .then(() => { location.reload(); });
                                            })
                                            .catch(error => {
                                                Swal.fire('Error', 'No se pudo asignar reclutadores', 'error');
                                            });
                                    }
                                });

                                return;
                            }
                        });

                        function escapeHtml(str) {
                            if (!str) return '';
                            return String(str)
                                .replace(/&/g, '&amp;')
                                .replace(/</g, '&lt;')
                                .replace(/>/g, '&gt;')
                                .replace(/"/g, '&quot;')
                                .replace(/'/g, '&#039;');
                        }

                        function openPostulanteModal(convocatoriaId, convocatoriaTitle) {
                            const modal = document.getElementById('modal-insertar-postulante');
                            if (!modal) return;
                            // Set convocatoria-id hidden input inside partial if present
                            const hidden = modal.querySelector('#convocatoria-id');
                            if (hidden) hidden.value = convocatoriaId;
                            const hiddenImportar = modal.querySelector('#convocatoria-id-importar');
                            if (hiddenImportar) hiddenImportar.value = convocatoriaId;
                            // Show modal
                            modal.classList.remove('hidden');
                            // Activate Postulantes tab by default
                            activateTab('postulantes');
                            // Load postulantes for this convocatoria
                            loadModalPostulantes(convocatoriaId);
                            // Emitir evento para que el partial de importación sepa el convocatoria_id
                            window.dispatchEvent(new CustomEvent('postulante:modal-opened', {
                                detail: { convocatoria_id: convocatoriaId }
                            }));
                        }

                        function closePostulanteModal() {
                            const modal = document.getElementById('modal-insertar-postulante');
                            if (modal) modal.classList.add('hidden');
                        }

                        // Botón cerrar
                        const cerrarBtn = document.getElementById('cerrar-modal-postulante');
                        if (cerrarBtn) cerrarBtn.addEventListener('click', function () { closePostulanteModal(); });

                        // Cerrar modal al hacer click en el backdrop (fuera del panel)
                        const modalOverlay = document.getElementById('modal-insertar-postulante');
                        if (modalOverlay) {
                            modalOverlay.addEventListener('click', function (ev) {
                                // Si el click es sobre el overlay (no dentro del contenido), cerramos
                                if (ev.target === modalOverlay) closePostulanteModal();
                            });
                        }

                        // Tabs
                        const tabPost = document.getElementById('tab-postulantes');
                        const tabIns = document.getElementById('tab-insertar');
                        const tabImp = document.getElementById('tab-importar');
                        if (tabPost) tabPost.addEventListener('click', function () { activateTab('postulantes'); });
                        if (tabIns) tabIns.addEventListener('click', function () { activateTab('insertar'); });
                        if (tabImp) tabImp.addEventListener('click', function () { activateTab('importar'); });

                        function activateTab(name) {
                            const tabP = document.getElementById('tab-panel-postulantes');
                            const tabI = document.getElementById('tab-panel-insertar');
                            const tabImp = document.getElementById('tab-panel-importar');
                            const tPost = document.getElementById('tab-postulantes');
                            const tIns = document.getElementById('tab-insertar');
                            const tImp = document.getElementById('tab-importar');
                            if (!tabP || !tabI || !tabImp || !tPost || !tIns || !tImp) return;

                            // Ocultar todos los paneles
                            tabP.classList.add('hidden');
                            tabI.classList.add('hidden');
                            tabImp.classList.add('hidden');

                            // Resetear estilos de todas las pestañas
                            [tPost, tIns, tImp].forEach(tab => {
                                tab.classList.remove('border-naranja', 'text-azul-noche', 'bg-white');
                                tab.classList.add('border-transparent', 'text-azul-noche', 'text-opacity-60');
                            });

                            // Activar la pestaña seleccionada
                            if (name === 'postulantes') {
                                tabP.classList.remove('hidden');
                                tPost.classList.add('border-naranja', 'text-azul-noche', 'bg-white');
                                tPost.classList.remove('border-transparent', 'text-azul-noche', 'text-opacity-60');
                            } else if (name === 'insertar') {
                                tabI.classList.remove('hidden');
                                tIns.classList.add('border-naranja', 'text-azul-noche', 'bg-white');
                                tIns.classList.remove('border-transparent', 'text-azul-noche', 'text-opacity-60');
                            } else if (name === 'importar') {
                                tabImp.classList.remove('hidden');
                                tImp.classList.add('border-naranja', 'text-azul-noche', 'bg-white');
                                tImp.classList.remove('border-transparent', 'text-azul-noche', 'text-opacity-60');
                            }
                        }

                        async function loadModalPostulantes(convocatoriaId) {
                            const listEl = document.getElementById('modal-postulantes-list');
                            if (!listEl) return;
                            listEl.innerHTML = '<p class="text-sm text-gray-600">Cargando postulantes...</p>';
                            try {
                                const res = await fetch(`/convocatorias/${convocatoriaId}/postulantes`);
                                if (!res.ok) throw new Error('Error al cargar postulantes');
                                const data = await res.json();
                                const postulantes = Array.isArray(data) ? data : (data.data || []);
                                if (!Array.isArray(postulantes) || postulantes.length === 0) {
                                    listEl.innerHTML = '<p class="text-sm text-gray-600">No hay postulantes para esta convocatoria.</p>';
                                    return;
                                }
                                const rows = postulantes.map(p => {
                                    const nombre = [p.nombres, p.ap_pat, p.ap_mat].filter(Boolean).join(' ') || (p.nombre_completo || 'Sin nombre');
                                    // Formatear tipo de documento y número
                                    const tipoDoc = p.tipo_documento || 'DNI';
                                    const numDoc = p.dni || 'Sin registro';
                                    const documentoCompleto = tipoDoc === 'Carnet de Extranjería'
                                        ? `<span class="inline-flex items-center px-2 py-0.5 rounded bg-naranja bg-opacity-20 text-naranja text-xs font-medium mr-1">CE</span>${escapeHtml(numDoc)}`
                                        : `<span class="inline-flex items-center px-2 py-0.5 rounded bg-azul-noche bg-opacity-20 text-azul-noche text-xs font-medium mr-1">DNI</span>${escapeHtml(numDoc)}`;

                                    return `
                                                                                                                                    <tr class="border-b border-celeste border-opacity-40">
                                                                                                                                        <td class="px-4 py-2 font-medium text-azul-noche">${escapeHtml(nombre)}</td>
                                                                                                                                        <td class="px-4 py-2 text-azul-noche text-opacity-80 font-mono">${documentoCompleto}</td>
                                                                                                                                        <td class="px-4 py-2 text-right">
                                                                                                                                            <button type="button" class="detalle-postulante-btn inline-flex items-center gap-1 px-3 py-1 text-sm font-semibold text-white bg-azul-noche rounded-full hover:bg-azul-noche/80 transition" data-postulante-id="${p.id || ''}">
                                                                                                                                                <i class="fas fa-eye text-xs"></i> Ver detalle
                                                                                                                                            </button>
                                                                                                                                        </td>
                                                                                                                                    </tr>
                                                                                                                                `;
                                }).join('');
                                listEl.innerHTML = `
                                                                                                                                <div class="space-y-3">
                                                                                                                                    <div class="text-sm text-azul-noche text-opacity-70 font-medium">Postulantes registrados: ${postulantes.length}</div>
                                                                                                                                    <div class="overflow-x-auto rounded-xl border border-celeste border-opacity-40 shadow-sm">
                                                                                                                                        <table class="min-w-full text-sm text-left text-azul-noche">
                                                                                                                                            <thead class="bg-celeste bg-opacity-50 text-azul-noche font-semibold">
                                                                                                                                                <tr>
                                                                                                                                                    <th class="px-4 py-3">Nombre completo</th>
                                                                                                                                                    <th class="px-4 py-3">Documento</th>
                                                                                                                                                    <th class="px-4 py-3 text-right">Acciones</th>
                                                                                                                                                </tr>
                                                                                                                                            </thead>
                                                                                                                                            <tbody class="bg-white">
                                                                                                                                                ${rows}
                                                                                                                                            </tbody>
                                                                                                                                        </table>
                                                                                                                                    </div>
                                                                                                                                </div>
                                                                                                                            `;
                            } catch (err) {
                                listEl.innerHTML = `<p class="text-sm text-red-600">${escapeHtml(err.message)}</p>`;
                            }
                        }

                        // Escuchar evento disparado por el partial tras guardar
                        window.addEventListener('postulante:created', function (ev) {
                            const convId = ev.detail && ev.detail.convocatoria_id;
                            if (convId) {
                                const modal = document.getElementById('modal-insertar-postulante');
                                if (modal && !modal.classList.contains('hidden')) {
                                    loadModalPostulantes(convId);
                                    activateTab('postulantes');
                                }
                            }
                        });

                        // Listener para botones de detalles del postulante (event delegation)
                        document.addEventListener('click', function (e) {
                            const btn = e.target.closest('.detalle-postulante-btn');
                            if (!btn) return;
                            const postulanteId = btn.getAttribute('data-postulante-id');
                            if (postulanteId) {
                                loadDetallePostulante(postulanteId);
                            }
                        });

                        // Función para cargar y mostrar detalles del postulante
                        async function loadDetallePostulante(postulanteId) {
                            const modal = document.getElementById('modal-detalles-postulante');
                            const content = document.getElementById('detalle-postulante-content');
                            if (!modal || !content) return;

                            modal.classList.remove('hidden');
                            content.innerHTML = `
                                                                                                                            <div class="flex items-center justify-center py-8">
                                                                                                                                <div class="text-center">
                                                                                                                                    <i class="fas fa-spinner fa-spin text-naranja text-2xl mb-2"></i>
                                                                                                                                    <p class="text-sm text-gray-600">Cargando detalles...</p>
                                                                                                                                </div>
                                                                                                                            </div>
                                                                                                                        `;

                            try {
                                const res = await fetch(`/postulantes/${postulanteId}`);
                                if (!res.ok) throw new Error('Error al cargar detalles');
                                const data = await res.json();
                                if (!data.success || !data.data) {
                                    throw new Error('No se encontraron datos');
                                }

                                const p = data.data;
                                const nombreCompleto = [p.nombres, p.ap_pat, p.ap_mat].filter(Boolean).join(' ') || 'Sin nombre';
                                const fechaNac = p.fecha_nac ? formatDate(p.fecha_nac) : 'N/A';
                                const sexo = p.sexo === '1' ? 'Masculino' : (p.sexo === '2' ? 'Femenino' : 'No especificado');
                                const experiencia = p.experiencia_callcenter === 'si' ? 'Sí' : 'No';
                                const discapacidad = p.discapacidad === 'si' ? 'Sí' : 'No';
                                const tipoDocumento = p.tipo_documento || 'DNI';
                                const estadoCivil = p.est_civil ? (p.est_civil.charAt(0).toUpperCase() + p.est_civil.slice(1)) : 'N/A';
                                // Convertir tipo_gestion (HorarioID numérico) a texto usando el mapa de horarios
                                const horarioTexto = (window.horariosMap && p.tipo_gestion && window.horariosMap[p.tipo_gestion])
                                    ? window.horariosMap[p.tipo_gestion]
                                    : (p.tipo_gestion || 'N/A');

                                content.innerHTML = `
                                                                                                                                <div class="space-y-6">
                                                                                                                                    <div class="bg-celeste bg-opacity-30 p-4 rounded-lg border border-azul-noche border-opacity-20">
                                                                                                                                        <h4 class="text-lg font-bold text-azul-noche mb-3 flex items-center">
                                                                                                                                            <i class="fas fa-id-card mr-2"></i>Información Personal
                                                                                                                                        </h4>
                                                                                                                                        <div class="grid grid-cols-2 gap-4">
                                                                                                                                            <div>
                                                                                                                                                <p class="text-xs text-azul-noche text-opacity-60 font-semibold">NOMBRE COMPLETO</p>
                                                                                                                                                <p class="text-azul-noche font-medium">${escapeHtml(nombreCompleto)}</p>
                                                                                                                                            </div>
                                                                                                                                            <div>
                                                                                                                                                <p class="text-xs text-azul-noche text-opacity-60 font-semibold">TIPO DE DOCUMENTO</p>
                                                                                                                                                <p class="text-azul-noche">${escapeHtml(tipoDocumento)}</p>
                                                                                                                                            </div>
                                                                                                                                            <div>
                                                                                                                                                <p class="text-xs text-azul-noche text-opacity-60 font-semibold">NÚMERO DE DOCUMENTO</p>
                                                                                                                                                <p class="text-azul-noche font-mono">${escapeHtml(p.dni || 'N/A')}</p>
                                                                                                                                            </div>
                                                                                                                                            <div>
                                                                                                                                                <p class="text-xs text-azul-noche text-opacity-60 font-semibold">FECHA DE NACIMIENTO</p>
                                                                                                                                                <p class="text-azul-noche">${fechaNac}</p>
                                                                                                                                            </div>
                                                                                                                                            <div>
                                                                                                                                                <p class="text-xs text-azul-noche text-opacity-60 font-semibold">SEXO</p>
                                                                                                                                                <p class="text-azul-noche">${sexo}</p>
                                                                                                                                            </div>
                                                                                                                                            <div>
                                                                                                                                                <p class="text-xs text-azul-noche text-opacity-60 font-semibold">ESTADO CIVIL</p>
                                                                                                                                                <p class="text-azul-noche">${estadoCivil}</p>
                                                                                                                                            </div>
                                                                                                                                            ${p.nacionalidad ? `
                                                                                                                                            <div>
                                                                                                                                                <p class="text-xs text-azul-noche text-opacity-60 font-semibold">NACIONALIDAD</p>
                                                                                                                                                <p class="text-azul-noche">${escapeHtml(p.nacionalidad)}</p>
                                                                                                                                            </div>
                                                                                                                                            ` : ''}
                                                                                                                                        </div>
                                                                                                                                    </div>

                                                                                                                                    <div class="bg-celeste bg-opacity-30 p-4 rounded-lg border border-azul-noche border-opacity-20">
                                                                                                                                        <h4 class="text-lg font-bold text-azul-noche mb-3 flex items-center">
                                                                                                                                            <i class="fas fa-map-marker-alt mr-2"></i>Contacto y Ubicación
                                                                                                                                        </h4>
                                                                                                                                        <div class="grid grid-cols-2 gap-4">
                                                                                                                                            <div>
                                                                                                                                                <p class="text-xs text-azul-noche text-opacity-60 font-semibold">DIRECCIÓN</p>
                                                                                                                                                <p class="text-azul-noche">${escapeHtml(p.direccion || 'N/A')}</p>
                                                                                                                                            </div>
                                                                                                                                            <div>
                                                                                                                                                <p class="text-xs text-azul-noche text-opacity-60 font-semibold">NÚMERO DE CONTACTO</p>
                                                                                                                                                <p class="text-azul-noche">${escapeHtml(p.celular || 'N/A')}</p>
                                                                                                                                            </div>
                                                                                                                                            <div>
                                                                                                                                                <p class="text-xs text-azul-noche text-opacity-60 font-semibold">NÚMERO DE WHATSAPP</p>
                                                                                                                                                <p class="text-azul-noche">${escapeHtml(p.whatsapp || 'N/A')}</p>
                                                                                                                                            </div>
                                                                                                                                            <div>
                                                                                                                                                <p class="text-xs text-azul-noche text-opacity-60 font-semibold">CORREO</p>
                                                                                                                                                <p class="text-azul-noche">${escapeHtml(p.correo || 'N/A')}</p>
                                                                                                                                            </div>
                                                                                                                                            <div>
                                                                                                                                                <p class="text-xs text-azul-noche text-opacity-60 font-semibold">PROVINCIA</p>
                                                                                                                                                <p class="text-azul-noche">${escapeHtml(p.provincia || 'N/A')}</p>
                                                                                                                                            </div>
                                                                                                                                            <div>
                                                                                                                                                <p class="text-xs text-azul-noche text-opacity-60 font-semibold">DISTRITO</p>
                                                                                                                                                <p class="text-azul-noche">${escapeHtml(p.distrito || 'N/A')}</p>
                                                                                                                                            </div>
                                                                                                                                        </div>
                                                                                                                                    </div>

                                                                                                                                    <div class="bg-celeste bg-opacity-30 p-4 rounded-lg border border-azul-noche border-opacity-20">
                                                                                                                                        <h4 class="text-lg font-bold text-azul-noche mb-3 flex items-center">
                                                                                                                                            <i class="fas fa-briefcase mr-2"></i>Información Laboral
                                                                                                                                        </h4>
                                                                                                                                        <div class="grid grid-cols-2 gap-4">
                                                                                                                                            <div>
                                                                                                                                                <p class="text-xs text-azul-noche text-opacity-60 font-semibold">EXPERIENCIA CALL CENTER</p>
                                                                                                                                                <p class="text-azul-noche">${experiencia}</p>
                                                                                                                                            </div>
                                                                                                                                            <div>
                                                                                                                                                <p class="text-xs text-azul-noche text-opacity-60 font-semibold">DISCAPACIDAD</p>
                                                                                                                                                <p class="text-azul-noche">${discapacidad}</p>
                                                                                                                                            </div>
                                                                                                                                            ${p.tipo_discapacidad ? `
                                                                                                                                            <div>
                                                                                                                                                <p class="text-xs text-azul-noche text-opacity-60 font-semibold">TIPO DE DISCAPACIDAD</p>
                                                                                                                                                <p class="text-azul-noche">${escapeHtml(p.tipo_discapacidad)}</p>
                                                                                                                                            </div>
                                                                                                                                            ` : ''}
                                                                                                                                            <div>
                                                                                                                                                <p class="text-xs text-azul-noche text-opacity-60 font-semibold">TIPO DE CONTRATO</p>
                                                                                                                                                <p class="text-azul-noche">${escapeHtml(p.tipo_contrato || 'N/A')}</p>
                                                                                                                                            </div>
                                                                                                                                            <div>
                                                                                                                                                <p class="text-xs text-azul-noche text-opacity-60 font-semibold">MODALIDAD DE TRABAJO</p>
                                                                                                                                                <p class="text-azul-noche">${escapeHtml(p.modalidad_trabajo || 'N/A')}</p>
                                                                                                                                            </div>
                                                                                                                                            <div>
                                                                                                                                                <p class="text-xs text-azul-noche text-opacity-60 font-semibold">HORARIO</p>
                                                                                                                                                <p class="text-azul-noche">${escapeHtml(horarioTexto)}</p>
                                                                                                                                            </div>
                                                                                                                                        </div>
                                                                                                                                    </div>
                                                                                                                                </div>
                                                                                                                            `;
                            } catch (err) {
                                content.innerHTML = `
                                                                                                                                <div class="text-center py-8">
                                                                                                                                    <i class="fas fa-exclamation-triangle text-naranja text-2xl mb-2"></i>
                                                                                                                                    <p class="text-sm text-red-600">${escapeHtml(err.message)}</p>
                                                                                                                                </div>
                                                                                                                            `;
                            }
                        }

                        // Cerrar modal de detalles del postulante
                        const cerrarDetallePostulante = document.getElementById('cerrar-modal-detalle-postulante');
                        if (cerrarDetallePostulante) {
                            cerrarDetallePostulante.addEventListener('click', function () {
                                const modal = document.getElementById('modal-detalles-postulante');
                                if (modal) modal.classList.add('hidden');
                            });
                        }

                        // Cerrar modal al hacer click en el backdrop
                        const modalDetallePostulante = document.getElementById('modal-detalles-postulante');
                        if (modalDetallePostulante) {
                            modalDetallePostulante.addEventListener('click', function (ev) {
                                if (ev.target === modalDetallePostulante) {
                                    modalDetallePostulante.classList.add('hidden');
                                }
                            });
                        }

                        // ========== MODAL DE DETALLES DE CONVOCATORIA ==========
                        let currentConvocatoriaId = null;
                        let diasSeleccionados = [];

                        // Función para formatear fecha a DD/MM/YYYY (sin problemas de zona horaria)
                        function formatDate(dateString) {
                            if (!dateString || dateString === 'N/A' || dateString === 'null' || dateString === null) return 'N/A';
                            try {
                                // Extraer solo la parte de fecha (YYYY-MM-DD) sin importar el formato
                                let datePart = '';

                                // Si viene en formato ISO completo (2025-11-15T00:00:00.000000Z)
                                if (dateString.includes('T')) {
                                    datePart = dateString.split('T')[0];
                                }
                                // Si viene en formato YYYY-MM-DD HH:mm:ss
                                else if (dateString.includes(' ')) {
                                    datePart = dateString.split(' ')[0];
                                }
                                // Si viene en formato YYYY-MM-DD
                                else if (dateString.includes('-')) {
                                    datePart = dateString;
                                }
                                // Si ya viene en formato DD/MM/YYYY, devolverlo tal cual
                                else if (dateString.includes('/')) {
                                    return dateString;
                                }
                                else {
                                    return dateString;
                                }

                                // Convertir YYYY-MM-DD a DD/MM/YYYY
                                if (datePart && datePart.includes('-')) {
                                    const parts = datePart.split('-');
                                    if (parts.length === 3) {
                                        return `${parts[2]}/${parts[1]}/${parts[0]}`;
                                    }
                                }

                                return dateString;
                            } catch (e) {
                                console.error('Error formateando fecha:', e, dateString);
                                return dateString;
                            }
                        }

                        // Listener para botón "Detalles"
                        document.addEventListener('click', function (e) {
                            const btn = e.target.closest('.detalles-conv-btn');
                            if (!btn) return;

                            const convId = btn.getAttribute('data-conv-id');
                            if (convId) {
                                openDetallesModal(convId);
                            }
                        });

                        // Abrir modal de detalles
                        function openDetallesModal(convocatoriaId) {
                            currentConvocatoriaId = convocatoriaId;
                            const modal = document.getElementById('modal-detalles-convocatoria');
                            const loading = document.getElementById('detalles-loading');
                            const content = document.getElementById('detalles-content');

                            if (!modal) return;

                            modal.classList.remove('hidden');
                            loading.classList.remove('hidden');
                            content.classList.add('hidden');

                            loadDetallesConvocatoria(convocatoriaId);
                        }

                        // Cerrar modal de detalles
                        function closeDetallesModal() {
                            const modal = document.getElementById('modal-detalles-convocatoria');
                            if (modal) modal.classList.add('hidden');
                            currentConvocatoriaId = null;
                            diasSeleccionados = [];
                            document.getElementById('form-detalles-convocatoria').reset();
                        }

                        // Cargar detalles de la convocatoria
                        async function loadDetallesConvocatoria(convocatoriaId) {
                            try {
                                const response = await fetch(`/convocatorias/${convocatoriaId}/detalles`);
                                if (!response.ok) throw new Error('Error al cargar detalles');

                                const data = await response.json();
                                const loading = document.getElementById('detalles-loading');
                                const content = document.getElementById('detalles-content');

                                // Llenar campos de solo lectura
                                document.getElementById('detalle-solicitante').value = data.solicitante || 'N/A';
                                document.getElementById('detalle-campana').value = data.convocatoria.campana || 'N/A';
                                document.getElementById('detalle-cargo').value = data.convocatoria.tipo_cargo || 'N/A';
                                document.getElementById('detalle-vacantes').value = data.convocatoria.requerimiento_personal || 'N/A';
                                document.getElementById('detalle-experiencia').value = data.convocatoria.experiencia || 'N/A';
                                document.getElementById('detalle-estado').value = data.convocatoria.estado || 'N/A';
                                document.getElementById('detalle-fecha-inicio').value = formatDate(data.convocatoria.fecha_inicio_capacitacion);
                                document.getElementById('detalle-fecha-fin').value = formatDate(data.convocatoria.fecha_fin_capacitacion);

                                // Llenar campos editables si existen detalles
                                if (data.detalles) {
                                    document.getElementById('razon_social_interna').value = data.detalles.razon_social_interna || '';
                                    document.getElementById('jefe_inmediato').value = data.detalles.jefe_inmediato || '';
                                    document.getElementById('cliente').value = data.detalles.cliente || '';
                                    document.getElementById('servicio_asociado').value = data.detalles.servicio_asociado || '';
                                    document.getElementById('centro_costo').value = data.detalles.centro_costo || '';
                                    document.getElementById('modalidad_trabajo_det').value = data.detalles.modalidad_trabajo || '';
                                    document.getElementById('lugar_trabajo').value = data.detalles.lugar_trabajo || '';
                                    document.getElementById('region_presencial').value = data.detalles.region_presencial || '';
                                    document.getElementById('tipo_contrato_det').value = data.detalles.tipo_contrato || '';

                                    if (data.detalles.dias_laborables && Array.isArray(data.detalles.dias_laborables)) {
                                        diasSeleccionados = data.detalles.dias_laborables;
                                        updateDiasText();
                                    }

                                    if (data.detalles.hora_inicio) {
                                        document.getElementById('hora_inicio').value = data.detalles.hora_inicio.substring(0, 5);
                                    }
                                    if (data.detalles.hora_fin) {
                                        document.getElementById('hora_fin').value = data.detalles.hora_fin.substring(0, 5);
                                    }

                                    document.getElementById('remuneracion').value = data.detalles.remuneracion || '';
                                    document.getElementById('variable').value = data.detalles.variable || '';
                                    document.getElementById('movilidad').value = data.detalles.movilidad || '';
                                    document.getElementById('bono_permanencia').value = data.detalles.bono_permanencia || '';
                                    document.getElementById('tipo_requerimiento').value = data.detalles.tipo_requerimiento || '';
                                    document.getElementById('motivo_requerimiento').value = data.detalles.motivo_requerimiento || '';

                                    if (data.detalles.fecha_sla) {
                                        const fechaSla = data.detalles.fecha_sla.includes('T') || data.detalles.fecha_sla.includes(' ')
                                            ? data.detalles.fecha_sla.split(' ')[0].split('T')[0]
                                            : data.detalles.fecha_sla;
                                        document.getElementById('fecha_sla').value = fechaSla;
                                    }
                                    if (data.detalles.fecha_objetivo) {
                                        const fechaObj = data.detalles.fecha_objetivo.includes('T') || data.detalles.fecha_objetivo.includes(' ')
                                            ? data.detalles.fecha_objetivo.split(' ')[0].split('T')[0]
                                            : data.detalles.fecha_objetivo;
                                        document.getElementById('fecha_objetivo').value = fechaObj;
                                    }

                                    document.getElementById('tipo_proceso').value = data.detalles.tipo_proceso || '';
                                    document.getElementById('tipo_gestion_det').value = data.detalles.tipo_gestion || '';
                                }

                                loading.classList.add('hidden');
                                content.classList.remove('hidden');
                            } catch (error) {
                                console.error('Error:', error);
                                Swal.fire('Error', 'No se pudieron cargar los detalles', 'error');
                                closeDetallesModal();
                            }
                        }

                        // Selector de días laborables con SweetAlert2
                        const btnSeleccionarDias = document.getElementById('btn-seleccionar-dias');
                        if (btnSeleccionarDias) {
                            btnSeleccionarDias.addEventListener('click', function () {
                                const dias = ['lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado', 'domingo'];

                                // Crear HTML con checkboxes
                                let htmlContent = '<div class="text-left space-y-2">';
                                dias.forEach(dia => {
                                    const checked = diasSeleccionados.includes(dia) ? 'checked' : '';
                                    const diaCapitalizado = dia.charAt(0).toUpperCase() + dia.slice(1);
                                    htmlContent += `
                                                                                                                                    <label class="flex items-center cursor-pointer p-2 hover:bg-celeste hover:bg-opacity-30 rounded">
                                                                                                                                        <input type="checkbox" value="${dia}" class="mr-3 w-4 h-4 text-naranja border-azul-noche rounded focus:ring-naranja" ${checked}>
                                                                                                                                        <span class="text-azul-noche">${diaCapitalizado}</span>
                                                                                                                                    </label>
                                                                                                                                `;
                                });
                                htmlContent += '</div>';

                                Swal.fire({
                                    title: 'Seleccionar Días Laborables',
                                    html: htmlContent,
                                    showCancelButton: true,
                                    confirmButtonText: 'Aceptar',
                                    cancelButtonText: 'Cancelar',
                                    confirmButtonColor: '#297373',
                                    cancelButtonColor: '#011627',
                                    width: '400px',
                                    didOpen: () => {
                                        // No necesitamos hacer nada especial aquí
                                    },
                                    preConfirm: () => {
                                        const checkboxes = Swal.getContainer().querySelectorAll('input[type="checkbox"]:checked');
                                        const selected = Array.from(checkboxes).map(cb => cb.value);
                                        if (selected.length === 0) {
                                            Swal.showValidationMessage('Debes seleccionar al menos un día');
                                            return false;
                                        }
                                        return selected;
                                    }
                                }).then((result) => {
                                    if (result.isConfirmed && result.value) {
                                        diasSeleccionados = result.value;
                                        document.getElementById('dias_laborables').value = JSON.stringify(diasSeleccionados);
                                        updateDiasText();
                                    }
                                });
                            });
                        }

                        function updateDiasText() {
                            const textEl = document.getElementById('dias-seleccionados-text');
                            if (diasSeleccionados.length > 0) {
                                textEl.textContent = diasSeleccionados.map(d => d.charAt(0).toUpperCase() + d.slice(1)).join(', ');
                            } else {
                                textEl.textContent = 'Seleccionar días';
                            }
                        }

                        // Guardar detalles
                        document.getElementById('btn-guardar-detalles').addEventListener('click', async function () {
                            const form = document.getElementById('form-detalles-convocatoria');
                            const formData = new FormData(form);

                            // Agregar días laborables
                            formData.append('dias_laborables', JSON.stringify(diasSeleccionados));

                            const payload = {};
                            formData.forEach((value, key) => {
                                if (key === 'dias_laborables') {
                                    payload[key] = JSON.parse(value);
                                } else {
                                    payload[key] = value || null;
                                }
                            });

                            Swal.fire({
                                title: 'Guardando...',
                                allowOutsideClick: false,
                                didOpen: () => { Swal.showLoading(); }
                            });

                            try {
                                const response = await fetch(`/convocatorias/${currentConvocatoriaId}/detalles`, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                    },
                                    body: JSON.stringify(payload)
                                });

                                const data = await response.json();

                                if (data.success) {
                                    Swal.fire('Éxito', 'Detalles guardados correctamente', 'success');
                                } else {
                                    Swal.fire('Error', data.error || 'No se pudieron guardar los detalles', 'error');
                                }
                            } catch (error) {
                                console.error('Error:', error);
                                Swal.fire('Error', 'Error al guardar los detalles', 'error');
                            }
                        });

                        // Botones cerrar
                        document.getElementById('cerrar-modal-detalles').addEventListener('click', closeDetallesModal);
                        document.getElementById('btn-cerrar-detalles').addEventListener('click', closeDetallesModal);

                        // Cerrar al hacer click fuera del modal
                        document.getElementById('modal-detalles-convocatoria').addEventListener('click', function (ev) {
                            if (ev.target === this) closeDetallesModal();
                        });
                    });
                </script>
    @endpush
@endif