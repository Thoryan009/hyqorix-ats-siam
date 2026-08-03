<?php

namespace App\Modules\Reports\Transaction\Resources;

use App\Modules\Application\Helpers\ApplicationPresenter;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Shared\Helpers\DateTimeFormatter;
use App\Modules\JobList\Helpers\JobListPayerHelper;
class TransactionReportResource extends JsonResource
{
    public function toArray($request)
    {
        $payers = JobListPayerHelper::resolveForApplication($this->application);
        $usesUsd = JobListPayerHelper::usesUsdFormatting($payers);

        return [
            'transaction_id'   => $this->transaction_id,
            'bill_no'          => $this->bill_no,
            'payment_method'   => $this->payment_method,
            'status'           => $this->status,

            'total_amount'     => $usesUsd ? '$' . $this->total_amount_usd : '৳' . $this->total_amount,
            'paid_amount'      => $usesUsd ? '$' . $this->paid_amount_usd : '৳' . $this->paid_amount,
            'discount_amount'  => $this->discount_amount,

            'payment_date'     => DateTimeFormatter::formatDate($this->payment_date),
            'payment_time'     => $this->payment_time,

            'candidate_name'   => ApplicationPresenter::fullName($this->application->sur_name, $this->application->given_name),
            'candidate_mobile'           => $this->application?->mobile,
            'job'              => $this->application?->jobList?->name,
            'work_order_id'       => $this->application?->jobList?->workOrder?->work_order_id,
            'payer'            => $payers,
            'payer_label'      => JobListPayerHelper::label($payers),
            'client'           => $this->application?->jobList?->workOrder?->client?->user?->name,

            'created_at'       => DateTimeFormatter::formatDateTime($this->created_at),
        ];
    }
}
