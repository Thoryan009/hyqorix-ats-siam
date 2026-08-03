<?php

namespace App\Modules\Application\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Shared\Helpers\DateTimeFormatter;

class SingleClientBillResource extends JsonResource
{
    public function toArray($request): array
    {
        $transactions = $this['transactions'];
        $job = $this['job'];


        // ✅ Fee Categories prepared BEFORE return
        $feeCategories = $job?->jobListDetails
            ?->groupBy(fn($detail) => $detail->jobListDetailsHead?->jobListDetailsCategory?->name);

        return [
            'bill_no' => $this['bill_no'],
            'work_order_primary_id' => $job->workOrder->id ?? null,
            'status' => 'bill-generated', // You can adjust this based on your actual status logic
            'status_label' => 'Bill Generated', // You can adjust this based on your actual status logic
            // Work Order Info
            'work_order_id' => $job->workOrder->work_order_id ?? null,
            'work_order_candidates' => $job->workOrder->candidates ?? null,
            'work_order_end_date' => $job->workOrder->end_date ?? null,
            'work_order_end_date_formatted' =>
                $job->workOrder->end_date
                    ? DateTimeFormatter::formatDate($job->workOrder->end_date)
                    : null,
            'work_order_price_usd' => $job->client_commission_per_candidate ?? null,

             // Fees
            'fees' => $feeCategories
                ? $feeCategories->map(function ($details, $category) {
                    return [
                        'fee_category' => $category,
                        'items' => $details->map(fn($detail) => [
                            'fee_name' => $detail->jobListDetailsHead?->name,
                            'amount'   =>  $detail->amount_usd,
                        ]),
                    ];
                })->values()
                : [],


            // Client Info
            'client_name' => $job->workOrder->client->user->name ?? null,
            'client_id' => $job->workOrder->client->client_id ?? null,
            'client_email' => $job->workOrder->client->user->email ?? null,
            'client_phone' => $job->workOrder->client->user->phone ?? null,
            'client_country' => $job->workOrder->client->country->name ?? null,

            // Transaction Summary
            'total_transactions' => $transactions->count(),
            'total_amount' => $transactions->sum('total_amount'),

            // Transactions
            'transactions' => $transactions->map(function ($transaction) {
                return [
                    'transaction_id' => $transaction->transaction_id,
                    'total_amount' => $transaction->total_amount,
                    'paid_amount' => $transaction->paid_amount,
                    'due_amount' => $transaction->total_amount - $transaction->paid_amount,
                    'candidate_name' => $transaction->application->sur_name . ' ' . $transaction->application->given_name,
                    'candidate_application_id' => $transaction->application->application_id,
                    'applied_position' => $transaction->application->jobList->name ?? null,
                     'applied_position_code' => $transaction->application->jobList->job_code ?? null,
                ];
            }),

            // positions and applications summary
            'positions_summary' => $transactions->groupBy(fn($t) => $t->application->jobList->name ?? 'Unknown')->map(function ($group, $position) {
                return [
                    'position' => $position,
                    'candidates_count' => $group->count(),
                ];
            })->values(),
            // send position summary in a paragraph Ex Backend Developer (3), Frontend Developer (2)
            'positions_summary_paragraph' => $transactions->groupBy(fn($t) => $t->application->jobList->name ?? 'Unknown')->map(function ($group, $position) {
                return "{$position} ({$group->count()})";
            })->implode(', '),

        ];
    }
}
