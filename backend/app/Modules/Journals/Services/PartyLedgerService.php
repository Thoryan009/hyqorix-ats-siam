<?php

namespace App\Modules\Journals\Services;

use App\Modules\Journals\Models\JournalLine;
use App\Modules\Shared\Helpers\DateTimeFormatter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;

class PartyLedgerService
{
    private const LEDGER_STATUSES = ['posted', 'approved', 'reversed'];

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
        $lines = $this->baseQuery($filters)
            ->orderByRaw("COALESCE(parties.code, parties.name, '')")
            ->orderBy('journals.voucher_date')
            ->orderBy('journals.id')
            ->orderBy('journal_lines.sort_order')
            ->orderBy('journal_lines.id')
            ->get();

        $mapped = $lines->map(function (JournalLine $line) {
            $journal = $line->journal;
            $party = $journal?->party;
            $account = $line->account;

            // Party ledger always groups by journal party — never by line sub_ledger.
            $partyRef = trim((string) ($party?->code ?? $party?->name ?? ''));
            if ($partyRef === '') {
                $partyRef = 'General';
            }

            $partyType = $this->resolvePartyTypeLabel(
                (string) ($journal?->party_type ?? ''),
                (string) ($party?->type ?? ''),
                $partyRef,
            );

            $debit = round((float) $line->debit, 2);
            $credit = round((float) $line->credit, 2);
            $partyKey = $party?->id
                ? 'party:'.$party->id
                : 'ref:'.strtolower($partyRef);

            return [
                'id' => $line->id,
                'journal_id' => $journal?->id,
                'party_ref' => $partyRef,
                'party_type' => $partyType,
                'party_ref_key' => $partyKey,
                'sub_ledger' => trim((string) ($line->sub_ledger ?? '')) ?: null,
                'date' => optional($journal?->voucher_date)?->format('Y-m-d'),
                'date_label' => DateTimeFormatter::formatDate($journal?->voucher_date),
                'je_no' => $journal?->voucher_no,
                'particulars' => $journal?->narration
                    ?: $journal?->transactionType?->name
                    ?: $journal?->transaction_type,
                'account_code' => $account?->code,
                'account_name' => $account?->name,
                'debit' => $debit > 0 ? $debit : null,
                'credit' => $credit > 0 ? $credit : null,
                'debit_raw' => $debit,
                'credit_raw' => $credit,
            ];
        });

        $filtered = $mapped
            ->when(!empty($filters['party_ref']), function (Collection $collection) use ($filters) {
                $needle = strtolower((string) $filters['party_ref']);

                return $collection->filter(
                    fn (array $row) => str_contains(strtolower($row['party_ref']), $needle)
                );
            })
            ->values();

        return $this->appendRunningBalances($filtered);
    }

    private function baseQuery(array $filters): Builder
    {
        $query = JournalLine::query()
            ->select('journal_lines.*')
            ->join('journals', 'journals.id', '=', 'journal_lines.journal_id')
            ->leftJoin('parties', 'parties.id', '=', 'journals.party_id')
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

        if (!empty($filters['party_type'])) {
            $partyType = (string) $filters['party_type'];
            $query->where(function (Builder $builder) use ($partyType) {
                $builder->where('journals.party_type', $partyType)
                    ->orWhereHas('journal.party', function (Builder $partyQuery) use ($partyType) {
                        $partyQuery->where('type', $partyType);
                    });
            });
        }

        if (!empty($filters['party_id'])) {
            $query->where('journals.party_id', (int) $filters['party_id']);
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

    private function resolvePartyTypeLabel(string $journalPartyType, string $partyType, string $partyRef): string
    {
        $candidate = trim($journalPartyType) ?: trim($partyType);
        if ($candidate !== '') {
            return $this->formatPartyTypeLabel($candidate);
        }

        if (stripos($partyRef, 'bank') !== false || stripos($partyRef, 'loan') !== false) {
            return 'Bank/Lender';
        }

        return 'Other';
    }

    private function formatPartyTypeLabel(string $value): string
    {
        $normalized = strtolower(str_replace(['_', '-'], ' ', $value));

        return match ($normalized) {
            'client' => 'Client',
            'principal' => 'Principal',
            'agent' => 'Agent',
            'candidate' => 'Candidate',
            'vendor' => 'Vendor',
            'staff' => 'Staff',
            'owner' => 'Owner',
            'bank', 'bank lender', 'lender' => 'Bank/Lender',
            default => ucwords($normalized),
        };
    }

    private function appendRunningBalances(Collection $rows): Collection
    {
        $balances = [];

        return $rows->map(function (array $row) use (&$balances) {
            $key = $row['party_ref_key'];
            $net = ($balances[$key] ?? 0) + ($row['debit_raw'] - $row['credit_raw']);
            $balances[$key] = $net;

            $row['running_balance'] = round(abs($net), 2);
            $row['balance_type'] = $net >= 0 ? 'Dr' : 'Cr';

            unset($row['party_ref_key'], $row['debit_raw'], $row['credit_raw']);

            return $row;
        });
    }
}
