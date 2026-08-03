<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();

            $table->string('work_order_id')->unique(); // already indexed
            $table->integer('candidates');
            $table->date('end_date')->index(); // For expiry tracking
            $table->foreignId('client_id')
                ->constrained()
                ->cascadeOnDelete()
                ->index();
            $table->foreignId('employee_id')
                ->constrained('employees')
                ->cascadeOnDelete();

            $table->integer('visa_issue_number')->nullable();
            $table->integer('sponsor_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');

            // 🔥 Composite index
            $table->index(['client_id', 'employee_id', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_orders');
    }
};
