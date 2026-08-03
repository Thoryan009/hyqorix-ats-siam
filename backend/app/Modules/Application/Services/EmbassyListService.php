<?php

namespace App\Modules\Application\Services;

use App\Modules\Application\Models\EmbassyList;
use App\Modules\Application\Models\EmbassyListItem;
use App\Modules\Application\Repositories\EmbassyListRepository;
use App\Services\BaseCachedService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EmbassyListService extends BaseCachedService
{
    public function __construct(protected EmbassyListRepository $repository)
    {
        parent::__construct(new EmbassyList());
    }

    public function getPaginatedDataWithCache(array $filters = [])
    {
        return $this->remember(
            $this->filtersCacheKey($filters),
            fn () => $this->repository->getPaginatedData($filters)
        );
    }

    public function getEmbassyList(EmbassyList $embassyList): EmbassyList
    {
        return $this->remember(
            $this->byIdCacheKey($embassyList->id),
            fn () => $embassyList->load([
                'items' => fn ($query) => $query->orderBy('sort_order')->orderBy('id'),
                'items.application.embassySubmission',
                'items.application.processes',
            ])
        );
    }

    public function createEmbassyList(array $data): EmbassyList
    {
        return $this->mutate(function () use ($data) {
            $this->assertSubmitDateAvailable($data['submit_date']);

            return DB::transaction(function () use ($data) {
                $this->validatePassportUniqueness($data['submit_date'], $data['items']);

                $list = EmbassyList::create([
                    'submit_date' => $data['submit_date'],
                ]);

                $this->syncItems($list, $data['items']);

                return $this->getEmbassyList($list);
            });
        });
    }

    public function updateEmbassyList(EmbassyList $embassyList, array $data): EmbassyList
    {
        return $this->mutate(function () use ($embassyList, $data) {
            return DB::transaction(function () use ($embassyList, $data) {
                $submitDate = $data['submit_date'];

                if ($embassyList->submit_date->format('Y-m-d') !== $submitDate) {
                    $this->assertSubmitDateAvailable($submitDate, $embassyList->id);
                }

                $this->validatePassportUniqueness($submitDate, $data['items'], $embassyList->id);

                $embassyList->update([
                    'submit_date' => $submitDate,
                ]);

                $embassyList->items()->delete();
                $this->syncItems($embassyList, $data['items']);

                return $this->getEmbassyList($embassyList->fresh());
            });
        });
    }

    public function deleteEmbassyList(EmbassyList $embassyList): bool
    {
        return $this->mutate(fn () => (bool) $embassyList->delete());
    }

    public function bulkDelete(array $ids): int
    {
        return $this->mutate(fn () => $this->model->whereIn('id', $ids)->delete());
    }

    private function syncItems(EmbassyList $list, array $items): void
    {
        $payload = collect($items)->values()->map(function (array $item, int $index) {
            return [
                'application_id' => $item['application_id'] ?? null,
                'list_type' => $item['list_type'],
                'passport_no' => trim($item['passport_no']),
                'sort_order' => $item['sort_order'] ?? ($index + 1),
            ];
        })->all();

        $list->items()->createMany($payload);
    }

    private function assertSubmitDateAvailable(string $submitDate, ?int $excludeId = null): void
    {
        $query = EmbassyList::query()->whereDate('submit_date', $submitDate);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'submit_date' => 'An embassy list already exists for this submit date.',
            ]);
        }
    }

    private function validatePassportUniqueness(string $submitDate, array $items, ?int $excludeListId = null): void
    {
        $normalizedPassports = collect($items)
            ->pluck('passport_no')
            ->map(fn ($passport) => strtolower(trim((string) $passport)))
            ->filter();

        if ($normalizedPassports->count() !== $normalizedPassports->unique()->count()) {
            throw ValidationException::withMessages([
                'items' => 'Duplicate passport numbers found in the list.',
            ]);
        }

        $existingPassports = EmbassyListItem::query()
            ->whereHas('embassyList', function ($query) use ($submitDate, $excludeListId) {
                $query->whereDate('submit_date', $submitDate);

                if ($excludeListId) {
                    $query->where('id', '!=', $excludeListId);
                }
            })
            ->pluck('passport_no')
            ->map(fn ($passport) => strtolower(trim((string) $passport)));

        $conflicts = $normalizedPassports->intersect($existingPassports);

        if ($conflicts->isNotEmpty()) {
            throw ValidationException::withMessages([
                'items' => 'Passport ' . $conflicts->first() . ' is already added for this submit date.',
            ]);
        }
    }
}
