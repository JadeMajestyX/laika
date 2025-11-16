<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'clinica_id')) {
                $table->unsignedBigInteger('clinica_id')->nullable()->after('is_active');
                $table->foreign('clinica_id')->references('id')->on('clinicas')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'clinica_id')) {
                $table->dropForeign(['clinica_id']);
                $table->dropColumn('clinica_id');
            }
        });
    }
};
