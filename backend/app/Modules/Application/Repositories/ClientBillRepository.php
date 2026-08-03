<?php

namespace App\Modules\Application\Repositories;

use Illuminate\Database\Eloquent\Builder;
use App\Modules\Application\Models\Transaction;
use App\Modules\Setting\Models\Setting;
use App\Repositories\BaseRepository;
use App\Modules\JobList\Helpers\JobListPayerHelper;

class ClientBillRepository extends BaseRepository
{

    public function __construct(Transaction $model)
    {
        $this->model = $model;
    }

    protected function baseQuery(): Builder
    {
        return $this->model->newQuery()
            ->whereHas(
                'application',
                fn ($q) => JobListPayerHelper::scopeWhereApplicationResponsible($q, JobListPayerHelper::CLIENT)
            )
            ->with(['application.jobList']);
    }

    public function applyFilters(Builder $query, array $filters): void
    {
        $this->applyApplicationSearch($query, $filters['search'] ?? null);
        $this->applyClientBillFilter($query, $filters ?? null);
        $this->applyDateFilter($query, $filters);
    }

    protected function applyApplicationSearch(
        Builder $query,
        ?string $search
    ): void {
        if (!$search) {
            return;
        }
        $query->whereHas('application', function ($q) use ($search) {
            $q->where(function ($innerQ) use ($search) {
                foreach ($this->applicationSearchFields as $field) {
                    $innerQ->orWhere($field, 'like', "%{$search}%");
                }
            });
        });
    }

    public function getBillFilters(): array
    {
        $baseQuery = $this->baseQuery()->whereIn('status', ['bill-generated', 'invoice-generated', 'invoice-sent', 'paid', 'cancelled']);

        return [
            [
                'label' => 'All',
                'value' => 'all',
                'count' => (clone $baseQuery)->count(),
            ],
            [
                'label' => 'Bill Generated',
                'value' => 'bill-generated',
                'count' => (clone $baseQuery)->where('status', 'bill-generated')->count(),
            ],
            [
                'label' => 'Invoice Generated',
                'value' => 'invoice-generated',
                'count' => (clone $baseQuery)->where('status', 'invoice-generated')->count(),
            ],
            [
                'label' => 'Invoice Sent',
                'value' => 'invoice-sent',
                'count' => (clone $baseQuery)->where('status', 'invoice-sent')->count(),
            ],
            [
                'label' => 'Invoice Collected',
                'value' => 'paid',
                'count' => (clone $baseQuery)->where('status', 'paid')->count(),
            ],
            // [
            //     'label' => 'Invoice Cancelled',
            //     'value' => 'cancelled',
            //     'count' => (clone $baseQuery)->where('status', 'cancelled')->count(),
            // ],
        ];
    }
    protected array $applicationSearchFields = [
        'sur_name',
        'given_name',
        'email',
        'mobile',
        'application_id',
    ];

    /**
     * Optional filter for Job_id
     */
    protected function applyClientBillFilter(Builder $query, ?array $filters): void
    {
        $jobListId = $filters['job_list_id'] ?? null;
        $billNo = $filters['bill_no'] ?? null;
        if ($jobListId) {
            $query->whereHas('application.jobList', function ($q) use ($jobListId) {
                $q->where('id', $jobListId);
            });
        }
        if ($billNo) {
            $query->where('bill_no', operator: $billNo);
        }

        $transactionStatus = $filters['transaction_status'] ?? null;
        if ($transactionStatus !== 'all') {
            $query->where('status', $transactionStatus);
        } else {
            $query->whereIn('status', ['bill-generated', 'invoice-generated', 'invoice-sent', 'paid', 'cancelled']);
        }
    }


    /* ================= Helper Methods ================= */


    public function getInvoiceSettings(): array
    {
        // Fetch settings from the database or configuration
        return Setting::first();
    }
}
