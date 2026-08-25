<?php

namespace App\Modules\Parties\Services;

use App\Modules\Journals\Models\Journal;
use App\Modules\Parties\Models\Party;
use App\Modules\Parties\Models\PartyType;
use App\Modules\Parties\Repositories\PartyTypeRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PartyTypeService
{
    public function __construct(
        protected PartyTypeRepository $repository,
        protected PartyType $model,
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

        return $query->get(['id', 'code', 'name', 'status', 'sort_order']);
    }

    public function getPartyType(PartyType $partyType): PartyType
    {
        return $partyType->load(['createdBy:id,name', 'updatedBy:id,name']);
    }

    public function create(array $data): PartyType
    {
        return $this->model->create($data);
    }

    public function update(PartyType $partyType, array $data): PartyType
    {
        return DB::transaction(function () use ($partyType, $data) {
            $oldCode = $partyType->code;
            $newCode = $data['code'] ?? $oldCode;

            $partyType->update($data);

            if ($newCode !== $oldCode) {
                Party::query()->where('type', $oldCode)->update(['type' => $newCode]);

                if (class_exists(Journal::class)) {
                    Journal::query()->where('party_type', $oldCode)->update(['party_type' => $newCode]);
                }
            }

            return $partyType->fresh(['createdBy:id,name', 'updatedBy:id,name']);
        });
    }

    public function delete(PartyType $partyType): bool
    {
        $this->assertNotInUse([$partyType->code]);

        return (bool) $partyType->delete();
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

    public function getByIds(array $ids): Collection
    {
        return $this->model->whereIn('id', $ids)->get();
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

        $inUse = Party::query()
            ->whereIn('type', $codes)
            ->select('type')
            ->distinct()
            ->pluck('type')
            ->all();

        if ($inUse === []) {
            return;
        }

        throw ValidationException::withMessages([
            'ids' => ['Cannot delete party type(s) in use: '.implode(', ', $inUse).'.'],
        ]);
    }
}
