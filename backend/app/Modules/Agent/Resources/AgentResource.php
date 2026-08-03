<?php

namespace App\Modules\Agent\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Shared\Helpers\DateTimeFormatter;
use App\Modules\Application\Helpers\ApplicationPresenter;

class AgentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'agent_id' => $this->agent_id,
            'address' => $this->address,
            'nid_no' => $this->nid_no,
            'points' => $this->points,
            'ats_applications_count' => (int) ($this->ats_applications_count ?? 0),
            'name' => $this->user->name,
            'email' => $this->user->email,
            'phone' => $this->user->phone,
            'whatsapp_no' => $this->user->whatsapp_no,
            'phone2' => $this->phone2,
            'agent_image_url' => $this->agent_image_url,
            'stuff_name' => $this->stuff_name,
            'stuff_phone' => $this->stuff_phone,
            'manager_name' => $this->manager_name,
            'type' => $this->user->type,
            'status' => $this->user->status,
            'status_formatted' => $this->user->status == 1 ? 'Active' : 'Inactive',
            'created_by' => $this->createdBy ? $this->createdBy->name : null,
            'updated_by' => $this->updatedBy ? $this->updatedBy->name : null,
            'role' => count($this->user->roles) > 0 ? $this->user->roles->pluck('name')->implode(', ') : null,
            'role_id' =>  count($this->user->roles) > 0 ? $this->user->roles->first()->id : null,
            'applications' => $this->whenLoaded('applications', function () {
                return $this->applications->map(function ($application) {
                    return [
                        'id' => $application->id,

                        'sur_name' => $application->sur_name,
                        'given_name' => $application->given_name,
                        'full_name' => ApplicationPresenter::fullName($application->sur_name, $application->given_name),

                        'worker_image_url' => $application->worker_image_url,
                        'worker_image_link' => $application->worker_image_link,

                        'date_of_birth' => $application->date_of_birth,
                        'sex' => strtolower($application->sex),
                        'nationality' => $application->nationality,
                        'qualification' => $application->qualification,
                        'bd_exp' => strtolower($application->bd_exp),
                        'overseas_exp' => strtolower($application->overseas_exp),
                        'language' => $application->language,

                        'mobile' => $application->mobile,
                        'email' => $application->email,
                        'application_id' => $application->application_id,

                        'nid_link' => $application->nid_link,
                        'nid_url' => $application->nid_url,
                        'nid_no' => $application->nid_no,

                        'passport_url' => $application->passport_url,
                        'passport_link' => $application->passport_link,
                        'passport_no' => $application->passport_no,

                        'offer_letter_link' => $application->offer_letter_link,
                        'offer_letter_url' => $application->offer_letter_url,

                        'acknowledgment_url' => $application->acknowledgment_url,

                        'documents_url' => $application->documents_url,

                        'date_of_issue' => $application->date_of_issue,
                        'date_of_expiry' => $application->date_of_expiry,

                        'place_of_birth' => $application->place_of_birth,

                        'resume_url' => $application->resume_url,
                        'resume_link' => $application->resume_link,

                        'status' => $application->current_process_name ?? 'hiring_list',
                        'remarks' => $application->remarks,
                        'discount_amount' => $application->discount_amount,

                        'job_list_id' => $application->job_list_id,
                        'job' => $application->jobList->name ?? null,
                        'work_order_id' => $application->jobList->workOrder->work_order_id ?? null,
                        'application_price' => $application->jobList->price ?? null,
                        'payer' => \App\Modules\JobList\Helpers\JobListPayerHelper::resolveForApplication($application),
                        'payer_label' => \App\Modules\JobList\Helpers\JobListPayerHelper::label(
                            \App\Modules\JobList\Helpers\JobListPayerHelper::resolveForApplication($application)
                        ),
                    ];
                });
            }),

            'created_at' => DateTimeFormatter::formatDateTime($this->created_at),
            'updated_at' => DateTimeFormatter::formatDateTime($this->updated_at),
        ];
    }
}
