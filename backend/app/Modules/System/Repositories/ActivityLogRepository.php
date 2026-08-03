<?php

namespace App\Modules\System\Repositories;

use App\Modules\System\Models\ActivityLog;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;

class ActivityLogRepository extends BaseRepository
{
    public function __construct(ActivityLog $model)
    {
        parent::__construct($model);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        $this->applySearch($query, $filters['search'] ?? null);
        $this->applyUserFilter($query, $filters['user_id'] ?? null);
        $this->applyDateFilter($query, $filters);
    }

    protected function applySearch(Builder $query, ?string $search): void
    {
        if (!$search) return;

        $query->where('description', 'like', "%{$search}%");
    }

    protected function applyUserFilter(Builder $query, ?int $userId): void
    {
        if (!$userId) return;

        $query->where('user_id', $userId);
    }
}
