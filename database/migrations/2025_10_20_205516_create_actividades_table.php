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
        Schema::create('actividades', function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->constrained("users")->nullable();
            $table->string("accion");
            $table->string("modelo");
            $table->foreignId("modelo_id")->nullable();
            $table->text("detalles")->nullable();
            $table->ipAddress("ip_address")->nullable();
            $table->string("navegador")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actividades');
    }
};
