<?php

namespace App\Modules\Finance\Services;

use App\Modules\Finance\Models\ExpenseCategory;
use App\Modules\Finance\Models\ExpenseHead;
use App\Modules\Finance\Models\FinanceAccountLedgerEntry;
use App\Modules\Finance\Models\FinanceBillEntry;
use App\Modules\Finance\Models\FinanceIncomeCollection;
use App\Modules\Finance\Models\IncomeHead;
use Illuminate\Support\Facades\DB;

class FinanceIncomeStatementService
{
    public function __construct(
        private readonly FinanceGrossProfitReportService $grossProfitReportService
    ) {}

    public function getStatement(array $filters = []): array
    {
        $grossProfitReport = $this->grossProfitReportService->getReport([
            'from_date' => $filters['from_date'] ?? null,
            'to_date' => $filters['to_date'] ?? null,
        ]);

        $grossProfit = round((float) ($grossProfitReport['summary']['adjusted_gross_profit_loss'] ?? 0), 2);

        $incomeHeads = IncomeHead::query()
            ->with('incomeCategory')
            ->where('income_heads.status', 'active')
            ->whereHas('incomeCategory', fn ($q) => $q->where('status', 'active'))
            ->join('income_categories', 'income_heads.income_category_id', '=', 'income_categories.id')
            ->orderBy('income_categories.sort_order')
            ->orderBy('income_heads.sort_order')
            ->orderBy('income_heads.name')
            ->select('income_heads.*')
            ->get();

        // Accrual: recognize income when earned (cash/bank/expense_link or due).
        // Later receipts that settle a due bill must not be counted again.
        $collectionQuery = FinanceIncomeCollection::query()
            ->whereIn('status', ['collected', 'due'])
            ->whereNull('settles_income_collection_id');

        if (!empty($filters['from_date'])) {
            $collectionQuery->whereDate('collection_date', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $collectionQuery->whereDate('collection_date', '<=', $filters['to_date']);
        }

        $amountsByIncomeHead = $collectionQuery
            ->select([
                'income_head_id',
                DB::raw('SUM(amount) as total_amount'),
            ])
            ->groupBy('income_head_id')
            ->pluck('total_amount', 'income_head_id');

        // Bills payable settled via linked Operating Income head
        // (e.g. Liability Written Back) post ledger CRs, not income collections.
        $linkIncomeQuery = FinanceAccountLedgerEntry::query()
            ->join(
                'finance_accounts',
                'finance_account_ledger_entries.finance_account_id',
                '=',
                'finance_accounts.id'
            )
            ->whereNotNull('finance_accounts.income_head_id')
            ->whereRaw('LOWER(COALESCE(finance_account_ledger_entries.payment_method, "")) = ?', [
                'income link',
            ]);

        if (!empty($filters['from_date'])) {
            $linkIncomeQuery->whereDate('finance_account_ledger_entries.entry_date', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $linkIncomeQuery->whereDate('finance_account_ledger_entries.entry_date', '<=', $filters['to_date']);
        }

        $linkAmountsByIncomeHead = $linkIncomeQuery
            ->select([
                'finance_accounts.income_head_id',
                DB::raw('SUM(finance_account_ledger_entries.cr_amount - finance_account_ledger_entries.dr_amount) as total_amount'),
            ])
            ->groupBy('finance_accounts.income_head_id')
            ->pluck('total_amount', 'income_head_id');

        foreach ($linkAmountsByIncomeHead as $headId => $linkAmount) {
            $amountsByIncomeHead[$headId] = round(
                (float) ($amountsByIncomeHead[$headId] ?? 0) + (float) $linkAmount,
                2
            );
        }

        $operatingCategory = ExpenseCategory::query()
            ->where('code', 'operating_cost')
            ->first();

        $expenseHeads = $operatingCategory
            ? ExpenseHead::query()
                ->where('expense_category_id', $operatingCategory->id)
                ->orderBy('id')
                ->get(['id', 'name'])
            : collect();

        $amountsByExpenseHead = collect();
        if ($operatingCategory) {
            $billQuery = FinanceBillEntry::query()
                ->where('expense_category_id', $operatingCategory->id)
                ->where('status', 'approved');

            if (!empty($filters['from_date'])) {
                $billQuery->whereDate('payment_date', '>=', $filters['from_date']);
            }

            if (!empty($filters['to_date'])) {
                $billQuery->whereDate('payment_date', '<=', $filters['to_date']);
            }

            $amountsByExpenseHead = $billQuery
                ->select([
                    'expense_head_id',
                    DB::raw('SUM(amount) as total_amount'),
                ])
                ->groupBy('expense_head_id')
                ->pluck('total_amount', 'expense_head_id');

            // Bills receivable settled via linked Operating Expense head
            // (e.g. Bad Debt Expense) post ledger DRs, not bill entries.
            $linkQuery = FinanceAccountLedgerEntry::query()
                ->join(
                    'finance_accounts',
                    'finance_account_ledger_entries.finance_account_id',
                    '=',
                    'finance_accounts.id'
                )
                ->where('finance_accounts.expense_category_id', $operatingCategory->id)
                ->whereNotNull('finance_accounts.expense_head_id')
                ->whereRaw('LOWER(COALESCE(finance_account_ledger_entries.payment_method, "")) = ?', [
                    'expense link',
                ]);

            if (!empty($filters['from_date'])) {
                $linkQuery->whereDate('finance_account_ledger_entries.entry_date', '>=', $filters['from_date']);
            }

            if (!empty($filters['to_date'])) {
                $linkQuery->whereDate('finance_account_ledger_entries.entry_date', '<=', $filters['to_date']);
            }

            $linkAmountsByExpenseHead = $linkQuery
                ->select([
                    'finance_accounts.expense_head_id',
                    DB::raw('SUM(finance_account_ledger_entries.dr_amount - finance_account_ledger_entries.cr_amount) as total_amount'),
                ])
                ->groupBy('finance_accounts.expense_head_id')
                ->pluck('total_amount', 'expense_head_id');

            foreach ($linkAmountsByExpenseHead as $headId => $linkAmount) {
                $amountsByExpenseHead[$headId] = round(
                    (float) ($amountsByExpenseHead[$headId] ?? 0) + (float) $linkAmount,
                    2
                );
            }
        }

        $debitLines = [];
        $totalExpenses = 0.0;

        foreach ($expenseHeads as $head) {
            $amount = round((float) ($amountsByExpenseHead[$head->id] ?? 0), 2);
            $totalExpenses += $amount;
            $debitLines[] = [
                'label' => $head->name,
                'amount' => $amount,
                'is_balancing' => false,
            ];
        }

        $totalExpenses = round($totalExpenses, 2);

        $creditLines = [
            [
                'label' => 'Gross Profit b/d',
                'amount' => $grossProfit,
                'is_balancing' => false,
            ],
        ];

        $totalIncomeCollections = 0.0;

        foreach ($incomeHeads as $head) {
            $amount = round((float) ($amountsByIncomeHead[$head->id] ?? 0), 2);
            $totalIncomeCollections += $amount;
            $creditLines[] = [
                'label' => $head->name,
                'amount' => $amount,
                'is_balancing' => false,
            ];
        }

        $totalIncomeCollections = round($totalIncomeCollections, 2);
        $totalIncome = round($grossProfit + $totalIncomeCollections, 2);
        $netProfit = round($totalIncome - $totalExpenses, 2);

        // Two-sided account balance: Net Profit c/d on debit when profit,
        // Net Loss c/d on credit when loss.
        if ($netProfit >= 0) {
            $debitLines[] = [
                'label' => 'Net Profit c/d',
                'amount' => $netProfit,
                'is_balancing' => true,
            ];
        } else {
            $creditLines[] = [
                'label' => 'Net Loss c/d',
                'amount' => abs($netProfit),
                'is_balancing' => true,
            ];
        }

        $debitTotal = round(array_sum(array_column($debitLines, 'amount')), 2);
        $creditTotal = round(array_sum(array_column($creditLines, 'amount')), 2);

        return [
            'debit_lines' => $debitLines,
            'credit_lines' => $creditLines,
            'debit_total' => $debitTotal,
            'credit_total' => $creditTotal,
            'summary' => [
                'gross_profit' => $grossProfit,
                'total_income_collections' => $totalIncomeCollections,
                'total_income' => $totalIncome,
                'total_operating_expense' => $totalExpenses,
                'net_profit' => $netProfit,
                'is_profit' => $netProfit >= 0,
            ],
        ];
    }
}
