<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('party_types', function (Blueprint $table) {
            $table->boolean('apply_job_filter')->default(false)->after('source_module');
        });
    }

    public function down(): void
    {
        Schema::table('party_types', function (Blueprint $table) {
            $table->dropColumn('apply_job_filter');
        });
    }
};
