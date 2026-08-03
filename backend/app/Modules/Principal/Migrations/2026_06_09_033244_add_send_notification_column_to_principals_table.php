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
        Schema::table('principals', function (Blueprint $table) {
            $table->boolean('send_notification')->default(true)->after('contact_person_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    
    public function down(): void
    {
        Schema::table('principals', function (Blueprint $table) {
            $table->dropColumn('send_notification');
        });
    }
};
