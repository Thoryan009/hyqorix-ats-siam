<?php

namespace App\Modules\Journals\Services;

use App\Modules\Journals\Models\Journal;
use App\Modules\Journals\Models\JournalTransactionType;
use App\Modules\Journals\Repositories\JournalTransactionTypeRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class JournalTransactionTypeService
{
    public function __construct(
        protected JournalTransactionTypeRepository $repository,
        protected JournalTransactionType $model,
    ) {}

    public function getPaginatedData(array $filters = []): LengthAwarePaginator
    {
        return $this->repository->getPaginatedData($filters);
    }

    public function getOptions(array $filters = []): Collection
    {
        $query = $this->model->newQuery()
            ->orderBy('sort_order')
            ->orderBy('name');

        if (!empty($filters['status'])) {
            $status = strtolower((string) $filters['status']) === 'inactive' ? 'inactive' : 'active';
            $query->where('status', $status);
        }

        return $query->get(['id', 'code', 'name', 'status', 'sort_order', 'subledger_required', 'demand_letter_required']);
    }

    public function getTransactionType(JournalTransactionType $transactionType): JournalTransactionType
    {
        return $transactionType->load(['createdBy:id,name', 'updatedBy:id,name']);
    }

    public function create(array $data): JournalTransactionType
    {
        return $this->model->create($data);
    }

    public function update(JournalTransactionType $transactionType, array $data): JournalTransactionType
    {
        return DB::transaction(function () use ($transactionType, $data) {
            $oldCode = $transactionType->code;
            $newCode = $data['code'] ?? $oldCode;

            $transactionType->update($data);

            if ($newCode !== $oldCode) {
                Journal::query()
                    ->where('transaction_type', $oldCode)
                    ->update(['transaction_type' => $newCode]);
            }

            return $transactionType->fresh(['createdBy:id,name', 'updatedBy:id,name']);
        });
    }

    public function delete(JournalTransactionType $transactionType): bool
    {
        $this->assertNotInUse([$transactionType->code]);

        return (bool) $transactionType->delete();
    }

    public function bulkDelete(array $ids): int
    {
        $types = $this->model->whereIn('id', $ids)->get(['id', 'code']);

        if ($types->isEmpty()) {
            return 0;
        }

        $this->assertNotInUse($types->pluck('code')->all());

        return $this->model->whereIn('id', $ids)->delete();
    }

    /**
     * @param  array<int, string>  $codes
     */
    private function assertNotInUse(array $codes): void
    {
        $codes = array_values(array_filter(array_map('strval', $codes)));

        if ($codes === []) {
            return;
        }

        $inUse = Journal::query()
            ->whereIn('transaction_type', $codes)
            ->select('transaction_type')
            ->distinct()
            ->pluck('transaction_type')
            ->all();

        if ($inUse === []) {
            return;
        }

        throw ValidationException::withMessages([
            'ids' => ['Cannot delete transaction type(s) in use: '.implode(', ', $inUse).'.'],
        ]);
    }
}
