<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('finance_bill_entries', function (Blueprint $table) {
            $table->dropForeign('fbill_category_fk');
            $table->dropForeign('fbill_head_fk');
        });

        Schema::table('finance_bill_entries', function (Blueprint $table) {
            $table->string('entry_type', 30)->default('expense_bill')->after('id');
            $table->unsignedBigInteger('expense_category_id')->nullable()->change();
            $table->unsignedBigInteger('expense_head_id')->nullable()->change();
            $table->foreignId('asset_account_id')
                ->nullable()
                ->after('expense_head_id')
                ->constrained('finance_accounts')
                ->nullOnDelete();

            $table->foreign('expense_category_id', 'fbill_category_fk')
                ->references('id')
                ->on('expense_categories')
                ->restrictOnDelete();

            $table->foreign('expense_head_id', 'fbill_head_fk')
                ->references('id')
                ->on('expense_heads')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('finance_bill_entries', function (Blueprint $table) {
            $table->dropForeign(['asset_account_id']);
            $table->dropColumn(['entry_type', 'asset_account_id']);
        });

        Schema::table('finance_bill_entries', function (Blueprint $table) {
            $table->dropForeign('fbill_category_fk');
            $table->dropForeign('fbill_head_fk');
        });

        Schema::table('finance_bill_entries', function (Blueprint $table) {
            $table->unsignedBigInteger('expense_category_id')->nullable(false)->change();
            $table->unsignedBigInteger('expense_head_id')->nullable(false)->change();

            $table->foreign('expense_category_id', 'fbill_category_fk')
                ->references('id')
                ->on('expense_categories')
                ->restrictOnDelete();

            $table->foreign('expense_head_id', 'fbill_head_fk')
                ->references('id')
                ->on('expense_heads')
                ->restrictOnDelete();
        });
    }
};
