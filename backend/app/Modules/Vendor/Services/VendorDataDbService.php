<?php

namespace App\Modules\Vendor\Services;

use App\Modules\Auth\Models\Role;
use App\Modules\Vendor\Contracts\VendorDataServiceInterface;
use App\Modules\Vendor\Models\VendorType;
use Illuminate\Support\Facades\Cache;

class VendorDataDbService implements VendorDataServiceInterface
{
    public string $cacheKey = 'vendor_data_all';

    public function getVendorData(): array
    {
        return Cache::remember($this->cacheKey, 3600, function () {
            $roles = Role::query()
                ->get()
                ->map(fn ($role) => [
                    'id' => $role->id,
                    'name' => $role->name,
                ])
                ->toArray();

            $vendorTypes = VendorType::query()
                ->where('status', 'active')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['name', 'code'])
                ->map(fn (VendorType $type) => [
                    'id' => $type->code,
                    'name' => $type->name,
                ])
                ->toArray();

            return [
                'roles' => $roles,
                'vendor_types' => $vendorTypes,
            ];
        });
    }

    public function clearVendorDataCache(): void
    {
        Cache::forget($this->cacheKey);
    }
}
