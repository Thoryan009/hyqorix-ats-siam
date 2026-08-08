<?php

namespace App\Modules\Finance\Repositories;

use App\Modules\Finance\Models\ExpenseHead;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;

class ExpenseHeadRepository extends BaseRepository
{
    public function __construct(ExpenseHead $model)
    {
        parent::__construct($model);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        $this->applySearch($query, $filters['search'] ?? null);

        if (!empty($filters['category_id'])) {
            $query->where('expense_category_id', (int) $filters['category_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $this->normalizeStatus($filters['status']));
        }
    }

    protected function applyEagerLoads(Builder $query, array $filters): void
    {
        $query->with('expenseCategory');
    }

    protected function applyOrder(Builder $query, array $filters): void
    {
        $query
            ->leftJoin('expense_categories', 'expense_heads.expense_category_id', '=', 'expense_categories.id')
            ->orderBy('expense_categories.name')
            ->orderBy('expense_heads.sort_order')
            ->orderBy('expense_heads.name')
            ->select('expense_heads.*');
    }

    protected function applySearch(Builder $query, ?string $search): void
    {
        if (!$search) {
            return;
        }

        $search = trim($search);

        $query->where(function (Builder $q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhereHas('expenseCategory', fn (Builder $categoryQuery) => $categoryQuery->where('name', 'like', "%{$search}%"));
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
        $totalBasePrice = round((float) (clone $query)->sum('base_price'), 2);

        $activeQuery = clone $query;
        $activeQuery->where('status', 'active');

        return [
            'total_count' => $totalCount,
            'active_count' => (int) $activeQuery->count(),
            'total_base_price' => $totalBasePrice,
        ];
    }
}
