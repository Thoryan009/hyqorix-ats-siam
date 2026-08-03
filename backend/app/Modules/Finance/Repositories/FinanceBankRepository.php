<?php

namespace App\Modules\Finance\Repositories;

use App\Modules\Finance\Models\FinanceBank;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;

class FinanceBankRepository extends BaseRepository
{
    public function __construct(FinanceBank $model)
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

    protected function applyOrder(Builder $query, array $filters): void
    {
        $query->orderBy('bank_name');
    }

    protected function applySearch(Builder $query, ?string $search): void
    {
        if (!$search) {
            return;
        }

        $search = trim($search);

        $query->where(function (Builder $q) use ($search) {
            $q->where('bank_name', 'like', "%{$search}%")
                ->orWhere('swift_code', 'like', "%{$search}%")
                ->orWhere('branch_name', 'like', "%{$search}%")
                ->orWhere('address', 'like', "%{$search}%");
        });
    }

    private function normalizeStatus(string $status): string
    {
        return strtolower($status) === 'inactive' ? 'inactive' : 'active';
    }
}
