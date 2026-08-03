<?php

namespace App\Modules\Reports\Transaction\Services;
use App\Modules\Reports\Transaction\Reports\{
    CandidateTransactionReport,
    ClientTransactionReport
};
use Illuminate\Support\Facades\Cache;
use App\Modules\Reports\Transaction\Repositories\TransactionReportRepository;
use App\Modules\Reports\Transaction\Resources\TransactionReportResource;
use InvalidArgumentException;

class TransactionReportService
{
    public function resolve(string $type)
    {
        return match ($type) {
            'candidate' => app(CandidateTransactionReport::class),
            'client'    => app(ClientTransactionReport::class),
            default     => throw new InvalidArgumentException('Invalid transaction report type'),
        };
    }

     public function getAllTransactionsReport(array $filters)
    {
        $repository = app(TransactionReportRepository::class);
        $query = $repository->baseQuery($filters);
        // Clone query for summary calculation
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

    public function getRecentDashboardDataWithSummary()
    {
        $cacheKey = 'transaction_dashboard_data';

        return Cache::remember($cacheKey, now()->addMinutes(10), function () {
            $repository = app(TransactionReportRepository::class);

            $baseQuery = $repository->baseQuery([]);

            // ✅ Candidate Query (BDT)
            $candidateQuery = $repository->filterByPayer(clone $baseQuery, 'candidate');

            $candidateCount = (clone $candidateQuery)->count();

            $candidateTotalAmount = (clone $candidateQuery)->sum('total_amount');
            $candidatePaidAmount = (clone $candidateQuery)->sum('paid_amount');
            $candidateDiscount = (clone $candidateQuery)->sum('discount_amount');

            $candidateDueAmount = $candidateTotalAmount - $candidatePaidAmount - $candidateDiscount;

            // ✅ Client Query (USD)
            $clientQuery = $repository->filterByPayer(clone $baseQuery, 'client');

            $clientCount = $clientQuery->count();
            $clientTotalAmount = (clone $clientQuery)->sum('total_amount_usd');
            $clientPaidAmount = (clone $clientQuery)->sum('paid_amount_usd');
            $clientDueAmount = $clientTotalAmount - $clientPaidAmount;

            // ✅ Total Count
            $totalTransactions = $candidateCount + $clientCount;

            // ✅ Recent Transactions
            $recentTransactions = (clone $baseQuery)
                ->latest()
                ->take(10)
                ->get();

            return (object)[
                // counts
                'total_candidate_transactions_count'  => $candidateCount,
                'total_client_transactions_count'     => $clientCount,
                'total_transactions_count'            => $totalTransactions,

                // ✅ candidate summary
                'candidate_total_amount'       => $candidateTotalAmount,
                'candidate_total_paid_amount'  => $candidatePaidAmount,
                'candidate_total_due_amount'   => $candidateDueAmount,

                // ✅ client summary (USD)
                'client_total_amount'       => $clientTotalAmount,
                'client_total_paid_amount'  => $clientPaidAmount,
                'client_total_due_amount'   => $clientDueAmount,

                'recent_transactions' => $recentTransactions,
            ];
        });
    }

}


