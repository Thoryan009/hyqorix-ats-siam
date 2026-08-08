<?php

namespace App\Modules\Finance\Repositories;

use App\Modules\Finance\Models\FinanceBillEntry;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;

class FinanceBillEntryRepository extends BaseRepository
{
    public function __construct(FinanceBillEntry $model)
    {
        parent::__construct($model);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        $this->applySearch($query, $filters['search'] ?? null);

        if (!empty($filters['category_id'])) {
            $query->where('expense_category_id', (int) $filters['category_id']);
        }

        if (!empty($filters['head_id'])) {
            $query->where('expense_head_id', (int) $filters['head_id']);
        }

        if (!empty($filters['entry_type'])) {
            $query->where('entry_type', strtolower((string) $filters['entry_type']));
        }

        $scope = strtolower(trim((string) ($filters['scope'] ?? '')));

        if ($scope === 'processed') {
            $query->where(function (Builder $q) {
                $q->where('status', 'rejected')
                    ->orWhere(function (Builder $inner) {
                        $inner->where('status', 'approved')
                            ->whereRaw('LOWER(COALESCE(payment_method, "")) != ?', ['due']);
                    });
            });
        } elseif ($scope === 'payable') {
            $query->where('status', 'approved')
                ->whereRaw('LOWER(COALESCE(payment_method, "")) = ?', ['due'])
                ->whereRaw('(COALESCE(amount, 0) - COALESCE(paid_amount, 0)) > 0');
        } elseif (!empty($filters['statuses']) && is_array($filters['statuses'])) {
            $statuses = collect($filters['statuses'])
                ->map(static fn ($status) => strtolower(trim((string) $status)))
                ->filter()
                ->unique()
                ->values()
                ->all();

            if ($statuses !== []) {
                $query->whereIn('status', $statuses);
            }
        } elseif (!empty($filters['status'])) {
            $query->where('status', strtolower((string) $filters['status']));
        }
    }

    protected function applyEagerLoads(Builder $query, array $filters): void
    {
        $query->with(['expenseCategory', 'expenseHead', 'assetAccount', 'vendorAccount']);
    }

    protected function applyOrder(Builder $query, array $filters): void
    {
        $query->orderByDesc('payment_date')->orderByDesc('id');
    }

    public function sumAmountByHead(int $headId, ?string $status = null): float
    {
        $query = $this->baseQuery()->where('expense_head_id', $headId);

        if ($status) {
            $query->where('status', $status);
        }

        return (float) $query->sum('amount');
    }

    public function countByStatus(?string $status = null): int
    {
        $query = $this->baseQuery();

        if ($status) {
            $query->where('status', $status);
        }

        return (int) $query->count();
    }

    public function countByStatusAndEntryType(string $status, string $entryType): int
    {
        $query = $this->baseQuery()->where('status', $status);

        if ($entryType === 'expense_bill') {
            $query->where(function (Builder $q) {
                $q->whereNull('entry_type')
                    ->orWhere('entry_type', '')
                    ->orWhere('entry_type', 'expense_bill');
            });
        } else {
            $query->where('entry_type', $entryType);
        }

        return (int) $query->count();
    }

    public function countPaid(): int
    {
        return (int) $this->baseQuery()
            ->where('status', 'approved')
            ->whereRaw('LOWER(COALESCE(payment_method, "")) != ?', ['due'])
            ->count();
    }

    public function countPayable(): int
    {
        return (int) $this->baseQuery()
            ->where('status', 'approved')
            ->whereRaw('LOWER(COALESCE(payment_method, "")) = ?', ['due'])
            ->whereRaw('(COALESCE(amount, 0) - COALESCE(paid_amount, 0)) > 0')
            ->count();
    }

    protected function applySearch(Builder $query, ?string $search): void
    {
        if (!$search) {
            return;
        }

        $search = trim($search);

        $query->where(function (Builder $q) use ($search) {
            $q->where('particular', 'like', "%{$search}%")
                ->orWhere('voucher_no', 'like', "%{$search}%")
                ->orWhere('batch_ref', 'like', "%{$search}%")
                ->orWhere('request_no', 'like', "%{$search}%")
                ->orWhere('candidate_name', 'like', "%{$search}%")
                ->orWhere('passport_no', 'like', "%{$search}%")
                ->orWhere('job_name', 'like', "%{$search}%")
                ->orWhere('demand_letter', 'like', "%{$search}%")
                ->orWhere('client_name', 'like', "%{$search}%")
                ->orWhereHas('expenseCategory', fn (Builder $categoryQuery) => $categoryQuery->where('name', 'like', "%{$search}%"))
                ->orWhereHas('expenseHead', fn (Builder $headQuery) => $headQuery->where('name', 'like', "%{$search}%"));
        });
    }
}
