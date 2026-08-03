<?php

namespace App\Modules\Application\Repositories;

use App\Modules\Application\Models\ClientMail;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;

class ClientMailRepository extends BaseRepository
{
    public function __construct(ClientMail $model)
    {
        parent::__construct($model);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        $this->applySearch($query, $filters['search'] ?? null);
        $this->applyDateFilter($query, $filters);
    }

    protected function applySearch(Builder $query, ?string $search): void
    {
        if (!$search) return;

        $query->where('name', 'like', "%{$search}%");
    }
}
