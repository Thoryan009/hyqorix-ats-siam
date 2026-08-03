<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_account_type_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_type');
            $table->decimal('amount', 14, 2);
            $table->date('transaction_date');
            $table->string('particular');
            $table->string('reference_no')->nullable();
            $table->text('remarks')->nullable();
            $table->string('voucher_no')->nullable();

            $table->string('account_category')->nullable();
            $table->string('main_account_type')->nullable();
            $table->unsignedBigInteger('account_id')->nullable();
            $table->string('account_label')->nullable();

            $table->string('from_account_category')->nullable();
            $table->string('from_main_account_type')->nullable();
            $table->unsignedBigInteger('from_account_id')->nullable();
            $table->string('from_account_label')->nullable();

            $table->string('to_account_category')->nullable();
            $table->string('to_main_account_type')->nullable();
            $table->unsignedBigInteger('to_account_id')->nullable();
            $table->string('to_account_label')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('account_id', 'fatt_account_fk')
                ->references('id')
                ->on('finance_accounts')
                ->nullOnDelete();

            $table->foreign('from_account_id', 'fatt_from_account_fk')
                ->references('id')
                ->on('finance_accounts')
                ->nullOnDelete();

            $table->foreign('to_account_id', 'fatt_to_account_fk')
                ->references('id')
                ->on('finance_accounts')
                ->nullOnDelete();

            $table->foreign('created_by', 'fatt_created_by_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('updated_by', 'fatt_updated_by_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->index(['transaction_type', 'transaction_date'], 'fatt_type_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_account_type_transactions');
    }
};
