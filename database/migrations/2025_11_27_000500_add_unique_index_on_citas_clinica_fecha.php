<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Asegura que no se puedan crear dos citas en la misma clínica a la misma fecha/hora
        Schema::table('citas', function (Blueprint $table) {
            // Evitar error si ya existe el índice (por migraciones previas)
            try {
                $table->unique(['clinica_id', 'fecha'], 'citas_clinica_fecha_unique');
            } catch (\Throwable $e) {
                // Silenciar si ya existe o la tabla aún no tiene estas columnas
            }
        });
    }

    public function down(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            try {
                $table->dropUnique('citas_clinica_fecha_unique');
            } catch (\Throwable $e) {
                // Silenciar si no existe
            }
        });
    }
};
