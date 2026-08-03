<?php

namespace App\Modules\Finance\Repositories;

use App\Modules\Finance\Models\ExpenseCategory;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;

class ExpenseCategoryRepository extends BaseRepository
{
    public function __construct(ExpenseCategory $model)
    {
        parent::__construct($model);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        $this->applySearch($query, $filters['search'] ?? null);

        if (!empty($filters['status'])) {
            $query->where('status', $this->normalizeStatus($filters['status']));
        }
    }

    protected function applyEagerLoads(Builder $query, array $filters): void
    {
        $query->withCount('expenseHeads');
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
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%");
        });
    }

    private function normalizeStatus(string $status): string
    {
        return strtolower($status) === 'inactive' ? 'inactive' : 'active';
    }
}
