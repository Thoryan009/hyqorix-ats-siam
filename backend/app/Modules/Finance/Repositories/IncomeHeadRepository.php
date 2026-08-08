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
            $query->where('income_heads.income_category_id', (int) $filters['category_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('income_heads.status', $this->normalizeStatus($filters['status']));
        }
    }

    protected function applyEagerLoads(Builder $query, array $filters): void
    {
        $query->with('incomeCategory');
    }

    protected function applyOrder(Builder $query, array $filters): void
    {
        $query
            ->leftJoin('income_categories', 'income_heads.income_category_id', '=', 'income_categories.id')
            ->orderBy('income_categories.name')
            ->orderBy('income_heads.sort_order')
            ->orderBy('income_heads.name')
            ->select('income_heads.*');
    }

    protected function applySearch(Builder $query, ?string $search): void
    {
        if (!$search) {
            return;
        }

        $search = trim($search);

        $query->where(function (Builder $q) use ($search) {
            $q->where('income_heads.name', 'like', "%{$search}%")
                ->orWhereHas('incomeCategory', fn (Builder $categoryQuery) => $categoryQuery->where('name', 'like', "%{$search}%"));
        });
    }

    private function normalizeStatus(string $status): string
    {
        return strtolower($status) === 'inactive' ? 'inactive' : 'active';
    }

    public function getSummary(array $filters = []): array
    {
        $query = $this->baseQuery();
        $this->applyFilters($query, $filters);

        $totalCount = (int) (clone $query)->count();
        $totalBasePrice = round((float) (clone $query)->sum('income_heads.base_price'), 2);

        $activeQuery = clone $query;
        $activeQuery->where('income_heads.status', 'active');

        return [
            'total_count' => $totalCount,
            'active_count' => (int) $activeQuery->count(),
            'total_base_price' => $totalBasePrice,
        ];
    }
}
