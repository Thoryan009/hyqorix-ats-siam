<?php

namespace App\Modules\Parties\Services;

use App\Modules\Application\Models\Application;
use App\Modules\Parties\Models\Party;
use App\Modules\Parties\Repositories\PartyRepository;
use App\Modules\Parties\Resources\PartyCandidateSourceResource;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use InvalidArgumentException;

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

    public function getSourceOptions(string $type): array
    {
        return match ($type) {
            'Candidate' => PartyCandidateSourceResource::collection($this->getCandidateSourceOptions())->resolve(),
            default => throw new InvalidArgumentException("Party source options are not available for type [{$type}]."),
        };
    }

    private function getCandidateSourceOptions(): Collection
    {
        return Application::query()
            ->select(['id', 'given_name', 'sur_name', 'application_id', 'passport_no'])
            ->whereNotNull('passport_no')
            ->where('passport_no', '!=', '')
            ->orderByDesc('id')
            ->limit(500)
            ->get();
    }
}
