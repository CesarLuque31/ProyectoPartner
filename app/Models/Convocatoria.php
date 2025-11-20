<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Convocatoria extends Model
{
    use HasFactory;

    protected $table = 'raz_convocatorias';

    protected $fillable = [
        'user_id',
        'campana',
        'requerimiento_personal',
        
        // --- NUEVOS CAMPOS AÑADIDOS A LA TABLA ---
        'experiencia',         
        'tipo_cargo',          
        'fecha_inicio_capacitacion', 
        'fecha_fin_capacitacion',    
        'turnos_json',         // Este campo guardará el array de turnos
        
        'estado',
    ];

    // Mantenemos la corrección para SQL Server
    protected $dateFormat = 'Y-m-d H:i:s';
    
    // --- NUEVOS CASTS ---
    protected $casts = [
        // Le indica a Laravel que este campo de la DB (NVARCHAR/TEXT) debe ser tratado como un array/JSON
        'turnos_json' => 'array', 
        
        // Opcional: Asegura que las fechas se manejen como objetos Carbon
        'fecha_inicio_capacitacion' => 'datetime',
        'fecha_fin_capacitacion' => 'datetime',
    ];

    // Relación con el usuario que creó la convocatoria
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relación con los detalles de la convocatoria
    public function detalle()
    {
        return $this->hasOne(ConvocatoriaDetalle::class, 'convocatoria_id');
    }
}