<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('raz_postulantes', function (Blueprint $table) {
            // Columnas que vamos a eliminar porque no se usan en el flujo actual
            $columns = [
                'fch_emision',
                'ubigeo_nac',
                'ubigeo_dir',
                'est_civil',
                'padre',
                'dig_ruc',
                'madre',
                'fch_caducidad',
                'fch_inscripcion',
            ];

            // En algunos entornos es necesario tener doctrine/dbal para dropColumn
            foreach ($columns as $col) {
                if (Schema::hasColumn('raz_postulantes', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }

    public function down()
    {
        Schema::table('raz_postulantes', function (Blueprint $table) {
            // Restaurar columnas con tipos aproximados (nullable)
            if (!Schema::hasColumn('raz_postulantes', 'fch_emision')) {
                $table->date('fch_emision')->nullable();
            }
            if (!Schema::hasColumn('raz_postulantes', 'ubigeo_nac')) {
                $table->string('ubigeo_nac')->nullable();
            }
            if (!Schema::hasColumn('raz_postulantes', 'ubigeo_dir')) {
                $table->string('ubigeo_dir')->nullable();
            }
            if (!Schema::hasColumn('raz_postulantes', 'est_civil')) {
                $table->string('est_civil')->nullable();
            }
            if (!Schema::hasColumn('raz_postulantes', 'padre')) {
                $table->string('padre')->nullable();
            }
            if (!Schema::hasColumn('raz_postulantes', 'dig_ruc')) {
                $table->string('dig_ruc')->nullable();
            }
            if (!Schema::hasColumn('raz_postulantes', 'madre')) {
                $table->string('madre')->nullable();
            }
            if (!Schema::hasColumn('raz_postulantes', 'fch_caducidad')) {
                $table->date('fch_caducidad')->nullable();
            }
            if (!Schema::hasColumn('raz_postulantes', 'fch_inscripcion')) {
                $table->date('fch_inscripcion')->nullable();
            }
        });
    }
};
