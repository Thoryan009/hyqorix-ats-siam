<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('embasy_submissions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('application_id')
                ->constrained('applications')
                ->onDelete('cascade');

            $table->enum('religion', ['muslim', 'non-muslim']);
            $table->string('visa_profession_ar')->nullable();
            $table->string('visa_profession_en')->nullable();
            $table->string('visit_work_for_ar')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('embasy_submissions');
    }
};
