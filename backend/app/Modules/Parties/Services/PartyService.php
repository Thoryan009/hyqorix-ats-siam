<?php

namespace App\Modules\Parties\Services;

use App\Modules\Agent\Models\Agent;
use App\Modules\Application\Models\Application;
use App\Modules\Application\Helpers\ApplicationPresenter;
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
use Illuminate\Validation\ValidationException;
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
        return $this->model->create($this->preparePartyData($data));
    }

    public function bulkCreate(array $parties): Collection
    {
        return DB::transaction(function () use ($parties) {
            $created = collect();

            foreach ($parties as $partyData) {
                $created->push($this->model->create($this->preparePartyData($partyData)));
            }

            return $created;
        });
    }

    public function update(Party $party, array $data): Party
    {
        unset($data['source_id']);

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

    public function createFromSourceModule(string $sourceModule, object $entity): ?Party
    {
        $partyType = PartyType::query()->where('source_module', $sourceModule)->first();

        if (!$partyType) {
            return null;
        }

        $payload = $this->buildPartyPayloadFromEntity($sourceModule, $partyType->code, $entity);

        if (!$payload) {
            return null;
        }

        if ($this->model->newQuery()->where('code', $payload['code'])->exists()) {
            return null;
        }

        if ($this->model->newQuery()
            ->where('type', $payload['type'])
            ->where('source_id', $payload['source_id'])
            ->exists()) {
            return null;
        }

        if ($this->model->newQuery()
            ->where('type', $payload['type'])
            ->where('code', $payload['code'])
            ->exists()) {
            return null;
        }

        if ($sourceModule === 'employee') {
            $existingNames = $this->getExistingPartyNameSet($payload['type']);
            $name = strtoupper(trim((string) $payload['name']));

            if ($name !== '' && isset($existingNames[$name])) {
                return null;
            }
        }

        return $this->create($payload);
    }

    public function shouldCreatePartyAccount(array $data, bool $default = true): bool
    {
        $value = array_key_exists('create_party_account', $data)
            ? $data['create_party_account']
            : ($default ? 1 : 0);

        return in_array($value, [1, '1', true], true);
    }

    private function buildPartyPayloadFromEntity(string $sourceModule, string $typeCode, object $entity): ?array
    {
        $code = '';
        $name = '';

        switch ($sourceModule) {
            case 'client':
                /** @var Client $entity */
                $code = $this->compactPartyCode($entity->client_id ?? '');
                $name = (string) ($entity->user?->name ?? '');
                break;
            case 'agent':
                /** @var Agent $entity */
                $code = $this->normalizeShortCode('AG', $entity->agent_id ?? null, $entity->id);
                $name = (string) ($entity->user?->name ?? '');
                break;
            case 'principal':
                /** @var Principal $entity */
                $code = $this->normalizeShortCode('PR', $entity->principal_id ?? null, $entity->id);
                $name = (string) ($entity->user?->name ?? '');
                break;
            case 'vendor':
                /** @var Vendor $entity */
                $code = $this->compactPartyCode($entity->vendor_id ?? '');
                $name = (string) ($entity->organization_name ?? $entity->user?->name ?? '');
                break;
            case 'employee':
                /** @var Employee $entity */
                $code = $this->compactPartyCode($entity->employee_id ?? '');
                if ($code === '') {
                    $code = $this->normalizeShortCode('ST', null, $entity->id);
                }
                $name = (string) ($entity->user?->name ?? '');
                break;
            case 'application':
                /** @var Application $entity */
                $code = strtoupper(trim((string) ($entity->passport_no ?? '')));
                $name = ApplicationPresenter::fullName($entity->given_name ?? '', $entity->sur_name ?? '') ?? '';
                break;
            default:
                return null;
        }

        $code = trim($code);
        $name = trim($name);

        if ($code === '' || $name === '') {
            return null;
        }

        return [
            'code' => $code,
            'name' => $name,
            'type' => $typeCode,
            'source_id' => (int) $entity->id,
            'status' => 'active',
            'opening_debit' => 0,
            'opening_credit' => 0,
            'remarks' => '',
        ];
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
        $existingSourceIds = $this->getExistingSourceIdSet($type);

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
            ->reject(fn (array $item) => $this->isExistingPartyCode($item['code'] ?? '', $existingCodes)
                || isset($existingSourceIds[(int) ($item['id'] ?? 0)]))
            ->values();
    }

    private function getPrincipalSourceOptions(string $type): Collection
    {
        $existingCodes = $this->getExistingPartyCodeSet($type, 'PR');
        $existingSourceIds = $this->getExistingSourceIdSet($type);

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
            ->reject(fn (array $item) => $this->isExistingPartyCode($item['code'] ?? '', $existingCodes)
                || isset($existingSourceIds[(int) ($item['id'] ?? 0)]))
            ->values();
    }

    private function getAgentSourceOptions(string $type): Collection
    {
        $existingCodes = $this->getExistingPartyCodeSet($type, 'AG');
        $existingSourceIds = $this->getExistingSourceIdSet($type);

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
            ->reject(fn (array $item) => $this->isExistingPartyCode($item['code'] ?? '', $existingCodes)
                || isset($existingSourceIds[(int) ($item['id'] ?? 0)]))
            ->values();
    }

    private function getVendorSourceOptions(string $type): Collection
    {
        $existingCodes = $this->getExistingPartyCodeSet($type);
        $existingSourceIds = $this->getExistingSourceIdSet($type);

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
            ->reject(fn (array $item) => $this->isExistingPartyCode($item['code'] ?? '', $existingCodes)
                || isset($existingSourceIds[(int) ($item['id'] ?? 0)]))
            ->values();
    }

    private function getStaffSourceOptions(string $type): Collection
    {
        $existingCodes = $this->getExistingPartyCodeSet($type, 'ST');
        $existingNames = $this->getExistingPartyNameSet($type);
        $existingSourceIds = $this->getExistingSourceIdSet($type);

        return Employee::query()
            ->select(['id', 'employee_id', 'user_id'])
            ->with(['user:id,name,status'])
            ->tap(fn (Builder $query) => $this->applyActiveUserFilter($query))
            ->orderBy('id')
            ->limit(500)
            ->get()
            ->map(function (Employee $employee) {
                $code = $this->compactPartyCode($employee->employee_id ?? '');
                if ($code === '') {
                    $code = $this->normalizeShortCode('ST', null, $employee->id);
                }

                return [
                    'id' => $employee->id,
                    'code' => $code,
                    'name' => $employee->user?->name,
                ];
            })
            ->reject(function (array $item) use ($existingCodes, $existingNames, $existingSourceIds) {
                if ($this->isExistingPartyCode($item['code'] ?? '', $existingCodes)) {
                    return true;
                }

                if (isset($existingSourceIds[(int) ($item['id'] ?? 0)])) {
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
        $existingSourceIds = $this->getExistingSourceIdSet($type);

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
            ) || isset($existingSourceIds[(int) $application->id]))
            ->values();
    }

    /**
     * @return array<int, true>
     */
    private function getExistingSourceIdSet(string $type): array
    {
        $set = [];

        $ids = $this->model->newQuery()
            ->where('type', $type)
            ->whereNotNull('source_id')
            ->pluck('source_id');

        foreach ($ids as $id) {
            $set[(int) $id] = true;
        }

        return $set;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function preparePartyData(array $data): array
    {
        $sourceId = isset($data['source_id']) && $data['source_id'] !== '' && $data['source_id'] !== null
            ? (int) $data['source_id']
            : null;

        if (!$sourceId) {
            unset($data['source_id']);

            return $data;
        }

        $type = (string) ($data['type'] ?? '');
        $partyType = PartyType::query()->where('code', $type)->first();

        if (!$partyType?->source_module) {
            unset($data['source_id']);

            return $data;
        }

        $this->assertSourceRecordExists($partyType->source_module, $sourceId);
        $this->assertSourceNotAlreadyLinked($type, $sourceId);

        $data['source_id'] = $sourceId;

        return $data;
    }

    private function assertSourceRecordExists(string $sourceModule, int $sourceId): void
    {
        $exists = match ($sourceModule) {
            'client' => Client::query()->whereKey($sourceId)->exists(),
            'agent' => Agent::query()->whereKey($sourceId)->exists(),
            'principal' => Principal::query()->whereKey($sourceId)->exists(),
            'vendor' => Vendor::query()->whereKey($sourceId)->exists(),
            'employee' => Employee::query()->whereKey($sourceId)->exists(),
            'application' => Application::query()->whereKey($sourceId)->exists(),
            default => false,
        };

        if (!$exists) {
            throw ValidationException::withMessages([
                'source_id' => ['Selected source record was not found.'],
            ]);
        }
    }

    private function assertSourceNotAlreadyLinked(string $type, int $sourceId): void
    {
        $exists = $this->model->newQuery()
            ->where('type', $type)
            ->where('source_id', $sourceId)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'source_id' => ['A party already exists for this source record.'],
            ]);
        }
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
