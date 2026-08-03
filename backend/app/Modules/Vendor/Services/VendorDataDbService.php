<?php

namespace App\Modules\Vendor\Services;

use App\Modules\Auth\Models\Role;
use App\Modules\Vendor\Contracts\VendorDataServiceInterface;
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

            return [
                'roles' => $roles,
            ];
        });
    }

    public function clearVendorDataCache(): void
    {
        Cache::forget($this->cacheKey);
    }
}
