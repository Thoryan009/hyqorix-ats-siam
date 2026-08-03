<?php

namespace App\Modules\Finance\Repositories;

use App\Modules\Finance\Models\FinanceAccountLedgerEntry;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;

class FinanceAccountLedgerRepository extends BaseRepository
{
    public function __construct(FinanceAccountLedgerEntry $model)
    {
        parent::__construct($model);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        if (!empty($filters['finance_account_id'])) {
            $query->where('finance_account_id', $filters['finance_account_id']);
        }

        if (!empty($filters['from_date'])) {
            $query->whereDate('entry_date', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->whereDate('entry_date', '<=', $filters['to_date']);
        }

        $this->applySearch($query, $filters['search'] ?? null);
    }

    protected function applyOrder(Builder $query, array $filters): void
    {
        // Opening rows always lead the statement, then chronological (oldest → newest).
        $query->orderByRaw("CASE
                WHEN particular IN ('Opening Balance', 'Opening Receivable', 'Opening Payable') THEN 0
                WHEN particular LIKE 'Opening Balance%' THEN 0
                ELSE 1
            END")
            ->orderBy('entry_date', 'asc')
            ->orderBy('id', 'asc');
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
                ->orWhere('client_name', 'like', "%{$search}%")
                ->orWhere('remarks', 'like', "%{$search}%");
        });
    }
}
