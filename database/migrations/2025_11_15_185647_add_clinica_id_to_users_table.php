<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddClinicaIdToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('clinica_id')->nullable()->after('id');
            // si quieres FK (recomendado) y la columna 'id' de clinicas es unsignedBigInteger:
            $table->foreign('clinica_id')->references('id')->on('clinicas')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['clinica_id']);
            $table->dropColumn('clinica_id');
        });
    }
}
