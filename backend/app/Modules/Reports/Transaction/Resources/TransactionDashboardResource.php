<?php

namespace App\Modules\Reports\Transaction\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Reports\Transaction\Resources\TransactionReportResource;

class TransactionDashboardResource extends JsonResource
{
public function toArray($request)
{
    return [
        // ✅ Candidate Summary (BDT)
        'candidate_transactions' => [
            'count' => $this->total_candidate_transactions_count,
            'total_amount' => '৳' . $this->candidate_total_amount,
            'total_paid_amount' => '৳' . $this->candidate_total_paid_amount,
            'total_due_amount' => '৳' . $this->candidate_total_due_amount,
        ],

        // ✅ Client Summary (USD)
        'client_transactions' => [
            'count' => $this->total_client_transactions_count,
            'total_amount' => '$' . $this->client_total_amount,
            'total_paid_amount' => '$' . $this->client_total_paid_amount,
            'total_due_amount' => '$' . $this->client_total_due_amount,
        ],

        // ✅ Total শুধু count রাখো (amount mix না করা better)
        'total_transactions_count' => $this->total_transactions_count,

        // ✅ Recent list
        'recent_transactions' => TransactionReportResource::collection(
            $this->recent_transactions
        ),
    ];
}
}
