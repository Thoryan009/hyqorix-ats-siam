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
        Schema::table('settings', function (Blueprint $table) {
            $table->string('embassy_name')->nullable();
            $table->string('embassy_address')->nullable();
            $table->string('embassy_company_name')->nullable();
            $table->string('company_rl')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('embassy_name');
            $table->dropColumn('embassy_address');
            $table->dropColumn('embassy_company_name');
            $table->dropColumn('company_rl');
        });
    }
};
