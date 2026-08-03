<?php

namespace App\Modules\Application\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Shared\Helpers\DateTimeFormatter;
use App\Modules\JobList\Helpers\JobListPayerHelper;

class ApplicationTransactionResource extends JsonResource
{
    public function toArray($request): array
    {
        $payers = JobListPayerHelper::resolveForApplication($this);

        return [
            'id' => $this->id,

            'full_name' => trim($this->sur_name . ' ' . $this->given_name),
            'payer' => $payers,
            'payer_label' => JobListPayerHelper::label($payers),
            'client' => $this->jobList->workOrder->client->user->name ?? null,
            'email' => $this->email,
            'agent' => $this->agent->user->name ?? null,
            'mobile' => $this->mobile,
            'job' => $this->jobList->name ?? null,
            'application_id' => $this->application_id,
            'work_order_id' => $this->jobList->workOrder->work_order_id ?? null,
            'application_price' => $this->jobList->price ?? null,
            'discount_amount' => $this->discount_amount,
            'created_by' => $this->createdBy ? $this->createdBy->name : null,
            'updated_by' => $this->updatedBy ? $this->updatedBy->name : null,

            'transaction_history' => (function () {

                // 1️⃣ Calculate FINAL due per bill
                $finalDueByBill = [];

                foreach ($this->transactions as $transaction) {
                    $billNo = $transaction->bill_no;

                    if (!isset($finalDueByBill[$billNo])) {
                        $finalDueByBill[$billNo] = $transaction->total_amount;
                    }

                    $finalDueByBill[$billNo] -= $transaction->paid_amount + $transaction->discount_amount;
                }

                // 2️⃣ Build transaction history with running paid
                $paidSoFarByBill = [];
                $paidSoFarByBillWithDiscount = [];

                return $this->transactions->map(function ($transaction) use (
                    &$paidSoFarByBill,
                    &$paidSoFarByBillWithDiscount,
                    $finalDueByBill
                ) {

                    $billNo = $transaction->bill_no;

                    if (!isset($paidSoFarByBill[$billNo])) {
                        $paidSoFarByBill[$billNo] = 0;
                    }

                    if (!isset($paidSoFarByBillWithDiscount[$billNo])) {
                        $paidSoFarByBillWithDiscount[$billNo] = 0;
                    }

                    $previousPaid = $paidSoFarByBill[$billNo];
                    $currentPaidWithDiscount  = $transaction->paid_amount + $transaction->discount_amount;
                    $currentPaid  = $transaction->paid_amount;

                    $paidSoFarByBillWithDiscount[$billNo] += $currentPaidWithDiscount;
                    $paidSoFarByBill[$billNo] += $currentPaid;

                    $dueAfterThisTxn =
                        $transaction->total_amount - $paidSoFarByBillWithDiscount[$billNo];

                    return [
                        'bill_no'      => $billNo,
                        'total_amount' => $transaction->total_amount,
                        'discount_amount' => $transaction->discount_amount ?? 0,

                        'paid_amount'  => $previousPaid > 0
                            ? "{$previousPaid} + {$currentPaid}"
                            : (string) $currentPaid,

                        // per-transaction due
                        'due_amount'   => $dueAfterThisTxn,

                        // 🔥 latest due (same for all rows of same bill)
                        'current_due'  => $finalDueByBill[$billNo],

                        'type'           => $transaction->type,
                        'payment_method' => $transaction->payment_method,
                        'status'         => $transaction->status,

                        'payment_date' => $transaction->payment_date,
                        'payment_time' => $transaction->payment_time,

                        'payment_date_formatted' =>
                        DateTimeFormatter::formatDate($transaction->payment_date),

                        'payment_time_formatted' =>
                        DateTimeFormatter::formatTime($transaction->payment_time),

                        'payment_date_time_formatted' =>
                        DateTimeFormatter::formatDate($transaction->payment_date)
                            . ' '
                            . DateTimeFormatter::formatTime($transaction->payment_time),

                        'remarks' => $transaction->remarks,
                    ];
                });
            })(),



        ];
    }
}
