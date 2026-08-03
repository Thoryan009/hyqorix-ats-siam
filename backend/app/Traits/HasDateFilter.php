<?php

namespace App\Traits;
use Illuminate\Database\Eloquent\Builder;

trait HasDateFilter
{
    protected function applyDateFilter(Builder $query, array $filters): void
    {
        if (!empty($filters['from_date'])) {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }
    }
}
