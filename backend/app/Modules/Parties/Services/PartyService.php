<?php

namespace App\Modules\Parties\Services;

use App\Modules\Agent\Models\Agent;
use App\Modules\Application\Models\Application;
use App\Modules\Client\Models\Client;
use App\Modules\Employee\Models\Employee;
use App\Modules\Parties\Models\Party;
use App\Modules\Parties\Repositories\PartyRepository;
use App\Modules\Parties\Resources\PartyCandidateSourceResource;
use App\Modules\Parties\Resources\PartySourceOptionResource;
use App\Modules\Principal\Models\Principal;
use App\Modules\Vendor\Models\Vendor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
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

    public function getSourceOptions(string $type, array $filters = []): array
    {
        return match ($type) {
            'Client' => PartySourceOptionResource::collection($this->getClientSourceOptions())->resolve(),
            'Principal' => PartySourceOptionResource::collection($this->getPrincipalSourceOptions())->resolve(),
            'Agent' => PartySourceOptionResource::collection($this->getAgentSourceOptions())->resolve(),
            'Candidate' => PartyCandidateSourceResource::collection(
                $this->getCandidateSourceOptions($filters)
            )->resolve(),
            'Vendor' => PartySourceOptionResource::collection($this->getVendorSourceOptions())->resolve(),
            'Staff' => PartySourceOptionResource::collection($this->getStaffSourceOptions())->resolve(),
            default => throw new InvalidArgumentException("Party source options are not available for type [{$type}]."),
        };
    }

    private function getClientSourceOptions(): Collection
    {
        return Client::query()
            ->select(['id', 'client_id', 'user_id'])
            ->with(['user:id,name,status'])
            ->tap(fn (Builder $query) => $this->applyActiveUserFilter($query))
            ->orderBy('id')
            ->limit(500)
            ->get()
            ->map(fn (Client $client) => [
                'id' => $client->id,
                'code' => $client->client_id,
                'name' => $client->user?->name,
            ]);
    }

    private function getPrincipalSourceOptions(): Collection
    {
        return Principal::query()
            ->select(['id', 'principal_id', 'user_id'])
            ->with(['user:id,name,status'])
            ->tap(fn (Builder $query) => $this->applyActiveUserFilter($query))
            ->orderBy('id')
            ->limit(500)
            ->get()
            ->map(fn (Principal $principal) => [
                'id' => $principal->id,
                'code' => $principal->principal_id,
                'name' => $principal->user?->name,
            ]);
    }

    private function getAgentSourceOptions(): Collection
    {
        return Agent::query()
            ->select(['id', 'agent_id', 'user_id'])
            ->with(['user:id,name,status'])
            ->tap(fn (Builder $query) => $this->applyActiveUserFilter($query))
            ->orderBy('id')
            ->limit(500)
            ->get()
            ->map(fn (Agent $agent) => [
                'id' => $agent->id,
                'code' => $agent->agent_id,
                'name' => $agent->user?->name,
            ]);
    }

    private function getVendorSourceOptions(): Collection
    {
        return Vendor::query()
            ->select(['id', 'vendor_id', 'organization_name', 'user_id'])
            ->with(['user:id,name,status'])
            ->tap(fn (Builder $query) => $this->applyActiveUserFilter($query))
            ->orderBy('id')
            ->limit(500)
            ->get()
            ->map(fn (Vendor $vendor) => [
                'id' => $vendor->id,
                'code' => $vendor->vendor_id,
                'name' => $vendor->organization_name,
            ]);
    }

    private function getStaffSourceOptions(): Collection
    {
        return Employee::query()
            ->select(['id', 'user_id'])
            ->with(['user:id,name,status'])
            ->tap(fn (Builder $query) => $this->applyActiveUserFilter($query))
            ->orderBy('id')
            ->limit(500)
            ->get()
            ->values()
            ->map(fn (Employee $employee, int $index) => [
                'id' => $employee->id,
                'code' => 'ST'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                'name' => $employee->user?->name,
            ]);
    }

    private function getCandidateSourceOptions(array $filters = []): Collection
    {
        $jobListId = isset($filters['job_list_id']) ? (int) $filters['job_list_id'] : 0;

        if ($jobListId <= 0) {
            return collect();
        }

        return Application::query()
            ->select(['id', 'given_name', 'sur_name', 'application_id', 'passport_no', 'job_list_id'])
            ->where('job_list_id', $jobListId)
            ->whereNotNull('passport_no')
            ->where('passport_no', '!=', '')
            ->orderByDesc('id')
            ->limit(500)
            ->get();
    }

    private function applyActiveUserFilter(Builder $query): void
    {
        $query->whereHas('user', function (Builder $userQuery) {
            $userQuery->where(function (Builder $statusQuery) {
                $statusQuery
                    ->where('status', 1)
                    ->orWhere('status', '1')
                    ->orWhere('status', 'active');
            });
        });
    }
}
