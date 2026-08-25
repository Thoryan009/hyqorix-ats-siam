<?php

namespace App\Modules\Journals\Repositories;

use App\Modules\Journals\Models\Journal;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;

class JournalRepository extends BaseRepository
{
    public function __construct(Journal $model)
    {
        parent::__construct($model);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        $this->applySearch($query, $filters['search'] ?? null);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['transaction_type'])) {
            $query->where('transaction_type', $filters['transaction_type']);
        }

        if (!empty($filters['from_date'])) {
            $query->whereDate('voucher_date', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->whereDate('voucher_date', '<=', $filters['to_date']);
        }
    }

    protected function applyEagerLoads(Builder $query, array $filters): void
    {
        $query->with([
            'party:id,code,name,type',
            'transactionType:id,code,name',
            'createdBy:id,name',
            'lines' => function ($lineQuery) {
                $lineQuery->orderBy('sort_order')->orderBy('id');
            },
            'lines.account:id,code,name',
        ]);
    }

    protected function applyOrder(Builder $query, array $filters): void
    {
        $query->orderByDesc('voucher_date')->orderByDesc('id');
    }

    protected function applySearch(Builder $query, ?string $search): void
    {
        if (!$search) {
            return;
        }

        $search = trim($search);

        $query->where(function (Builder $q) use ($search) {
            $q->where('voucher_no', 'like', "%{$search}%")
                ->orWhere('transaction_type', 'like', "%{$search}%")
                ->orWhere('reference_no', 'like', "%{$search}%")
                ->orWhere('narration', 'like', "%{$search}%")
                ->orWhereHas('party', function (Builder $partyQuery) use ($search) {
                    $partyQuery->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                })
                ->orWhereHas('lines.account', function (Builder $accountQuery) use ($search) {
                    $accountQuery->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
        });
    }
}
