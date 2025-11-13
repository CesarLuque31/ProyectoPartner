@php
    // Obtenemos una instancia del controlador desde el Service Container de Laravel
    $TalentoController = app(\App\Http\Controllers\TalentoController::class);
    
    $cargos = collect([]); 
    
    try {
        // Llamada al método NO ESTATICO
        $cargos = $TalentoController->getCargos(); 
    } catch (\Exception $e) {
        // Si falla (aunque no debería con el try/catch), sigue siendo una colección vacía
        \Log::error('Fallo al cargar cargos en vista: ' . $e->getMessage());
    }
@endphp

<div class="p-6 bg-white shadow sm:rounded-lg">
    <h2 class="text-3xl font-bold mb-4 text-indigo-700">Crear Nueva Convocatoria</h2>
    <p class="text-gray-600 mb-6">Completa los detalles de la nueva campaña de reclutamiento.</p>

    <form method="POST" action="{{ route('convocatorias.store') }}" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            {{-- CAMPO 1: RECLUTADOR / CARGO (Menú Desplegable) --}}
            <div>
                <x-input-label for="reclutador_cargo_id" value="Cargo Reclutador" />
                <select id="reclutador_cargo_id" name="reclutador_cargo_id" required 
                        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
                    <option value="" disabled selected>Selecciona el Cargo/Reclutador</option>
                    @if($cargos->isEmpty())
                        <option value="" disabled>-- Cargos no disponibles o Error de Conexión --</option>
                    @endif
                    @foreach ($cargos as $cargo)
                        <option value="{{ $cargo->CargoID }}" {{ old('reclutador_cargo_id') == $cargo->CargoID ? 'selected' : '' }}>
                            {{ $cargo->NombreCargo }}
                        </option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('reclutador_cargo_id')" class="mt-2" />
            </div>

            {{-- CAMPO 2: CAMPAÑA --}}
            <div>
                <x-input-label for="campana" value="Nombre de la Campaña" />
                <x-text-input id="campana" class="block mt-1 w-full" type="text" name="campana" :value="old('campana')" required />
                <x-input-error :messages="$errors->get('campana')" class="mt-2" />
            </div>

            {{-- CAMPO 3: REQUERIMIENTO DE PERSONAL --}}
            <div>
                <x-input-label for="requerimiento_personal" value="Requerimiento de Personal (Número)" />
                <x-text-input id="requerimiento_personal" class="block mt-1 w-full" type="number" name="requerimiento_personal" :value="old('requerimiento_personal')" min="1" required />
                <x-input-error :messages="$errors->get('requerimiento_personal')" class="mt-2" />
            </div>

            {{-- CAMPO 4: TURNO (Select) --}}
            <div>
                <x-input-label for="turno" value="Turno" />
                <select id="turno" name="turno" required 
                        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full">
                    <option value="" disabled selected>Selecciona el Turno</option>
                    <option value="Mañana" {{ old('turno') == 'Mañana' ? 'selected' : '' }}>Mañana</option>
                    <option value="Tarde" {{ old('turno') == 'Tarde' ? 'selected' : '' }}>Tarde</option>
                    <option value="Noche" {{ old('turno') == 'Noche' ? 'selected' : '' }}>Noche</option>
                </select>
                <x-input-error :messages="$errors->get('turno')" class="mt-2" />
            </div>

        </div>

        <div class="flex items-center justify-start mt-6">
            <x-primary-button>
                {{ __('Crear Convocatoria') }}
            </x-primary-button>
        </div>
    </form>
</div>