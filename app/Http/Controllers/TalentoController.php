<?php

namespace App\Http\Controllers;

use App\Models\Convocatoria;
use App\Http\Requests\ConvocatoriaStoreRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class TalentoController extends Controller
{
    /**
     * Maneja la creación de una nueva convocatoria.
     */
    public function storeConvocatoria(ConvocatoriaStoreRequest $request): RedirectResponse
    {
        // ... (Tu lógica storeConvocatoria sigue igual) ...
        $validated = $request->validated();
        
        // Mapear los datos validados a la estructura de la tabla
        $data = [
            'user_id' => auth()->id(), // El ID del Jefe que crea la convocatoria
            'cargo_id' => $validated['reclutador_cargo_id'],
            'campana' => $validated['campana'],
            'requerimiento_personal' => $validated['requerimiento_personal'],
            'turno' => $validated['turno'],
        ];

        try {
            Convocatoria::create($data);
            
            return redirect()->route('dashboard')->with('status', 'Convocatoria creada exitosamente.');

        } catch (\Exception $e) {
            \Log::error('Error al guardar convocatoria: ' . $e->getMessage());
            return back()->withInput()->with('error', 'No se pudo crear la convocatoria debido a un error del servidor.');
        }
    }
    
    /**
     * Obtiene los reclutadores/cargos disponibles para el menú desplegable.
     */
    public function getCargos() // <--- ¡AQUÍ SE ELIMINÓ EL 'static'!
    {
        try {
            // Usamos la conexión por defecto y la tabla pri.Cargos
            $cargos = DB::table('pri.Cargos') 
                         ->select('CargoID', 'NombreCargo') 
                         ->get();

            return $cargos;

        } catch (\Exception $e) {
            // Si la consulta o conexión falla, devolvemos una colección vacía.
            \Log::error("Error de DB al cargar cargos (pri.Cargos): " . $e->getMessage());
            return collect([]); 
        }
    }
}