<?php

namespace App\Modules\__PARENT_MODEL__\Services;

use App\Modules\__PARENT_MODEL__\Models\__PARENT_MODEL__;
use App\Modules\__PARENT_MODEL__\Contracts\__MODEL__DataServiceInterface;
use Illuminate\Support\Facades\Cache;

class __MODEL__DataDbService implements __MODEL__DataServiceInterface
{
    public $cacheKey = "__MODEL___data_all";

    public function get__MODEL__Data(): array
    {
        $cacheKey = $this->cacheKey;

        $cacheTTL = 3600;

        return Cache::remember($cacheKey, $cacheTTL, function () {
            $__plural_parent_model__ = __PARENT_MODEL__::query(); // fetch from DB

             $__plural_parent_model__ = $__plural_parent_model__->get()->map(function ($__LOWER_PARENT_MODEL__) {
                return [
                    'id' => $__LOWER_PARENT_MODEL__->id,
                    'name' => $__LOWER_PARENT_MODEL__->name,
                ];
            })->toArray();

            return [
                '__plural_parent_model__' => $__plural_parent_model__,
            ];
        });
    }


    public function clear__MODEL__DataCache(): void
    {
        Cache::forget($this->cacheKey);
    }
}
