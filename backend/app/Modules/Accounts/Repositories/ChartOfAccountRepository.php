<?php

namespace App\Modules\Accounts\Repositories;

use App\Modules\Accounts\Models\ChartOfAccount;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;

class ChartOfAccountRepository extends BaseRepository
{
    public function __construct(ChartOfAccount $model)
    {
        parent::__construct($model);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        $this->applySearch($query, $filters['search'] ?? null);

        if (!empty($filters['status'])) {
            $query->where('status', $this->normalizeStatus($filters['status']));
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['financial_statement'])) {
            $query->where('financial_statement', $filters['financial_statement']);
        }

        if (!empty($filters['normal_balance'])) {
            $query->where(
                'normal_balance',
                strtolower((string) $filters['normal_balance']) === 'credit' ? 'credit' : 'debit'
            );
        }
    }

    protected function applyEagerLoads(Builder $query, array $filters): void
    {
        $query->with(['createdBy:id,name', 'updatedBy:id,name']);
    }

    protected function applyOrder(Builder $query, array $filters): void
    {
        $query->orderBy('code')->orderBy('sort_order')->orderBy('name');
    }

    protected function applySearch(Builder $query, ?string $search): void
    {
        if (!$search) {
            return;
        }

        $search = trim($search);

        $query->where(function (Builder $q) use ($search) {
            $q->where('code', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%")
                ->orWhere('type', 'like', "%{$search}%")
                ->orWhere('financial_statement', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        });
    }

    private function normalizeStatus(string $status): string
    {
        return strtolower($status) === 'inactive' ? 'inactive' : 'active';
    }
}
