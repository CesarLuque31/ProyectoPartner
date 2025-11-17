@if(auth()->check() && (auth()->user()->rol === 'jefe' || auth()->user()->rol === 'reclutador'))
    <div class="max-w-4xl p-6">
        <h2 class="text-2xl font-bold mb-6">Insertar Postulante</h2>

        <div class="bg-white shadow-md rounded-lg p-6">
        <!-- Sección: Búsqueda por DNI -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold mb-4 text-gray-700">Datos Personales (Búsqueda por DNI)</h3>
            <div class="flex gap-4 items-end">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">DNI</label>
                    <input id="dni-input" type="text" placeholder="Ingresa el DNI" class="border border-gray-300 p-2 w-full rounded" />
                </div>
                <button id="btn-buscar" type="button" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition">Buscar</button>
            </div>
        </div>

        <!-- Formulario de Postulante -->
        <form id="postulante-form">
            @csrf
            
            <!-- Campos Autofill (Read-only) -->
            <div class="mb-6">
                <h3 class="text-lg font-semibold mb-4 text-gray-700">Información Obtenida</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombres</label>
                        <input name="nombres" id="nombres" class="border border-gray-300 p-2 w-full rounded bg-gray-100" readonly />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Apellido Paterno</label>
                        <input name="ap_pat" id="ap_pat" class="border border-gray-300 p-2 w-full rounded bg-gray-100" readonly />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Apellido Materno</label>
                        <input name="ap_mat" id="ap_mat" class="border border-gray-300 p-2 w-full rounded bg-gray-100" readonly />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Nacimiento</label>
                        <input name="fecha_nac" id="fecha_nac" type="date" class="border border-gray-300 p-2 w-full rounded bg-gray-100" readonly />
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
                        <input name="direccion" id="direccion" class="border border-gray-300 p-2 w-full rounded bg-gray-100" readonly />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Sexo</label>
                        <input name="sexo" id="sexo" class="border border-gray-300 p-2 w-full rounded bg-gray-100" readonly />
                    </div>
                </div>
            </div>

            <!-- Campo DNI Oculto para envío -->
            <input type="hidden" name="dni" id="dni-field" />

            <!-- Campos que el usuario debe rellenar -->
            <div class="mb-6" id="additional-fields" style="display: none;">
                <h3 class="text-lg font-semibold mb-4 text-gray-700">Información Adicional (Obligatorio)</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Celular <span class="text-red-600">*</span></label>
                        <input name="celular" id="celular" type="tel" class="border border-gray-300 p-2 w-full rounded" required />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Correo <span class="text-red-600">*</span></label>
                        <input name="correo" id="correo" type="email" class="border border-gray-300 p-2 w-full rounded" required />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Departamento <span class="text-red-600">*</span></label>
                        <select name="departamento" id="departamento" class="border border-gray-300 p-2 w-full rounded" required>
                            <option value="">Cargando...</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Provincia <span class="text-red-600">*</span></label>
                        <select name="provincia" id="provincia" class="border border-gray-300 p-2 w-full rounded" required>
                            <option value="">Seleccionar departamento primero</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Distrito <span class="text-red-600">*</span></label>
                        <select name="distrito" id="distrito" class="border border-gray-300 p-2 w-full rounded" required>
                            <option value="">Seleccionar provincia primero</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Experiencia Call Center <span class="text-red-600">*</span></label>
                        <select name="experiencia_callcenter" id="experiencia_callcenter" class="border border-gray-300 p-2 w-full rounded" required>
                            <option value="">Seleccionar</option>
                            <option value="si">Sí</option>
                            <option value="no">No</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Discapacidad <span class="text-red-600">*</span></label>
                        <select name="discapacidad" id="discapacidad" class="border border-gray-300 p-2 w-full rounded" required>
                            <option value="">Seleccionar</option>
                            <option value="si">Sí</option>
                            <option value="no">No</option>
                        </select>
                    </div>

                    <div id="tipo_discapacidad_container" style="display: none;">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo Discapacidad</label>
                        <input name="tipo_discapacidad" id="tipo_discapacidad" class="border border-gray-300 p-2 w-full rounded" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo Contrato <span class="text-red-600">*</span></label>
                        <select name="tipo_contrato" id="tipo_contrato" class="border border-gray-300 p-2 w-full rounded" required>
                            <option value="">Cargando...</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Modalidad Trabajo <span class="text-red-600">*</span></label>
                        <select name="modalidad_trabajo" id="modalidad_trabajo" class="border border-gray-300 p-2 w-full rounded" required>
                            <option value="">Seleccionar</option>
                            <option value="presencial">Presencial</option>
                            <option value="remoto">Remoto</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo Gestión (Horario) <span class="text-red-600">*</span></label>
                        <select name="tipo_gestion" id="tipo_gestion" class="border border-gray-300 p-2 w-full rounded" required>
                            <option value="">Cargando...</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Botón Guardar (Oculto hasta buscar) -->
            <div id="button-container" style="display: none;" class="flex gap-4">
                <button id="btn-guardar" type="button" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700 transition">Guardar Postulante</button>
                <button id="btn-limpiar" type="button" class="bg-gray-400 text-white px-6 py-2 rounded hover:bg-gray-500 transition">Limpiar Formulario</button>
            </div>
        </form>
    </div>
