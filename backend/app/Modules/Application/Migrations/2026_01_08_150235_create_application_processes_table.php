<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
       Schema::create('application_processes', function (Blueprint $table) {
            $table->id();

            $table->enum('status', ['pending', 'draft', 'completed', 'rejected','declined'])->default('pending')->index();
            $table->json('data')->nullable();
            $table->timestamp('started_at')->nullable()->index();
            $table->timestamp('completed_at')->nullable()->index();
            $table->string('remarks')->nullable();

            // Foreign keys
            $table->unsignedBigInteger('application_id')->index();
            $table->unsignedBigInteger('process_id')->index();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->foreign('application_id', 'application_processes_application_id_fk')
                ->references('id')->on('applications')->onDelete('cascade');
            $table->foreign('process_id', 'application_processes_process_id_fk')
                ->references('id')->on('processes')->onDelete('cascade');
            $table->foreign('created_by', 'application_processes_created_by_fk')
                ->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by', 'application_processes_updated_by_fk')
                ->references('id')->on('users')->onDelete('set null');

            $table->timestamps();

            // Composite indexes
            $table->index(['application_id', 'process_id']);
            $table->index(['application_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_processes');
    }
};
