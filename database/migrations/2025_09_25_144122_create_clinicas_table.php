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

        Schema::create('clinicas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('direccion');
            $table->string('telefono', 15);
            $table->string('email', 150);
            $table->boolean('is_open')->default(true);
            $table->boolean('is_visible')->default(true);
            $table->string('site')->nullable();
            $table->timestamps();
            $table->dateTime('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clinicas');
    }
};
