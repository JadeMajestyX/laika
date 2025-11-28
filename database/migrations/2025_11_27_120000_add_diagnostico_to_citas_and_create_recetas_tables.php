<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Agregar columna diagnostico a citas si no existe
        if (!Schema::hasColumn('citas', 'diagnostico')) {
            Schema::table('citas', function (Blueprint $table) {
                $table->text('diagnostico')->nullable()->after('notas');
            });
        }

        // Crear tabla recetas
        if (!Schema::hasTable('recetas')) {
            Schema::create('recetas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('cita_id')->constrained('citas')->onDelete('cascade');
                $table->text('notas')->nullable();
                $table->timestamps();
            });
        }

        // Crear tabla receta_items
        if (!Schema::hasTable('receta_items')) {
            Schema::create('receta_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('receta_id')->constrained('recetas')->onDelete('cascade');
                $table->string('medicamento', 150);
                $table->string('dosis', 200);
                $table->string('notas', 250)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('receta_items');
        Schema::dropIfExists('recetas');
        if (Schema::hasColumn('citas', 'diagnostico')) {
            Schema::table('citas', function (Blueprint $table) {
                $table->dropColumn('diagnostico');
            });
        }
    }
};
