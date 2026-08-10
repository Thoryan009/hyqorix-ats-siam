<?php

namespace App\Modules\Vendor\Services;

use App\Modules\Vendor\Contracts\VendorDataServiceInterface;
use App\Modules\Vendor\Models\Vendor;
use App\Modules\Vendor\Models\VendorType;
use App\Modules\Vendor\Repositories\VendorTypeRepository;
use App\Services\BaseCachedService;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class VendorTypeService extends BaseCachedService
{
    public function __construct(
        protected VendorTypeRepository $repository,
        protected VendorDataServiceInterface $vendorDataService,
    ) {
        parent::__construct(new VendorType());
    }

    public function getPaginatedDataWithCache(array $filters = [])
    {
        return $this->remember(
            $this->filtersCacheKey($filters),
            fn () => $this->repository->getPaginatedData($filters)
        );
    }

    public function getVendorType(VendorType $vendorType): VendorType
    {
        return $this->remember(
            $this->byIdCacheKey($vendorType->id),
            fn () => $vendorType->load(['createdBy', 'updatedBy'])
        );
    }

    public function createVendorType(array $data): VendorType
    {
        $data['code'] = $this->uniqueCode($data['name'], $data['code'] ?? null);
        $data['status'] = $data['status'] ?? 'active';
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $vendorType = $this->mutate(fn () => $this->model->create($data));
        $this->vendorDataService->clearVendorDataCache();

        return $vendorType;
    }

    public function updateVendorType(VendorType $vendorType, array $data): VendorType
    {
        unset($data['code']);

        $updated = $this->mutate(fn () => tap($vendorType)->update($data));
        $this->vendorDataService->clearVendorDataCache();

        return $updated->fresh(['createdBy', 'updatedBy']);
    }

    public function deleteVendorType(VendorType $vendorType): bool
    {
        $this->assertNotInUse($vendorType);

        $deleted = $this->mutate(fn () => $vendorType->delete());
        $this->vendorDataService->clearVendorDataCache();

        return $deleted;
    }

    public function bulkDelete(array $ids): int
    {
        $types = $this->model->whereIn('id', $ids)->get();

        foreach ($types as $type) {
            $this->assertNotInUse($type);
        }

        $count = $this->mutate(
            fn () => $this->model->whereIn('id', $ids)->delete()
        );
        $this->vendorDataService->clearVendorDataCache();

        return $count;
    }

    public function getActiveOptions(): array
    {
        return $this->model
            ->query()
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'code'])
            ->map(fn (VendorType $type) => [
                'id' => $type->code,
                'name' => $type->name,
            ])
            ->toArray();
    }

    private function uniqueCode(string $name, ?string $code = null): string
    {
        $base = Str::slug($code ?: $name, '_');
        $base = $base !== '' ? $base : 'type';
        $candidate = $base;
        $suffix = 1;

        while ($this->model->where('code', $candidate)->exists()) {
            $candidate = $base . '_' . $suffix;
            $suffix++;
        }

        return $candidate;
    }

    private function assertNotInUse(VendorType $vendorType): void
    {
        if (Vendor::query()->where('vendor_type', $vendorType->code)->exists()) {
            throw ValidationException::withMessages([
                'vendor_type' => "Vendor type \"{$vendorType->name}\" is assigned to one or more vendors and cannot be deleted.",
            ]);
        }
    }
}
