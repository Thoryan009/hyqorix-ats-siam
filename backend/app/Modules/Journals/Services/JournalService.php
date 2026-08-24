<?php

namespace App\Modules\Journals\Services;

use App\Modules\Journals\Models\Journal;
use App\Modules\Journals\Repositories\JournalRepository;
use App\Modules\Parties\Models\Party;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class JournalService
{
    public function __construct(
        protected JournalRepository $repository,
        protected Journal $model,
    ) {}

    public function getPaginatedData(array $filters = []): LengthAwarePaginator
    {
        return $this->repository->getPaginatedData($filters);
    }

    public function getJournal(Journal $journal): Journal
    {
        return $journal->load([
            'party:id,code,name,type',
            'createdBy:id,name',
            'updatedBy:id,name',
            'lines.account:id,code,name',
        ]);
    }

    public function create(array $data): Journal
    {
        return DB::transaction(function () use ($data) {
            $lines = $data['lines'] ?? [];
            unset($data['lines']);

            $totals = $this->sumLineTotals($lines);
            $partyId = $data['party_id'] ?? null;
            $partyCode = $this->resolvePartyCode($partyId);

            $journal = $this->model->create([
                'voucher_no' => $this->nextVoucherNo(),
                'voucher_date' => $data['voucher_date'],
                'transaction_type' => $data['transaction_type'],
                'reference_no' => $data['reference_no'] ?? null,
                'party_type' => $data['party_type'] ?? null,
                'party_id' => $partyId,
                'project_id' => $data['project_id'] ?? null,
                'narration' => $data['narration'] ?? null,
                'total_debit' => $totals['debit'],
                'total_credit' => $totals['credit'],
                'status' => $data['status'] ?? 'posted',
            ]);

            foreach ($lines as $index => $line) {
                $journal->lines()->create([
                    'account_id' => $line['account_id'],
                    'sub_ledger' => trim((string) ($line['sub_ledger'] ?? '')) ?: $partyCode,
                    'cost_type' => $line['cost_type'] ?? null,
                    'debit' => $line['debit'] ?? 0,
                    'credit' => $line['credit'] ?? 0,
                    'sort_order' => $index + 1,
                ]);
            }

            return $this->getJournal($journal);
        });
    }

    private function sumLineTotals(array $lines): array
    {
        $debit = 0.0;
        $credit = 0.0;

        foreach ($lines as $line) {
            $debit += (float) ($line['debit'] ?? 0);
            $credit += (float) ($line['credit'] ?? 0);
        }

        return [
            'debit' => round($debit, 2),
            'credit' => round($credit, 2),
        ];
    }

    private function resolvePartyCode(?int $partyId): ?string
    {
        if (!$partyId) {
            return null;
        }

        $code = Party::query()->whereKey($partyId)->value('code');

        return $code ? (string) $code : null;
    }

    private function nextVoucherNo(): string
    {
        $last = $this->model->newQuery()
            ->lockForUpdate()
            ->orderByDesc('id')
            ->value('voucher_no');

        $number = 1;

        if ($last && preg_match('/(\d+)\s*$/', (string) $last, $matches)) {
            $number = ((int) $matches[1]) + 1;
        }

        return 'JE'.str_pad((string) $number, 3, '0', STR_PAD_LEFT);
    }
}
