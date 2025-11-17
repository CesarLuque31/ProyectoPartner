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

    <div class="p-6 bg-white shadow sm:rounded-lg">
    <h2 class="text-3xl font-bold mb-4 text-indigo-700">Crear Nueva Convocatoria</h2>
    <p class="text-gray-600 mb-6">Completa los detalles de la nueva campaña de reclutamiento.</p>

    <form method="POST" action="{{ route('convocatorias.store') }}" class="space-y-6">
        @csrf

        {{-- PRIMERA FILA (CAMPANA & REQUERIMIENTO) --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <div>
                <x-input-label for="campana" value="Nombre de la Campaña" />
                <select id="campana" name="campana" required class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
                    <option value="" disabled {{ old('campana') ? '' : 'selected' }}>Selecciona una Campaña</option>
                    @if($campanias->isEmpty())
                        <option value="" disabled>-- No se pudieron cargar las campañas --</option>
                    @endif
                    @foreach($campanias as $camp)
                        <option value="{{ $camp->id }}" {{ old('campana') == $camp->id ? 'selected' : '' }}>{{ $camp->nombre }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('campana')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="requerimiento_personal" value="Requerimiento de Personal (Número)" />
                <x-text-input id="requerimiento_personal" class="block mt-1 w-full" type="number" name="requerimiento_personal" :value="old('requerimiento_personal')" min="1" required />
                <x-input-error :messages="$errors->get('requerimiento_personal')" class="mt-2" />
            </div>
        </div>

        {{-- SEGUNDA FILA (TIPO DE CARGO & EXPERIENCIA) --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- CAMPO: TIPO DE CARGO (Texto libre) --}}
            <div>
                <x-input-label for="tipo_cargo" value="Tipo de Cargo" />
                <select id="tipo_cargo" name="tipo_cargo" required class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
                    <option value="" disabled {{ old('tipo_cargo') ? '' : 'selected' }}>Selecciona un Cargo</option>
                    @if($cargos->isEmpty())
                        <option value="" disabled>-- No se pudieron cargar los cargos --</option>
                    @endif
                    @foreach($cargos as $cargo)
                        <option value="{{ $cargo->id }}" {{ old('tipo_cargo') == $cargo->id ? 'selected' : '' }}>{{ $cargo->nombre }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('tipo_cargo')" class="mt-2" />
            </div>

            {{-- CAMPO: EXPERIENCIA --}}
            <div>
                <x-input-label for="experiencia" value="¿Requiere Experiencia Previa?" />
                <select id="experiencia" name="experiencia" required class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
                    <option value="" disabled selected>Selecciona una Opción</option>
                    <option value="si" {{ old('experiencia') == 'si' ? 'selected' : '' }}>Sí</option>
                    <option value="no" {{ old('experiencia') == 'no' ? 'selected' : '' }}>No</option>
                    <option value="indiferente" {{ old('experiencia') == 'indiferente' ? 'selected' : '' }}>Indiferente</option>
                </select>
                <x-input-error :messages="$errors->get('experiencia')" class="mt-2" />
            </div>
        </div>

        {{-- TERCERA FILA (TURNO MULTISELECT CON SELECT2) --}}
        <div class="grid grid-cols-1 gap-6">
            <div>
                <x-input-label for="turnos" value="Turno(s) Disponibles (Selección Múltiple) - (Opcional)" />
                
                {{-- Elemento Select2 --}}
                <select id="turnos" name="turnos[]" multiple
                        class="select2 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
                    
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
                
            </div>
        </div>

        {{-- CUARTA FILA (FECHA DE CAPACITACIÓN) --}}
        <div class="pt-4 border-t border-gray-200">
            <h3 class="text-lg font-semibold text-gray-700 mb-3">Rango de Capacitación</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                {{-- FECHA INICIO --}}
                <div>
                    <x-input-label for="fecha_inicio_capacitacion" value="Fecha de Inicio de Capacitación" />
                    <x-text-input id="fecha_inicio_capacitacion" class="block mt-1 w-full" type="date" name="fecha_inicio_capacitacion" :value="old('fecha_inicio_capacitacion')" min="{{ now()->format('Y-m-d') }}" required />
                    <x-input-error :messages="$errors->get('fecha_inicio_capacitacion')" class="mt-2" />
                </div>

                {{-- FECHA FIN --}}
                <div>
                    <x-input-label for="fecha_fin_capacitacion" value="Fecha de Fin de Capacitación" />
                    <x-text-input id="fecha_fin_capacitacion" class="block mt-1 w-full" type="date" name="fecha_fin_capacitacion" :value="old('fecha_fin_capacitacion')" required />
                    <x-input-error :messages="$errors->get('fecha_fin_capacitacion')" class="mt-2" />
                </div>
            </div>
        </div>
        
        <div class="flex items-center justify-start mt-6 pt-4">
            <x-primary-button>
                {{ __('Crear Convocatoria') }}
            </x-primary-button>
        </div>
    </form>
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
            
            // Estilos adicionales de Tailwind para integración visual
            $('.select2-container').css('width', '100%');
            $('.select2-selection--multiple').addClass('!rounded-md !border-gray-300 !shadow-sm !py-1');
            $('.select2-selection__choice').addClass('!bg-indigo-100 !border-indigo-400 !text-gray-700 !rounded-md');

        });
    </script>
    @endpush
@endif