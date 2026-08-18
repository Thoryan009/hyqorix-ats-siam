<?php

namespace App\Modules\Finance\Repositories;

use App\Modules\Finance\Models\FinanceAccountTypeTransaction;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;

class FinanceAccountTypeTransactionRepository extends BaseRepository
{
    public function __construct(FinanceAccountTypeTransaction $model)
    {
        parent::__construct($model);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        $this->applySearch($query, $filters['search'] ?? null);

        if (!empty($filters['transaction_type'])) {
            $query->where('transaction_type', (string) $filters['transaction_type']);
        }
    }

    protected function applyOrder(Builder $query, array $filters): void
    {
        $query->orderByDesc('transaction_date')->orderByDesc('id');
    }

    protected function applySearch(Builder $query, ?string $search): void
    {
        if (!$search) {
            return;
        }

        $search = trim($search);

        $query->where(function (Builder $q) use ($search) {
            $q->where('particular', 'like', "%{$search}%")
                ->orWhere('reference_no', 'like', "%{$search}%")
                ->orWhere('remarks', 'like', "%{$search}%")
                ->orWhere('from_account_label', 'like', "%{$search}%")
                ->orWhere('to_account_label', 'like', "%{$search}%")
                ->orWhere('account_label', 'like', "%{$search}%")
                ->orWhere('transaction_type', 'like', "%{$search}%")
                ->orWhere('voucher_no', 'like', "%{$search}%")
                ->orWhere('transaction_no', 'like', "%{$search}%");
        });
    }

    public function getSummary(array $filters = []): array
    {
        $query = $this->baseQuery();
        $this->applyFilters($query, $filters);

        $totalCount = (int) (clone $query)->count();

        $thisMonthQuery = clone $query;
        $thisMonthQuery
            ->whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year);

        return [
            'total_count' => $totalCount,
            'this_month_count' => (int) $thisMonthQuery->count(),
        ];
    }
}
