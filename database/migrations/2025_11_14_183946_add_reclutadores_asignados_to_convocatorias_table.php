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
        Schema::table('raz_convocatorias', function (Blueprint $table) {
            $table->json('reclutadores_asignados')->nullable()->after('turnos_json');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('raz_convocatorias', function (Blueprint $table) {
            $table->dropColumn('reclutadores_asignados');
        });
    }
};
