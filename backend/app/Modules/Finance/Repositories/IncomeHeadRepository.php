<?php

namespace App\Modules\Finance\Repositories;

use App\Modules\Finance\Models\IncomeHead;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;

class IncomeHeadRepository extends BaseRepository
{
    public function __construct(IncomeHead $model)
    {
        parent::__construct($model);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        $this->applySearch($query, $filters['search'] ?? null);

        // Retired with Recruitment Income category — kept for historical collections.
        $query->whereDoesntHave(
            'incomeCategory',
            fn (Builder $categoryQuery) => $categoryQuery->where('code', 'recruitment_income')
        );

        if (!empty($filters['category_id'])) {
            $query->where('income_category_id', (int) $filters['category_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $this->normalizeStatus($filters['status']));
        }
    }

    protected function applyEagerLoads(Builder $query, array $filters): void
    {
        $query->with('incomeCategory');
    }

    protected function applyOrder(Builder $query, array $filters): void
    {
        $query->orderBy('sort_order')->orderBy('name');
    }

    protected function applySearch(Builder $query, ?string $search): void
    {
        if (!$search) {
            return;
        }

        $search = trim($search);

        $query->where(function (Builder $q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhereHas('incomeCategory', fn (Builder $categoryQuery) => $categoryQuery->where('name', 'like', "%{$search}%"));
        });
    }

    private function normalizeStatus(string $status): string
    {
        return strtolower($status) === 'inactive' ? 'inactive' : 'active';
    }
}
