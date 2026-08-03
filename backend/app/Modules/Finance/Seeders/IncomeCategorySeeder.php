<?php

namespace App\Modules\Finance\Seeders;

use App\Modules\Finance\Models\IncomeCategory;
use App\Modules\Finance\Models\IncomeHead;
use App\Modules\Finance\Services\FinanceAccountService;
use Illuminate\Database\Seeder;

class IncomeCategorySeeder extends Seeder
{
    public function run(): void
    {
        $financeAccountService = app(FinanceAccountService::class);

        $this->retireRecruitmentIncome();

        $categories = [
            [
                'code' => 'client_income',
                'name' => 'Client Income',
                'description' => 'Income received from overseas clients / principals',
                'sort_order' => 1,
                'heads' => [
                    ['name' => 'Client Commission', 'base_price' => 50000],
                ],
            ],
            [
                'code' => 'other_income',
                'name' => 'Operating Income',
                'description' => 'Indirect and miscellaneous operating income for recruiting agencies',
                'sort_order' => 2,
                'heads' => [
                    ['name' => 'Bank Interest', 'base_price' => 0],
                    ['name' => 'Miscellaneous Income', 'base_price' => 0],
                    ['name' => 'Liability Written Back', 'base_price' => 0],
                ],
            ],
        ];

        foreach ($categories as $categoryData) {
            $heads = $categoryData['heads'];
            unset($categoryData['heads']);

            $category = IncomeCategory::query()->updateOrCreate(
                ['code' => $categoryData['code']],
                array_merge($categoryData, ['status' => 'active'])
            );

            // Repair legacy rows that were created by name without a code.
            if (!$category->wasRecentlyCreated && blank($category->code)) {
                $category->update(['code' => $categoryData['code']]);
            }

            $legacy = IncomeCategory::query()
                ->whereNull('code')
                ->where('name', $categoryData['name'])
                ->where('id', '!=', $category->id)
                ->first();

            if ($legacy) {
                IncomeHead::query()
                    ->where('income_category_id', $legacy->id)
                    ->update(['income_category_id' => $category->id]);
                $legacy->delete();
            }

            foreach ($heads as $sortOrder => $headData) {
                $head = IncomeHead::query()->updateOrCreate(
                    [
                        'income_category_id' => $category->id,
                        'name' => $headData['name'],
                    ],
                    [
                        'base_price' => $headData['base_price'] ?? 0,
                        'status' => 'active',
                        'sort_order' => $sortOrder + 1,
                    ]
                );

                $financeAccountService->ensureIncomeHeadAccount($head);
            }
        }

        IncomeHead::query()
            ->with('incomeCategory')
            ->whereDoesntHave(
                'incomeCategory',
                fn ($query) => $query->where('code', 'recruitment_income')
            )
            ->get()
            ->each(fn (IncomeHead $head) => $financeAccountService->ensureIncomeHeadAccount($head));
    }

    private function retireRecruitmentIncome(): void
    {
        $categories = IncomeCategory::query()
            ->where(function ($query) {
                $query->where('code', 'recruitment_income')
                    ->orWhere('name', 'Recruitment Income');
            })
            ->get();

        foreach ($categories as $category) {
            IncomeHead::query()
                ->where('income_category_id', $category->id)
                ->update(['status' => 'inactive']);

            $category->update(['status' => 'inactive']);
        }
    }
}
