<?php

namespace App\Modules\Principal\Services;

use App\Modules\Auth\Models\Role;
use Illuminate\Support\Facades\Cache;

use App\Modules\Principal\Contracts\PrincipalDataServiceInterface;

class PrincipalDataDbService implements PrincipalDataServiceInterface
{
    public $cacheKey = 'principal_all_data';
    public function getPrincipalData(): array
    {

        $cacheKey = $this->cacheKey;
        $cacheTTL = 3600;
        return Cache::remember($cacheKey, $cacheTTL, function () {
            $roles = Role::query(); // fetch from DB
            $roles = $roles
                ->get()
                ->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                    ];
                })
                ->toArray();
            return [
                'roles' => $roles,
            ];
        });
    }

    public function clearPrincipalDataCache(): void
    {
        Cache::forget($this->cacheKey);
    }

}
