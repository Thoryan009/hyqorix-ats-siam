<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

abstract class BaseCachedService
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /* ============================
     | Cache Core Helpers
     |============================ */

    protected function remember(string $key, \Closure $callback)
    {
        return Cache::tags($this->getCacheTag())
            ->remember($key, $this->getCacheTtl(), $callback);
    }

    protected function mutate(callable $action, bool $invalidateCache = true)
    {
        $result = $action(); // perform the mutation

        if ($invalidateCache) {
            $this->flushCache(); // automatically flush cache
            $this->flushRelatedCache(); // flush related caches if needed
        }

        return $result;
    }

    public function flushCache(): void
    {
        Cache::tags($this->getCacheTag())->flush();
    }


    protected function flushRelatedCache(): void
    {
        Cache::forget($this->getCacheTag() . '_all');
    }


    protected function getCacheTag(): string
    {
        return Str::plural(Str::snake(class_basename($this->model))); // e.g. 'job_lists'
    }
    protected function getCacheTtl(): int
    {
        return config(
            strtolower(class_basename($this->model)) . '_cache.ttl',
            600
        );
    }

    /* ============================
     | Cache Key Helpers
     |============================ */

    protected function byIdCacheKey(int $id): string
    {
        return "{$this->getCacheTag()}_by_id_{$id}";
    }

    protected function filtersCacheKey(array $filters = []): string
    {
        // Extract pagination params with defaults
        $page = (int) ($filters['page'] ?? 1);
        $perPage = (int) ($filters['per_page'] ?? 10);

        // Remove pagination params from filters before hashing
        unset($filters['page'], $filters['per_page']);

        // Normalize filter order
        ksort($filters);

        $filtersHash = empty($filters)
            ? 'nofilter'
            : md5(json_encode($filters));

        return sprintf(
            '%s_paginated_%d_%d_%s',
            $this->getCacheTag(),
            $page,
            $perPage,
            $filtersHash
        );
    }
}
