@if(auth()->check() && auth()->user()->rol === 'jefe')
    <div class="p-6 bg-white shadow sm:rounded-lg">
        <h2 class="text-3xl font-bold mb-4 text-indigo-700">Listado de Convocatorias</h2>

        @if($convocatorias->isEmpty())
            <p class="text-gray-600">No hay convocatorias registradas.</p>
        @else
            <div class="grid grid-cols-1 gap-6">
                @foreach($convocatorias as $c)
                    <div class="bg-white border rounded-lg p-6 shadow-sm">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <p class="text-xs text-gray-400">Creada: {{ optional($c->created_at)->format('d/m/Y H:i') }}</p>
                        </div>
                        <span class="px-3 py-1 rounded-full text-sm font-semibold bg-{{ $c->estado === 'Abierta' ? 'green-100 text-green-700' : 'yellow-100 text-yellow-700' }}">
                            {{ $c->estado }}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <p class="text-xs text-gray-500 font-semibold">CAMPAÑA</p>
                            <p class="text-indigo-600 font-semibold">{{ $campanias[$c->campana] ?? $c->campana }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 font-semibold">CARGO</p>
                            <p class="text-gray-700 font-semibold">{{ $cargos[$c->tipo_cargo] ?? $c->tipo_cargo }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <p class="text-xs text-gray-500 font-semibold">VACANTES</p>
                            <p class="text-gray-700">{{ $c->requerimiento_personal }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 font-semibold">EXPERIENCIA REQUERIDA</p>
                            <p class="text-gray-700">
                                @if($c->experiencia === 'si')
                                    Sí
                                @elseif($c->experiencia === 'no')
                                    No
                                @else
                                    Indiferente
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <p class="text-xs text-gray-500 font-semibold">INICIO CAPACITACIÓN</p>
                            <p class="text-gray-700">{{ optional($c->fecha_inicio_capacitacion)->format('d/m/Y') ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 font-semibold">FIN CAPACITACIÓN</p>
                            <p class="text-gray-700">{{ optional($c->fecha_fin_capacitacion)->format('d/m/Y') ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="mb-4">
                        <p class="text-xs text-gray-500 font-semibold">TURNOS</p>
                        @php
                            $turnos = is_array($c->turnos_json) ? $c->turnos_json : json_decode($c->turnos_json, true);
                            $turnLabels = [];
                            if (is_array($turnos)) {
                                foreach ($turnos as $t) {
                                    $turnLabels[] = $horarios[$t] ?? $t;
                                }
                            }
                        @endphp
                        @if(!empty($turnLabels))
                            @php
                                $displayLimit = 4;
                                $total = count($turnLabels);
                                $visible = array_slice($turnLabels, 0, $displayLimit);
                                $more = $total - count($visible);
                            @endphp

                            <div class="flex flex-wrap gap-2">
                                @foreach($visible as $lbl)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-indigo-50 border border-indigo-100 text-indigo-700 text-sm font-medium">
                                        {{ $lbl }}
                                    </span>
                                @endforeach

                                @if($more > 0)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-100 border border-gray-200 text-gray-700 text-sm font-medium">
                                        +{{ $more }} más
                                    </span>
                                @endif
                            </div>
                        @else
                            <p class="text-gray-500">Sin turnos asignados</p>
                        @endif
                    </div>

                    <div class="mt-6 flex items-center space-x-3">
                        @if(auth()->user()->rol === 'jefe')
                            <button type="button" class="swal-assign-btn bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded font-semibold" data-conv-id="{{ $c->id }}" data-conv-name="{{ $campanias[$c->campana] ?? $c->campana }}">
                                <i class="fas fa-users mr-2"></i> Asignar Reclutadores
                            </button>

                            <form action="{{ route('convocatorias.destroy', $c->id) }}" method="POST" class="inline delete-conv-form">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="swal-delete-btn bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded font-semibold">
                                    <i class="fas fa-trash mr-2"></i> Eliminar
                                </button>
                            </form>
                        @endif
                    </div>

                    {{-- Indicador de reclutadores (solo para jefe) --}}
                    @if(auth()->user()->rol === 'jefe')
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <p class="text-xs text-gray-500 font-semibold">RECLUTADORES ASIGNADOS</p>
                            @php
                                // Decodificar el JSON si es string
                                $recIDs = $c->reclutadores_asignados;
                                if (is_string($recIDs)) {
                                    $recIDs = json_decode($recIDs, true);
                                }
                                $recIDs = $recIDs ?? [];
                                
                                $recAsignados = [];
                                if (is_array($recIDs)) {
                                    foreach ($recIDs as $rID) {
                                        $recAsignados[] = $reclutadores_disponibles[$rID] ?? "ID: $rID";
                                    }
                                }
                            @endphp
                            @if(!empty($recAsignados))
                                <div class="flex flex-wrap gap-2 mt-2">
                                    @foreach($recAsignados as $rec)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full bg-green-50 border border-green-100 text-green-700 text-xs font-medium">
                                            {{ $rec }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <div class="flex items-center mt-2">
                                    <i class="fas fa-exclamation-circle text-yellow-500 mr-2"></i>
                                    <p class="text-yellow-700 text-sm">Sin reclutadores asignados</p>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
                </div>
            @endif
    </div>
@endif

@if(auth()->check() && auth()->user()->rol === 'jefe')
    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Mostrar mensaje de éxito si existe
        @if (session('status') && strpos(session('status'), 'eliminada') !== false)
            Swal.fire({
                icon: 'success',
                title: '¡Convocatoria Eliminada!',
                text: "{{ session('status') }}",
                showConfirmButton: false,
                timer: 3000,
                toast: true,
                position: 'top-end'
            });
        @endif

        // Botones de Eliminar
        document.querySelectorAll('.swal-delete-btn').forEach(function(btn){
            btn.addEventListener('click', function(e){
                e.preventDefault();
                const form = this.closest('form');
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
                            }).then(() => {
                                location.reload();
                            });
                        })
                        .catch(error => {
                            Swal.fire('Error', 'No se pudo eliminar la convocatoria', 'error');
                            console.error(error);
                        });
                    }
                });
            });
        });

        // Botones de Asignar Reclutadores
        document.querySelectorAll('.swal-assign-btn').forEach(function(btn){
            btn.addEventListener('click', function(e){
                const convID = this.getAttribute('data-conv-id');
                const convName = this.getAttribute('data-conv-name');
                const reclutadoresDisp = {!! json_encode($reclutadores_disponibles) !!};
                const currentReclutadores = {!! json_encode($convocatorias->pluck('reclutadores_asignados', 'id')) !!}[convID] || [];

                // Crear opciones para Select2
                let options = '';
                for (let id in reclutadoresDisp) {
                    const selected = currentReclutadores.includes(parseInt(id)) ? 'selected' : '';
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
                        $('#swal-reclutadores-select').select2({
                            dropdownParent: $('.swal2-container'),
                            placeholder: 'Selecciona reclutadores',
                            allowClear: true,
                            width: '100%'
                        });
                    },
                    preConfirm: () => {
                        const selected = $('#swal-reclutadores-select').val() || [];
                        return selected;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const selectedReclutadores = result.value;
                        
                        // Enviar datos al servidor
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
                                .then(() => {
                                    location.reload();
                                });
                        })
                        .catch(error => {
                            Swal.fire('Error', 'No se pudo asignar reclutadores', 'error');
                        });
                    }
                });
            });
        });
    });
</script>
@endpush
@endif