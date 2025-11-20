<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('raz_convocatorias_detalles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('convocatoria_id');
            $table->string('razon_social_interna')->nullable();
            $table->string('jefe_inmediato')->nullable();
            $table->string('cliente')->nullable();
            $table->string('servicio_asociado')->nullable();
            $table->string('centro_costo')->nullable();
            $table->string('modalidad_trabajo')->nullable();
            $table->string('lugar_trabajo')->nullable();
            $table->string('region_presencial')->nullable();
            $table->string('tipo_contrato')->nullable();
            $table->json('dias_laborables')->nullable(); // Array de días: lunes, martes, etc.
            $table->time('hora_inicio')->nullable();
            $table->time('hora_fin')->nullable();
            $table->decimal('remuneracion', 10, 2)->nullable();
            $table->decimal('variable', 10, 2)->nullable();
            $table->decimal('movilidad', 10, 2)->nullable();
            $table->string('bono_permanencia')->nullable(); // si/no
            $table->string('tipo_requerimiento')->nullable();
            $table->text('motivo_requerimiento')->nullable();
            $table->date('fecha_sla')->nullable();
            $table->date('fecha_objetivo')->nullable();
            $table->string('tipo_proceso')->nullable();
            $table->string('tipo_gestion')->nullable();
            $table->timestamps();
            
            // Foreign key
            $table->foreign('convocatoria_id')->references('id')->on('raz_convocatorias')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('raz_convocatorias_detalles');
    }
};
