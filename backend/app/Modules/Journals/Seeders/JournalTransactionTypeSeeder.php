<?php

namespace App\Modules\Journals\Seeders;

use App\Modules\Journals\Models\JournalTransactionType;
use Illuminate\Database\Seeder;

class JournalTransactionTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['code' => 'journal_voucher', 'name' => 'Journal Voucher', 'sort_order' => 1],
            ['code' => 'direct_expense', 'name' => 'Direct Expense', 'sort_order' => 2],
            ['code' => 'operating_expense', 'name' => 'Operating Expense', 'sort_order' => 3],
            ['code' => 'recruitment_revenue', 'name' => 'Recruitment Revenue', 'sort_order' => 4],
            ['code' => 'recruitment_refund', 'name' => 'Recruitment Refund', 'sort_order' => 5],
            ['code' => 'asset_purchase', 'name' => 'Asset Purchase', 'sort_order' => 6],
            ['code' => 'asset_return', 'name' => 'Asset Return', 'sort_order' => 7],
            ['code' => 'staff_advance', 'name' => 'Staff Advance', 'sort_order' => 8],
            ['code' => 'advance_adjustment', 'name' => 'Advance Adjustment', 'sort_order' => 9],
            ['code' => 'owner_capital', 'name' => 'Owner Capital', 'sort_order' => 10],
        ];

        foreach ($types as $type) {
            JournalTransactionType::query()->updateOrCreate(
                ['code' => $type['code']],
                [
                    'name' => $type['name'],
                    'sort_order' => $type['sort_order'],
                    'status' => 'active',
                ]
            );
        }
    }
}
