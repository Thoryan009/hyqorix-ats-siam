<?php

namespace App\Modules\Application\Resources;

use App\Modules\Application\Helpers\ApplicationPresenter;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Shared\Helpers\DateTimeFormatter;
use App\Modules\JobList\Helpers\JobListPayerHelper;

class ClientBillResource extends JsonResource
{
    public function toArray($request): array
    {
        $payers = JobListPayerHelper::resolveForApplication($this->application);

        return [
            'id' => $this->id,

            // Transaction-specific
            'bill_no'        => $this->bill_no,
            'total_amount_usd'   => '$' . $this->total_amount_usd,
            'due_amount_usd'     => '$' . ($this->total_amount_usd - $this->application->totalPaidAmountUsd() - $this->application->discount_amount_usd),
            'status'         => $this->status,
            'job' => $this->application->jobList->name ?? null,
            'job_id' => $this->application->jobList->id,
            //client Information
            'client_name' => $this->application->jobList->workOrder->client->user->name ?? null,
            'client_id' => $this->application->jobList->workOrder->client->id ?? null,
            'client_unique_id' => $this->application->jobList->workOrder->client->client_id ?? null,
            'client_email' => $this->application->jobList->workOrder->client->user->email ?? null,
            'client_phone' => $this->application->jobList->workOrder->client->user->phone ?? null,
            'client_country' => $this->application->jobList->workOrder->client->country->name ?? null,

            // Related application

            //Work-order Information
            'work_order_primary_id' => $this->application->jobList->workOrder->id ?? null,
            'work_order_id' => $this->application->jobList->workOrder->work_order_id ?? null,
            'work_order_candidates' => $this->application->joblist->workOrder->candidates ?? null,
            'work_order_end_date' => $this->application->joblist->workOrder->end_date ?? null,
            'work_order_end_date_formatted' => DateTimeFormatter::formatDate($this->application->joblist->workOrder->end_date) ?? null,
            'work_order_price_usd' => $this->application->joblist->client_commission_per_candidate ?? null,

            'application_id' => $this->application_id,
            'payer' => $payers,
            'payer_label' => JobListPayerHelper::label($payers),
            'payer_name'    => ApplicationPresenter::fullName($this->application->sur_name, $this->application->given_name),
            'payer_mobile'   => $this->application->mobile,
            'payer_application_id' => $this->application->application_id,



            'created_at' => DateTimeFormatter::formatDateTime($this->created_at),
            'updated_at' => DateTimeFormatter::formatDateTime($this->updated_at),
            'created_by' => $this->createdBy ? $this->createdBy->name : null,
            'updated_by' => $this->updatedBy ? $this->updatedBy->name : null,
            'created' => $this->createdBy
                ? $this->createdBy->name . ' at ' . DateTimeFormatter::formatDate($this->created_at)
                : null,

            'updated' => $this->updatedBy
                ? $this->updatedBy->name . ' at ' . DateTimeFormatter::formatDate($this->updated_at)
                : null,
        ];
    }
}
