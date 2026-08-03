<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('finance_accounts', function (Blueprint $table) {
            $table->foreignId('income_head_id')
                ->nullable()
                ->after('expense_category_id')
                ->constrained('income_heads')
                ->nullOnDelete();
            $table->foreignId('income_category_id')
                ->nullable()
                ->after('income_head_id')
                ->constrained('income_categories')
                ->nullOnDelete();

            $table->unique(['category', 'income_head_id'], 'finance_accounts_category_income_head_unique');
        });
    }

    public function down(): void
    {
        Schema::table('finance_accounts', function (Blueprint $table) {
            $table->dropUnique('finance_accounts_category_income_head_unique');
            $table->dropConstrainedForeignId('income_category_id');
            $table->dropConstrainedForeignId('income_head_id');
        });
    }
};
