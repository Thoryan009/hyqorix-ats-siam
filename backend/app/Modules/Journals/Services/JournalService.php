<?php

namespace App\Modules\Journals\Services;

use App\Modules\Application\Helpers\ApplicationPresenter;
use App\Modules\Application\Models\Application;
use App\Modules\Journals\Models\Journal;
use App\Modules\Journals\Repositories\JournalRepository;
use App\Modules\JobList\Models\JobList;
use App\Modules\Parties\Models\Party;
use App\Modules\WorkOrder\Models\WorkOrder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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

    public function getJobOptions(): array
    {
        return JobList::query()
            ->select(['id', 'name'])
            ->whereHas('applications')
            ->withCount('applications')
            ->orderBy('name')
            ->get()
            ->map(fn (JobList $job) => [
                'id' => $job->id,
                'job_name' => $job->name,
                'application_count' => (int) $job->applications_count,
            ])
            ->values()
            ->all();
    }

    public function getSubLedgerApplicants(int $jobListId): array
    {
        if ($jobListId <= 0) {
            return [];
        }

        return Application::query()
            ->select(['id', 'given_name', 'sur_name', 'passport_no', 'application_id'])
            ->where('job_list_id', $jobListId)
            ->whereNotNull('passport_no')
            ->where('passport_no', '!=', '')
            ->orderBy('passport_no')
            ->limit(500)
            ->get()
            ->map(fn (Application $application) => [
                'id' => $application->id,
                'passport_no' => strtoupper(trim((string) $application->passport_no)),
                'full_name' => ApplicationPresenter::fullName(
                    $application->given_name ?? '',
                    $application->sur_name ?? '',
                ),
                'application_id' => $application->application_id,
            ])
            ->values()
            ->all();
    }

    public function getDemandLetterOptions(): array
    {
        return WorkOrder::query()
            ->select(['id', 'work_order_id'])
            ->whereHas('applications')
            ->withCount([
                'jobLists as job_count',
                'applications as application_count',
            ])
            ->orderBy('work_order_id')
            ->get()
            ->map(fn (WorkOrder $workOrder) => [
                'id' => $workOrder->id,
                'name' => $workOrder->work_order_id,
                'job_count' => (int) $workOrder->job_count,
                'application_count' => (int) $workOrder->application_count,
            ])
            ->values()
            ->all();
    }

    public function getJournal(Journal $journal): Journal
    {
        return $journal->load([
            'party:id,code,name,type',
            'transactionType:id,code,name',
            'createdBy:id,name',
            'updatedBy:id,name',
            'reversedBy:id,name',
            'reversalJournal:id,voucher_no',
            'originalJournal:id,voucher_no',
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
                'receipt_path' => $data['receipt_path'] ?? null,
                'total_debit' => $totals['debit'],
                'total_credit' => $totals['credit'],
                'status' => $data['status'] ?? 'posted',
            ]);

            $this->syncLines($journal, $lines, $partyCode);

            return $this->getJournal($journal);
        });
    }

    public function approve(Journal $journal, string $managerComment): Journal
    {
        if ($journal->status !== 'pending_approval') {
            throw ValidationException::withMessages([
                'status' => 'Only journals waiting for approval can be approved.',
            ]);
        }

        $journal->update([
            'status' => 'approved',
            'manager_comment' => $managerComment,
        ]);

        return $this->getJournal($journal->fresh());
    }

    public function return(Journal $journal, string $managerComment): Journal
    {
        if ($journal->status !== 'pending_approval') {
            throw ValidationException::withMessages([
                'status' => 'Only journals waiting for approval can be returned.',
            ]);
        }

        $journal->update([
            'status' => 'returned',
            'manager_comment' => $managerComment,
        ]);

        return $this->getJournal($journal->fresh());
    }

    public function resubmit(Journal $journal, array $data): Journal
    {
        if ($journal->status !== 'returned') {
            throw ValidationException::withMessages([
                'status' => 'Only returned journals can be updated and resubmitted.',
            ]);
        }

        return DB::transaction(function () use ($journal, $data) {
            $lines = $data['lines'] ?? [];
            unset($data['lines']);

            $totals = $this->sumLineTotals($lines);
            $partyId = $data['party_id'] ?? null;
            $partyCode = $this->resolvePartyCode($partyId);

            $update = [
                'voucher_date' => $data['voucher_date'],
                'transaction_type' => $data['transaction_type'],
                'reference_no' => $data['reference_no'] ?? null,
                'party_type' => $data['party_type'] ?? null,
                'party_id' => $partyId,
                'project_id' => $data['project_id'] ?? null,
                'narration' => $data['narration'] ?? null,
                'total_debit' => $totals['debit'],
                'total_credit' => $totals['credit'],
                'status' => 'pending_approval',
            ];

            if (array_key_exists('receipt_path', $data)) {
                $update['receipt_path'] = $data['receipt_path'];
            }

            $journal->update($update);

            $journal->lines()->delete();
            $this->syncLines($journal, $lines, $partyCode);

            return $this->getJournal($journal->fresh());
        });
    }

    public function pay(Journal $journal, array $data): Journal
    {
        if ($journal->status !== 'approved') {
            throw ValidationException::withMessages([
                'status' => 'Only approved journals can be paid and posted.',
            ]);
        }

        return DB::transaction(function () use ($journal, $data) {
            $lines = $data['lines'] ?? [];
            unset($data['lines']);

            $totals = $this->sumLineTotals($lines);
            $partyId = $data['party_id'] ?? null;
            $partyCode = $this->resolvePartyCode($partyId);

            $update = [
                'voucher_date' => $data['voucher_date'],
                'transaction_type' => $data['transaction_type'],
                'reference_no' => $data['reference_no'] ?? null,
                'party_type' => $data['party_type'] ?? null,
                'party_id' => $partyId,
                'project_id' => $data['project_id'] ?? null,
                'narration' => $data['narration'] ?? null,
                'total_debit' => $totals['debit'],
                'total_credit' => $totals['credit'],
                'status' => 'posted',
            ];

            if (array_key_exists('receipt_path', $data)) {
                $update['receipt_path'] = $data['receipt_path'];
            }

            $journal->update($update);

            $journal->lines()->delete();
            $this->syncLines($journal, $lines, $partyCode);

            return $this->getJournal($journal->fresh());
        });
    }

    public function reverse(Journal $journal): Journal
    {
        if ($journal->status !== 'posted') {
            throw ValidationException::withMessages([
                'status' => 'Only posted journals can be reversed.',
            ]);
        }

        if ($journal->reversed_at || $journal->reversal_journal_id) {
            throw ValidationException::withMessages([
                'status' => 'This journal has already been reversed.',
            ]);
        }

        if ($journal->reverses_journal_id) {
            throw ValidationException::withMessages([
                'status' => 'Reversal journals cannot be reversed again.',
            ]);
        }

        return DB::transaction(function () use ($journal) {
            $journal->load('lines');

            $reversalLines = $journal->lines->map(static fn ($line) => [
                'account_id' => $line->account_id,
                'sub_ledger' => $line->sub_ledger,
                'cost_type' => $line->cost_type,
                'debit' => $line->credit,
                'credit' => $line->debit,
            ])->all();

            $totals = $this->sumLineTotals($reversalLines);
            $partyCode = $this->resolvePartyCode($journal->party_id);

            $narration = 'Reversal of '.$journal->voucher_no;
            if ($journal->narration) {
                $narration .= ' — '.$journal->narration;
            }

            $reversal = $this->model->create([
                'voucher_no' => $this->nextVoucherNo(),
                'voucher_date' => now()->toDateString(),
                'transaction_type' => $journal->transaction_type,
                'reference_no' => $journal->reference_no,
                'party_type' => $journal->party_type,
                'party_id' => $journal->party_id,
                'project_id' => $journal->project_id,
                'narration' => $narration,
                'total_debit' => $totals['debit'],
                'total_credit' => $totals['credit'],
                'status' => 'posted',
                'reverses_journal_id' => $journal->id,
            ]);

            $this->syncLines($reversal, $reversalLines, $partyCode);

            $journal->update([
                'status' => 'reversed',
                'reversed_at' => now(),
                'reversed_by' => auth()->id(),
                'reversal_journal_id' => $reversal->id,
            ]);

            return $this->getJournal($journal->fresh());
        });
    }

    private function syncLines(Journal $journal, array $lines, ?string $partyCode): void
    {
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
