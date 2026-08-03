<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expense_heads', function (Blueprint $table) {
            $table->boolean('is_bills_receivable_link')
                ->default(false)
                ->after('linked_accounts');
        });
    }

    public function down(): void
    {
        Schema::table('expense_heads', function (Blueprint $table) {
            $table->dropColumn('is_bills_receivable_link');
        });
    }
};
