<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            $table->index(['veterinario_id', 'fecha'], 'citas_veterinario_fecha_index');
            $table->index(['veterinario_id', 'status', 'fecha'], 'citas_veterinario_status_fecha_index');
        });
    }

    public function down(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            $table->dropIndex('citas_veterinario_fecha_index');
            $table->dropIndex('citas_veterinario_status_fecha_index');
        });
    }
};
