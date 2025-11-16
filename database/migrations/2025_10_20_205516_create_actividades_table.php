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
            
            // Al eliminar un usuario, user_id se volverá NULL
            $table->foreignId("user_id")
                  ->nullable()
                  ->constrained("users")
                  ->nullOnDelete();

            $table->string("accion");
            $table->string("modelo");
            
            // Si quieres que la FK modelo_id también tenga onDelete, agrega ->nullOnDelete()
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
