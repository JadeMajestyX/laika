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
        Schema::create('dispensador_valors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dispensador_id')->constrained('dispensadors')->onDelete('cascade');
            $table->decimal('cantidad_comida', 5, 2);
            $table->integer('numero');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dispensador_valors');
    }
};
