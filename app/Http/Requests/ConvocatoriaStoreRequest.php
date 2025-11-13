<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConvocatoriaStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Solo el jefe puede crear convocatorias (asegúrate de que este rol coincida con tu DB)
        return auth()->user()->rol === 'jefe';
    }

    public function rules(): array
    {
        return [
            'reclutador_cargo_id' => ['required', 'exists:pri.empleados,CargoID'], // Asumiendo que el ID se pasa como 'reclutador_cargo_id' y existe en pri.empleados.CargoID
            'campana' => ['required', 'string', 'max:255'],
            'requerimiento_personal' => ['required', 'integer', 'min:1'],
            'turno' => ['required', 'string', 'in:Mañana,Tarde,Noche'],
        ];
    }
    
    // Opcional: Mensajes de error en español
    public function messages()
    {
        return [
            'reclutador_cargo_id.required' => 'Debes seleccionar un Reclutador/Cargo.',
            'reclutador_cargo_id.exists' => 'El Cargo seleccionado no es válido.',
            'campana.required' => 'El nombre de la Campaña es obligatorio.',
            'requerimiento_personal.required' => 'El requerimiento de personal es obligatorio.',
            'requerimiento_personal.integer' => 'El requerimiento debe ser un número entero.',
            'requerimiento_personal.min' => 'Se requiere al menos 1 persona.',
            'turno.required' => 'El turno es obligatorio.',
            'turno.in' => 'El turno seleccionado no es válido.',
        ];
    }
}