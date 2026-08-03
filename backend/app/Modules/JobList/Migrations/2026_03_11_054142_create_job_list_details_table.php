<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('job_list_details', function (Blueprint $table) {
            $table->id();
             $table->foreignId('job_list_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('job_list_details_head_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->float('amount', 10, 2);
            $table->float('amount_usd', 10)->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_list_details');
    }
};
