<?php

namespace App\Modules\Vendor\Repositories;

use App\Modules\Vendor\Models\VendorType;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;

class VendorTypeRepository extends BaseRepository
{
    public function __construct(VendorType $model)
    {
        parent::__construct($model);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        $this->applySearch($query, $filters['search'] ?? null);
        $this->applyStatusFilter($query, $filters['status'] ?? null);
        $this->applyDateFilter($query, $filters);
    }

    protected function applySearch(Builder $query, ?string $search): void
    {
        if (!$search) {
            return;
        }

        $query->where(function (Builder $q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%");
        });
    }

    protected function applyStatusFilter(Builder $query, $status): void
    {
        if ($status === null || $status === '') {
            return;
        }

        $query->where('status', $status);
    }

    protected function applyOrder(Builder $query, array $filters): void
    {
        $query->orderBy('sort_order', 'asc')->orderBy('name', 'asc');
    }
}
