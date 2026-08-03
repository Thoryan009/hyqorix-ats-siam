<?php

namespace App\Modules\Finance\Repositories;

use App\Modules\Finance\Models\IncomeCategory;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;

class IncomeCategoryRepository extends BaseRepository
{
    public function __construct(IncomeCategory $model)
    {
        parent::__construct($model);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        $this->applySearch($query, $filters['search'] ?? null);

        // Retired from Income Setup — kept inactive for historical collections.
        $query->where(function (Builder $q) {
            $q->whereNull('code')->orWhere('code', '!=', 'recruitment_income');
        });

        if (!empty($filters['status'])) {
            $query->where('status', $this->normalizeStatus($filters['status']));
        }
    }

    protected function applyEagerLoads(Builder $query, array $filters): void
    {
        $query->withCount('incomeHeads');
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
