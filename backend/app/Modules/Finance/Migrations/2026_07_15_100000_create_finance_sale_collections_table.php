<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_sale_collections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('application_id');
            $table->unsignedBigInteger('job_list_id')->nullable();
            $table->string('candidate_name')->nullable();
            $table->string('passport_no')->nullable();
            $table->decimal('sale_price', 14, 2)->default(0);
            $table->decimal('amount', 14, 2)->default(0);
            $table->string('payer_type')->nullable();
            $table->string('payment_method')->nullable();
            $table->date('collection_date');
            $table->string('entry_no')->nullable();
            $table->string('voucher_no')->nullable();
            $table->unsignedBigInteger('finance_account_type_transaction_id')->nullable();
            $table->string('job_code')->nullable();
            $table->string('job_title')->nullable();
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('application_id', 'fsc_application_fk')
                ->references('id')
                ->on('applications')
                ->cascadeOnDelete();

            $table->foreign('job_list_id', 'fsc_job_list_fk')
                ->references('id')
                ->on('job_lists')
                ->nullOnDelete();

            $table->foreign('finance_account_type_transaction_id', 'fsc_type_txn_fk')
                ->references('id')
                ->on('finance_account_type_transactions')
                ->nullOnDelete();

            $table->foreign('created_by', 'fsc_created_by_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('updated_by', 'fsc_updated_by_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->index(['application_id', 'collection_date'], 'fsc_application_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_sale_collections');
    }
};
