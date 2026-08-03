<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id')->index();

            $table->string('action');
            $table->text('description')->nullable();

            $table->timestamps();

            // 🔥 Useful indexes
            $table->foreign('user_id', 'activity_logs_user_id_fk')->references('id')->on('users')->onDelete('cascade');

            $table->index(['action']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
