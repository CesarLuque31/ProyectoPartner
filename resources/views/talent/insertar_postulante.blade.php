@if(auth()->check() && (auth()->user()->rol === 'jefe' || auth()->user()->rol === 'reclutador'))
    @php
        // Cargar datos necesarios una sola vez en el servidor
        $TalentoController = app(\App\Http\Controllers\TalentoController::class);
        
        // Obtener tipos de contrato y horarios base
        $tiposContrato = collect([]);
        $horariosBase = collect([]);
        
        try {
            // Cargar tipos de contrato desde DB (tabla en SQL Server)
            $tiposContrato = \Illuminate\Support\Facades\DB::table('raz_tipocontratos')
                ->where('estado', 1)
                ->select('tipo_contrato')
                ->orderBy('tipo_contrato')
                ->get();
        } catch (\Exception $e) {
            \Log::warning('Error cargando tipos_contrato: ' . $e->getMessage());
        }
        
        try {
            // Cargar horarios base desde DB
            $horariosBase = $TalentoController->getHorariosBase();
        } catch (\Exception $e) {
            \Log::warning('Error cargando horarios_base: ' . $e->getMessage());
        }
        
        // Cargar ubigeos desde archivo JSON
        $ubigeos = [];
        $ubigeoPath = public_path('data');
        
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
    @endphp
    <div class="w-full">
        <!-- Sección: Selector de Tipo de Documento -->
        <div class="mb-6 bg-gradient-to-r from-celeste to-celeste rounded-lg p-5 border-2 border-azul-noche border-opacity-20">
            <h3 class="text-lg font-semibold mb-4 text-azul-noche flex items-center">
                <i class="fas fa-id-card mr-2 text-naranja"></i>
                Tipo de Documento
            </h3>
            <div class="mb-4">
                <label class="block text-sm font-medium text-azul-noche mb-2">Selecciona el tipo de documento <span class="text-naranja">*</span></label>
                <select id="tipo-documento-select" class="border-2 border-azul-noche border-opacity-30 focus:border-naranja focus:ring-2 focus:ring-naranja focus:ring-opacity-30 p-3 w-full rounded-lg transition-all outline-none">
                    <option value="">Seleccionar tipo de documento</option>
                    <option value="DNI">DNI</option>
                    <option value="Carnet de Extranjería">Carnet de Extranjería</option>
                </select>
            </div>
        </div>

        <!-- Sección: Búsqueda por DNI -->
        <div id="busqueda-dni-section" class="mb-6 bg-gradient-to-r from-celeste to-celeste rounded-lg p-5 border-2 border-azul-noche border-opacity-20" style="display: none;">
            <h3 class="text-lg font-semibold mb-4 text-azul-noche flex items-center">
                <i class="fas fa-search mr-2 text-naranja"></i>
                Búsqueda por DNI
            </h3>
            <div class="flex gap-3 items-end">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-azul-noche mb-2">Número de DNI</label>
                    <input id="dni-input" type="text" placeholder="Ingresa el DNI (8 dígitos)" maxlength="8" class="border-2 border-azul-noche border-opacity-30 focus:border-naranja focus:ring-2 focus:ring-naranja focus:ring-opacity-30 p-3 w-full rounded-lg transition-all outline-none" />
                </div>
                <button id="btn-buscar" type="button" class="bg-naranja hover:bg-naranja hover:bg-opacity-90 text-white px-6 py-3 rounded-lg font-semibold transition-all shadow-md hover:shadow-lg flex items-center">
                    <i class="fas fa-search mr-2"></i>Buscar
                </button>
            </div>
        </div>

        <!-- Sección: Formulario Manual para Carnet de Extranjería -->
        <div id="formulario-extranjeria-section" class="mb-6 bg-gradient-to-r from-celeste to-celeste rounded-lg p-5 border-2 border-azul-noche border-opacity-20" style="display: none;">
            <h3 class="text-lg font-semibold mb-4 text-azul-noche flex items-center">
                <i class="fas fa-passport mr-2 text-naranja"></i>
                Datos del Extranjero
            </h3>
            <p class="text-sm text-azul-noche text-opacity-70 mb-4">Complete todos los datos manualmente</p>
            <div class="flex gap-3 items-end">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-azul-noche mb-2">Número de Carnet de Extranjería</label>
                    <input id="carnet-input" type="text" placeholder="Ingresa el número de carnet" class="border-2 border-azul-noche border-opacity-30 focus:border-naranja focus:ring-2 focus:ring-naranja focus:ring-opacity-30 p-3 w-full rounded-lg transition-all outline-none" />
                </div>
                <button id="btn-continuar-extranjeria" type="button" class="bg-naranja hover:bg-naranja hover:bg-opacity-90 text-white px-6 py-3 rounded-lg font-semibold transition-all shadow-md hover:shadow-lg flex items-center">
                    <i class="fas fa-arrow-right mr-2"></i>Continuar
                </button>
            </div>
        </div>
            
        <!-- Formulario de Postulante -->
        <form id="postulante-form">
            @csrf
            
            <!-- Campos Autofill (Read-only para DNI, Editable para Carnet) -->
            <div class="mb-6 bg-gray-50 rounded-lg p-5 border border-gray-200">
                <h3 class="text-lg font-semibold mb-4 text-azul-noche flex items-center">
                    <i class="fas fa-user-check mr-2 text-verde"></i>
                    Información Personal
                </h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-azul-noche mb-2">Nombres <span class="text-naranja campo-requerido">*</span></label>
                        <input name="nombres" id="nombres" class="border-2 border-azul-noche border-opacity-20 bg-white p-3 w-full rounded-lg text-azul-noche cursor-not-allowed campo-personal" readonly />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-azul-noche mb-2">Apellido Paterno <span class="text-naranja campo-requerido">*</span></label>
                        <input name="ap_pat" id="ap_pat" class="border-2 border-azul-noche border-opacity-20 bg-white p-3 w-full rounded-lg text-azul-noche cursor-not-allowed campo-personal" readonly />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-azul-noche mb-2">Apellido Materno <span class="text-naranja campo-requerido">*</span></label>
                        <input name="ap_mat" id="ap_mat" class="border-2 border-azul-noche border-opacity-20 bg-white p-3 w-full rounded-lg text-azul-noche cursor-not-allowed campo-personal" readonly />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-azul-noche mb-2">Número de Documento <span class="text-naranja campo-requerido">*</span></label>
                        <input name="dni" id="documento-numero" class="border-2 border-azul-noche border-opacity-20 bg-white p-3 w-full rounded-lg text-azul-noche cursor-not-allowed campo-personal" readonly />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-azul-noche mb-2">Fecha de Nacimiento <span class="text-naranja campo-requerido">*</span></label>
                        <input name="fecha_nac" id="fecha_nac" type="date" class="border-2 border-azul-noche border-opacity-20 bg-white p-3 w-full rounded-lg text-azul-noche cursor-not-allowed campo-personal" readonly />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-azul-noche mb-2">Sexo / Género <span class="text-naranja campo-requerido">*</span></label>
                        <select name="sexo" id="sexo" class="border-2 border-azul-noche border-opacity-20 bg-white p-3 w-full rounded-lg text-azul-noche cursor-not-allowed campo-personal" disabled>
                            <option value="">Seleccionar</option>
                            <option value="1">Masculino</option>
                            <option value="2">Femenino</option>
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-azul-noche mb-2">Dirección <span class="text-naranja campo-requerido">*</span></label>
                        <input name="direccion" id="direccion" class="border-2 border-azul-noche border-opacity-20 bg-white p-3 w-full rounded-lg text-azul-noche cursor-not-allowed campo-personal" readonly />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-azul-noche mb-2">Estado Civil</label>
                        <select name="est_civil" id="est_civil" class="border-2 border-azul-noche border-opacity-20 bg-white p-3 w-full rounded-lg text-azul-noche cursor-not-allowed campo-personal" disabled>
                            <option value="">Seleccionar (opcional)</option>
                            <option value="soltero">Soltero(a)</option>
                            <option value="casado">Casado(a)</option>
                            <option value="divorciado">Divorciado(a)</option>
                            <option value="viudo">Viudo(a)</option>
                            <option value="conviviente">Conviviente</option>
                        </select>
                    </div>
                    <div id="nacionalidad-container" style="display: none;">
                        <label class="block text-sm font-medium text-azul-noche mb-2">Nacionalidad <span class="text-naranja">*</span></label>
                        <input name="nacionalidad" id="nacionalidad" class="border-2 border-azul-noche border-opacity-30 focus:border-naranja focus:ring-2 focus:ring-naranja focus:ring-opacity-30 p-3 w-full rounded-lg transition-all outline-none" />
                    </div>
                </div>
            </div>

            <!-- Campos ocultos -->
            <input type="hidden" name="tipo_documento" id="tipo-documento-field" />
            <input type="hidden" id="sexo_numeric" />
            <!-- Campo convocatoria_id (rellenado desde el botón de la convocatoria) -->
            <input type="hidden" name="convocatoria_id" id="convocatoria-id" />

            <!-- Campos que el usuario debe rellenar -->
            <div class="mb-6" id="additional-fields" style="display: none;">
                <div class="bg-celeste border-l-4 border-naranja p-4 mb-4 rounded-r-lg">
                    <h3 class="text-lg font-semibold text-azul-noche flex items-center mb-1">
                        <i class="fas fa-info-circle mr-2 text-naranja"></i>
                        Información Adicional Requerida
                    </h3>
                    <p class="text-sm text-azul-noche text-opacity-70">Por favor completa todos los campos marcados con <span class="text-naranja font-semibold">*</span></p>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-azul-noche mb-2">Número de Contacto <span class="text-naranja">*</span></label>
                        <input name="celular" id="celular" type="tel" placeholder="Ej: 987654321" class="border-2 border-azul-noche border-opacity-30 focus:border-naranja focus:ring-2 focus:ring-naranja focus:ring-opacity-30 p-3 w-full rounded-lg transition-all outline-none" required />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-azul-noche mb-2">Número de WhatsApp <span class="text-naranja">*</span></label>
                        <input name="whatsapp" id="whatsapp" type="tel" placeholder="Ej: 987654321" class="border-2 border-azul-noche border-opacity-30 focus:border-naranja focus:ring-2 focus:ring-naranja focus:ring-opacity-30 p-3 w-full rounded-lg transition-all outline-none" required />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-azul-noche mb-2">Correo Electrónico <span class="text-naranja">*</span></label>
                        <input name="correo" id="correo" type="email" placeholder="Ej: correo@ejemplo.com" class="border-2 border-azul-noche border-opacity-30 focus:border-naranja focus:ring-2 focus:ring-naranja focus:ring-opacity-30 p-3 w-full rounded-lg transition-all outline-none" required />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-azul-noche mb-2">Departamento <span class="text-naranja">*</span></label>
                        <select name="departamento" id="departamento" class="border-2 border-azul-noche border-opacity-30 focus:border-naranja focus:ring-2 focus:ring-naranja focus:ring-opacity-30 p-3 w-full rounded-lg transition-all outline-none" required>
                            <option value="">Seleccionar departamento</option>
                            @php
                                $departamentos = $ubigeos['departamentos'] ?? [];
                                usort($departamentos, fn($a, $b) => strcmp($a['name'] ?? '', $b['name'] ?? ''));
                            @endphp
                            @foreach($departamentos as $dept)
                                <option value="{{ $dept['id'] ?? '' }}">{{ $dept['name'] ?? '' }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Provincia <span class="text-red-600">*</span></label>
                        <select name="provincia" id="provincia" class="border-2 border-azul-noche border-opacity-30 focus:border-naranja focus:ring-2 focus:ring-naranja focus:ring-opacity-30 p-3 w-full rounded-lg transition-all outline-none" required>
                            <option value="">Seleccionar departamento primero</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-azul-noche mb-2">Distrito <span class="text-naranja">*</span></label>
                        <select name="distrito" id="distrito" class="border-2 border-azul-noche border-opacity-30 focus:border-naranja focus:ring-2 focus:ring-naranja focus:ring-opacity-30 p-3 w-full rounded-lg transition-all outline-none" required>
                            <option value="">Seleccionar provincia primero</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Experiencia en Call Center <span class="text-red-600">*</span></label>
                        <select name="experiencia_callcenter" id="experiencia_callcenter" class="border-2 border-azul-noche border-opacity-30 focus:border-naranja focus:ring-2 focus:ring-naranja focus:ring-opacity-30 p-3 w-full rounded-lg transition-all outline-none" required>
                            <option value="">Seleccionar</option>
                            <option value="si">Sí</option>
                            <option value="no">No</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-azul-noche mb-2">Discapacidad <span class="text-naranja">*</span></label>
                        <select name="discapacidad" id="discapacidad" class="border-2 border-azul-noche border-opacity-30 focus:border-naranja focus:ring-2 focus:ring-naranja focus:ring-opacity-30 p-3 w-full rounded-lg transition-all outline-none" required>
                            <option value="">Seleccionar</option>
                            <option value="si">Sí</option>
                            <option value="no">No</option>
                        </select>
                    </div>

                    <div id="tipo_discapacidad_container" style="display: none;">
                        <label class="block text-sm font-medium text-azul-noche mb-2">Tipo de Discapacidad</label>
                        <input name="tipo_discapacidad" id="tipo_discapacidad" class="border-2 border-azul-noche border-opacity-30 focus:border-naranja focus:ring-2 focus:ring-naranja focus:ring-opacity-30 p-3 w-full rounded-lg transition-all outline-none" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-azul-noche mb-2">Tipo de Contrato <span class="text-naranja">*</span></label>
                        <select name="tipo_contrato" id="tipo_contrato" class="border-2 border-azul-noche border-opacity-30 focus:border-naranja focus:ring-2 focus:ring-naranja focus:ring-opacity-30 p-3 w-full rounded-lg transition-all outline-none" required>
                            <option value="">Seleccionar</option>
                            @foreach($tiposContrato as $tc)
                                <option value="{{ $tc->tipo_contrato }}">{{ $tc->tipo_contrato }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-azul-noche mb-2">Modalidad de Trabajo <span class="text-naranja">*</span></label>
                        <select name="modalidad_trabajo" id="modalidad_trabajo" class="border-2 border-azul-noche border-opacity-30 focus:border-naranja focus:ring-2 focus:ring-naranja focus:ring-opacity-30 p-3 w-full rounded-lg transition-all outline-none" required>
                            <option value="">Seleccionar</option>
                            <option value="presencial">Presencial</option>
                            <option value="remoto">Remoto</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-azul-noche mb-2">Tipo de Gestión (Horario) <span class="text-naranja">*</span></label>
                        <select name="tipo_gestion" id="tipo_gestion" class="border-2 border-azul-noche border-opacity-30 focus:border-naranja focus:ring-2 focus:ring-naranja focus:ring-opacity-30 p-3 w-full rounded-lg transition-all outline-none" required>
                            <option value="">Seleccionar</option>
                            @foreach($horariosBase as $h)
                                <option value="{{ $h->HorarioID }}">{{ $h->HorarioCompleto }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Botón Guardar (Oculto hasta buscar) -->
            <div id="button-container" style="display: none;" class="flex gap-3 mt-6 pt-6 border-t border-azul-noche border-opacity-20">
                <button id="btn-guardar" type="button" class="flex-1 bg-gradient-to-r from-verde to-verde hover:from-verde hover:to-verde hover:bg-opacity-90 text-white px-6 py-3 rounded-lg font-semibold transition-all shadow-md hover:shadow-lg flex items-center justify-center">
                    <i class="fas fa-save mr-2"></i>Guardar Postulante
                </button>
                <button id="btn-limpiar" type="button" class="bg-azul-noche bg-opacity-60 hover:bg-opacity-80 text-white px-6 py-3 rounded-lg font-semibold transition-all shadow-md hover:shadow-lg flex items-center justify-center">
                    <i class="fas fa-redo mr-2"></i>Limpiar
                </button>
            </div>
        </form>
    </div>

    @php
        // Preparar datos para JS (en array PHP, no en JSON embebido)
        $departamentosJson = json_encode($ubigeos['departamentos'] ?? []);
        $provinciasJson = json_encode($ubigeos['provincias'] ?? []);
        $distritosJson = json_encode($ubigeos['distritos'] ?? []);
    @endphp
    @push('scripts')
    <script>
        // Datos de ubigeos ya cargados desde PHP (sin necesidad de fetch)
        window.ubigeoData = {
            departamentos: {!! $departamentosJson !!},
            provincias: {!! $provinciasJson !!},
            distritos: {!! $distritosJson !!}
        };
    </script>
    <script>
document.addEventListener('DOMContentLoaded', function(){
    const tipoGestionSelect = document.getElementById('tipo_gestion');
    const discapacidadSelect = document.getElementById('discapacidad');
    const tipoDiscapacidadContainer = document.getElementById('tipo_discapacidad_container');
    const additionalFields = document.getElementById('additional-fields');
    const buttonContainer = document.getElementById('button-container');
    const tipoDocumentoSelect = document.getElementById('tipo-documento-select');
    const busquedaDniSection = document.getElementById('busqueda-dni-section');
    const formularioExtranjeriaSection = document.getElementById('formulario-extranjeria-section');
    const nacionalidadContainer = document.getElementById('nacionalidad-container');
    const camposPersonales = document.querySelectorAll('.campo-personal');
    
    // Flag para evitar búsquedas concurrentes o dobles
    let isSearchingPostulante = false;
    let isExtranjeria = false;

    // Manejar cambio de tipo de documento
    tipoDocumentoSelect.addEventListener('change', function(){
        const tipoDoc = this.value;
        document.getElementById('tipo-documento-field').value = tipoDoc;
        
        // Resetear formulario
        resetFormulario();
        
        if(tipoDoc === 'DNI') {
            busquedaDniSection.style.display = 'block';
            formularioExtranjeriaSection.style.display = 'none';
            nacionalidadContainer.style.display = 'none';
            isExtranjeria = false;
        } else if(tipoDoc === 'Carnet de Extranjería') {
            busquedaDniSection.style.display = 'none';
            formularioExtranjeriaSection.style.display = 'block';
            nacionalidadContainer.style.display = 'block';
            isExtranjeria = true;
        } else {
            busquedaDniSection.style.display = 'none';
            formularioExtranjeriaSection.style.display = 'none';
            nacionalidadContainer.style.display = 'none';
            isExtranjeria = false;
        }
    });

    // Botón continuar para Carnet de Extranjería
    document.getElementById('btn-continuar-extranjeria').addEventListener('click', function(){
        const carnet = document.getElementById('carnet-input').value;
        
        if(!carnet || carnet.trim() === '') {
            Swal.fire('Error', 'Por favor ingresa el número de carnet', 'error');
            return;
        }

        // Verificar si el carnet ya existe
        Swal.fire({
            title: 'Verificando...',
            didOpen: () => { Swal.showLoading(); },
            allowOutsideClick: false,
        });

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        fetch('/postulantes/check-dni', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ dni: carnet })
        })
        .then(response => response.json())
        .then(data => {
            Swal.close();
            
            if(data.success && data.exists) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Postulante ya Registrado',
                    text: 'Este número de carnet ya está registrado en el sistema.',
                });
                return;
            }

            // Habilitar todos los campos para edición manual
            habilitarCamposExtranjeria(carnet);
            
            Swal.fire('Continuar', 'Complete todos los datos manualmente', 'info');
        })
        .catch(err => {
            Swal.close();
            Swal.fire('Error', 'Error al verificar el carnet: ' + err.message, 'error');
        });
    });

    function habilitarCamposExtranjeria(carnet) {
        // Establecer el número de documento
        document.getElementById('documento-numero').value = carnet;
        
        // Habilitar todos los campos personales para edición
        camposPersonales.forEach(campo => {
            campo.removeAttribute('readonly');
            campo.removeAttribute('disabled');
            campo.classList.remove('cursor-not-allowed', 'bg-white');
            campo.classList.add('focus:border-naranja', 'focus:ring-2', 'focus:ring-naranja', 'focus:ring-opacity-30');
            
            // Para el select de sexo
            if(campo.tagName === 'SELECT') {
                campo.removeAttribute('disabled');
            }
        });
        
        // Limpiar valores previos
        document.getElementById('nombres').value = '';
        document.getElementById('ap_pat').value = '';
        document.getElementById('ap_mat').value = '';
        document.getElementById('fecha_nac').value = '';
        document.getElementById('direccion').value = '';
        document.getElementById('sexo').value = '';
        document.getElementById('est_civil').value = '';
        document.getElementById('nacionalidad').value = '';
        
        // Mostrar campos adicionales
        additionalFields.style.display = 'block';
        buttonContainer.style.display = 'flex';
    }

    function resetFormulario() {
        document.getElementById('postulante-form').reset();
        document.getElementById('dni-input').value = '';
        document.getElementById('carnet-input').value = '';
        document.getElementById('documento-numero').value = '';
        additionalFields.style.display = 'none';
        buttonContainer.style.display = 'none';
        tipoDiscapacidadContainer.style.display = 'none';
        
        // Restaurar readonly en campos personales
        camposPersonales.forEach(campo => {
            campo.setAttribute('readonly', 'readonly');
            if(campo.tagName === 'SELECT') {
                campo.setAttribute('disabled', 'disabled');
            }
            campo.classList.add('cursor-not-allowed', 'bg-white');
            campo.classList.remove('focus:border-naranja', 'focus:ring-2', 'focus:ring-naranja', 'focus:ring-opacity-30');
        });
        
        if (departamentoSelect) departamentoSelect.selectedIndex = 0;
        if (provinciaSelect) provinciaSelect.innerHTML = '<option value="">Seleccionar departamento primero</option>';
        if (distritoSelect) distritoSelect.innerHTML = '<option value="">Seleccionar provincia primero</option>';
    }

    // Mostrar campo "Tipo Discapacidad" solo si se selecciona "Sí"
    discapacidadSelect.addEventListener('change', function(){
        if(this.value === 'si') {
            tipoDiscapacidadContainer.style.display = 'block';
            document.getElementById('tipo_discapacidad').required = true;
        } else {
            tipoDiscapacidadContainer.style.display = 'none';
            document.getElementById('tipo_discapacidad').required = false;
            document.getElementById('tipo_discapacidad').value = '';
        }
    });

    // Selectores dependientes para ubicación
    const departamentoSelect = document.getElementById('departamento');
    const provinciaSelect = document.getElementById('provincia');
    const distritoSelect = document.getElementById('distrito');

    // Datos de ubigeos (ya cargados desde PHP en el servidor, no necesitamos fetch)
    let departamentosData = window.ubigeoData.departamentos;
    let provinciasData = window.ubigeoData.provincias;
    let distritosData = window.ubigeoData.distritos;

    // Poblar departamentos al cargar
    function populateDepartamentos() {
        departamentoSelect.innerHTML = '<option value="">Seleccionar</option>';
        departamentosData.forEach((d) => {
            const opt = document.createElement('option');
            opt.value = d.id;
            opt.textContent = d.name;
            departamentoSelect.appendChild(opt);
        });
        provinciaSelect.innerHTML = '<option value="">Seleccionar departamento primero</option>';
        distritoSelect.innerHTML = '<option value="">Seleccionar provincia primero</option>';
    }

    function populateProvincias(depId) {
        provinciaSelect.innerHTML = '<option value="">Seleccionar</option>';
        distritoSelect.innerHTML = '<option value="">Seleccionar provincia primero</option>';
        if (!depId) return;
        
        const depKey = String(depId);
        const provincias = provinciasData.filter(p => String(p.department_id) === depKey);
        provincias.forEach((p) => {
            const opt = document.createElement('option');
            opt.value = p.id;
            opt.textContent = p.name;
            provinciaSelect.appendChild(opt);
        });
    }

    function populateDistritos(provId) {
        distritoSelect.innerHTML = '<option value="">Seleccionar</option>';
        if (!provId) return;
        
        const provKey = String(provId);
        const distritos = distritosData.filter(d => String(d.province_id) === provKey);
        distritos.forEach(d => {
            const opt = document.createElement('option');
            opt.value = d.id;
            opt.textContent = d.name;
            distritoSelect.appendChild(opt);
        });
    }

    // Inicializar al cargar si hay datos
    if (departamentosData.length > 0) {
        populateDepartamentos();
    }

    departamentoSelect.addEventListener('change', function(){
        populateProvincias(this.value);
    });

    provinciaSelect.addEventListener('change', function(){
        populateDistritos(this.value);
    });

    // Helpers para obtener nombres de ubigeo
    function findDepartamentoName(id) {
        const dep = departamentosData.find(d => String(d.id) === String(id));
        return dep ? dep.name : null;
    }
    function findProvinciaName(id) {
        const prov = provinciasData.find(p => String(p.id) === String(id));
        return prov ? prov.name : null;
    }
    function findDistritoName(id) {
        const dist = distritosData.find(d => String(d.id) === String(id));
        return dist ? dist.name : null;
    }

    // Buscar por DNI
    // Usamos `onclick` en vez de addEventListener para evitar handlers duplicados
    document.getElementById('btn-buscar').onclick = async function(event) {
        const dni = document.getElementById('dni-input').value;

        if (!dni || dni.length !== 8) {
            Swal.fire('Error', 'Por favor ingresa un DNI válido (8 dígitos)', 'error');
            return;
        }

        // Evitar ejecuciones concurrentes o dobles
        if (isSearchingPostulante) {
            console.warn('Busqueda ya en progreso - ignorando clic adicional');
            return;
        }
        isSearchingPostulante = true;

        // Intentar detener otros handlers y deshabilitar el botón mientras se procesa
        try { if (event.stopPropagation) event.stopPropagation(); } catch(e) {}
        try { if (event.stopImmediatePropagation) event.stopImmediatePropagation(); } catch(e) {}
        const btnBuscar = document.getElementById('btn-buscar');
        if (btnBuscar) btnBuscar.disabled = true;

        Swal.fire({
            title: 'Verificando...',
            didOpen: () => {
                Swal.showLoading();
            },
            allowOutsideClick: false,
        });

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        try {
            // PASO 1: Verificar si el DNI ya existe en la BD
            console.log('🔍 Verificando si DNI existe en BD...');
            const checkResponse = await fetch('/postulantes/check-dni', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ dni: dni })
            });

            const checkData = await checkResponse.json();
            console.log('✅ checkDNI response:', checkData);

            // Si el DNI ya existe, DETENER AQUÍ
            if (checkData.success && checkData.exists) {
                console.log('⛔ DNI EXISTE - Deteniendo proceso');
                Swal.fire({
                    icon: 'warning',
                    title: 'Postulante ya Registrado',
                    text: 'Este DNI ya está registrado en el sistema. No se puede registrar nuevamente.',
                });
                return; // RETORNAR AQUÍ - NO CONTINUAR
            }

            // PASO 2: Si no existe, buscar en la API
            console.log('✅ DNI disponible - Buscando en API...');
            Swal.update({ title: 'Buscando datos...' });

            const routeUrl = document.querySelector('meta[name="route-consulta"]')?.getAttribute('content') || '/postulantes/consulta';
            
            const consultaResponse = await fetch(routeUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ dni: dni })
            });

            const consultaData = await consultaResponse.json();
            console.log('✅ consulta response:', consultaData);

            if (!consultaResponse.ok) {
                throw new Error(consultaData.error || 'Error en búsqueda de API');
            }

            Swal.close();

            // PASO 3: Rellenar formulario con los datos
            if(consultaData.success && consultaData.data) {
                const d = consultaData.data;
                
                // Poblar campos autofill
                document.getElementById('nombres').value = d.nombres || '';
                document.getElementById('ap_pat').value = d.ap_pat || '';
                document.getElementById('ap_mat').value = d.ap_mat || '';
                document.getElementById('fecha_nac').value = d.fecha_nac || '';
                document.getElementById('direccion').value = d.direccion || '';
                document.getElementById('documento-numero').value = d.dni || dni;
                
                // Mapeo de sexo para el select
                const sexoSelect = document.getElementById('sexo');
                if(d.sexo === '1' || d.sexo === 1) {
                    sexoSelect.value = '1';
                } else if(d.sexo === '2' || d.sexo === 2) {
                    sexoSelect.value = '2';
                }
                
                // Guardar sexo numérico en campo oculto
                document.getElementById('sexo_numeric').value = d.sexo || '';
                
                // Cargar estado civil si viene de la API
                if(d.est_civil) {
                    const estCivilSelect = document.getElementById('est_civil');
                    // Mapear valores comunes de la API a los valores del select
                    const estCivilLower = String(d.est_civil).toLowerCase().trim();
                    let estCivilValue = '';
                    
                    if(estCivilLower.includes('soltero') || estCivilLower === 's') {
                        estCivilValue = 'soltero';
                    } else if(estCivilLower.includes('casado') || estCivilLower === 'c') {
                        estCivilValue = 'casado';
                    } else if(estCivilLower.includes('divorciado') || estCivilLower === 'd') {
                        estCivilValue = 'divorciado';
                    } else if(estCivilLower.includes('viudo') || estCivilLower === 'v') {
                        estCivilValue = 'viudo';
                    } else if(estCivilLower.includes('conviviente') || estCivilLower.includes('union')) {
                        estCivilValue = 'conviviente';
                    }
                    
                    if(estCivilValue && estCivilSelect) {
                        estCivilSelect.value = estCivilValue;
                    }
                }

                // Mostrar formulario
                additionalFields.style.display = 'block';
                buttonContainer.style.display = 'flex';

                // Si la API retornó ubicación, intentar seleccionar en los selects dependientes
                if (d.departamento && departamentosData.length > 0) {
                    const depName = d.departamento;
                    const depExists = departamentosData.find(x => x.name.toLowerCase() === (depName || '').toLowerCase());
                    if (depExists) {
                        departamentoSelect.value = depExists.id;
                        populateProvincias(depExists.id);
                        if (d.provincia) {
                            const provName = d.provincia;
                            const provExists = provinciasData.find(p => p.name.toLowerCase() === (provName || '').toLowerCase() && p.department_id === depExists.id);
                            if (provExists) {
                                provinciaSelect.value = provExists.id;
                                populateDistritos(provExists.id);
                                if (d.distrito) {
                                    const distName = d.distrito;
                                    const distExists = distritosData.find(dist => dist.name.toLowerCase() === (distName || '').toLowerCase() && dist.province_id === provExists.id);
                                    if (distExists) {
                                        distritoSelect.value = distExists.id;
                                    }
                                }
                            }
                        }
                    }
                }

                Swal.fire('Éxito', 'Datos encontrados. Completa los campos obligatorios.', 'success');
            } else {
                const errorMsg = consultaData.error || 'No se encontraron datos para ese DNI';
                Swal.fire('Error', errorMsg, 'error');
            }

        } catch (err) {
            Swal.close();
            console.error('❌ Error:', err);
            Swal.fire('Error', err.message || 'No se pudo completar la búsqueda. Intenta de nuevo.', 'error');
        } finally {
            try { if (typeof btnBuscar !== 'undefined' && btnBuscar) btnBuscar.disabled = false; } catch(e) {}
            isSearchingPostulante = false;
        }
    };

    // Limpiar formulario
    document.getElementById('btn-limpiar').addEventListener('click', function(){
        resetFormulario();
        tipoDocumentoSelect.value = '';
        document.getElementById('tipo-documento-field').value = '';
    });

    // Guardar postulante
    document.getElementById('btn-guardar').addEventListener('click', function(){
        const form = document.getElementById('postulante-form');
        
        // Validación manual de campos obligatorios
        const camposObligatorios = [
            { id: 'nombres', label: 'Nombres' },
            { id: 'ap_pat', label: 'Apellido Paterno' },
            { id: 'ap_mat', label: 'Apellido Materno' },
            { id: 'documento-numero', label: 'Número de Documento' },
            { id: 'fecha_nac', label: 'Fecha de Nacimiento' },
            { id: 'sexo', label: 'Sexo' },
            { id: 'direccion', label: 'Dirección' },
            // est_civil es opcional (nullable)
            { id: 'celular', label: 'Número de Contacto' },
            { id: 'whatsapp', label: 'Número de WhatsApp' },
            { id: 'correo', label: 'Correo Electrónico' },
            { id: 'departamento', label: 'Departamento' },
            { id: 'provincia', label: 'Provincia' },
            { id: 'distrito', label: 'Distrito' },
            { id: 'experiencia_callcenter', label: 'Experiencia en Call Center' },
            { id: 'discapacidad', label: 'Discapacidad' },
            { id: 'tipo_contrato', label: 'Tipo de Contrato' },
            { id: 'modalidad_trabajo', label: 'Modalidad de Trabajo' },
            { id: 'tipo_gestion', label: 'Tipo de Gestión' }
        ];

        // Si es extranjería, validar nacionalidad
        if(isExtranjeria) {
            camposObligatorios.push({ id: 'nacionalidad', label: 'Nacionalidad' });
        }

        let camposFaltantes = [];
        camposObligatorios.forEach(campo => {
            const elemento = document.getElementById(campo.id);
            if(elemento && (!elemento.value || elemento.value.trim() === '')) {
                camposFaltantes.push(campo.label);
            }
        });

        if(camposFaltantes.length > 0) {
            Swal.fire('Error', 'Por favor completa todos los campos obligatorios:\n' + camposFaltantes.join(', '), 'error');
            return;
        }

        const formData = new FormData(form);
        const payload = {};
        formData.forEach((v,k)=> payload[k]=v);

        // Convertir IDs de ubigeo a nombres antes de enviar
        if (payload.departamento) {
            const depName = findDepartamentoName(payload.departamento);
            if (depName) payload.departamento = depName;
        }
        if (payload.provincia) {
            const provName = findProvinciaName(payload.provincia);
            if (provName) payload.provincia = provName;
        }
        if (payload.distrito) {
            const distName = findDistritoName(payload.distrito);
            if (distName) payload.distrito = distName;
        }

        // Asegurar que el formulario incluya la convocatoria asociada (si fue establecida desde la lista)
        const convInput = document.getElementById('convocatoria-id');
        if (convInput && convInput.value) {
            payload.convocatoria_id = convInput.value;
        }
        
        // Para DNI, usar sexo del API (numérico)
        // Para extranjería, usar el valor del select
        if(!isExtranjeria && payload.sexo_numeric) {
            payload.sexo = payload.sexo_numeric;
            delete payload.sexo_numeric;
        }

        Swal.fire({
            title: 'Guardando...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        const storeUrl = document.querySelector('meta[name="route-store"]')?.getAttribute('content') || '/postulantes';
        
        fetch(storeUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(payload)
        }).then(r=>r.json()).then(res=>{
            if(res.success){ 
                Swal.fire('Éxito','Postulante guardado correctamente','success').then(() => {
                    // Reset completo del formulario
                    resetFormulario();
                    tipoDocumentoSelect.value = '';
                    document.getElementById('tipo-documento-field').value = '';
                    
                    // Emitir evento global para que el modal (o la lista) recargue postulantes
                    try {
                        const createdConvId = payload.convocatoria_id || document.getElementById('convocatoria-id')?.value;
                        window.dispatchEvent(new CustomEvent('postulante:created', { detail: { convocatoria_id: createdConvId } }));
                    } catch(e){}
                });
            } else {
                Swal.fire('Error', res.error || 'No se pudo guardar', 'error');
            }
        }).catch(err=> Swal.fire('Error', 'Error al guardar: '+err.message, 'error'));
    });
});
    </script>
    @endpush
    </div>
@endif
