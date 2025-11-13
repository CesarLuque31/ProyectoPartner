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
        'turno',
        'estado',
    ];

    // Mantenemos la corrección para SQL Server
    protected $dateFormat = 'Y-m-d H:i:s';
    
    // Relación con el cargo/reclutador (si lo necesitas más adelante)
    // public function cargo()
    // {
    //     return $this->belongsTo(Cargo::class, 'cargo_id', 'CargoID'); // Asumiendo que existe un modelo Cargo
    // }
}