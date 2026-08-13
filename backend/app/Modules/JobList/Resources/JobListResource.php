<?php

namespace App\Modules\JobList\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Shared\Helpers\DateTimeFormatter;
use App\Modules\Application\Helpers\ApplicationPresenter;

class JobListResource extends JsonResource
{
    public function toArray($request): array
    {
        $user = auth()->user();
        $applications = $this->applications;
        if ($user->type == 'agent') {
            $agentApplicationIds = $user->agent ? $user->agent->applications()->pluck('id')->toArray() : [];
            $applications = $applications->filter(function ($application) use ($agentApplicationIds) {
                return in_array($application->id, $agentApplicationIds);
            });
        } else if ($user->type == 'client') {

            $client = $this->workOrder->client;
            $clientApplicationIds = $client ? $client->workOrders()->with('jobLists.applications')->get()->pluck('jobLists.*.applications.*.id')->flatten()->toArray() : [];

            $applications = $applications->filter(function ($application) use ($clientApplicationIds) {
                return in_array($application->id, $clientApplicationIds);
            });
        }
        return [
            'id' => $this->id,
            'name' => $this->name,
            'job_code' => $this->job_code,
            'vacancy' => $this->vacancy,
            'experience' => $this->experience,
            'min_age' => $this->min_age,
            'max_age' => $this->max_age,
            'price' => $this->price,
            'client_commission_per_candidate' => $this->client_commission_per_candidate,
            'contract_length' => $this->contract_length,
            'description' => $this->description,
            'qualification' => $this->qualification,
            'language' => $this->language,
            'salary' => $this->salary,
            'deadline' => $this->deadline,
            'interview_date' => $this->interview_date,
            'status' => $this->status,
            'ats_applications_count' => (int) ($this->ats_applications_count ?? 0),
            'work_order_id' => $this->work_order_id,
            'work_order' => $this->workOrder->work_order_id ?? null,
             $this->mergeWhen(auth()->user()?->hasPermission('job.select_principal'), [
                'principal_id' => $this->principal_id,
                'principal_name' => $this->principal->user?->name ?? null,
            ]),

            'client_name' => $this->workOrder->client->user->name ?? null,

           

            'created_at' => DateTimeFormatter::formatDateTime($this->created_at),
            'updated_at' => DateTimeFormatter::formatDateTime($this->updated_at),
            'created_by' => $this->createdBy ? $this->createdBy->name : null,
            'updated_by' => $this->updatedBy ? $this->updatedBy->name : null,
        ];
    }
}
