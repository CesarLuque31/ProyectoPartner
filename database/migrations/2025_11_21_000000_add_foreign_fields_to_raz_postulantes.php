<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('raz_postulantes', function (Blueprint $table) {
            $table->string('tipo_documento', 50)->default('DNI')->after('id'); // DNI o Carnet de Extranjería
            $table->string('whatsapp', 20)->nullable()->after('celular'); // Número de WhatsApp
            $table->string('nacionalidad', 100)->nullable()->after('sexo'); // Nacionalidad (para extranjeros)
        });
    }

    public function down()
    {
        Schema::table('raz_postulantes', function (Blueprint $table) {
            $table->dropColumn(['tipo_documento', 'whatsapp', 'nacionalidad']);
        });
    }
};

