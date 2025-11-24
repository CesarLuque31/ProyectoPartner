@if(auth()->check() && (auth()->user()->rol === 'jefe' || auth()->user()->rol === 'reclutador'))
    <!-- Sección: Selección de Tipo de Documento -->
    <div class="mb-3 bg-gradient-to-r from-celeste to-celeste rounded-lg p-3 border-2 border-azul-noche border-opacity-20">
        <h3 class="text-md font-semibold mb-2 text-azul-noche flex items-center">
            <i class="fas fa-file-excel mr-2 text-naranja"></i>
            Importación Masiva de Postulantes
        </h3>
        <div class="mb-0">
            <label class="block text-xs font-medium text-azul-noche mb-1">Selecciona el tipo de documento <span
                    class="text-naranja">*</span></label>
            <select id="tipo-documento-importar"
                class="border-2 border-azul-noche border-opacity-30 focus:border-naranja focus:ring-2 focus:ring-naranja focus:ring-opacity-30 p-2 w-full md:w-1/2 rounded-lg transition-all outline-none text-sm">
                <option value="">Seleccionar tipo de documento</option>
                <option value="DNI">DNI</option>
                <option value="Carnet de Extranjería">Carnet de Extranjería</option>
            </select>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Sección: Descargar Plantilla -->
        <div id="seccion-plantilla" class="mb-3 bg-white rounded-lg p-3 border-2 border-azul-noche border-opacity-20"
            style="display: none;">
            <h4 class="text-sm font-semibold mb-2 text-azul-noche flex items-center">
                <i class="fas fa-download mr-2 text-verde"></i>
                Descargar Plantilla
            </h4>
            <p class="text-xs text-azul-noche text-opacity-70 mb-3">
                Descarga la plantilla, complétala y súbela.
            </p>
            <button id="btn-descargar-plantilla" type="button"
                class="w-full bg-verde hover:bg-verde hover:bg-opacity-90 text-white px-4 py-2 rounded-lg font-semibold transition-all shadow-md hover:shadow-lg flex items-center justify-center text-sm">
                <i class="fas fa-file-excel mr-2"></i>Descargar
            </button>
        </div>

        <!-- Sección: Subir Archivo -->
        <div id="seccion-subir" class="mb-3 bg-white rounded-lg p-3 border-2 border-azul-noche border-opacity-20"
            style="display: none;">
            <h4 class="text-sm font-semibold mb-2 text-azul-noche flex items-center">
                <i class="fas fa-upload mr-2 text-naranja"></i>
                Subir Archivo Excel
            </h4>
            <p class="text-xs text-azul-noche text-opacity-70 mb-3">
                Sube el archivo .xlsm completado.
            </p>
            <form id="form-importar" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="convocatoria_id" id="convocatoria-id-importar" />
                <input type="hidden" name="tipo_documento" id="tipo-documento-importar-field" />
                <div class="mb-3">
                    <input type="file" id="archivo-excel" name="archivo_excel" accept=".xlsm,.xlsx"
                        class="border-2 border-azul-noche border-opacity-30 focus:border-naranja focus:ring-2 focus:ring-naranja focus:ring-opacity-30 p-2 w-full rounded-lg transition-all outline-none text-xs"
                        required />
                </div>
                <button id="btn-importar" type="button"
                    class="w-full bg-naranja hover:bg-naranja hover:bg-opacity-90 text-white px-4 py-2 rounded-lg font-semibold transition-all shadow-md hover:shadow-lg flex items-center justify-center text-sm">
                    <i class="fas fa-upload mr-2"></i>Importar
                </button>
            </form>
        </div>
    </div>

    <!-- Sección: Resultados -->
    <div id="seccion-resultados" class="mt-4 bg-white rounded-lg p-5 border-2 border-azul-noche border-opacity-20 hidden">
        <h4 class="text-md font-semibold mb-3 text-azul-noche flex items-center">
            <i class="fas fa-chart-bar mr-2 text-azul-noche"></i>
            Resultados de la Importación
        </h4>
        <div id="resultados-contenido"></div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const tipoDocSelect = document.getElementById('tipo-documento-importar');
                const seccionPlantilla = document.getElementById('seccion-plantilla');
                const seccionSubir = document.getElementById('seccion-subir');
                const btnDescargar = document.getElementById('btn-descargar-plantilla');
                const btnImportar = document.getElementById('btn-importar');
                const formImportar = document.getElementById('form-importar');
                const archivoExcel = document.getElementById('archivo-excel');
                const seccionResultados = document.getElementById('seccion-resultados');
                const resultadosContenido = document.getElementById('resultados-contenido');

                // Cuando se selecciona el tipo de documento
                tipoDocSelect.addEventListener('change', function () {
                    const tipoDoc = this.value;
                    document.getElementById('tipo-documento-importar-field').value = tipoDoc;

                    if (tipoDoc) {
                        seccionPlantilla.style.display = 'block';
                        seccionSubir.style.display = 'block';
                    } else {
                        seccionPlantilla.style.display = 'none';
                        seccionSubir.style.display = 'none';
                    }
                });

                // Descargar plantilla
                btnDescargar.addEventListener('click', function () {
                    const tipoDoc = tipoDocSelect.value;
                    if (!tipoDoc) {
                        Swal.fire('Error', 'Por favor selecciona un tipo de documento primero', 'error');
                        return;
                    }

                    // Redirigir a la ruta de descarga
                    const url = `/postulantes/descargar-plantilla?tipo=${encodeURIComponent(tipoDoc)}`;
                    window.location.href = url;
                });

                // Importar archivo
                btnImportar.addEventListener('click', function () {
                    const tipoDoc = tipoDocSelect.value;
                    const archivo = archivoExcel.files[0];
                    const convocatoriaId = document.getElementById('convocatoria-id-importar').value;

                    if (!tipoDoc) {
                        Swal.fire('Error', 'Por favor selecciona un tipo de documento', 'error');
                        return;
                    }

                    if (!archivo) {
                        Swal.fire('Error', 'Por favor selecciona un archivo Excel', 'error');
                        return;
                    }

                    // Validar extensión
                    const extension = archivo.name.split('.').pop().toLowerCase();
                    if (extension !== 'xlsm' && extension !== 'xlsx') {
                        Swal.fire('Error', 'El archivo debe ser un Excel (.xlsm o .xlsx)', 'error');
                        return;
                    }

                    Swal.fire({
                        title: 'Importando...',
                        text: 'Por favor espera mientras se procesan los datos',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });

                    const formData = new FormData(formImportar);
                    formData.append('tipo_documento', tipoDoc);
                    if (convocatoriaId) {
                        formData.append('convocatoria_id', convocatoriaId);
                    }

                    fetch('/postulantes/importar', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: formData
                    })
                        .then(response => response.json())
                        .then(data => {
                            Swal.close();

                            if (data.success) {
                                mostrarResultados(data);
                                // Limpiar formulario
                                formImportar.reset();
                                archivoExcel.value = '';

                                // Emitir evento para recargar lista de postulantes
                                if (convocatoriaId) {
                                    window.dispatchEvent(new CustomEvent('postulante:created', {
                                        detail: { convocatoria_id: convocatoriaId }
                                    }));
                                }
                            } else {
                                Swal.fire('Error', data.error || 'No se pudo importar el archivo', 'error');
                            }
                        })
                        .catch(error => {
                            Swal.close();
                            console.error('Error:', error);
                            Swal.fire('Error', 'Error al importar el archivo: ' + error.message, 'error');
                        });
                });

                function mostrarResultados(data) {
                    seccionResultados.classList.remove('hidden');

                    let html = `
                                <div class="space-y-4">
                                    <div class="grid grid-cols-3 gap-4">
                                        <div class="bg-verde bg-opacity-20 p-4 rounded-lg border border-verde">
                                            <p class="text-xs text-azul-noche text-opacity-60 font-semibold mb-1">EXITOSOS</p>
                                            <p class="text-2xl font-bold text-verde">${data.exitosos || 0}</p>
                                        </div>
                                        <div class="bg-naranja bg-opacity-20 p-4 rounded-lg border border-naranja">
                                            <p class="text-xs text-azul-noche text-opacity-60 font-semibold mb-1">ERRORES</p>
                                            <p class="text-2xl font-bold text-naranja">${data.errores || 0}</p>
                                        </div>
                                        <div class="bg-azul-noche bg-opacity-20 p-4 rounded-lg border border-azul-noche">
                                            <p class="text-xs text-azul-noche text-opacity-60 font-semibold mb-1">TOTAL</p>
                                            <p class="text-2xl font-bold text-azul-noche">${data.total || 0}</p>
                                        </div>
                                    </div>
                            `;

                    if (data.detalles_errores && data.detalles_errores.length > 0) {
                        html += `
                                    <div class="mt-4">
                                        <p class="text-sm font-semibold text-azul-noche mb-2">Detalles de Errores:</p>
                                        <div class="border border-naranja border-opacity-30 rounded-lg p-3">
                                            <ul class="space-y-1 text-sm text-azul-noche">
                                `;
                        data.detalles_errores.forEach((error, index) => {
                            html += `<li class="flex items-start"><span class="text-naranja mr-2">${index + 1}.</span><span>${error}</span></li>`;
                        });
                        html += `
                                            </ul>
                                        </div>
                                    </div>
                                `;
                    }

                    html += `</div>`; // Close the .space-y-4 div
                    resultadosContenido.innerHTML = html;
                }

                // Establecer convocatoria_id cuando se abre el modal
                window.addEventListener('postulante:modal-opened', function (ev) {
                    const convId = ev.detail && ev.detail.convocatoria_id;
                    if (convId) {
                        document.getElementById('convocatoria-id-importar').value = convId;
                    }
                });
            });
        </script>
    @endpush
@endif