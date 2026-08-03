<?php

namespace App\Modules\Application\Repositories;

use App\Modules\Application\Models\EmbassyList;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;

class EmbassyListRepository extends BaseRepository
{
    public function __construct(EmbassyList $model)
    {
        parent::__construct($model);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        $this->applySearch($query, $filters['search'] ?? null);

        if (!empty($filters['from_date'])) {
            $query->whereDate('submit_date', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->whereDate('submit_date', '<=', $filters['to_date']);
        }
    }

    protected function applyEagerLoads(Builder $query, array $filters): void
    {
        $query->withCount([
            'items as new_stamping_count' => fn (Builder $q) => $q->where('list_type', 'new_stamping'),
            'items as cancel_stamping_count' => fn (Builder $q) => $q->where('list_type', 'cancellation'),
            'items as restamping_count' => fn (Builder $q) => $q->where('list_type', 'restamping'),
        ]);
    }

    protected function applySearch(Builder $query, ?string $search): void
    {
        if (!$search) {
            return;
        }

        $search = trim($search);

        $query->where(function (Builder $q) use ($search) {
            $q->whereDate('submit_date', 'like', "%{$search}%")
                ->orWhereHas('items', fn (Builder $iq) => $iq->where('passport_no', 'like', "%{$search}%"));
        });
    }

    protected function applyOrder(Builder $query, array $filters): void
    {
        $allowedSortColumns = ['id', 'submit_date', 'created_at', 'updated_at'];

        $sortBy = $filters['sort_by'] ?? 'submit_date';
        if (!in_array($sortBy, $allowedSortColumns, true)) {
            $sortBy = 'submit_date';
        }

        $sortDirection = $filters['sort_direction'] ?? 'desc';
        if (!in_array(strtolower($sortDirection), ['asc', 'desc'], true)) {
            $sortDirection = 'desc';
        }

        $query->orderBy($sortBy, $sortDirection);
    }
}
