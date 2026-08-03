<?php

namespace App\Modules\Document\Repositories;

use App\Modules\Document\Models\Document;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;

class DocumentRepository extends BaseRepository
{
    public function __construct(Document $model)
    {
        parent::__construct($model);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        $this->applySearch($query, $filters['search'] ?? null);

        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }
    }

    protected function applyEagerLoads(Builder $query, array $filters): void
    {
        $query->with(['createdBy', 'updatedBy']);
    }

    protected function applySearch(Builder $query, ?string $search): void
    {
        if (!$search) {
            return;
        }

        $search = trim($search);

        $query->where(function (Builder $q) use ($search) {
            $q->where('document_no', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%")
                ->orWhere('category', 'like', "%{$search}%");
        });
    }

    protected function applyOrder(Builder $query, array $filters): void
    {
        $query->orderByDesc('id');
    }
}
