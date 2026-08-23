<?php

namespace App\Modules\Parties\Services;

use App\Modules\Parties\Models\Party;
use App\Modules\Parties\Repositories\PartyRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class PartyService
{
    public function __construct(
        protected PartyRepository $repository,
        protected Party $model,
    ) {}

    public function getPaginatedData(array $filters = []): LengthAwarePaginator
    {
        return $this->repository->getPaginatedData($filters);
    }

    public function getParty(Party $party): Party
    {
        return $party->load(['createdBy:id,name', 'updatedBy:id,name']);
    }

    public function create(array $data): Party
    {
        return $this->model->create($data);
    }

    public function update(Party $party, array $data): Party
    {
        $party->update($data);

        return $party->fresh(['createdBy:id,name', 'updatedBy:id,name']);
    }

    public function delete(Party $party): bool
    {
        return (bool) $party->delete();
    }

    public function bulkDelete(array $ids): int
    {
        return $this->model->whereIn('id', $ids)->delete();
    }

    public function getByIds(array $ids): Collection
    {
        return $this->model->whereIn('id', $ids)->get();
    }
}
