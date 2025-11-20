<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConvocatoriaDetalle extends Model
{
    use HasFactory;

    protected $table = 'raz_convocatorias_detalles';

    protected $fillable = [
        'convocatoria_id',
        'razon_social_interna',
        'jefe_inmediato',
        'cliente',
        'servicio_asociado',
        'centro_costo',
        'modalidad_trabajo',
        'lugar_trabajo',
        'region_presencial',
        'tipo_contrato',
        'dias_laborables',
        'hora_inicio',
        'hora_fin',
        'remuneracion',
        'variable',
        'movilidad',
        'bono_permanencia',
        'tipo_requerimiento',
        'motivo_requerimiento',
        'fecha_sla',
        'fecha_objetivo',
        'tipo_proceso',
        'tipo_gestion',
    ];

    protected $casts = [
        'dias_laborables' => 'array',
        'hora_inicio' => 'datetime:H:i',
        'hora_fin' => 'datetime:H:i',
        'remuneracion' => 'decimal:2',
        'variable' => 'decimal:2',
        'movilidad' => 'decimal:2',
        'fecha_sla' => 'date',
        'fecha_objetivo' => 'date',
    ];

    protected $dateFormat = 'Y-m-d H:i:s';

    public function convocatoria()
    {
        return $this->belongsTo(Convocatoria::class, 'convocatoria_id');
    }
}
