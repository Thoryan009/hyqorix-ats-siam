<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('finance_bill_entries', function (Blueprint $table) {
            if (!Schema::hasColumn('finance_bill_entries', 'receipt_path')) {
                $table->string('receipt_path')->nullable()->after('remarks');
            }
        });
    }

    public function down(): void
    {
        Schema::table('finance_bill_entries', function (Blueprint $table) {
            if (Schema::hasColumn('finance_bill_entries', 'receipt_path')) {
                $table->dropColumn('receipt_path');
            }
        });
    }
};
