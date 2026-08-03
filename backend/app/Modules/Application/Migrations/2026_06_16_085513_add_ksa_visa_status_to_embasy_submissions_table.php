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
        Schema::table('embasy_submissions', function (Blueprint $table) {
            $table->enum('ksa_visa_status', ['done', 'cancel'])->nullable();
            $table->enum('religion', ['muslim', 'non-muslim'])->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('embasy_submissions', function (Blueprint $table) {
            $table->dropColumn('ksa_visa_status');
        });
    }
};
