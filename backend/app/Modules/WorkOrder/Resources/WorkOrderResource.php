<?php

namespace App\Modules\WorkOrder\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Shared\Helpers\DateTimeFormatter;


class WorkOrderResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'work_order_id' => $this->work_order_id,
            'candidates' => $this->candidates,
            'end_date' => $this->end_date,
            'end_date_formatted' => DateTimeFormatter::formatDate($this->end_date),
            'employee_id' => $this->employee_id,
            'employee' => $this->employee ? $this->employee->user->name : null,
            'client_id' => $this->client_id,
            'client' => $this->client->user->name ?? null,
            'client_name' => $this->client->user->name ?? null,
            'country' => $this->client?->country?->name ?? null,
            'client_email' => $this->client->user->email ?? null,
            'client_phone' => $this->client->user->phone ?? null,
            'jobs_count' => (int) ($this->jobs_count ?? $this->jobLists?->count() ?? 0),
            'work_order_url' => $this->work_order_url,
            'visa_issue_number' => $this->visa_issue_number,
            'sponsor_id' => $this->sponsor_id,


            // Job list info
            'job_list' => $this->jobLists->map(function ($job) {
                return [
                    'id' => $job->id,
                    'name' => $job->name,
                    'job_code' => $job->job_code,
                    'vacancy' => $job->vacancy,
                    'experience' => $job->experience,
                    'min_age' => $job->min_age,
                    'max_age' => $job->max_age,
                    'contract_length' => $job->contract_length,
                    'description' => $job->description,
                    'qualification' => $job->qualification,
                    'language' => $job->language,
                    'salary' => $job->salary,
                    'work_order_id' => $job->work_order_id,
                    'work_order' => $job->workOrder->work_order_id,
                    'created_at' => DateTimeFormatter::formatDateTime($job->created_at),
                    'updated_at' => DateTimeFormatter::formatDateTime($job->updated_at),
                    'created_by' => $job->createdBy ? $job->createdBy->name : null,
                    'updated_by' => $job->updatedBy ? $job->updatedBy->name : null,
                ];
            }),

            'created_at' => DateTimeFormatter::formatDateTime($this->created_at),
            'updated_at' => DateTimeFormatter::formatDateTime($this->updated_at),
            'created_by' => $this->createdBy ? $this->createdBy->name : null,
            'updated_by' => $this->updatedBy ? $this->updatedBy->name : null,
        ];
    }
}
