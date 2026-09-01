<?php

namespace App\Modules\Parties\Repositories;

use App\Modules\Application\Models\Application;
use App\Modules\Parties\Models\Party;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;

class PartyRepository extends BaseRepository
{
    public function __construct(Party $model)
    {
        parent::__construct($model);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        $this->applySearch($query, $filters['search'] ?? null);

        if (!empty($filters['status'])) {
            $query->where('status', $this->normalizeStatus($filters['status']));
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['job_list_id'])) {
            $jobListId = (int) $filters['job_list_id'];
            $applicationIds = Application::query()
                ->where('job_list_id', $jobListId)
                ->pluck('id');

            if ($applicationIds->isEmpty()) {
                $query->whereRaw('1 = 0');

                return;
            }

            $query->whereNotNull('source_id')
                ->whereIn('source_id', $applicationIds);
        }
    }

    protected function applyEagerLoads(Builder $query, array $filters): void
    {
        $query->with(['createdBy:id,name', 'updatedBy:id,name']);
    }

    protected function applyOrder(Builder $query, array $filters): void
    {
        $query->orderBy('code')->orderBy('name');
    }

    protected function applySearch(Builder $query, ?string $search): void
    {
        if (!$search) {
            return;
        }

        $search = trim($search);

        $query->where(function (Builder $q) use ($search) {
            $q->where('code', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%")
                ->orWhere('type', 'like', "%{$search}%")
                ->orWhere('remarks', 'like', "%{$search}%");
        });
    }

    private function normalizeStatus(string $status): string
    {
        return strtolower($status) === 'inactive' ? 'inactive' : 'active';
    }
}
