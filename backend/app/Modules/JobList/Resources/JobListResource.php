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
            'details' =>  $this->jobListDetails->map(function ($detail) {
                return [
                    'id' => $detail->id,
                    'job_code' => $detail->jobList->job_code ?? null,
                    'fee_name' => $detail->jobListDetailsHead->name,
                    'fee_category' => $detail->jobListDetailsHead->jobListDetailsCategory->name,
                    'amount' => $detail->amount,
                    'amount_usd' => $detail->amount_usd,
                    'created_at' => DateTimeFormatter::formatDateTime($detail->created_at),
                    'updated_at' => DateTimeFormatter::formatDateTime($detail->updated_at),
                    'created_by' => $detail->createdBy ? $detail->createdBy->name : null,
                    'updated_by' => $detail->updatedBy ? $detail->updatedBy->name : null,
                ];
            }),
            'client_name' => $this->workOrder->client->user->name ?? null,

            'applications' => $applications->map(function ($application) {
                return [
                    'sur_name' => $application->sur_name,
                    'given_name' => $application->given_name,
                    'full_name' => ApplicationPresenter::fullName($application->sur_name, $application->given_name),
                    'date_of_birth' => $application->date_of_birth,
                    'sex' => strtolower($application->sex),
                    'nationality' => $application->nationality,
                    'qualification' => $application->qualification,
                    'bd_exp' => strtolower($application->bd_exp),
                    'overseas_exp' => strtolower($application->overseas_exp),

                    'mobile' => $application->mobile,
                    'email' => $application->email,
                    'application_id' => $application->application_id,

                    'nid_path' => $application->nid_path,
                    'nid_link' => $application->nid_link,
                    'nid_no' => $application->nid_no,

                    'driving_license_url' => $application->driving_license_url,
                    'driving_license_link' => $application->driving_license_link,

                    'visa_copy_url' => $application->visa_copy_url,
                    'visa_copy_link' => $application->visa_copy_link,

                    'immigration_clearance_url' => $application->immigration_clearance_url,
                    'immigration_clearance_link' => $application->immigration_clearance_link,

                    'passport_path' => $application->passport_path,
                    'passport_link' => $application->passport_link,
                    'passport_no' => $application->passport_no,
                    'date_of_issue' => $application->date_of_issue,
                    'date_of_expiry' => $application->date_of_expiry,

                    'place_of_birth' => $application->place_of_birth,

                    'resume_link' => $application->resume_link,
                    'resume_path' => $application->resume_path,

                    'status' => $application->currentProcess->type ?? 'hiring_list',
                    'discount_amount' => $application->discount_amount,

                    'job_list_id' => $application->job_list_id,
                    'job' => $application->jobList->name ?? null,
                    'work_order_id' => $application->jobList->workOrder->work_order_id ?? null,
                    'application_price' => $application->jobList->price ?? null,

                    'created_at' => DateTimeFormatter::formatDateTime($application->created_at),
                    'updated_at' => DateTimeFormatter::formatDateTime($application->updated_at),
                    'created_by' => $application->createdBy ? $application->createdBy->name : null,
                    'updated_by' => $application->updatedBy ? $application->updatedBy->name : null,
                ];
            }),

            'created_at' => DateTimeFormatter::formatDateTime($this->created_at),
            'updated_at' => DateTimeFormatter::formatDateTime($this->updated_at),
            'created_by' => $this->createdBy ? $this->createdBy->name : null,
            'updated_by' => $this->updatedBy ? $this->updatedBy->name : null,
        ];
    }
}
