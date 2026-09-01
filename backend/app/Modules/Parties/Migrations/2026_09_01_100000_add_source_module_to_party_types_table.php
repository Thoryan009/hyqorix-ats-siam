<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('party_types', function (Blueprint $table) {
            $table->string('source_module', 50)->nullable()->unique()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('party_types', function (Blueprint $table) {
            $table->dropUnique(['source_module']);
            $table->dropColumn('source_module');
        });
    }
};
