<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Convocatoria; // Asegúrate de tener este modelo

class ConvocatoriaStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Solo el jefe puede crear convocatorias
        return auth()->user()->rol === 'jefe';
    }

    public function rules(): array
    {
        $campaniaIds = [1,2,3,4,5,10,11,14,15,17,19,24];
        $cargoIds = [1,2,3,4,5,6,7,9,10,11];

        return [
            'campana' => ['required', 'integer', Rule::in($campaniaIds)],
            'requerimiento_personal' => ['required', 'integer', 'min:1'],
            
            // --- REGLAS PARA LOS NUEVOS CAMPOS ---
            'tipo_cargo' => ['required', 'integer', Rule::in($cargoIds)],
            'experiencia' => ['required', 'string', 'in:si,no,indiferente'],
            
            // Turnos (Multiselect) - Opcional
            'turnos' => ['nullable', 'array'], 
            'turnos.*' => ['integer'], // Validamos que cada elemento del array sea un entero (HorarioID)
            
            // Fechas de Capacitación
            'fecha_inicio_capacitacion' => ['required', 'date'],
            'fecha_fin_capacitacion' => ['required', 'date', 'after_or_equal:fecha_inicio_capacitacion'],
        ];
    }
    
    public function messages()
    {
        return [
            'campana.required' => 'El nombre de la Campaña es obligatorio.',
            'campana.integer' => 'Selecciona una campaña válida.',
            'campana.in' => 'La campaña seleccionada no es válida.',
            
            'requerimiento_personal.required' => 'El requerimiento de personal es obligatorio.',
            'requerimiento_personal.integer' => 'El requerimiento debe ser un número entero.',
            'requerimiento_personal.min' => 'Se requiere al menos 1 persona.',
            
            'tipo_cargo.required' => 'El tipo de cargo es obligatorio.',
            'tipo_cargo.integer' => 'Selecciona un cargo válido.',
            'tipo_cargo.in' => 'El cargo seleccionado no es válido.',
            'experiencia.in' => 'La opción de experiencia no es válida.',
            
            'turnos.array' => 'La selección de turnos debe ser una lista válida.',

            'fecha_inicio_capacitacion.required' => 'La fecha de inicio es obligatoria.',
            'fecha_fin_capacitacion.required' => 'La fecha de fin es obligatoria.',
            'fecha_fin_capacitacion.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
        ];
    }
}