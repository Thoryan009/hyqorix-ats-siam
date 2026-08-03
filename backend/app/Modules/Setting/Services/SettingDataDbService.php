<?php
namespace App\Modules\Setting\Services;

use Illuminate\Support\Facades\Cache;
use App\Modules\Employee\Models\Department;

class SettingDataDbService
{
     public $cacheKey = 'setting_all_data';
    public function getSettingData(): array
    {

        $cacheKey = $this->cacheKey;
        $cacheTTL = 3600;
        return Cache::remember($cacheKey, $cacheTTL, function () {
            $departments = Department::query(); // fetch from DB
            $departments = $departments
                ->get()
                ->map(function ($department) {
                    return [
                        'id' => $department->id,
                        'name' => $department->name,
                    ];
                })
                ->toArray();
            return [
                'departments' => $departments,
            ];
        });
    }

    public function clearSettingDataCache(): void
    {
        Cache::forget($this->cacheKey);
    }
}
