<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            $table->string('transaction_id')->unique();
            $table->string('bill_no')->index()->nullable();
            $table->float('total_amount')->nullable();
            $table->float('paid_amount')->default(0);
            $table->float('total_amount_usd')->nullable();
            $table->float('paid_amount_usd')->default(0);
            $table->float('discount_amount')->nullable();

            $table->enum('payment_method', ['bank', 'bkash', 'cash', 'pending'])->index();
            $table->enum('status', ['draft', 'due', 'paid', 'bill-generated', 'invoice-generated', 'invoice-sent', 'cancelled'])->index();

            $table->date('payment_date')->index();
            $table->time('payment_time');

            $table->string('remarks')->nullable();

            // Foreign keys
            $table->unsignedBigInteger('application_id')->index();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->foreign('application_id', 'transactions_application_id_fk')
                ->references('id')->on('applications')->onDelete('cascade');
            $table->foreign('created_by', 'transactions_created_by_fk')
                ->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by', 'transactions_updated_by_fk')
                ->references('id')->on('users')->onDelete('set null');

            $table->timestamps();

            // Composite index for reporting
            $table->index(['status', 'payment_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
