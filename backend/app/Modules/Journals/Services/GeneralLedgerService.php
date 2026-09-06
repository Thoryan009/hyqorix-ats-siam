<?php

namespace App\Modules\Journals\Services;

use App\Modules\Journals\Models\JournalLine;
use App\Modules\Shared\Helpers\DateTimeFormatter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;

class GeneralLedgerService
{
    private const LEDGER_STATUSES = ['posted', 'approved', 'reversed'];

    public function getExportLedger(array $filters = []): Collection
    {
        return $this->buildLedgerRows($filters)->values();
    }

    public function getPaginatedLedger(array $filters = []): LengthAwarePaginator
    {
        $rows = $this->buildLedgerRows($filters);
        $page = max(1, (int) ($filters['page'] ?? 1));
        $perPage = max(1, min(200, (int) ($filters['per_page'] ?? 50)));
        $total = $rows->count();
        $items = $rows->slice(($page - 1) * $perPage, $perPage)->values();

        return new Paginator(
            $items,
            $total,
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );
    }

    private function buildLedgerRows(array $filters): Collection
    {
        $lines = $this->baseQuery($filters)->get();

        $mapped = $lines->map(function (JournalLine $line) {
            $journal = $line->journal;
            $party = $journal?->party;
            $account = $line->account;

            // General ledger party/ref always comes from the journal main party — never sub_ledger.
            $partyCode = trim((string) ($party?->code ?? ''));
            $partyName = trim((string) ($party?->name ?? ''));
            $partyRef = $partyCode !== '' ? $partyCode : ($partyName !== '' ? $partyName : '');

            $accountCode = trim((string) ($account?->code ?? ''));
            $accountName = trim((string) ($account?->name ?? ''));
            $debit = round((float) $line->debit, 2);
            $credit = round((float) $line->credit, 2);

            return [
                'id' => $line->id,
                'journal_id' => $journal?->id,
                'account_id' => $account?->id,
                'account_code' => $accountCode !== '' ? $accountCode : '—',
                'account_name' => $accountName !== '' ? $accountName : '—',
                'account_key' => strtolower($accountCode !== '' ? $accountCode : 'account-'.($account?->id ?? $line->id)),
                'party_id' => $party?->id,
                'party_code' => $partyCode !== '' ? $partyCode : null,
                'party_name' => $partyName !== '' ? $partyName : null,
                'party_ref' => $partyRef !== '' ? $partyRef : '—',
                'date' => optional($journal?->voucher_date)?->format('Y-m-d'),
                'date_label' => DateTimeFormatter::formatDate($journal?->voucher_date),
                'je_no' => $journal?->voucher_no,
                'particulars' => $journal?->narration
                    ?: $journal?->transactionType?->name
                    ?: $journal?->transaction_type,
                'debit' => $debit > 0 ? $debit : null,
                'credit' => $credit > 0 ? $credit : null,
                'debit_raw' => $debit,
                'credit_raw' => $credit,
                'sort_code' => $accountCode !== '' ? $accountCode : '999999',
                'sort_date' => optional($journal?->voucher_date)?->format('Y-m-d') ?? '',
                'sort_journal_id' => (int) ($journal?->id ?? 0),
                'sort_line_order' => (int) ($line->sort_order ?? 0),
                'sort_line_id' => (int) $line->id,
            ];
        });

        $sorted = $mapped
            ->sortBy([
                ['sort_code', 'asc'],
                ['sort_date', 'asc'],
                ['sort_journal_id', 'asc'],
                ['sort_line_order', 'asc'],
                ['sort_line_id', 'asc'],
            ])
            ->values();

        $filtered = $sorted
            ->when(!empty($filters['party_ref']), function (Collection $collection) use ($filters) {
                $needle = strtolower((string) $filters['party_ref']);

                return $collection->filter(function (array $row) use ($needle) {
                    return str_contains(strtolower((string) $row['party_ref']), $needle)
                        || str_contains(strtolower((string) ($row['party_name'] ?? '')), $needle)
                        || str_contains(strtolower((string) ($row['party_code'] ?? '')), $needle);
                });
            })
            ->values();

        return $this->appendRunningBalances($filtered);
    }

    private function baseQuery(array $filters): Builder
    {
        $query = JournalLine::query()
            ->select('journal_lines.*')
            ->join('journals', 'journals.id', '=', 'journal_lines.journal_id')
            ->whereIn('journals.status', self::LEDGER_STATUSES)
            ->with([
                'account:id,code,name',
                'journal' => function ($journalQuery) {
                    $journalQuery->select([
                        'id',
                        'voucher_no',
                        'voucher_date',
                        'transaction_type',
                        'narration',
                        'party_type',
                        'party_id',
                    ])->with([
                        'party:id,code,name,type',
                        'transactionType:id,code,name',
                    ]);
                },
            ]);

        if (!empty($filters['account_id'])) {
            $query->where('journal_lines.account_id', (int) $filters['account_id']);
        }

        if (!empty($filters['from_date'])) {
            $query->whereDate('journals.voucher_date', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->whereDate('journals.voucher_date', '<=', $filters['to_date']);
        }

        if (!empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function (Builder $builder) use ($search) {
                $builder->where('journals.voucher_no', 'like', "%{$search}%")
                    ->orWhere('journals.narration', 'like', "%{$search}%")
                    ->orWhere('journal_lines.sub_ledger', 'like', "%{$search}%")
                    ->orWhereHas('journal.party', function (Builder $partyQuery) use ($search) {
                        $partyQuery->where('code', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('account', function (Builder $accountQuery) use ($search) {
                        $accountQuery->where('code', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                    });
            });
        }

        return $query;
    }

    private function appendRunningBalances(Collection $rows): Collection
    {
        $balances = [];

        return $rows->map(function (array $row) use (&$balances) {
            $key = $row['account_key'];
            $net = ($balances[$key] ?? 0) + ($row['debit_raw'] - $row['credit_raw']);
            $balances[$key] = $net;

            $row['running_balance'] = round(abs($net), 2);
            $row['balance_type'] = $net >= 0 ? 'Dr' : 'Cr';

            unset(
                $row['account_key'],
                $row['debit_raw'],
                $row['credit_raw'],
                $row['sort_code'],
                $row['sort_date'],
                $row['sort_journal_id'],
                $row['sort_line_order'],
                $row['sort_line_id'],
            );

            return $row;
        });
    }
}
