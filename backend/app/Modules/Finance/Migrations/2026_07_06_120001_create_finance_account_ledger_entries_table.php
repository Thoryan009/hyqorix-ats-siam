<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_account_ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('finance_account_id');
            $table->unsignedBigInteger('finance_account_transaction_id')->nullable();
            $table->date('entry_date');
            $table->string('particular');
            $table->string('voucher_no');
            $table->string('demand_letter')->nullable();
            $table->string('job')->nullable();
            $table->string('client_name')->nullable();
            $table->decimal('dr_amount', 14, 2)->default(0);
            $table->decimal('discount', 14, 2)->default(0);
            $table->decimal('cr_amount', 14, 2)->default(0);
            $table->string('payment_method')->nullable();
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('finance_account_id', 'fa_ledger_account_fk')
                ->references('id')
                ->on('finance_accounts')
                ->cascadeOnDelete();

            $table->foreign('finance_account_transaction_id', 'fa_ledger_txn_fk')
                ->references('id')
                ->on('finance_account_transactions')
                ->nullOnDelete();

            $table->foreign('created_by', 'fa_ledger_created_by_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->index(['finance_account_id', 'entry_date'], 'fa_ledger_account_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_account_ledger_entries');
    }
};
