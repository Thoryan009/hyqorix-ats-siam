<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('finance_account_ledger_entries', function (Blueprint $table) {
            $table->unsignedBigInteger('finance_bill_entry_id')->nullable()->after('finance_account_type_transaction_id');

            $table->foreign('finance_bill_entry_id', 'fa_ledger_bill_fk')
                ->references('id')
                ->on('finance_bill_entries')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('finance_account_ledger_entries', function (Blueprint $table) {
            $table->dropForeign('fa_ledger_bill_fk');
            $table->dropColumn('finance_bill_entry_id');
        });
    }
};
