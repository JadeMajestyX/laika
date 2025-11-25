<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('token', 512)->index();
            $table->string('platform', 32)->nullable();
            $table->string('device_id', 128)->nullable();
            $table->string('app_version', 64)->nullable();
            $table->string('lang', 8)->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id','token']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_tokens');
    }
};
