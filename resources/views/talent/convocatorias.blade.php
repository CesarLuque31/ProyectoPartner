@if(auth()->check() && auth()->user()->rol === 'jefe')
    @php
        // Obtenemos una instancia del controlador y los horarios
        $TalentoController = app(\App\Http\Controllers\TalentoController::class);
        $horarios = new \Illuminate\Support\Collection();
        $campanias = new \Illuminate\Support\Collection();
        $cargos = new \Illuminate\Support\Collection();
        
        try {
            // Obtenemos los horarios base de la base de datos
            $horarios = $TalentoController->getHorariosBase(); 
            $campanias = $TalentoController->getCampanias();
            $cargos = $TalentoController->getCargos();
        } catch (\Exception $e) {
            // En caso de fallo (problema de DB o conexión), $horarios será una colección vacía.
            \Log::error('Fallo al cargar horarios: ' . $e->getMessage());
        }
    @endphp

    <div class="bg-white shadow-xl rounded-xl border-2 border-azul-noche border-opacity-20 overflow-hidden">
        <div class="bg-gradient-to-r from-azul-noche to-azul-noche px-6 py-4">
            <h2 class="text-2xl font-bold text-white flex items-center">
                <i class="fas fa-bullhorn mr-2"></i>
                Crear Nueva Convocatoria
            </h2>
        </div>
        <div class="p-6">
            <p class="text-azul-noche text-opacity-70 mb-6 flex items-center">
                <i class="fas fa-info-circle mr-2 text-naranja"></i>
                Completa los detalles de la nueva campaña de reclutamiento.
            </p>

            <form id="form-crear-convocatoria" method="POST" action="{{ route('convocatorias.store') }}" class="space-y-6">
                @csrf

                {{-- SECCIÓN: INFORMACIÓN BÁSICA --}}
                <div class="bg-celeste bg-opacity-30 p-6 rounded-lg border border-azul-noche border-opacity-20">
                    <h3 class="text-lg font-bold text-azul-noche mb-4 flex items-center">
                        <i class="fas fa-info-circle mr-2 text-naranja"></i>
                        Información Básica
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-input-label for="campana" value="Nombre de la Campaña" class="text-azul-noche font-semibold mb-2" />
                            <div class="relative">
                                <i class="fas fa-building absolute left-3 top-1/2 transform -translate-y-1/2 text-naranja z-10"></i>
                                <select id="campana" name="campana" required class="block w-full pl-10 pr-3 py-2 border-2 border-azul-noche border-opacity-30 focus:border-naranja focus:ring-2 focus:ring-naranja focus:ring-opacity-30 rounded-lg transition-all appearance-none bg-white">
                                    <option value="" disabled {{ old('campana') ? '' : 'selected' }}>Selecciona una Campaña</option>
                                    @if($campanias->isEmpty())
                                        <option value="" disabled>-- No se pudieron cargar las campañas --</option>
                                    @endif
                                    @foreach($campanias as $camp)
                                        <option value="{{ $camp->id }}" {{ old('campana') == $camp->id ? 'selected' : '' }}>{{ $camp->nombre }}</option>
                                    @endforeach
                                </select>
                                <i class="fas fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-azul-noche pointer-events-none"></i>
                            </div>
                            <x-input-error :messages="$errors->get('campana')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="requerimiento_personal" value="Requerimiento de Personal" class="text-azul-noche font-semibold mb-2" />
                            <div class="relative">
                                <i class="fas fa-users absolute left-3 top-1/2 transform -translate-y-1/2 text-naranja z-10"></i>
                                <x-text-input id="requerimiento_personal" class="block w-full pl-10 border-2 border-azul-noche border-opacity-30 focus:border-naranja focus:ring-2 focus:ring-naranja focus:ring-opacity-30 rounded-lg transition-all" type="number" name="requerimiento_personal" :value="old('requerimiento_personal')" min="1" required />
                            </div>
                            <x-input-error :messages="$errors->get('requerimiento_personal')" class="mt-2" />
                        </div>
                    </div>
                </div>

                {{-- SECCIÓN: CARGO Y EXPERIENCIA --}}
                <div class="bg-celeste bg-opacity-30 p-6 rounded-lg border border-azul-noche border-opacity-20">
                    <h3 class="text-lg font-bold text-azul-noche mb-4 flex items-center">
                        <i class="fas fa-briefcase mr-2 text-naranja"></i>
                        Cargo y Requisitos
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-input-label for="tipo_cargo" value="Tipo de Cargo" class="text-azul-noche font-semibold mb-2" />
                            <div class="relative">
                                <i class="fas fa-user-tie absolute left-3 top-1/2 transform -translate-y-1/2 text-naranja z-10"></i>
                                <select id="tipo_cargo" name="tipo_cargo" required class="block w-full pl-10 pr-3 py-2 border-2 border-azul-noche border-opacity-30 focus:border-naranja focus:ring-2 focus:ring-naranja focus:ring-opacity-30 rounded-lg transition-all appearance-none bg-white">
                                    <option value="" disabled {{ old('tipo_cargo') ? '' : 'selected' }}>Selecciona un Cargo</option>
                                    @if($cargos->isEmpty())
                                        <option value="" disabled>-- No se pudieron cargar los cargos --</option>
                                    @endif
                                    @foreach($cargos as $cargo)
                                        <option value="{{ $cargo->id }}" {{ old('tipo_cargo') == $cargo->id ? 'selected' : '' }}>{{ $cargo->nombre }}</option>
                                    @endforeach
                                </select>
                                <i class="fas fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-azul-noche pointer-events-none"></i>
                            </div>
                            <x-input-error :messages="$errors->get('tipo_cargo')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="experiencia" value="¿Requiere Experiencia Previa?" class="text-azul-noche font-semibold mb-2" />
                            <div class="relative">
                                <i class="fas fa-star absolute left-3 top-1/2 transform -translate-y-1/2 text-naranja z-10"></i>
                                <select id="experiencia" name="experiencia" required class="block w-full pl-10 pr-3 py-2 border-2 border-azul-noche border-opacity-30 focus:border-naranja focus:ring-2 focus:ring-naranja focus:ring-opacity-30 rounded-lg transition-all appearance-none bg-white">
                                    <option value="" disabled selected>Selecciona una Opción</option>
                                    <option value="si" {{ old('experiencia') == 'si' ? 'selected' : '' }}>Sí</option>
                                    <option value="no" {{ old('experiencia') == 'no' ? 'selected' : '' }}>No</option>
                                    <option value="indiferente" {{ old('experiencia') == 'indiferente' ? 'selected' : '' }}>Indiferente</option>
                                </select>
                                <i class="fas fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-azul-noche pointer-events-none"></i>
                            </div>
                            <x-input-error :messages="$errors->get('experiencia')" class="mt-2" />
                        </div>
                    </div>
                </div>

                {{-- SECCIÓN: TURNOS --}}
                <div class="bg-celeste bg-opacity-30 p-6 rounded-lg border border-azul-noche border-opacity-20">
                    <h3 class="text-lg font-bold text-azul-noche mb-4 flex items-center">
                        <i class="fas fa-clock mr-2 text-naranja"></i>
                        Turnos Disponibles (Opcional)
                    </h3>
                    <div>
                        <x-input-label for="turnos" value="Selecciona uno o más horarios" class="text-azul-noche font-semibold mb-2" />
                        <select id="turnos" name="turnos[]" multiple class="select2 block w-full border-2 border-azul-noche border-opacity-30 focus:border-naranja focus:ring-2 focus:ring-naranja focus:ring-opacity-30 rounded-lg transition-all">
                            @if($horarios->isEmpty())
                                <option value="" disabled>-- Error de Conexión o Horarios Vacíos --</option>
                            @endif
                            @foreach ($horarios as $horario)
                                <option value="{{ $horario->HorarioID }}" 
                                        {{ in_array($horario->HorarioID, old('turnos', [])) ? 'selected' : '' }}>
                                    {{ $horario->HorarioCompleto }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('turnos')" class="mt-2" />
                        <p class="mt-2 text-sm text-azul-noche text-opacity-70 flex items-center">
                            <i class="fas fa-info-circle mr-2 text-naranja"></i>
                            Puedes seleccionar múltiples turnos.
                        </p>
                    </div>
                </div>

                {{-- SECCIÓN: FECHAS DE CAPACITACIÓN --}}
                <div class="bg-celeste bg-opacity-30 p-6 rounded-lg border border-azul-noche border-opacity-20">
                    <h3 class="text-lg font-bold text-azul-noche mb-4 flex items-center">
                        <i class="fas fa-calendar-alt mr-2 text-naranja"></i>
                        Rango de Capacitación
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-input-label for="fecha_inicio_capacitacion" value="Fecha de Inicio" class="text-azul-noche font-semibold mb-2" />
                            <div class="relative">
                                <i class="fas fa-calendar-check absolute left-3 top-1/2 transform -translate-y-1/2 text-naranja z-10"></i>
                                <x-text-input id="fecha_inicio_capacitacion" class="block w-full pl-10 border-2 border-azul-noche border-opacity-30 focus:border-naranja focus:ring-2 focus:ring-naranja focus:ring-opacity-30 rounded-lg transition-all" type="date" name="fecha_inicio_capacitacion" :value="old('fecha_inicio_capacitacion')" min="{{ now()->format('Y-m-d') }}" required />
                            </div>
                            <x-input-error :messages="$errors->get('fecha_inicio_capacitacion')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="fecha_fin_capacitacion" value="Fecha de Fin" class="text-azul-noche font-semibold mb-2" />
                            <div class="relative">
                                <i class="fas fa-calendar-times absolute left-3 top-1/2 transform -translate-y-1/2 text-naranja z-10"></i>
                                <x-text-input id="fecha_fin_capacitacion" class="block w-full pl-10 border-2 border-azul-noche border-opacity-30 focus:border-naranja focus:ring-2 focus:ring-naranja focus:ring-opacity-30 rounded-lg transition-all" type="date" name="fecha_fin_capacitacion" :value="old('fecha_fin_capacitacion')" required />
                            </div>
                            <x-input-error :messages="$errors->get('fecha_fin_capacitacion')" class="mt-2" />
                        </div>
                    </div>
                </div>
                
                <div class="flex items-center justify-start pt-4 border-t border-azul-noche border-opacity-20">
                    <button type="submit" class="bg-gradient-to-r from-verde to-verde hover:from-verde hover:to-verde hover:bg-opacity-90 text-white px-8 py-3 rounded-lg font-semibold transition-all shadow-md hover:shadow-lg flex items-center">
                        <i class="fas fa-check-circle mr-2"></i>
                        {{ __('Crear Convocatoria') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

    @push('scripts')
    <script>
        @if (session('status') && strpos(session('status'), 'Convocatoria creada') !== false)
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: '¡Convocatoria Creada!',
                    text: "{{ session('status') }}",
                    showConfirmButton: false,
                    timer: 3000,
                    toast: true,
                    position: 'top-end'
                });
            });
        @endif
    </script>
    <script>
        /**
         * Calcula la fecha mínima para "fecha_fin_capacitacion"
         * sumando 5 días hábiles DESPUÉS de la fecha de inicio
         * Ejemplo: Si inicio es 18 (martes), cuenta 19,20,21,24,25 y permite marcar desde 26
         */
        function calcularFechaMinima(fechaInicio) {
            const fecha = new Date(fechaInicio);
            let diasHabilesContados = 0;

            // Sumamos días hasta contar 5 días hábiles
            while (diasHabilesContados < 5) {
                fecha.setDate(fecha.getDate() + 1);
                const diaSemana = fecha.getDay();
                
                // 0 = domingo, 6 = sábado
                if (diaSemana !== 0 && diaSemana !== 6) {
                    diasHabilesContados++;
                }
            }

            // Sumamos un día más para que sea el primer día disponible DESPUÉS de los 5 hábiles
            fecha.setDate(fecha.getDate() + 1);
            
            // Si el siguiente día es fin de semana, saltamos al próximo lunes
            let diaSemana = fecha.getDay();
            while (diaSemana === 0 || diaSemana === 6) {
                fecha.setDate(fecha.getDate() + 1);
                diaSemana = fecha.getDay();
            }

            return fecha.toISOString().split('T')[0];
        }

        document.addEventListener('DOMContentLoaded', function() {
            const fechaInicio = document.getElementById('fecha_inicio_capacitacion');
            const fechaFin = document.getElementById('fecha_fin_capacitacion');

            /**
             * Actualiza el atributo min de fecha_fin cuando cambia fecha_inicio
             */
            fechaInicio.addEventListener('change', function() {
                if (this.value) {
                    const fechaMinima = calcularFechaMinima(this.value);
                    fechaFin.setAttribute('min', fechaMinima);
                    
                    // Si la fecha fin actual es menor a la mínima, limpiarla
                    if (fechaFin.value && fechaFin.value < fechaMinima) {
                        fechaFin.value = '';
                    }
                }
            });

            // Inicializar en caso de que ya haya una fecha de inicio cargada
            if (fechaInicio.value) {
                const fechaMinima = calcularFechaMinima(fechaInicio.value);
                fechaFin.setAttribute('min', fechaMinima);
            }
        });
    </script>
    <script>
        // Se ejecuta el código de inicialización después de que las librerías se cargan
        $(document).ready(function() {
            $('#turnos').select2({
                placeholder: "Selecciona uno o más horarios",
                allowClear: true 
            });
            
            // Estilos adicionales de Tailwind para integración visual con la paleta de colores
            $('.select2-container').css('width', '100%');
            $('.select2-selection--multiple').addClass('!rounded-lg !border-2 !border-azul-noche !border-opacity-30 !shadow-sm !py-2 !min-h-[42px]');
            $('.select2-selection__choice').addClass('!bg-celeste !border-azul-noche !border-opacity-40 !text-azul-noche !rounded-md !font-medium');
            $('.select2-selection__choice__remove').addClass('!text-azul-noche !hover:text-naranja');

            // AJAX para crear convocatoria sin recargar
            const formCrear = document.getElementById('form-crear-convocatoria');
            if(formCrear) {
                formCrear.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    
                    const submitBtn = formCrear.querySelector('button[type="submit"]');
                    const originalText = submitBtn.innerHTML;
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Guardando...';

                    const formData = new FormData(formCrear);

                    try {
                        const response = await fetch(formCrear.action, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: formData
                        });

                        const data = await response.json();

                        if (response.ok && data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Convocatoria Creada!',
                                text: data.message,
                                showConfirmButton: false,
                                timer: 3000,
                                toast: true,
                                position: 'top-end'
                            });
                            
                            // Resetear formulario
                            formCrear.reset();
                            $('#turnos').val(null).trigger('change'); // Reset select2
                            
                            // Disparar evento para actualizar la lista
                            window.dispatchEvent(new CustomEvent('convocatoria:created'));
                            
                        } else {
                            throw new Error(data.message || 'Error al crear la convocatoria');
                        }
                    } catch (error) {
                        console.error(error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: error.message || 'Ocurrió un error al procesar la solicitud.'
                        });
                    } finally {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }
                });
            }
        });
    </script>
    @endpush
@endif