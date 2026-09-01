<?php

namespace App\Modules\Parties\Services;

use App\Modules\Agent\Models\Agent;
use App\Modules\Application\Models\Application;
use App\Modules\Client\Models\Client;
use App\Modules\Employee\Models\Employee;
use App\Modules\Parties\Models\Party;
use App\Modules\Parties\Models\PartyType;
use App\Modules\Parties\Repositories\PartyRepository;
use App\Modules\Parties\Resources\PartyCandidateSourceResource;
use App\Modules\Parties\Resources\PartySourceOptionResource;
use App\Modules\Principal\Models\Principal;
use App\Modules\Vendor\Models\Vendor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
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

    public function bulkCreate(array $parties): Collection
    {
        return DB::transaction(function () use ($parties) {
            $created = collect();

            foreach ($parties as $partyData) {
                $created->push($this->model->create($partyData));
            }

            return $created;
        });
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
        $partyType = PartyType::query()->where('code', $type)->first();

        if (!$partyType || !$partyType->source_module) {
            throw new InvalidArgumentException("Party source options are not available for type [{$type}].");
        }

        return match ($partyType->source_module) {
            'client' => PartySourceOptionResource::collection($this->getClientSourceOptions($type))->resolve(),
            'principal' => PartySourceOptionResource::collection($this->getPrincipalSourceOptions($type))->resolve(),
            'agent' => PartySourceOptionResource::collection($this->getAgentSourceOptions($type))->resolve(),
            'application' => PartyCandidateSourceResource::collection(
                $this->getCandidateSourceOptions($type, $filters)
            )->resolve(),
            'vendor' => PartySourceOptionResource::collection($this->getVendorSourceOptions($type))->resolve(),
            'employee' => PartySourceOptionResource::collection($this->getStaffSourceOptions($type))->resolve(),
            default => throw new InvalidArgumentException("Party source options are not available for type [{$type}]."),
        };
    }

    private function getClientSourceOptions(string $type): Collection
    {
        $existingCodes = $this->getExistingPartyCodeSet($type);

        return Client::query()
            ->select(['id', 'client_id', 'user_id'])
            ->with(['user:id,name,status'])
            ->tap(fn (Builder $query) => $this->applyActiveUserFilter($query))
            ->orderBy('id')
            ->limit(500)
            ->get()
            ->map(fn (Client $client) => [
                'id' => $client->id,
                'code' => $this->compactPartyCode($client->client_id),
                'name' => $client->user?->name,
            ])
            ->reject(fn (array $item) => $this->isExistingPartyCode($item['code'] ?? '', $existingCodes))
            ->values();
    }

    private function getPrincipalSourceOptions(string $type): Collection
    {
        $existingCodes = $this->getExistingPartyCodeSet($type, 'PR');

        return Principal::query()
            ->select(['id', 'principal_id', 'user_id'])
            ->with(['user:id,name,status'])
            ->tap(fn (Builder $query) => $this->applyActiveUserFilter($query))
            ->orderBy('id')
            ->limit(500)
            ->get()
            ->values()
            ->map(fn (Principal $principal, int $index) => [
                'id' => $principal->id,
                'code' => $this->normalizeShortCode('PR', $principal->principal_id, $index + 1),
                'name' => $principal->user?->name,
            ])
            ->reject(fn (array $item) => $this->isExistingPartyCode($item['code'] ?? '', $existingCodes))
            ->values();
    }

    private function getAgentSourceOptions(string $type): Collection
    {
        $existingCodes = $this->getExistingPartyCodeSet($type, 'AG');

        return Agent::query()
            ->select(['id', 'agent_id', 'user_id'])
            ->with(['user:id,name,status'])
            ->tap(fn (Builder $query) => $this->applyActiveUserFilter($query))
            ->orderBy('id')
            ->limit(500)
            ->get()
            ->values()
            ->map(fn (Agent $agent, int $index) => [
                'id' => $agent->id,
                'code' => $this->normalizeShortCode('AG', $agent->agent_id, $index + 1),
                'name' => $agent->user?->name,
            ])
            ->reject(fn (array $item) => $this->isExistingPartyCode($item['code'] ?? '', $existingCodes))
            ->values();
    }

    private function getVendorSourceOptions(string $type): Collection
    {
        $existingCodes = $this->getExistingPartyCodeSet($type);

        return Vendor::query()
            ->select(['id', 'vendor_id', 'organization_name', 'user_id'])
            ->with(['user:id,name,status'])
            ->tap(fn (Builder $query) => $this->applyActiveUserFilter($query))
            ->orderBy('id')
            ->limit(500)
            ->get()
            ->map(fn (Vendor $vendor) => [
                'id' => $vendor->id,
                'code' => $this->compactPartyCode($vendor->vendor_id),
                'name' => $vendor->organization_name,
            ])
            ->reject(fn (array $item) => $this->isExistingPartyCode($item['code'] ?? '', $existingCodes))
            ->values();
    }

    private function getStaffSourceOptions(string $type): Collection
    {
        $existingCodes = $this->getExistingPartyCodeSet($type, 'ST');
        $existingNames = $this->getExistingPartyNameSet($type);

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
                'code' => $this->normalizeShortCode('ST', null, $index + 1),
                'name' => $employee->user?->name,
            ])
            ->reject(function (array $item) use ($existingCodes, $existingNames) {
                if ($this->isExistingPartyCode($item['code'] ?? '', $existingCodes)) {
                    return true;
                }

                $name = strtoupper(trim((string) ($item['name'] ?? '')));

                return $name !== '' && isset($existingNames[$name]);
            })
            ->values();
    }

    private function getCandidateSourceOptions(string $type, array $filters = []): Collection
    {
        $jobListId = isset($filters['job_list_id']) ? (int) $filters['job_list_id'] : 0;

        if ($jobListId <= 0) {
            return collect();
        }

        $existingCodes = $this->getExistingPartyCodeSet($type);

        return Application::query()
            ->select(['id', 'given_name', 'sur_name', 'application_id', 'passport_no', 'job_list_id'])
            ->where('job_list_id', $jobListId)
            ->whereNotNull('passport_no')
            ->where('passport_no', '!=', '')
            ->orderByDesc('id')
            ->limit(500)
            ->get()
            ->reject(fn (Application $application) => $this->isExistingPartyCode(
                $application->passport_no,
                $existingCodes
            ))
            ->values();
    }

    /**
     * @return array<string, true>
     */
    private function getExistingPartyCodeSet(string $type, ?string $shortPrefix = null): array
    {
        $set = [];

        $codes = $this->model->newQuery()
            ->where('type', $type)
            ->pluck('code');

        foreach ($codes as $code) {
            $normalized = strtoupper(trim((string) $code));
            if ($normalized === '') {
                continue;
            }

            $set[$normalized] = true;
            $set[$this->compactPartyCode($normalized)] = true;

            if ($shortPrefix) {
                $set[$this->normalizeShortCode($shortPrefix, $normalized)] = true;
            }
        }

        return $set;
    }

    /**
     * @return array<string, true>
     */
    private function getExistingPartyNameSet(string $type): array
    {
        $set = [];

        $names = $this->model->newQuery()
            ->where('type', $type)
            ->pluck('name');

        foreach ($names as $name) {
            $normalized = strtoupper(trim((string) $name));
            if ($normalized !== '') {
                $set[$normalized] = true;
            }
        }

        return $set;
    }

    /**
     * @param  array<string, true>  $existingCodes
     */
    private function isExistingPartyCode(?string $code, array $existingCodes): bool
    {
        $normalized = strtoupper(trim((string) $code));
        $compact = $this->compactPartyCode($normalized);

        return $normalized !== '' && (
            isset($existingCodes[$normalized]) || isset($existingCodes[$compact])
        );
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

    /**
     * Normalize source codes to a short prefix format (e.g. AG001, PR001, ST001).
     */
    private function normalizeShortCode(string $prefix, ?string $rawCode, ?int $fallbackNumber = null): string
    {
        $prefix = strtoupper(trim($prefix));
        $rawCode = trim((string) $rawCode);

        $number = null;

        if ($rawCode !== '' && preg_match('/(\d+)\s*$/', $rawCode, $matches)) {
            $number = (int) $matches[1];
        }

        if ($number === null || $number <= 0) {
            $number = $fallbackNumber && $fallbackNumber > 0 ? $fallbackNumber : 1;
        }

        return $prefix.str_pad((string) $number, 3, '0', STR_PAD_LEFT);
    }

    private function compactPartyCode(?string $code): string
    {
        return strtoupper(str_replace('-', '', trim((string) $code)));
    }
}
