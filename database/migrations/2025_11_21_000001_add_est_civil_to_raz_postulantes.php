<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('raz_postulantes', function (Blueprint $table) {
            // Verificar si el campo ya existe antes de agregarlo
            if (!Schema::hasColumn('raz_postulantes', 'est_civil')) {
                $table->string('est_civil', 50)->nullable()->after('sexo');
            }
        });
    }

    public function down()
    {
        Schema::table('raz_postulantes', function (Blueprint $table) {
            if (Schema::hasColumn('raz_postulantes', 'est_civil')) {
                $table->dropColumn('est_civil');
            }
        });
    }
};


