<?php

namespace App\Modules\Journals\Repositories;

use App\Modules\Journals\Models\JournalTransactionType;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;

class JournalTransactionTypeRepository extends BaseRepository
{
    public function __construct(JournalTransactionType $model)
    {
        parent::__construct($model);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        $this->applySearch($query, $filters['search'] ?? null);

        if (!empty($filters['status'])) {
            $query->where('status', $this->normalizeStatus($filters['status']));
        }
    }

    protected function applyEagerLoads(Builder $query, array $filters): void
    {
        $query->with(['createdBy:id,name', 'updatedBy:id,name']);
    }

    protected function applyOrder(Builder $query, array $filters): void
    {
        $query->orderBy('sort_order')->orderBy('name');
    }

    protected function applySearch(Builder $query, ?string $search): void
    {
        if (!$search) {
            return;
        }

        $search = trim($search);

        $query->where(function (Builder $q) use ($search) {
            $q->where('code', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%");
        });
    }

    private function normalizeStatus(string $status): string
    {
        return strtolower($status) === 'inactive' ? 'inactive' : 'active';
    }
}
