<?php

namespace App\Modules\Finance\Repositories;

use App\Modules\Finance\Models\FinanceIncomeTax;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;

class FinanceIncomeTaxRepository extends BaseRepository
{
    public function __construct(FinanceIncomeTax $model)
    {
        parent::__construct($model);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        $this->applySearch($query, $filters['search'] ?? null);

        if (!empty($filters['year'])) {
            $query->where('year', (int) $filters['year']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $this->normalizeStatus($filters['status']));
        }
    }

    protected function applyOrder(Builder $query, array $filters): void
    {
        $query->orderByDesc('year');
    }

    protected function applyEagerLoads(Builder $query, array $filters): void
    {
        $query->with(['createdBy', 'updatedBy']);

        parent::applyEagerLoads($query, $filters);
    }

    protected function applySearch(Builder $query, ?string $search): void
    {
        if (!$search) {
            return;
        }

        $search = trim($search);

        $query->where(function (Builder $q) use ($search) {
            $q->where('year', 'like', "%{$search}%")
                ->orWhere('notes', 'like', "%{$search}%");
        });
    }

    private function normalizeStatus(string $status): string
    {
        return strtolower($status) === 'inactive' ? 'inactive' : 'active';
    }
}
