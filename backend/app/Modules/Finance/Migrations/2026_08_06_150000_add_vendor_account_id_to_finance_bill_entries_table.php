<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('finance_bill_entries', function (Blueprint $table) {
            $table->foreignId('vendor_account_id')
                ->nullable()
                ->after('asset_account_id')
                ->constrained('finance_accounts')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('finance_bill_entries', function (Blueprint $table) {
            $table->dropForeign(['vendor_account_id']);
            $table->dropColumn('vendor_account_id');
        });
    }
};
