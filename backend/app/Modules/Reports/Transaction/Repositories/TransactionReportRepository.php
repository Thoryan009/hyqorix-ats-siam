<?php

namespace App\Modules\Reports\Transaction\Repositories;
use App\Modules\Application\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;
use App\Modules\JobList\Helpers\JobListPayerHelper;
use App\Modules\JobList\Helpers\JobListPayerHelper;

class TransactionReportRepository
{
    public function baseQuery(array $filters): Builder
    {
        return Transaction::query()
            ->with([
                'application.jobList.workOrder.client.user'
            ])
            ->when($filters['work_order_id'] ?? null, function ($query, $workOrderId) {
            $query->whereHas('application.jobList.workOrder', function ($q) use ($workOrderId) {
                $q->where('id', $workOrderId);
                });
            })
            ->when($filters['job_list_id'] ?? null, function ($query, $jobListId) {
            $query->whereHas('application.jobList', function ($q) use ($jobListId) {
                $q->where('id', $jobListId);
                });
            })
            ->when($filters['client_id'] ?? null, function ($query, $clientId) {
            $query->whereHas('application.jobList.workOrder.client', function ($q) use ($clientId) {
                $q->where('id', $clientId);
                });
            })
            ->when($filters['payment_method'] ?? null,
                fn ($q, $method) => $q->where('payment_method', $method)
            )
            ->when($filters['status'] ?? null,
                fn ($q, $status) => $q->where('status', $status)
            )
            ->when($filters['payment_from'] ?? null,
                fn ($q, $from) => $q->whereDate('payment_date', '>=', $from)
            )
            ->when($filters['payment_to'] ?? null,
                fn ($q, $to) => $q->whereDate('payment_date', '<=', $to)
            );
    }

    public function filterByPayer(Builder $query, string $payer): Builder
    {
        return $query->whereHas('application', function ($q) use ($payer) {
            JobListPayerHelper::scopeWhereApplicationResponsible($q, $payer);
        });
    }

}

