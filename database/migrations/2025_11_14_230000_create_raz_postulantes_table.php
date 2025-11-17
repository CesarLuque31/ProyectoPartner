<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('raz_postulantes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('dni', 20)->index();
            $table->string('nombres')->nullable();
            $table->string('ap_pat')->nullable();
            $table->string('ap_mat')->nullable();
            $table->date('fecha_nac')->nullable();
            $table->date('fch_emision')->nullable();
            $table->string('ubigeo_nac')->nullable();
            $table->text('direccion')->nullable();
            $table->string('est_civil')->nullable();
            $table->string('padre')->nullable();
            $table->string('dig_ruc')->nullable();
            $table->string('ubigeo_dir')->nullable();
            $table->date('fch_caducidad')->nullable();
            $table->date('fch_inscripcion')->nullable();
            $table->string('sexo')->nullable();
            $table->string('madre')->nullable();

            // Campos adicionales solicitados
            $table->string('celular')->nullable();
            $table->string('correo')->nullable();
            $table->string('provincia')->nullable();
            $table->string('distrito')->nullable();
            $table->string('experiencia_callcenter')->nullable(); // si/no
            $table->string('discapacidad')->nullable(); // si/no
            $table->string('tipo_discapacidad')->nullable();
            $table->string('tipo_contrato')->nullable();
            $table->string('modalidad_trabajo')->nullable();
            $table->string('tipo_gestion')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('raz_postulantes');
    }
};
