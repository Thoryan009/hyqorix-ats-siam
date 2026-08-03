<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->string('education_path')->nullable();
            $table->string('training_path')->nullable();
            $table->string('experience_path')->nullable();
            $table->string('driving_license_path')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn('education_path');
            $table->dropColumn('training_path');
            $table->dropColumn('experience_path');
            $table->dropColumn('driving_license_path');
        });
    }
};
