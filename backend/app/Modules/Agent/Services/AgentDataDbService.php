<?php

namespace App\Modules\Agent\Services;

use App\Modules\Auth\Models\Role;
use Illuminate\Support\Facades\Cache;
use App\Modules\Agent\Contracts\AgentDataServiceInterface;

class AgentDataDbService implements AgentDataServiceInterface
{
    public $cacheKey = 'agent_all_data';
    public function getAgentData(): array
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

    public function clearAgentDataCache(): void
    {
        Cache::forget($this->cacheKey);
    }

}
