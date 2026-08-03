<?php

namespace App\Modules\Finance\Seeders;

use App\Modules\Finance\Models\ExpenseCategory;
use App\Modules\Finance\Models\ExpenseHead;
use App\Modules\Finance\Services\FinanceAccountService;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $financeAccountService = app(FinanceAccountService::class);

        $categories = [
            [
                'code' => 'direct_cost',
                'name' => 'Direct Expense',
                'description' => 'Direct expenses related to recruitment operations',
                'sort_order' => 1,
                'heads' => [
                    ['name' => 'Medical', 'base_price' => 3500],
                    ['name' => 'Police Clearance', 'base_price' => 1200],
                    ['name' => 'Air Ticket', 'base_price' => 48500],
                    ['name' => 'MOFA', 'base_price' => 2800],
                    ['name' => 'Training', 'base_price' => 7500],
                    ['name' => 'Visa', 'base_price' => 16500],
                    ['name' => 'Manpower', 'base_price' => 22000],
                    ['name' => 'Commission', 'base_price' => 15000],
                    ['name' => 'Other', 'base_price' => 4500],
                ],
            ],
            [
                'code' => 'client_recruitment_cost',
                'name' => 'Client Recruitment Expense',
                'description' => 'Client-specific recruitment related expenses',
                'sort_order' => 2,
                'heads' => [
                    'Advertisement',
                    'Interview Venue',
                    'Trade Test',
                    'Transportation',
                    'Entertainment',
                    'Other',
                ],
            ],
            [
                'code' => 'operating_cost',
                'name' => 'Operating Expense',
                'description' => 'General operating and administrative expenses',
                'sort_order' => 3,
                'heads' => [
                    'Salary',
                    'Rent',
                    'Electricity',
                    'Bad Debt Expense',
                    'Internet',
                    'Stationery',
                    'Maintenance',
                    'Tax',
                    'VAT',
                    'Bank Interest',
                    'Other',
                ],
            ],
        ];

        foreach ($categories as $index => $categoryData) {
            $heads = $categoryData['heads'];
            unset($categoryData['heads']);

            $category = ExpenseCategory::query()->updateOrCreate(
                ['code' => $categoryData['code']],
                array_merge($categoryData, ['status' => 'active'])
            );

            // Repair legacy rows that were created by name without a code.
            if (!$category->wasRecentlyCreated && blank($category->code)) {
                $category->update(['code' => $categoryData['code']]);
            }

            $legacy = ExpenseCategory::query()
                ->whereNull('code')
                ->where('name', $categoryData['name'])
                ->where('id', '!=', $category->id)
                ->first();

            if ($legacy) {
                ExpenseHead::query()
                    ->where('expense_category_id', $legacy->id)
                    ->update(['expense_category_id' => $category->id]);
                $legacy->delete();
            }

            foreach ($heads as $sortOrder => $headData) {
                $name = is_array($headData) ? $headData['name'] : $headData;
                $basePrice = is_array($headData) ? ($headData['base_price'] ?? 0) : 0;

                $head = ExpenseHead::query()->updateOrCreate(
                    [
                        'expense_category_id' => $category->id,
                        'name' => $name,
                    ],
                    [
                        'base_price' => $basePrice,
                        'linked_accounts' => [],
                        'status' => 'active',
                        'sort_order' => $sortOrder + 1,
                    ]
                );

                $financeAccountService->ensureExpenseHeadAccount($head);
            }
        }

        ExpenseHead::query()
            ->with('expenseCategory')
            ->get()
            ->each(fn (ExpenseHead $head) => $financeAccountService->ensureExpenseHeadAccount($head));
    }
}
