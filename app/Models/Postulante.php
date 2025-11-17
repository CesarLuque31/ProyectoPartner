<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Postulante extends Model
{
    use HasFactory;

    protected $table = 'raz_postulantes';

    protected $fillable = [
        'dni', 'nombres', 'ap_pat', 'ap_mat', 'fecha_nac', 'direccion', 'sexo',
        'celular', 'correo', 'provincia', 'distrito', 'experiencia_callcenter',
        'discapacidad', 'tipo_discapacidad', 'tipo_contrato', 'modalidad_trabajo', 'tipo_gestion'
    ];
    
    // Evitar casts automáticos de fecha para prevenir conversiones indeseadas con el driver ODBC
    protected $casts = [];
}
