<?php

namespace App\Modules\JobList\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Shared\Helpers\DateTimeFormatter;

class JobListDetailResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'job_list_id' => $this->job_list_id,
            'job_name' => $this->jobList->name,
            'work_order_id' => $this->jobList->workOrder->work_order_id,
            'job_list_code' => $this->jobList->job_code,
            'job_list_details_head_id' => $this->job_list_details_head_id,
            'fee_name' => $this->jobListDetailsHead->name,
            'fee_category' => $this->jobListDetailsHead->jobListDetailsCategory->name,
            'amount' => $this->amount,
            'amount_usd' => $this->amount_usd,


            'created_at' => DateTimeFormatter::formatDateTime($this->created_at),
            'updated_at' => DateTimeFormatter::formatDateTime($this->updated_at),
            'created_by' => $this->createdBy ? $this->createdBy->name : null,
            'updated_by' => $this->updatedBy ? $this->updatedBy->name : null,
        ];
    }
}
