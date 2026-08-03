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
        Schema::table('work_orders', function (Blueprint $table) {
            $table->integer('candidates')->nullable()->change();
            $table->date('end_date')->nullable()->change();
            $table->string('visa_issue_number')->nullable()->change();
            $table->string('sponsor_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->integer('candidates')->default(0)->nullable(false)->change();
            $table->date('end_date')->nullable(false)->change();
            $table->string('visa_issue_number')->default('N/A')->nullable(false)->change();
            $table->string('sponsor_id')->default('N/A')->nullable(false)->change();
        });
    }
};
