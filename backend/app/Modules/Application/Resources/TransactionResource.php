<?php

namespace App\Modules\Application\Resources;

use App\Modules\Application\Helpers\ApplicationPresenter;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Shared\Helpers\DateTimeFormatter;
use App\Modules\JobList\Helpers\JobListPayerHelper;

class TransactionResource extends JsonResource
{
    public function toArray($request): array
    {
        $application = $this->application;
        $job = $application?->jobList;
        $workOrder = $job?->workOrder;
        $client = $workOrder?->client?->user;

        $totalPaid = $application?->totalPaidAmount() ?? 0;
        $totalPaidUsd = $application?->totalPaidAmountUsd() ?? 0;
        $discount = $application?->discount_amount ?? 0;

        $payers = JobListPayerHelper::resolveForApplication($application);
        $usesUsd = JobListPayerHelper::usesUsdFormatting($payers);
        $usesCombined = JobListPayerHelper::usesCombinedAmounts($payers);

        $totalAmount = $usesCombined
            ? ($this->total_amount + $this->total_amount_usd)
            : ($usesUsd ? $this->total_amount_usd : $this->total_amount);

        // ✅ Fee Categories prepared BEFORE return
        $feeCategories = $workOrder?->workOrderDetails?->groupBy(fn($detail) => $detail->orderDetailsHead?->orderDetailsCategory?->name);

        return [
            'id' => $this->id,

            // Transaction
            'bill_no' => $this->bill_no,
            'total_amount' => $this->total_amount,
            'total_amount_usd' => $this->total_amount_usd,
            'total_amount_formatted' => $usesUsd ? '$' . $this->total_amount_usd : '৳' . $this->total_amount,
            'transaction_id' => $this->transaction_id,
            'total_paid_amount' => $usesUsd ? $totalPaidUsd : $totalPaid,
            'total_paid_amount_formatted' => $usesUsd ? '$' . $totalPaidUsd : '৳' . $totalPaid,
            'paid_amount' => $this->paid_amount,
            'paid_amount_usd' => $this->paid_amount_usd,
            'paid_amount_formatted' => $usesUsd ? '$' . $this->paid_amount_usd : '৳' . $this->paid_amount,
            'discount_amount' => $usesUsd ? '$' . $this->discount_amount : '৳' . $this->discount_amount,
            'due_amount' => $usesUsd ? '$' . ($this->total_amount_usd - $totalPaidUsd) : '৳' . ($this->total_amount - $totalPaid - $discount),
            'payment_method' => $this->payment_method,
            'status' => $this->status,
            'payment_date' => $this->payment_date,
            'payment_date_formatted' => DateTimeFormatter::formatDate($this->payment_date),
            'payment_time' => $this->payment_time,
            'payment_time_formatted' => DateTimeFormatter::formatTime($this->payment_time),
            'payment_date_time_formatted' => DateTimeFormatter::formatDate($this->payment_date) . ' ' . DateTimeFormatter::formatTime($this->payment_time),
            'remarks' => $this->remarks,
            'job_list' => $this->application?->jobList?->name,
            'work_order_id' => $this->application?->jobList?->workOrder?->work_order_id,
            'client_name' => $this->application?->jobList?->workOrder?->client?->user->name,

            // Application
            'application_id' => $this->application_id,
            'applied_job' => $job?->name,
            'payer' => $payers,
            'payer_label' => JobListPayerHelper::label($payers),
            'payer_name' => ApplicationPresenter::fullName($application?->sur_name, $application?->given_name),
            'payer_mobile' => $application?->mobile,
            'payer_email' => $application?->email,
            'payer_application_id' => $application?->application_id,
            'work_order' => $workOrder?->work_order_id,
            'client' => $client?->name,
            'passport_no' => $this->application?->passport_no,


            // Bill Transactions
            'bill_transactions' => $this->billTransactions->map(function ($transaction) use ($job, $usesUsd) {
                $app = $transaction->application;
                $discount = $app?->discount_amount ?? 0;

                return [
                    'id' => $transaction->id,
                    'transaction_id' => $transaction->transaction_id,
                    'payer_name' => ApplicationPresenter::fullName($app?->sur_name, $app?->given_name),
                    'payer_application_id' => $app?->application_id,
                    'bill_no' => $transaction->bill_no,
                    'total_amount' => $usesUsd ? '$' . $transaction->total_amount_usd : '৳' . $transaction->total_amount,
                    'discount_amount' =>  '৳' . $transaction->discount_amount,
                    'paid_amount' => $usesUsd ? '$' . $transaction->paid_amount_usd : '৳' . $transaction->paid_amount,
                    'due_amount' => $usesUsd ? '$' . ($transaction->total_amount_usd - $transaction->paid_amount_usd) : '৳' . ($transaction->total_amount - $transaction->paid_amount - $transaction->discount_amount),
                    'payment_method' => $transaction->payment_method,
                    'status' => $transaction->status,
                    'payment_date_formatted' => DateTimeFormatter::formatDate($transaction->payment_date),
                    'payment_time_formatted' => DateTimeFormatter::formatTime($transaction->payment_time),
                    'remarks' => $transaction->remarks,
                    'created_at' => DateTimeFormatter::formatDateTime($transaction->created_at),
                    'updated_at' => DateTimeFormatter::formatDateTime($transaction->updated_at),
                    'created_by' => $transaction->createdBy ? $transaction->createdBy->name : null,
                    'updated_by' => $transaction->updatedBy ? $transaction->updatedBy->name : null,
                ];
            }),

            // Fees
            'fees' => $feeCategories
                ? $feeCategories
                    ->map(function ($details, $category) {
                        return [
                            'fee_category' => $category,
                            'items' => $details->map(
                                fn($detail) => [
                                    'fee_name' => $detail->orderDetailsHead?->name,
                                    'amount' => $detail->amount,
                                ],
                            ),
                        ];
                    })
                    ->values()
                : [],

            'created_at' => DateTimeFormatter::formatDateTime($this->created_at),
            'updated_at' => DateTimeFormatter::formatDateTime($this->updated_at),
            'created_by' => $this->createdBy ? $this->createdBy->name : null,
            'updated_by' => $this->updatedBy ? $this->updatedBy->name : null,
        ];
    }
}
