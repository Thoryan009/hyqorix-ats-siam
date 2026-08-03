<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_income_collections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('income_category_id');
            $table->unsignedBigInteger('income_head_id');
            $table->decimal('amount', 14, 2);
            $table->string('payment_method')->default('cash');
            $table->date('collection_date');
            $table->string('particular');
            $table->string('reference_no')->nullable();
            $table->string('voucher_no')->nullable();
            $table->text('remarks')->nullable();
            $table->string('status')->default('collected');

            $table->string('linked_account_category')->nullable();
            $table->unsignedBigInteger('linked_account_id')->nullable();
            $table->string('linked_account_name')->nullable();
            $table->string('linked_account_type')->nullable();

            $table->string('receive_account_category')->nullable();
            $table->string('receive_account_type')->nullable();
            $table->unsignedBigInteger('receive_account_id')->nullable();
            $table->string('receive_account_name')->nullable();

            $table->unsignedBigInteger('finance_account_type_transaction_id')->nullable();

            $table->unsignedBigInteger('collected_by_id')->nullable();
            $table->string('collected_by_name')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('income_category_id', 'fic_category_fk')
                ->references('id')
                ->on('income_categories')
                ->restrictOnDelete();

            $table->foreign('income_head_id', 'fic_head_fk')
                ->references('id')
                ->on('income_heads')
                ->restrictOnDelete();

            $table->foreign('receive_account_id', 'fic_receive_account_fk')
                ->references('id')
                ->on('finance_accounts')
                ->nullOnDelete();

            $table->foreign('linked_account_id', 'fic_linked_account_fk')
                ->references('id')
                ->on('finance_accounts')
                ->nullOnDelete();

            $table->foreign('created_by', 'fic_created_by_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('updated_by', 'fic_updated_by_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->index(['collection_date'], 'fic_collection_date_idx');
            $table->index(['status'], 'fic_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_income_collections');
    }
};
