<?php

namespace App\Modules\Finance\Repositories;

use App\Modules\Finance\Models\FinanceIncomeCollection;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;

class FinanceIncomeCollectionRepository extends BaseRepository
{
    public function __construct(FinanceIncomeCollection $model)
    {
        parent::__construct($model);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        $this->applySearch($query, $filters['search'] ?? null);

        if (!empty($filters['category_id'])) {
            $query->where('income_category_id', (int) $filters['category_id']);
        }

        if (!empty($filters['head_id'])) {
            $query->where('income_head_id', (int) $filters['head_id']);
        }

        if (!empty($filters['from_date'])) {
            $query->whereDate('collection_date', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->whereDate('collection_date', '<=', $filters['to_date']);
        }
    }

    protected function applyEagerLoads(Builder $query, array $filters): void
    {
        $query->with(['incomeCategory', 'incomeHead']);
    }

    protected function applyOrder(Builder $query, array $filters): void
    {
        $query->orderByDesc('collection_date')->orderByDesc('id');
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
                ->orWhere('voucher_no', 'like', "%{$search}%")
                ->orWhere('client_name', 'like', "%{$search}%")
                ->orWhere('job_code', 'like', "%{$search}%")
                ->orWhere('job_title', 'like', "%{$search}%")
                ->orWhere('receive_account_name', 'like', "%{$search}%")
                ->orWhere('linked_account_name', 'like', "%{$search}%")
                ->orWhere('collected_by_name', 'like', "%{$search}%")
                ->orWhereHas('incomeCategory', fn (Builder $cq) => $cq->where('name', 'like', "%{$search}%"))
                ->orWhereHas('incomeHead', fn (Builder $hq) => $hq->where('name', 'like', "%{$search}%"));
        });
    }

    public function getSummary(array $filters = []): array
    {
        $query = $this->baseQuery();
        $this->applyFilters($query, $filters);

        $totalCount = (int) (clone $query)->count();
        $totalCollected = round((float) (clone $query)->sum('amount'), 2);

        $thisMonthQuery = clone $query;
        $thisMonthQuery
            ->whereMonth('collection_date', now()->month)
            ->whereYear('collection_date', now()->year);

        return [
            'total_count' => $totalCount,
            'this_month_count' => (int) $thisMonthQuery->count(),
            'total_collected' => $totalCollected,
        ];
    }
}
