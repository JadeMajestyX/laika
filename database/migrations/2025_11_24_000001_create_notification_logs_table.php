<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('title', 150);
            $table->text('body');
            $table->json('data_json')->nullable();
            $table->json('tokens_json')->nullable();
            $table->unsignedInteger('success')->default(0);
            $table->unsignedInteger('fail')->default(0);
            $table->unsignedInteger('total')->default(0);
            $table->json('results_json')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
