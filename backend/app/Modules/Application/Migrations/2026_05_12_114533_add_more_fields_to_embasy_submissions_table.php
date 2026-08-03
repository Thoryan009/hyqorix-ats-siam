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
            $table->string('mofa_no')->nullable();
            $table->string('police_clearance_no')->nullable();
            $table->string('alwakala_no')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('embasy_submissions', function (Blueprint $table) {
            $table->dropColumn('mofa_no');
            $table->dropColumn('police_clearance_no');
            $table->dropColumn('alwakala_no');
        });
    }
};
