<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('passport_handover_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('passport_handover_id')
                ->constrained('passport_handovers')
                ->cascadeOnDelete();
            $table->unsignedBigInteger('application_id')->nullable()->index();
            $table->string('passport_no');
            $table->date('expected_return_date')->nullable();
            $table->text('taken_reason')->nullable();
            $table->date('return_date')->nullable();
            $table->enum('status', ['handed_over', 'collected'])->default('handed_over');
            $table->timestamp('collected_at')->nullable();
            $table->unsignedInteger('sort_order')->default(1);
            $table->timestamps();

            $table->foreign('application_id', 'passport_handover_items_application_id_foreign')
                ->references('id')->on('applications')->onDelete('set null');

            $table->index(['passport_no', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('passport_handover_items');
    }
};
