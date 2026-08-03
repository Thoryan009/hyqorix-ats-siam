<?php

namespace App\Modules\PassportHandover\Repositories;

use App\Modules\PassportHandover\Models\PassportHandover;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;

class PassportHandoverRepository extends BaseRepository
{
    public function __construct(PassportHandover $model)
    {
        parent::__construct($model);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        $this->applySearch($query, $filters['search'] ?? null);

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['passport_no'])) {
            $passportNo = trim((string) $filters['passport_no']);
            $query->whereHas(
                'items',
                fn (Builder $itemQuery) => $itemQuery->where('passport_no', 'like', "%{$passportNo}%")
            );
        }

        if (!empty($filters['from_date'])) {
            $query->whereDate('taken_at', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->whereDate('taken_at', '<=', $filters['to_date']);
        }
    }

    protected function applyEagerLoads(Builder $query, array $filters): void
    {
        $query->withCount([
            'items as total_items_count',
            'items as collected_items_count' => fn (Builder $q) => $q->where('status', 'collected'),
            'items as rejected_items_count' => fn (Builder $q) => $q->where('status', 'rejected'),
            'items as pending_items_count' => fn (Builder $q) => $q->where('status', 'handed_over'),
        ]);
    }

    protected function applySearch(Builder $query, ?string $search): void
    {
        if (!$search) {
            return;
        }

        $search = trim($search);

        $query->where(function (Builder $q) use ($search) {
            $q->where('handover_no', 'like', "%{$search}%")
                ->orWhere('taker_name', 'like', "%{$search}%")
                ->orWhere('taker_phone', 'like', "%{$search}%")
                ->orWhereHas('items', fn (Builder $iq) => $iq->where('passport_no', 'like', "%{$search}%"));
        });
    }

    protected function applyOrder(Builder $query, array $filters): void
    {
        $allowedSortColumns = ['id', 'handover_no', 'taken_at', 'created_at', 'updated_at'];

        $sortBy = $filters['sort_by'] ?? 'id';
        if (!in_array($sortBy, $allowedSortColumns, true)) {
            $sortBy = 'id';
        }

        $sortDirection = $filters['sort_direction'] ?? 'desc';
        if (!in_array(strtolower($sortDirection), ['asc', 'desc'], true)) {
            $sortDirection = 'desc';
        }

        $query->orderBy($sortBy, $sortDirection);
    }
}
