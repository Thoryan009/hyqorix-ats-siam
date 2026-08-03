<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('finance_bill_entries', function (Blueprint $table) {
            $table->timestamp('manager_approved_at')->nullable()->after('requested_at');
            $table->string('manager_approved_by')->nullable()->after('manager_approved_at');
        });
    }

    public function down(): void
    {
        Schema::table('finance_bill_entries', function (Blueprint $table) {
            $table->dropColumn(['manager_approved_at', 'manager_approved_by']);
        });
    }
};
