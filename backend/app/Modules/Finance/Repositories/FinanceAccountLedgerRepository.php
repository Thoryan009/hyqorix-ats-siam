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
        // Opening rows lead, then chronological by date + transaction.
        // Within a voucher/transaction, DR (bill) must appear before CR (receive)
        // even if the credit row was inserted later.
        $query->orderByRaw("CASE
                WHEN particular IN ('Opening Balance', 'Opening Receivable', 'Opening Payable') THEN 0
                WHEN particular LIKE 'Opening Balance%' THEN 0
                ELSE 1
            END")
            ->orderBy('entry_date', 'asc')
            ->orderByRaw('COALESCE(finance_account_type_transaction_id, 0) ASC')
            ->orderByRaw("CASE
                WHEN COALESCE(dr_amount, 0) > 0 THEN 0
                WHEN COALESCE(cr_amount, 0) > 0 THEN 1
                ELSE 2
            END")
            ->orderByRaw("CASE
                WHEN voucher_no IS NULL OR voucher_no = '' THEN 1
                ELSE 0
            END")
            ->orderBy('voucher_no', 'asc')
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
