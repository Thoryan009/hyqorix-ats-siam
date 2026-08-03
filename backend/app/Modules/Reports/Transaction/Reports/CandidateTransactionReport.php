<?php

namespace App\Modules\Reports\Transaction\Reports;
use App\Modules\Reports\Transaction\Contracts\TransactionReportContract;
use App\Modules\Reports\Transaction\Repositories\TransactionReportRepository;
use App\Modules\Reports\Transaction\Resources\TransactionReportResource;
class CandidateTransactionReport implements TransactionReportContract
{
    public function __construct(
        protected TransactionReportRepository $repository
    ) {}

    public function generate(array $filters)
    {
        $query = $this->repository->baseQuery($filters);
        $query = $this->repository->filterByPayer($query, 'candidate');

        $summaryQuery = clone $query;

        $transactions = $query->get();

        $totalAmount = $summaryQuery->sum('total_amount');
        $totalPaidAmount = $summaryQuery->sum('paid_amount');
        $totalDueAmount = $totalAmount - $totalPaidAmount;

        return response()->json([
            'data' => TransactionReportResource::collection($transactions),
            'summary' => [
                'total_amount'      => $totalAmount,
                'total_paid_amount' => $totalPaidAmount,
                'total_due_amount'  => $totalDueAmount,
            ]
        ]);
    }
}

