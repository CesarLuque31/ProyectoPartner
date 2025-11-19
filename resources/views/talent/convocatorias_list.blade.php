@if(auth()->check() && auth()->user()->rol === 'jefe')
    <div class="p-6 bg-white shadow sm:rounded-lg">
        <h2 class="text-3xl font-bold mb-4 text-indigo-700">Listado de Convocatorias</h2>

        <!-- Filtros -->
        <div class="mb-6 p-4 bg-gray-50 border border-gray-200 rounded-lg">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Filtros</h3>
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Filtro por Campaña -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Campaña</label>
                        <select id="filtro-campana" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                            <option value="">Todas</option>
                            @foreach($campanias as $id => $nombre)
                                <option value="{{ $id }}">{{ $nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filtro por Cargo -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cargo</label>
                        <select id="filtro-cargo" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                            <option value="">Todos</option>
                            @foreach($cargos as $id => $nombre)
                                <option value="{{ $id }}">{{ $nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filtro por Estado -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                        <select id="filtro-estado" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                            <option value="">Todos</option>
                            <option value="Abierta">Abierta</option>
                            <option value="Cerrada">Cerrada</option>
                        </select>
                    </div>

                    <!-- Filtro por Fecha Inicio -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Desde Fecha Inicio</label>
                        <input type="date" id="filtro-fecha-inicio" class="w-full border border-gray-300 rounded px-3 py-2 text-sm" />
                    </div>

                    <!-- Filtro por Fecha Fin -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Hasta Fecha Fin</label>
                        <input type="date" id="filtro-fecha-fin" class="w-full border border-gray-300 rounded px-3 py-2 text-sm" />
                    </div>

                    <!-- Filtro por Reclutador Asignado -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Reclutador Asignado</label>
                        <select id="filtro-reclutador" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                            <option value="">Cualquiera</option>
                            <option value="sin-asignar">Sin Asignar</option>
                            @foreach($reclutadores_disponibles as $id => $nombre)
                                <option value="{{ $id }}">{{ $nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Botones de Filtro -->
                <div class="flex gap-2">
                    <button type="button" id="btn-aplicar-filtros" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded font-semibold text-sm">
                        Aplicar Filtros
                    </button>
                    <button type="button" id="btn-limpiar-filtros" class="bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded font-semibold text-sm">
                        Limpiar Filtros
                    </button>
                </div>
            </div>
        </div>

        <div id="convocatorias-container" class="grid grid-cols-1 md:grid-cols-2 gap-6"></div>
        <div id="convocatorias-empty" class="text-gray-600 hidden">No hay convocatorias que coincidan con los filtros seleccionados.</div>
        <!-- Modal Insertar Postulante -->
        <div id="modal-insertar-postulante" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl relative">
                <button id="cerrar-modal-postulante" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 text-2xl font-bold">&times;</button>
                <div class="p-6">
                    <h2 class="text-2xl font-bold mb-4 text-indigo-700">Insertar Postulante</h2>
                    <!-- Formulario de Postulante -->
                    <form id="form-insertar-postulante" autocomplete="off">
                        <input type="hidden" name="convocatoria_id" id="modal-convocatoria-id" />
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">DNI</label>
                            <div class="flex gap-2">
                                <input type="text" name="dni" id="modal-dni" class="w-full border border-gray-300 rounded px-3 py-2 text-sm" placeholder="Ingresa el DNI" />
                                <button type="button" id="modal-buscar-dni" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded font-semibold">Buscar</button>
                            </div>
                        </div>
                        <div class="mb-4">
                            <h3 class="text-lg font-semibold text-gray-700 mb-2">Información Obtenida</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-2">
                                <div>
                                    <label class="block text-sm text-gray-700">Nombres</label>
                                    <input type="text" name="nombres" id="modal-nombres" class="w-full border border-gray-200 rounded px-3 py-2 text-sm bg-gray-100" readonly />
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-700">Apellido Paterno</label>
                                    <input type="text" name="apellido_paterno" id="modal-apellido-paterno" class="w-full border border-gray-200 rounded px-3 py-2 text-sm bg-gray-100" readonly />
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-2">
                                <div>
                                    <label class="block text-sm text-gray-700">Apellido Materno</label>
                                    <input type="text" name="apellido_materno" id="modal-apellido-materno" class="w-full border border-gray-200 rounded px-3 py-2 text-sm bg-gray-100" readonly />
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-700">Fecha Nacimiento</label>
                                    <input type="text" name="fecha_nacimiento" id="modal-fecha-nacimiento" class="w-full border border-gray-200 rounded px-3 py-2 text-sm bg-gray-100" placeholder="dd/mm/aaaa" readonly />
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="block text-sm text-gray-700">Dirección</label>
                                <input type="text" name="direccion" id="modal-direccion" class="w-full border border-gray-200 rounded px-3 py-2 text-sm bg-gray-100" readonly />
                            </div>
                            <div class="mb-2">
                                <label class="block text-sm text-gray-700">Sexo</label>
                                <input type="text" name="sexo" id="modal-sexo" class="w-full border border-gray-200 rounded px-3 py-2 text-sm bg-gray-100" readonly />
                            </div>
                        </div>
                        <div class="flex justify-end gap-2 mt-6">
                            <button type="button" id="modal-cancelar" class="bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded font-semibold">Cancelar</button>
                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded font-semibold">Guardar Postulante</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endif

@if(auth()->check() && auth()->user()->rol === 'jefe')
    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
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
                    turnos += `<span class="inline-flex items-center px-3 py-1 rounded-full bg-indigo-50 border border-indigo-100 text-indigo-700 text-sm font-medium mr-2 mb-2">${lbl}</span>`;
                });
            } else {
                turnos = '<p class="text-gray-500">Sin turnos asignados</p>';
            }

            let recAsignados = '';
            if (Array.isArray(c.reclutadores_asignados_labels) && c.reclutadores_asignados_labels.length > 0) {
                c.reclutadores_asignados_labels.forEach(rec => {
                    recAsignados += `<span class="inline-flex items-center px-2 py-1 rounded-full bg-green-50 border border-green-100 text-green-700 text-xs font-medium mr-2 mb-2">${rec}</span>`;
                });
            } else {
                recAsignados = `<div class="flex items-center mt-2"><i class="fas fa-exclamation-circle text-yellow-500 mr-2"></i><p class="text-yellow-700 text-sm">Sin reclutadores asignados</p></div>`;
            }

            return `
            <div class="bg-white border rounded-lg p-6 shadow-sm convocatoria-item">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <p class="text-xs text-gray-400">Creada: ${c.created_at}</p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-sm font-semibold ${c.estado === 'Abierta' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'}">
                        ${c.estado}
                    </span>
                </div>
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <p class="text-xs text-gray-500 font-semibold">CAMPAÑA</p>
                        <p class="text-indigo-600 font-semibold">${c.campania_nombre}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 font-semibold">CARGO</p>
                        <p class="text-gray-700 font-semibold">${c.cargo_nombre}</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <p class="text-xs text-gray-500 font-semibold">VACANTES</p>
                        <p class="text-gray-700">${c.requerimiento_personal}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 font-semibold">EXPERIENCIA REQUERIDA</p>
                        <p class="text-gray-700">${c.experiencia_label}</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <p class="text-xs text-gray-500 font-semibold">INICIO CAPACITACIÓN</p>
                        <p class="text-gray-700">${c.fecha_inicio_capacitacion}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 font-semibold">FIN CAPACITACIÓN</p>
                        <p class="text-gray-700">${c.fecha_fin_capacitacion}</p>
                    </div>
                </div>
                <div class="mb-4">
                    <p class="text-xs text-gray-500 font-semibold">TURNOS</p>
                    <div class="flex flex-wrap gap-2">${turnos}</div>
                </div>
                <div class="mt-6 flex items-center space-x-1">
                    <button type="button" class="swal-assign-btn bg-blue-500 hover:bg-blue-600 text-white px-2 py-0.5 rounded text-xs font-semibold" data-conv-id="${c.id}" data-conv-name="${c.campania_nombre}">
                        <i class="fas fa-users mr-1"></i> Asignar Reclutadores
                    </button>
                    <form action="/convocatorias/${c.id}" method="POST" class="inline delete-conv-form">
                        <input type="hidden" name="_token" value="${c.csrf_token}">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="button" class="swal-delete-btn bg-red-500 hover:bg-red-600 text-white px-2 py-0.5 rounded text-xs font-semibold">
                            <i class="fas fa-trash mr-1"></i> Eliminar
                        </button>
                    </form>
                    <button type="button" class="insertar-postulante-btn bg-green-600 hover:bg-green-700 text-white px-2 py-0.5 rounded text-xs font-semibold" data-conv-id="${c.id}" data-conv-name="${c.campania_nombre}">
                        <i class="fas fa-user-plus mr-1"></i> Insertar Postulante
                    </button>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <p class="text-xs text-gray-500 font-semibold">RECLUTADORES ASIGNADOS</p>
                    <div class="flex flex-wrap gap-2 mt-2">${recAsignados}</div>
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
                    resp.convocatorias.forEach(c => {
                        container.innerHTML += renderConvocatoriaCard(c, resp.campanias, resp.cargos, resp.horarios, resp.reclutadores_disponibles);
                    });
                } else {
                    emptyMsg.classList.remove('hidden');
                }
            })
            .catch(() => {
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
            emptyMsg.classList.add('hidden');
        }

        if (btnAplicar) btnAplicar.addEventListener('click', aplicarFiltros);
        if (btnLimpiar) btnLimpiar.addEventListener('click', limpiarFiltros);

        // Aquí puedes agregar los listeners para los botones de eliminar y asignar reclutadores después de renderizar
        // (puedes usar event delegation si lo prefieres)
    });
    </script>
@endpush
@endif