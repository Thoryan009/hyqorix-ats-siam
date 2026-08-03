<?php

namespace App\Modules\System\Services;

use App\Modules\Auth\Models\User;
use App\Modules\System\Models\Setting;
use App\Modules\System\Contracts\ActivityLogDataServiceInterface;
use Illuminate\Support\Facades\Cache;

class ActivityLogDataDbService implements ActivityLogDataServiceInterface
{
    public $cacheKey = "ActivityLog_data_all";

    public function getActivityLogData(): array
    {
        $cacheKey = $this->cacheKey;

        $cacheTTL = 3600;

        return Cache::remember($cacheKey, $cacheTTL, function () {
            $users = User::query(); // fetch from DB

            $users = $users->get()->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                ];
            })->toArray();

            return [
                'users' => $users,
            ];
        });
    }


    public function clearActivityLogDataCache(): void
    {
        Cache::forget($this->cacheKey);
    }
}
