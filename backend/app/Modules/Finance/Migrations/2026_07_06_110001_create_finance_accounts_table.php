<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('category', 50);
            $table->string('account_name');
            $table->string('account_label')->nullable();
            $table->enum('account_type', ['cash', 'bank'])->nullable();
            $table->foreignId('bank_id')->nullable()->constrained('finance_banks')->nullOnDelete();
            $table->string('icon')->nullable();
            $table->string('code')->nullable();
            $table->string('phone')->nullable();
            $table->decimal('balance', 14, 2)->default(0);
            $table->decimal('opening_balance', 14, 2)->default(0);
            $table->foreignId('expense_head_id')->nullable()->constrained('expense_heads')->nullOnDelete();
            $table->foreignId('expense_category_id')->nullable()->constrained('expense_categories')->nullOnDelete();
            $table->decimal('base_price', 12, 2)->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->unsignedBigInteger('bill_agent_id')->nullable();
            $table->json('metadata')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['category', 'status']);
            $table->unique(['category', 'expense_head_id']);
            $table->unique(['category', 'entity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_accounts');
    }
};
