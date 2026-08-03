<?php

namespace App\Modules\Application\Resources;

use App\Modules\Application\Helpers\ApplicationPresenter;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Shared\Helpers\DateTimeFormatter;
use App\Modules\JobList\Helpers\JobListPayerHelper;

class CandidateBillResource extends JsonResource
{
    public function toArray($request): array
    {
        $payers = JobListPayerHelper::resolveForApplication($this->application);

        return [
            'id' => $this->id,

            // Transaction-specific
            'bill_no'        => $this->bill_no,
            'total_amount'   => '৳' . $this->total_amount,
            'due_amount'     => '৳' . $this->total_amount - $this->application->totalPaidAmount() - $this->application->discount_amount,
            'status'         => $this->status,
            'job' => $this->application->jobList->name ?? null,
            'job_id' => $this->application->jobList->id,
            'client_name' => $this->application->jobList->workOrder->client->user->name ?? null,
            'work_order_id' => $this->application->jobList->workOrder->work_order_id ?? null,
            // Related application
            'application_id' => $this->application_id,
            'payer' => $payers,
            'payer_label' => JobListPayerHelper::label($payers),
            'payer_name'    => ApplicationPresenter::fullName($this->application->given_name, $this->application->sur_name),
            'payer_mobile'   => $this->application->mobile,
            'payer_application_id' => $this->application->application_id,

            'created_at' => DateTimeFormatter::formatDateTime($this->created_at),
            'updated_at' => DateTimeFormatter::formatDateTime($this->updated_at),
            'created_by' => $this->createdBy ? $this->createdBy->name : null,
            'updated_by' => $this->updatedBy ? $this->updatedBy->name : null,
        ];
    }
}
