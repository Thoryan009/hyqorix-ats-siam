<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('finance_bill_entries', function (Blueprint $table) {
            $table->foreignId('advance_adjustment_asset_account_id')
                ->nullable()
                ->after('vendor_account_id')
                ->constrained('finance_accounts')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('finance_bill_entries', function (Blueprint $table) {
            $table->dropForeign(['advance_adjustment_asset_account_id']);
            $table->dropColumn('advance_adjustment_asset_account_id');
        });
    }
};
