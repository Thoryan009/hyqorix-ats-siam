<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_account_transactions', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['transfer', 'deposit', 'withdraw']);
            $table->foreignId('from_account_id')->constrained('finance_accounts');
            $table->foreignId('to_account_id')->constrained('finance_accounts');
            $table->decimal('amount', 14, 2);
            $table->string('voucher_no');
            $table->string('particular')->nullable();
            $table->string('reference_no')->nullable();
            $table->text('remarks')->nullable();
            $table->date('transaction_date');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['type', 'transaction_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_account_transactions');
    }
};
