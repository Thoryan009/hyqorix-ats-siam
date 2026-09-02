<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('chart_of_accounts')
            ->whereIn('type', ['Direct Cost A', 'Direct Cost B', 'Revenue', 'Contra Revenue'])
            ->where('financial_statement', '!=', 'Gross Profit')
            ->update(['financial_statement' => 'Gross Profit']);
    }

    public function down(): void
    {
        // No rollback — corrected data should remain on Gross Profit.
    }
};
