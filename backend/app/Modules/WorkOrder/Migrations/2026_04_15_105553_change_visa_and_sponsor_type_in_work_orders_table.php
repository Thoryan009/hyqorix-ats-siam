<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->string('visa_issue_number')->nullable()->change();
            $table->string('sponsor_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->integer('visa_issue_number')->nullable()->change();
            $table->integer('sponsor_id')->nullable()->change();
        });
    }
};
