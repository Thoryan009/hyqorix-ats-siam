<?php

namespace App\Modules\PassportHandover\Services;

use App\Modules\PassportHandover\Models\PassportHandover;
use App\Modules\PassportHandover\Models\PassportHandoverItem;
use App\Modules\PassportHandover\Repositories\PassportHandoverRepository;
use App\Modules\Employee\Models\Employee;
use App\Services\BaseCachedService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PassportHandoverService extends BaseCachedService
{
    public function __construct(protected PassportHandoverRepository $repository)
    {
        parent::__construct(new PassportHandover());
    }

    public function getPaginatedDataWithCache(array $filters = [])
    {
        return $this->remember(
            $this->filtersCacheKey($filters),
            fn () => $this->repository->getPaginatedData($filters)
        );
    }

    public function searchByPassport(string $passportNo)
    {
        $passportNo = trim($passportNo);

        if ($passportNo === '') {
            return collect();
        }

        return PassportHandoverItem::query()
            ->where('passport_no', 'like', "%{$passportNo}%")
            ->with(['application', 'passportHandover'])
            ->orderByDesc('id')
            ->limit(15)
            ->get();
    }

    public function getPassportHandover(PassportHandover $passportHandover): PassportHandover
    {
        return $this->remember(
            $this->byIdCacheKey($passportHandover->id),
            function () use ($passportHandover) {
                return $passportHandover->load([
                    'items' => fn ($query) => $query->orderBy('sort_order')->orderBy('id'),
                    'items.application',
                    'items.handedOverBy.user',
                    'items.collectedBy.user',
                    'items.collectedByUser',
                    'items.rejectedBy.user',
                    'items.rejectedByUser',
                    'items.passportHandover.handedOverBy.user',
                    'items.passportHandover.createdBy',
                    'handedOverBy.user',
                    'createdBy',
                ])->loadCount([
                    'items as total_items_count',
                    'items as collected_items_count' => fn ($query) => $query->where('status', 'collected'),
                    'items as rejected_items_count' => fn ($query) => $query->where('status', 'rejected'),
                    'items as pending_items_count' => fn ($query) => $query->where('status', 'handed_over'),
                ]);
            }
        );
    }

    public function createPassportHandover(array $data): PassportHandover
    {
        return $this->mutate(function () use ($data) {
            return DB::transaction(function () use ($data) {
                $isPermanent = (bool) ($data['is_permanent'] ?? false);
                $items = $this->normalizeItemsFromPayload($data, $isPermanent);
                $this->validatePassportAvailability($items);
                $employeeId = $this->resolveAuthEmployeeId();

                $handover = PassportHandover::create([
                    'handover_no' => $this->generateHandoverNo($data['taken_at']),
                    'type' => $data['type'],
                    'is_permanent' => $isPermanent,
                    'taker_name' => trim($data['taker_name']),
                    'taker_phone' => trim($data['taker_phone']),
                    'taken_at' => $data['taken_at'],
                    'expected_return_date' => $isPermanent ? null : ($data['expected_return_date'] ?? null),
                    'taken_reason' => $data['taken_reason'] ?? null,
                    'return_date' => $isPermanent ? null : ($data['return_date'] ?? null),
                    'status' => 'handed_over',
                    'handed_over_by' => $employeeId,
                ]);

                $this->syncItems($handover, $items, $employeeId);

                return $this->getPassportHandover($handover);
            });
        });
    }

    public function deletePassportHandover(PassportHandover $passportHandover): bool
    {
        return $this->mutate(fn () => (bool) $passportHandover->delete());
    }

    public function bulkDelete(array $ids): int
    {
        return $this->mutate(fn () => $this->model->whereIn('id', $ids)->delete());
    }

    public function collectPassports(PassportHandover $passportHandover, array $data): PassportHandover
    {
        return $this->mutate(function () use ($passportHandover, $data) {
            return DB::transaction(function () use ($passportHandover, $data) {
                if ($passportHandover->is_permanent) {
                    throw ValidationException::withMessages([
                        'handover' => 'Permanent handovers cannot be collected.',
                    ]);
                }

                $employeeId = $this->resolveAuthEmployeeId();
                $userId = auth()->id();
                $requestedItems = collect($data['items'] ?? [])->values();
                $itemIds = $requestedItems->pluck('id')->map(fn ($id) => (int) $id)->unique()->values();

                $items = PassportHandoverItem::query()
                    ->where('passport_handover_id', $passportHandover->id)
                    ->whereIn('id', $itemIds)
                    ->where('status', 'handed_over')
                    ->get()
                    ->keyBy('id');

                if ($items->count() !== $itemIds->count()) {
                    throw ValidationException::withMessages([
                        'items' => 'One or more selected passports are invalid or already processed.',
                    ]);
                }

                foreach ($requestedItems as $requestedItem) {
                    $item = $items->get((int) $requestedItem['id']);

                    if (!$item) {
                        continue;
                    }

                    if ($requestedItem['action'] === 'reject') {
                        $item->update([
                            'status' => 'rejected',
                            'reject_date' => $requestedItem['reject_date'] ?? null,
                            'rejected_by' => $employeeId,
                            'rejected_by_user_id' => $userId,
                        ]);
                        continue;
                    }

                    $item->update([
                        'status' => 'collected',
                        'return_date' => $requestedItem['return_date'] ?? null,
                        'collected_by' => $employeeId,
                        'collected_by_user_id' => $userId,
                    ]);
                }

                $this->refreshHandoverStatus($passportHandover);

                return $this->getPassportHandover($passportHandover->fresh());
            });
        });
    }

    private function normalizeItemsFromPayload(array $data, bool $isPermanent = false): array
    {
        if ($data['type'] === 'single') {
            return collect($data['entries'] ?? [])->values()->map(function (array $entry, int $index) use ($isPermanent) {
                return [
                    'application_id' => $entry['application_id'] ?? null,
                    'passport_no' => trim((string) ($entry['passport_no'] ?? '')),
                    'expected_return_date' => $isPermanent ? null : ($entry['expected_return_date'] ?? null),
                    'taken_reason' => $entry['taken_reason'] ?? null,
                    'return_date' => $isPermanent ? null : ($entry['return_date'] ?? null),
                    'sort_order' => $index + 1,
                ];
            })->all();
        }

        return collect($data['applications'] ?? [])->values()->map(function (array $application, int $index) use ($data, $isPermanent) {
            return [
                'application_id' => $application['application_id'] ?? null,
                'passport_no' => trim((string) ($application['passport_no'] ?? '')),
                'expected_return_date' => $isPermanent ? null : ($data['expected_return_date'] ?? null),
                'taken_reason' => $data['taken_reason'] ?? null,
                'return_date' => $isPermanent ? null : ($data['return_date'] ?? null),
                'sort_order' => $index + 1,
            ];
        })->all();
    }

    private function syncItems(PassportHandover $handover, array $items, ?int $employeeId = null): void
    {
        $handover->items()->createMany(
            collect($items)->map(fn (array $item) => array_merge($item, [
                'handed_over_by' => $employeeId,
            ]))->all()
        );
    }

    private function validatePassportAvailability(array $items): void
    {
        $normalizedPassports = collect($items)
            ->pluck('passport_no')
            ->map(fn ($passport) => strtolower(trim((string) $passport)))
            ->filter();

        if ($normalizedPassports->count() !== $normalizedPassports->unique()->count()) {
            throw ValidationException::withMessages([
                'items' => 'Duplicate passport numbers found in the handover request.',
            ]);
        }

        $activePassports = PassportHandoverItem::query()
            ->where('status', 'handed_over')
            ->pluck('passport_no')
            ->map(fn ($passport) => strtolower(trim((string) $passport)));

        $conflicts = $normalizedPassports->intersect($activePassports);

        if ($conflicts->isNotEmpty()) {
            throw ValidationException::withMessages([
                'items' => 'Passport ' . $conflicts->first() . ' is already handed over and not collected yet.',
            ]);
        }
    }

    private function refreshHandoverStatus(PassportHandover $passportHandover): void
    {
        $counts = PassportHandoverItem::query()
            ->where('passport_handover_id', $passportHandover->id)
            ->selectRaw("
                SUM(CASE WHEN status = 'collected' THEN 1 ELSE 0 END) as collected_count,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_count,
                SUM(CASE WHEN status = 'handed_over' THEN 1 ELSE 0 END) as pending_count,
                COUNT(*) as total_count
            ")
            ->first();

        $total = (int) ($counts->total_count ?? 0);
        $collected = (int) ($counts->collected_count ?? 0);
        $rejected = (int) ($counts->rejected_count ?? 0);
        $pending = (int) ($counts->pending_count ?? 0);
        $resolved = $collected + $rejected;

        $status = 'handed_over';

        if ($pending > 0 && $resolved > 0) {
            $status = 'partially_collected';
        } elseif ($pending === 0 && $resolved === $total) {
            if ($collected === $total) {
                $status = 'collected';
            } elseif ($rejected === $total) {
                $status = 'rejected';
            } else {
                $status = 'partially_collected';
            }
        }

        $latestReturnDate = PassportHandoverItem::query()
            ->where('passport_handover_id', $passportHandover->id)
            ->where('status', 'collected')
            ->max('return_date');

        $passportHandover->update([
            'status' => $status,
            'return_date' => $latestReturnDate,
        ]);
    }

    private function generateHandoverNo(string $takenAt): string
    {
        $yearSuffix = date('y', strtotime($takenAt));
        $prefix = 'PH';

        $count = PassportHandover::query()
            ->where('handover_no', 'like', "{$prefix}-%/%")
            ->where('handover_no', 'like', "%/{$yearSuffix}")
            ->count() + 1;

        return sprintf('%s-%03d/%s', $prefix, $count, $yearSuffix);
    }

    private function resolveAuthEmployeeId(): ?int
    {
        $user = auth()->user();

        if (!$user) {
            return null;
        }

        return Employee::where('user_id', $user->id)->value('id');
    }
}
