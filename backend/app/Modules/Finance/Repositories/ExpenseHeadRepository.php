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
                ->orWhereHas('expenseCategory', fn (Builder $categoryQuery) => $categoryQuery->where('name', 'like', "%{$search}%"));
        });
    }

    private function normalizeStatus(string $status): string
    {
        return strtolower($status) === 'inactive' ? 'inactive' : 'active';
    }
}
