<?php

namespace App\Modules\Finance\Repositories;

use App\Modules\Finance\Models\FinanceAccount;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;

class FinanceAccountRepository extends BaseRepository
{
    public function __construct(FinanceAccount $model)
    {
        parent::__construct($model);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $this->normalizeStatus($filters['status']));
        }

        if (!empty($filters['account_type'])) {
            $query->where('account_type', strtolower($filters['account_type']));
        }

        $this->applySearch($query, $filters['search'] ?? null);
    }

    protected function applyEagerLoads(Builder $query, array $filters): void
    {
        $query->with(['bank', 'expenseHead', 'expenseCategory', 'incomeHead', 'incomeCategory']);

        $category = $filters['category'] ?? null;
        if ($category === 'applicant') {
            $query->with('applicantApplication');
        }

        // Ledger net (CR − DR) powers balance/amount on every accounts list table.
        $query->withSum('ledgerEntries as ledger_dr_amount', 'dr_amount');
        $query->withSum('ledgerEntries as ledger_cr_amount', 'cr_amount');
        $query->withCount('ledgerEntries');
    }

    protected function applyOrder(Builder $query, array $filters): void
    {
        $query->orderBy('account_name');
    }

    protected function applySearch(Builder $query, ?string $search): void
    {
        if (!$search) {
            return;
        }

        $search = trim($search);

        $query->where(function (Builder $q) use ($search) {
            $q->where('account_name', 'like', "%{$search}%")
                ->orWhere('account_label', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%");
        });
    }

    public function getCategorySummary(string $category): array
    {
        $baseQuery = $this->baseQuery()->where('category', $category);

        $ledgerNetSubquery = '(
            SELECT COALESCE(SUM(cr_amount), 0) - COALESCE(SUM(dr_amount), 0)
            FROM finance_account_ledger_entries
            WHERE finance_account_ledger_entries.finance_account_id = finance_accounts.id
        )';

        return [
            'total_accounts' => (clone $baseQuery)->count(),
            'active_accounts' => (clone $baseQuery)->where('status', 'active')->count(),
            'inactive_accounts' => (clone $baseQuery)->where('status', 'inactive')->count(),
            'total_current_balance' => (float) (clone $baseQuery)
                ->selectRaw("COALESCE(SUM({$ledgerNetSubquery}), 0) as total_ledger_balance")
                ->value('total_ledger_balance'),
        ];
    }

    private function normalizeStatus(string $status): string
    {
        return strtolower($status) === 'inactive' ? 'inactive' : 'active';
    }
}
