<?php


namespace App\Http\Controllers;

use App\Models\Convocatoria;
use App\Models\User;
use App\Http\Requests\ConvocatoriaStoreRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;

class TalentoController extends Controller
{
    /**
     * Maneja la creación de una nueva convocatoria.
     */
    public function storeConvocatoria(ConvocatoriaStoreRequest $request): RedirectResponse
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
            
            return redirect()->route('dashboard')->with('status', 'Convocatoria creada exitosamente.');

        } catch (\Exception $e) {
            \Log::error('Error al guardar convocatoria: ' . $e->getMessage());
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
            $ids = [1,2,3,4,5,10,11,14,15,17,19,24];

            // Intentamos varias combinaciones de nombres de tabla y columnas
            $tableCandidates = [
                'pri.Campanias', 'Campanias', 'pri.campanias', 'campanias', 'pri.Campañas', 'Campañas'
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
                $ids = [1,2,3,4,5,6,7,9,10,11];
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
                $campMap = $this->getCampanias()->keyBy('id')->map(function($item){ return $item->nombre; })->toArray();
                $cargoMap = $this->getCargos()->keyBy('id')->map(function($item){ return $item->nombre; })->toArray();
                $horMap = $this->getHorariosBase()->keyBy('HorarioID')->map(function($h){ return $h->HorarioCompleto; })->toArray();
                $reclutadoresDisp = $this->getReclutadores()->keyBy('id')->map(function($r){ return $r->nombre; })->toArray();

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

                $campMap = $this->getCampanias()->keyBy('id')->map(function($item){ return $item->nombre; })->toArray();
                $cargoMap = $this->getCargos()->keyBy('id')->map(function($item){ return $item->nombre; })->toArray();
                $horariosData = $this->getHorariosBase()->keyBy('HorarioID')->map(function($item){ return $item->HorarioCompleto; })->toArray();
                $reclutadoresData = User::where('rol', 'reclutador')->get()->keyBy('id')->map(function($item){ return $item->nombre; })->toArray();

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
                $conv->delete();
                return back()->with('status', 'Convocatoria eliminada.');
            } catch (\Exception $e) {
                \Log::error('Error al eliminar convocatoria: ' . $e->getMessage());
                return back()->with('error', 'No se pudo eliminar la convocatoria.');
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
    
}