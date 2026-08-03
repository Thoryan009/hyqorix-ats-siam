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
            $table->unsignedBigInteger('expiry_report_notify_department_id')->nullable()->index();
            $table->foreign('expiry_report_notify_department_id', 'settings_expiry_report_notify_department_id_fk')->references('id')->on('departments')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropForeign('settings_expiry_report_notify_department_id_fk');
            $table->dropColumn('expiry_report_notify_department_id');
        });
    }
};
