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
        Schema::table('agents', function (Blueprint $table) {
            $table->string('agent_image_path')->nullable();
            $table->string('phone2')->nullable();
            $table->string('stuff_name')->nullable()->after('phone2');
            $table->string('stuff_phone')->nullable()->after('stuff_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agents', function (Blueprint $table) {
            $table->dropColumn('agent_image_path');
            $table->dropColumn('phone2');
            $table->dropColumn('stuff_name');
            $table->dropColumn('stuff_phone');
        });
    }
};
