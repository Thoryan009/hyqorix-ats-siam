<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('job_lists', function (Blueprint $table) {
            $table->id();

            $table->string('job_code')->unique();
            $table->integer('vacancy')->nullable();
            $table->string('name')->index();
            $table->string('experience')->nullable();
            $table->integer('min_age')->nullable();
            $table->integer('max_age')->nullable();

            $table->decimal('price', 12, 2)->nullable();
            $table->decimal('client_commission_per_candidate', 12, 2)->nullable();

            $table->string('contract_length')->nullable();
            $table->text('description')->nullable();
            $table->string('qualification')->nullable();
            $table->string('language')->nullable();
            $table->string('salary')->nullable();

            $table->enum('payer', ['candidate', 'client'])->index()->default('client');
            $table->date('deadline')->nullable()->index();
            $table->date('interview_date')->nullable()->index();

            $table->enum('status', ['open', 'closed', 'hold'])->default('open')->index();

            // Foreign keys
            $table->unsignedBigInteger('work_order_id')->index();
            $table->foreign('work_order_id', 'job_lists_work_order_id_fk')->references('id')->on('work_orders')->onDelete('cascade');

            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('created_by', 'job_lists_created_by_fk')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by', 'job_lists_updated_by_fk')->references('id')->on('users')->onDelete('set null');

            $table->timestamps();

            // Composite indexes
            $table->index(['status', 'deadline']);
            $table->index(['work_order_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_lists');
    }
};
