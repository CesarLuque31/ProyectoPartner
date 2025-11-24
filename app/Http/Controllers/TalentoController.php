<?php

namespace App\Http\Controllers;

use App\Models\Convocatoria;
use App\Models\ConvocatoriaDetalle;
use App\Models\User;
use App\Http\Requests\ConvocatoriaStoreRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;


class TalentoController extends Controller
{
    /**
     * Filtra convocatorias según los filtros recibidos por AJAX y retorna solo los datos necesarios.
     */
    public function filtrarConvocatorias(Request $request)
    {
        // Filtros recibidos
        $campana = $request->input('campana');
        $cargo = $request->input('cargo');
        $estado = $request->input('estado');
        $fecha_inicio = $request->input('fecha_inicio');
        $fecha_fin = $request->input('fecha_fin');
        $reclutador = $request->input('reclutador');

        $query = Convocatoria::query();

        if ($campana) {
            $query->where('campana', $campana);
        }
        if ($cargo) {
            $query->where('tipo_cargo', $cargo);
        }
        if ($estado) {
            $query->where('estado', $estado);
        }
        if ($fecha_inicio) {
            $query->whereDate('fecha_inicio_capacitacion', '>=', $fecha_inicio);
        }
        if ($fecha_fin) {
            $query->whereDate('fecha_fin_capacitacion', '<=', $fecha_fin);
        }
        if ($reclutador) {
            if ($reclutador === 'sin-asignar') {
                $query->where(function ($q) {
                    $q->whereNull('reclutadores_asignados')->orWhere('reclutadores_asignados', '[]')->orWhere('reclutadores_asignados', 'null')->orWhere('reclutadores_asignados', '');
                });
            } else {
                $query->where(function ($q) use ($reclutador) {
                    $q->where('reclutadores_asignados', 'like', '%"' . $reclutador . '"%');
                });
            }
        }

        $convocatorias = $query->orderBy('created_at', 'desc')->get();

        // Mapas auxiliares para nombres
        $campanias = $this->getCampanias()->keyBy('id')->map(function ($item) {
            return $item->nombre; })->toArray();
        $cargos = $this->getCargos()->keyBy('id')->map(function ($item) {
            return $item->nombre; })->toArray();
        $horarios = $this->getHorariosBase()->keyBy('HorarioID')->map(function ($h) {
            return $h->HorarioCompleto; })->toArray();
        $reclutadores_disponibles = $this->getReclutadores()->keyBy('id')->map(function ($r) {
            return $r->nombre; })->toArray();

        // Solo los datos necesarios para la tarjeta
        $result = $convocatorias->map(function ($c) use ($campanias, $cargos, $horarios, $reclutadores_disponibles, $request) {
            // Turnos
            $turnos = is_array($c->turnos_json) ? $c->turnos_json : json_decode($c->turnos_json, true);
            $turnos_labels = [];
            if (is_array($turnos)) {
                foreach ($turnos as $t) {
                    $turnos_labels[] = $horarios[$t] ?? $t;
                }
            }
            // Reclutadores asignados
            $recIDs = $c->reclutadores_asignados;
            if (is_string($recIDs)) {
                $recIDs = json_decode($recIDs, true);
            }
            $recIDs = $recIDs ?? [];
            $reclutadores_asignados_labels = [];
            if (is_array($recIDs)) {
                foreach ($recIDs as $rID) {
                    $reclutadores_asignados_labels[] = $reclutadores_disponibles[$rID] ?? (is_numeric($rID) ? ("ID: $rID") : $rID);
                }
            }
            // Experiencia label
            $expLabel = $c->experiencia === 'si' ? 'Sí' : ($c->experiencia === 'no' ? 'No' : 'Indiferente');

            return [
                'id' => $c->id,
                'created_at' => optional($c->created_at)->format('d/m/Y'),
                'estado' => $c->estado,
                'campania_nombre' => $campanias[$c->campana] ?? $c->campana,
                'cargo_nombre' => $cargos[$c->tipo_cargo] ?? $c->tipo_cargo,
                'requerimiento_personal' => $c->requerimiento_personal,
                'experiencia_label' => $expLabel,
                'fecha_inicio_capacitacion' => optional($c->fecha_inicio_capacitacion)->format('d/m/Y') ?? 'N/A',
                'fecha_fin_capacitacion' => optional($c->fecha_fin_capacitacion)->format('d/m/Y') ?? 'N/A',
                'turnos_labels' => $turnos_labels,
            'reclutadores_asignados' => $recIDs, // IDs para el modal
            'reclutadores_asignados_labels' => $reclutadores_asignados_labels,
            'csrf_token' => csrf_token(),
            ];
        });

        return response()->json([
            'convocatorias' => $result,
            'campanias' => $campanias,
            'cargos' => $cargos,
            'horarios' => $horarios,
            'reclutadores_disponibles' => $reclutadores_disponibles,
        ]);
    }
    /**
     * Maneja la creación de una nueva convocatoria.
     */
    public function storeConvocatoria(ConvocatoriaStoreRequest $request)
    {
        $validated = $request->validated();

        // Mapear los datos validados a la nueva estructura de la tabla (raz_convocatorias)
        $data = [
            'user_id' => auth()->id(), // El ID del Jefe que crea la convocatoria
            // 'cargo_id' y 'turno' (singular) FUERON ELIMINADOS

            'campana' => $validated['campana'],
            'requerimiento_personal' => $validated['requerimiento_personal'],

            // NUEVOS CAMPOS
            'tipo_cargo' => $validated['tipo_cargo'],
            'experiencia' => $validated['experiencia'],
            'fecha_inicio_capacitacion' => $validated['fecha_inicio_capacitacion'],
            'fecha_fin_capacitacion' => $validated['fecha_fin_capacitacion'],
            'turnos_json' => $validated['turnos'],
            'estado' => 'Abierta',
        ];

        try {
            Convocatoria::create($data);

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Convocatoria creada exitosamente.']);
            }

            return redirect()->route('dashboard')->with('status', 'Convocatoria creada exitosamente.');

        } catch (\Exception $e) {
            \Log::error('Error al guardar convocatoria: ' . $e->getMessage());
            
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()], 500);
            }

            return back()->withInput()->with('error', 'No se pudo crear la convocatoria debido a un error del servidor. Revise los logs.');
        }
    }

    /**
     * Obtiene los horarios base concatenados para el multiselect.
     * Este es el método que se llama desde la vista.
     */
    public function getHorariosBase()
    {
        try {
            // Consulta SQL Server para concatenar los campos
            $horarios = DB::table('horarios_base')
                ->select(DB::raw("HorarioID, NombreHorario + ' (' + CONVERT(NVARCHAR, HoraEntrada, 8) + ' - ' + CONVERT(NVARCHAR, HoraSalida, 8) + ')' AS HorarioCompleto"))
                ->get();

            return $horarios;

        } catch (\Exception $e) {
            \Log::error("Error de DB al cargar horarios_base: " . $e->getMessage());
            // Devolvemos una colección vacía para no romper el Blade
            return collect([]);
        }
    }

    /**
     * Obtiene las campañas filtradas según IDs especificados.
     */
    public function getCampanias()
    {
        $ids = [1, 2, 3, 4, 5, 10, 11, 14, 15, 17, 19, 24];

        // Intentamos varias combinaciones de nombres de tabla y columnas
        $tableCandidates = [
            'pri.Campanias',
            'Campanias',
            'pri.campanias',
            'campanias',
            'pri.Campañas',
            'Campañas'
        ];

        $columnPairs = [
            ['CampaniaID', 'NombreCampana'],
            ['CampaniaID', 'NombreCampaña'],
            ['CampanaID', 'NombreCampana'],
            ['CampañaID', 'NombreCampaña'],
            ['CampaniaId', 'NombreCampania'],
            ['CampaniaID', 'Nombre_Campana'],
        ];

        foreach ($tableCandidates as $table) {
            foreach ($columnPairs as $cols) {
                try {
                    $idCol = $cols[0];
                    $nameCol = $cols[1];

                    $campanias = DB::table($table)
                        ->select(DB::raw("$idCol as id, $nameCol as nombre"))
                        ->whereIn($idCol, $ids)
                        ->get();

                    if ($campanias && $campanias->isNotEmpty()) {
                        return $campanias;
                    }

                } catch (\Exception $e) {
                    // Registramos un warning y seguimos probando otras combinaciones
                    \Log::warning("Intento fallido getCampanias table={$table} cols={$idCol},{$nameCol}: " . $e->getMessage());
                    continue;
                }
            }
        }

        // Si ninguna combinación devolvió resultados, registramos y devolvemos colección vacía
        \Log::error("No se pudieron cargar campañas: ninguna combinación de tabla/columnas devolvió datos.");
        return collect([]);
    }

    /**
     * Obtiene los cargos filtrados (1..11 excepto 8).
     */
    public function getCargos()
    {
        try {
            $ids = [1, 2, 3, 4, 5, 6, 7, 9, 10, 11];
            $cargos = DB::table('pri.Cargos')
                ->select('CargoID as id', 'NombreCargo as nombre')
                ->whereIn('CargoID', $ids)
                ->get();

            return $cargos;

        } catch (\Exception $e) {
            \Log::error("Error de DB al cargar pri.Cargos: " . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Método auxiliar que devuelve datos en array (para uso en Blade desde include).
     */
    public function getConvocatoriasData()
    {
        try {
            $convocatorias = Convocatoria::orderBy('created_at', 'desc')->get();
            $campMap = $this->getCampanias()->keyBy('id')->map(function ($item) {
                return $item->nombre; })->toArray();
            $cargoMap = $this->getCargos()->keyBy('id')->map(function ($item) {
                return $item->nombre; })->toArray();
            $horMap = $this->getHorariosBase()->keyBy('HorarioID')->map(function ($h) {
                return $h->HorarioCompleto; })->toArray();
            $reclutadoresDisp = $this->getReclutadores()->keyBy('id')->map(function ($r) {
                return $r->nombre; })->toArray();

            return [
                'convocatorias' => $convocatorias,
                'campanias' => $campMap,
                'cargos' => $cargoMap,
                'horarios' => $horMap,
                'reclutadores_disponibles' => $reclutadoresDisp,
            ];
        } catch (\Exception $e) {
            \Log::error('Error obteniendo datos de convocatorias: ' . $e->getMessage());
            return [
                'convocatorias' => collect([]),
                'campanias' => [],
                'cargos' => [],
                'horarios' => [],
                'reclutadores_disponibles' => [],
            ];
        }
    }

    /**
     * Lista todas las convocatorias y pasa mapas de nombres de campaña y cargo.
     */
    public function listConvocatorias()
    {
        try {
            $convocatorias = Convocatoria::orderBy('created_at', 'desc')->get();

            $campMap = $this->getCampanias()->keyBy('id')->map(function ($item) {
                return $item->nombre; })->toArray();
            $cargoMap = $this->getCargos()->keyBy('id')->map(function ($item) {
                return $item->nombre; })->toArray();
            $horariosData = $this->getHorariosBase()->keyBy('HorarioID')->map(function ($item) {
                return $item->HorarioCompleto; })->toArray();
            $reclutadoresData = User::where('rol', 'reclutador')->get()->keyBy('id')->map(function ($item) {
                return $item->nombre; })->toArray();

            return view('talent.convocatorias_list', [
                'convocatorias' => $convocatorias,
                'campanias' => $campMap,
                'cargos' => $cargoMap,
                'horarios' => $horariosData,
                'reclutadores_disponibles' => $reclutadoresData,
            ]);

        } catch (\Exception $e) {
            \Log::error('Error listando convocatorias: ' . $e->getMessage());
            return back()->with('error', 'No se pudo cargar el listado de convocatorias.');
        }
    }

    /**
     * Elimina una convocatoria (solo rol jefe).
     */
    public function destroyConvocatoria($id)
    {
        if (auth()->user()->rol !== 'jefe') {
            abort(403);
        }

        $conv = Convocatoria::findOrFail($id);
        try {
            $conv->estado = 'Cancelada';
            $conv->save();
            return back()->with('status', 'Convocatoria cancelada.');
        } catch (\Exception $e) {
            \Log::error('Error al cancelar convocatoria: ' . $e->getMessage());
            return back()->with('error', 'No se pudo cancelar la convocatoria.');
        }
    }

    /**
     * Obtiene reclutadores (Pri.empleados, CampañaID=33) concatenados.
     */
    public function getReclutadores()
    {
        try {
            $reclutadores = DB::table('pri.empleados')
                ->select(
                    'DNI as id',
                    DB::raw("Nombres + ' ' + ApellidoPaterno + ' ' + ApellidoMaterno as nombre")
                )
                ->where('CampañaID', 33)
                ->where('EstadoEmpleado', 'Activo')
                ->orderBy('Nombres', 'asc')
                ->get();

            return $reclutadores;

        } catch (\Exception $e) {
            \Log::error("Error de DB al cargar pri.empleados: " . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Asigna reclutadores a una convocatoria.
     */
    public function assignReclutadores(Request $request, $id)
    {
        if (auth()->user()->rol !== 'jefe') {
            abort(403);
        }


        $request->validate([
            'reclutadores' => 'array',
            'reclutadores.*' => 'string',
        ]);

        try {
            $conv = Convocatoria::findOrFail($id);
            $conv->reclutadores_asignados = json_encode($request->input('reclutadores', []));
            $conv->save();

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            \Log::error('Error al asignar reclutadores: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'No se pudo asignar reclutadores.'], 500);
        }
    }

    /**
     * Obtiene los tipos de contrato activos de la tabla raz_tipocontratos.
     */
    public function getTiposContrato()
    {
        try {
            $tiposContrato = DB::table('raz_tipocontratos')
                ->where('estado', 1)
                ->select('tipo_contrato')
                ->get();

            return $tiposContrato;

        } catch (\Exception $e) {
            \Log::error("Error al cargar tipos de contrato: " . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Obtener detalles de una convocatoria
     */
    public function getDetallesConvocatoria($id): JsonResponse
    {
        try {
            $convocatoria = Convocatoria::with(['usuario', 'detalle'])->findOrFail($id);

            // Obtener nombres de campaña y cargo
            $campanias = $this->getCampanias()->keyBy('id');
            $cargos = $this->getCargos()->keyBy('id');

            $campanaNombre = $campanias->get($convocatoria->campana)->nombre ?? 'N/A';
            $cargoNombre = $cargos->get($convocatoria->tipo_cargo)->nombre ?? 'N/A';

            $data = [
                'convocatoria' => [
                    'id' => $convocatoria->id,
                    'campana' => $campanaNombre,
                    'requerimiento_personal' => $convocatoria->requerimiento_personal,
                    'tipo_cargo' => $cargoNombre,
                    'experiencia' => ucfirst($convocatoria->experiencia ?? 'N/A'),
                    'fecha_inicio_capacitacion' => $convocatoria->fecha_inicio_capacitacion ? $convocatoria->fecha_inicio_capacitacion->format('Y-m-d') : null,
                    'fecha_fin_capacitacion' => $convocatoria->fecha_fin_capacitacion ? $convocatoria->fecha_fin_capacitacion->format('Y-m-d') : null,
                    'estado' => $convocatoria->estado,
                    'created_at' => $convocatoria->created_at,
                ],
                'solicitante' => $convocatoria->usuario ? $convocatoria->usuario->name : 'N/A',
                'detalles' => $convocatoria->detalle ? $convocatoria->detalle->toArray() : null,
            ];

            return response()->json($data);
        } catch (\Exception $e) {
            \Log::error('Error al obtener detalles: ' . $e->getMessage());
            return response()->json(['error' => 'No se pudieron obtener los detalles'], 500);
        }
    }

    /**
     * Guardar o actualizar detalles de una convocatoria
     */
    public function saveDetallesConvocatoria(Request $request, $id): JsonResponse
    {
        try {
            Log::info('Guardando detalles de convocatoria', [
                'convocatoria_id' => $id,
                'data' => $request->all()
            ]);

            $validated = $request->validate([
                'razon_social_interna' => 'nullable|string|max:255',
                'jefe_inmediato' => 'nullable|string|max:255',
                'cliente' => 'nullable|string|max:255',
                'servicio_asociado' => 'nullable|string|max:255',
                'centro_costo' => 'nullable|string|max:255',
                'modalidad_trabajo' => 'nullable|string|max:255',
                'lugar_trabajo' => 'nullable|string|max:255',
                'region_presencial' => 'nullable|string|max:255',
                'tipo_contrato' => 'nullable|string|max:255',
                'dias_laborables' => 'nullable|array',
                'hora_inicio' => 'nullable|date_format:H:i',
                'hora_fin' => 'nullable|date_format:H:i',
                'remuneracion' => 'nullable|numeric|min:0',
                'variable' => 'nullable|numeric|min:0',
                'movilidad' => 'nullable|numeric|min:0',
                'bono_permanencia' => 'nullable|string|in:si,no',
                'tipo_requerimiento' => 'nullable|string|max:255',
                'motivo_requerimiento' => 'nullable|string',
                'fecha_sla' => 'nullable|date',
                'fecha_objetivo' => 'nullable|date',
                'tipo_proceso' => 'nullable|string|max:255',
                'tipo_gestion' => 'nullable|string|max:255',
            ]);

            $convocatoria = Convocatoria::findOrFail($id);

            // Actualizar o crear los detalles
            $detalle = ConvocatoriaDetalle::updateOrCreate(
                ['convocatoria_id' => $id],
                $validated
            );

            Log::info('Detalles guardados exitosamente', ['detalle_id' => $detalle->id]);

            return response()->json([
                'success' => true,
                'message' => 'Detalles guardados exitosamente',
                'detalles' => $detalle
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Error de validación al guardar detalles', [
                'convocatoria_id' => $id,
                'errors' => $e->errors()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Convocatoria no encontrada', ['convocatoria_id' => $id]);
            return response()->json([
                'success' => false,
                'error' => 'Convocatoria no encontrada'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error al guardar detalles de convocatoria', [
                'convocatoria_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'error' => 'No se pudieron guardar los detalles: ' . $e->getMessage()
            ], 500);
        }
    }

}