</div>

    @push('scripts')
    <script>
document.addEventListener('DOMContentLoaded', function(){
    const tipoGestionSelect = document.getElementById('tipo_gestion');
    const discapacidadSelect = document.getElementById('discapacidad');
    const tipoDiscapacidadContainer = document.getElementById('tipo_discapacidad_container');
    const additionalFields = document.getElementById('additional-fields');
    const buttonContainer = document.getElementById('button-container');
    // Flag para evitar búsquedas concurrentes o dobles
    let isSearchingPostulante = false;

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

    let departamentosData = null;
    let provinciasData = null;
    let distritosData = null;

    // Cargar todos los JSONs de ubigeos
    Promise.all([
        fetch('/data/ubigeo_peru_2016_departamentos.json').then(r => r.json()),
        fetch('/data/ubigeo_peru_2016_provincias.json').then(r => r.json()),
        fetch('/data/ubigeo_peru_2016_distritos.json').then(r => r.json())
    ]).then(([depts, provs, dists]) => {
        departamentosData = depts;
        provinciasData = provs;
        distritosData = dists;
        populateDepartamentos();
    }).catch(err => {
        console.error('Error cargando ubigeos:', err);
        departamentoSelect.innerHTML = '<option value="">Error al cargar departamentos</option>';
    });

    function populateDepartamentos() {
        departamentoSelect.innerHTML = '<option value="">Seleccionar</option>';
        departamentosData.forEach((d) => {
            const opt = document.createElement('option');
            opt.value = d.name;
            opt.textContent = d.name;
            departamentoSelect.appendChild(opt);
        });
        provinciaSelect.innerHTML = '<option value="">Seleccionar departamento primero</option>';
        distritoSelect.innerHTML = '<option value="">Seleccionar provincia primero</option>';
    }

    function populateProvincias(depName) {
        provinciaSelect.innerHTML = '<option value="">Seleccionar</option>';
        distritoSelect.innerHTML = '<option value="">Seleccionar provincia primero</option>';
        if (!depName) return;
        
        const depId = departamentosData.find(d => d.name === depName)?.id;
        if (!depId) return;
        
        const provincias = provinciasData.filter(p => p.department_id === depId);
        provincias.forEach((p) => {
            const opt = document.createElement('option');
            opt.value = p.name;
            opt.textContent = p.name;
            provinciaSelect.appendChild(opt);
        });
    }

    function populateDistritos(provName) {
        distritoSelect.innerHTML = '<option value="">Seleccionar</option>';
        if (!provName) return;
        
        const provId = provinciasData.find(p => p.name === provName)?.id;
        if (!provId) return;
        
        const distritos = distritosData.filter(d => d.province_id === provId);
        distritos.forEach(d => {
            const opt = document.createElement('option');
            opt.value = d.name;
            opt.textContent = d.name;
            distritoSelect.appendChild(opt);
        });
    }

    departamentoSelect.addEventListener('change', function(){
        populateProvincias(this.value);
    });

    provinciaSelect.addEventListener('change', function(){
        populateDistritos(this.value);
    });

    // Cargar opciones de horarios (tipo_gestion)
    fetch('/api/horarios-base')
        .then(r => r.json())
        .then(data => {
            tipoGestionSelect.innerHTML = '<option value="">Seleccionar</option>';
            if (Array.isArray(data)) {
                data.forEach(h => {
                    const opt = document.createElement('option');
                    opt.value = h.HorarioID;
                    opt.textContent = h.HorarioCompleto;
                    tipoGestionSelect.appendChild(opt);
                });
            }
        }).catch(()=>{ tipoGestionSelect.innerHTML = '<option value="">Error al cargar</option>'; });

    // Cargar opciones de tipos de contrato
    const tipoContratoSelect = document.getElementById('tipo_contrato');
    fetch('/api/tipos-contrato')
        .then(r => r.json())
        .then(data => {
            tipoContratoSelect.innerHTML = '<option value="">Seleccionar</option>';
            if (Array.isArray(data)) {
                data.forEach(tc => {
                    const opt = document.createElement('option');
                    opt.value = tc.tipo_contrato;
                    opt.textContent = tc.tipo_contrato;
                    tipoContratoSelect.appendChild(opt);
                });
            }
        }).catch(()=>{ tipoContratoSelect.innerHTML = '<option value="">Error al cargar</option>'; });

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
                
                // Mapeo de sexo
                let sexoLabel = d.sexo;
                if(d.sexo === '1' || d.sexo === 1) sexoLabel = 'Masculino';
                else if(d.sexo === '2' || d.sexo === 2) sexoLabel = 'Femenino';
                
                // Poblar campos autofill
                document.getElementById('nombres').value = d.nombres || '';
                document.getElementById('ap_pat').value = d.ap_pat || '';
                document.getElementById('ap_mat').value = d.ap_mat || '';
                document.getElementById('fecha_nac').value = d.fecha_nac || '';
                document.getElementById('direccion').value = d.direccion || '';
                document.getElementById('sexo').value = sexoLabel || '';
                
                // Guardar sexo numérico
                let sexoNumericInput = document.getElementById('sexo_numeric');
                if (!sexoNumericInput) {
                    sexoNumericInput = document.createElement('input');
                    sexoNumericInput.type = 'hidden';
                    sexoNumericInput.name = 'sexo_numeric';
                    sexoNumericInput.id = 'sexo_numeric';
                    document.getElementById('postulante-form').appendChild(sexoNumericInput);
                }
                sexoNumericInput.value = d.sexo || '';
                
                document.getElementById('dni-field').value = d.dni || dni;

                // Mostrar formulario
                additionalFields.style.display = 'block';
                buttonContainer.style.display = 'flex';

                // Si la API retornó ubicación, intentar seleccionar en los selects dependientes
                if (d.departamento && departamentosData) {
                    const depName = d.departamento;
                    const depExists = departamentosData.find(x => x.name.toLowerCase() === (depName || '').toLowerCase());
                    if (depExists) {
                        departamentoSelect.value = depExists.name;
                        populateProvincias(depExists.name);
                        if (d.provincia) {
                            const provName = d.provincia;
                            const provExists = provinciasData.find(p => p.name.toLowerCase() === (provName || '').toLowerCase() && p.department_id === depExists.id);
                            if (provExists) {
                                provinciaSelect.value = provExists.name;
                                populateDistritos(provExists.name);
                                if (d.distrito) {
                                    const distName = d.distrito;
                                    const distExists = distritosData.find(dist => dist.name.toLowerCase() === (distName || '').toLowerCase() && dist.province_id === provExists.id);
                                    if (distExists) {
                                        distritoSelect.value = distExists.name;
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
        document.getElementById('postulante-form').reset();
        document.getElementById('dni-input').value = '';
        document.getElementById('dni-field').value = '';
        additionalFields.style.display = 'none';
        buttonContainer.style.display = 'none';
        tipoDiscapacidadContainer.style.display = 'none';
        if (departamentoSelect) departamentoSelect.selectedIndex = 0;
        if (provinciaSelect) provinciaSelect.innerHTML = '<option value="">Seleccionar departamento primero</option>';
        if (distritoSelect) distritoSelect.innerHTML = '<option value="">Seleccionar provincia primero</option>';
    });

    // Guardar postulante
    document.getElementById('btn-guardar').addEventListener('click', function(){
        const form = document.getElementById('postulante-form');
        
        if(!form.checkValidity()) {
            Swal.fire('Error', 'Por favor completa todos los campos obligatorios', 'error');
            return;
        }

        const formData = new FormData(form);
        const payload = {};
        formData.forEach((v,k)=> payload[k]=v);
        
        // Usar sexo numérico
        if(payload.sexo_numeric) {
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
                    document.getElementById('postulante-form').reset();
                    document.getElementById('dni-input').value = '';
                    document.getElementById('dni-field').value = '';
                    additionalFields.style.display = 'none';
                    buttonContainer.style.display = 'none';
                    tipoDiscapacidadContainer.style.display = 'none';
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
