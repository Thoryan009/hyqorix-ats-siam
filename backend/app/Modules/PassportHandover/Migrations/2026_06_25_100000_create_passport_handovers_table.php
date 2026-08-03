<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('passport_handovers', function (Blueprint $table) {
            $table->id();
            $table->string('handover_no')->unique();
            $table->enum('type', ['single', 'group']);
            $table->string('taker_name');
            $table->string('taker_phone');
            $table->date('taken_at');
            $table->date('expected_return_date')->nullable();
            $table->text('taken_reason')->nullable();
            $table->date('return_date')->nullable();
            $table->enum('status', ['handed_over', 'partially_collected', 'collected'])->default('handed_over');
            $table->timestamps();

            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->unsignedBigInteger('updated_by')->nullable()->index();

            $table->foreign('created_by', 'passport_handovers_created_by_foreign')
                ->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by', 'passport_handovers_updated_by_foreign')
                ->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('passport_handovers');
    }
};
