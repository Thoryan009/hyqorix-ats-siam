<?php

namespace App\Modules\System\Services;

use App\Modules\System\Models\ActivityLog;
use App\Services\BaseCachedService;
use App\Modules\System\Repositories\ActivityLogRepository;

class ActivityLogService extends BaseCachedService
{
    public function __construct(protected ActivityLogRepository $repository)
    {
        parent::__construct(new ActivityLog());
    }

    /* ==========================================================
     | Read Operations (Cached)
     |========================================================== */

    public function getPaginatedDataWithCache(array $filters = [])
    {
        return $this->remember(
            $this->filtersCacheKey($filters),
            fn() => $this->repository->getPaginatedData($filters)
        );
    }

    public function getAll()
    {
        return $this->model->all();
    }

    public function getActivityLog(ActivityLog $activityLog)
    {
        return $this->remember(
            $this->byIdCacheKey($activityLog->id),
            fn() => $activityLog
        );
    }

    /* ==========================================================
     | Write Operations (Invalidate Cache)
     |========================================================== */

    public function createActivityLog(array $data)
    {
        $activityLog = $this->mutate(fn() => $this->model->create($data));
        return $activityLog;
    }

    public function updateActivityLog(ActivityLog $activityLog, array $data)
    {
        return $this->mutate(fn() => tap($activityLog)->update($data));
    }

    public function deleteActivityLog(ActivityLog $activityLog): bool
    {
        return $this->mutate(fn() => $activityLog->delete());
    }

    public function bulkDelete(array $ids): int
    {
        return $this->mutate(fn() => $this->model->whereIn('id', $ids)->delete());
    }
}
