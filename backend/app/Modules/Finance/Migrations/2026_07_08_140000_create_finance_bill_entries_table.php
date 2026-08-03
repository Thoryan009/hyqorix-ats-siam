<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_bill_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('expense_category_id');
            $table->unsignedBigInteger('expense_head_id');
            $table->decimal('amount', 14, 2);
            $table->string('payment_method')->default('cash');
            $table->date('payment_date');
            $table->string('particular');
            $table->string('reference_no')->nullable();
            $table->string('voucher_no')->nullable();
            $table->string('batch_ref')->nullable();
            $table->text('remarks')->nullable();
            $table->string('status')->default('pending');
            $table->text('approval_remarks')->nullable();

            $table->string('linked_account_category')->nullable();
            $table->unsignedBigInteger('linked_account_id')->nullable();
            $table->string('linked_account_name')->nullable();
            $table->string('linked_account_type')->nullable();

            $table->string('expense_cost_type')->nullable();
            $table->unsignedBigInteger('expense_cost_account_id')->nullable();
            $table->string('expense_cost_account_name')->nullable();
            $table->string('expense_cost_category_name')->nullable();

            $table->unsignedBigInteger('application_id')->nullable();
            $table->unsignedBigInteger('job_list_id')->nullable();
            $table->string('candidate_name')->nullable();
            $table->string('passport_no')->nullable();
            $table->string('application_status')->nullable();
            $table->string('job_name')->nullable();
            $table->string('job_code')->nullable();
            $table->string('client_name')->nullable();

            $table->unsignedBigInteger('work_order_id')->nullable();
            $table->string('demand_letter')->nullable();
            $table->string('demand_letter_country')->nullable();

            $table->string('payment_account_category')->nullable();
            $table->string('payment_account_type')->nullable();
            $table->unsignedBigInteger('payment_account_id')->nullable();
            $table->string('payment_account_name')->nullable();

            $table->unsignedBigInteger('requested_by_id')->nullable();
            $table->string('requested_by_name')->nullable();
            $table->string('requested_by_email')->nullable();
            $table->string('requested_by_type')->nullable();
            $table->timestamp('requested_at')->nullable();

            $table->timestamp('approved_at')->nullable();
            $table->string('approved_by')->nullable();
            $table->timestamp('rejected_at')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('expense_category_id', 'fbill_category_fk')
                ->references('id')
                ->on('expense_categories')
                ->restrictOnDelete();

            $table->foreign('expense_head_id', 'fbill_head_fk')
                ->references('id')
                ->on('expense_heads')
                ->restrictOnDelete();

            $table->foreign('application_id', 'fbill_application_fk')
                ->references('id')
                ->on('applications')
                ->nullOnDelete();

            $table->foreign('job_list_id', 'fbill_job_list_fk')
                ->references('id')
                ->on('job_lists')
                ->nullOnDelete();

            $table->foreign('work_order_id', 'fbill_work_order_fk')
                ->references('id')
                ->on('work_orders')
                ->nullOnDelete();

            $table->foreign('created_by', 'fbill_created_by_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('updated_by', 'fbill_updated_by_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->index(['expense_head_id', 'status'], 'fbill_head_status_idx');
            $table->index(['payment_date'], 'fbill_payment_date_idx');
            $table->index(['status'], 'fbill_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_bill_entries');
    }
};
