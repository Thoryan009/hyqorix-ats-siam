<?php

namespace App\Modules\Application\Resources;

use App\Modules\Application\Helpers\ApplicationPresenter;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Shared\Helpers\DateTimeFormatter;
use App\Modules\JobList\Helpers\JobListPayerHelper;

class ApplicationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'application_id' => substr($this->application_id, 4), // Shortened application ID

            'given_name' => $this->given_name,
            'sur_name' => $this->sur_name,
            'marital_status' => $this->marital_status,
            'full_name' => ApplicationPresenter::fullName($this->given_name, $this->sur_name),

            'worker_image_url' => $this->worker_image_url,
            'worker_image_link' => $this->worker_image_link,

            'date_of_birth' => $this->date_of_birth,
            'age' => $this->age,
            'sex' => $this->sex ,
            'nationality' => $this->nationality,
            'bd_exp' => strtolower($this->bd_exp),
            'overseas_exp' => strtolower($this->overseas_exp),
            'language' => $this->language,
            'height' => $this->height,
            'weight' => $this->weight,

            'mobile' => str_starts_with($this->mobile, '+88') ? substr($this->mobile, 3) : $this->mobile,
            'whatsapp_no' => $this->whatsapp_no,
            'email' => auth()->user()?->type !== 'client' ? $this->email : null,
            // 'application_id' => $this->application_id,

            'nid_link' => $this->nid_link,
            'nid_url' => $this->nid_url,
            'nid_no' => $this->nid_no,

            'father_name' => $this->father_name,
            'mother_name' => $this->mother_name,
            'address' => $this->address,

            'passport_url' => $this->passport_url,
            'passport_pdf_url' => $this->passport_pdf_url,
            'single_document_url' => $this->single_document_url,
            'passport_link' => $this->passport_link,
            'passport_no' => $this->passport_no,

            'offer_letter_link' => $this->offer_letter_link,
            'offer_letter_url' => $this->offer_letter_url,

            'acknowledgment_url' => $this->acknowledgment_url,

            'documents_url' => $this->documents_url,

            'date_of_issue' => $this->date_of_issue,
            'date_of_expiry' => $this->date_of_expiry,

            'place_of_birth' => $this->place_of_birth,

            'resume_url' => $this->resume_url,
            'resume_link' => $this->resume_link,

            'driving_license_no' => $this->driving_license_no,

            'visa_copy_url' => $this->visa_copy_url,
            'visa_copy_link' => $this->visa_copy_link,

            'svp_url' => $this->svp_url,
            'svp_link' => $this->svp_link,

            'qvp_url' => $this->qvp_url,
            'qvp_link' => $this->qvp_link,

            'ticket_url' => $this->ticket_url,
            'ticket_link' => $this->ticket_link,

            'immigration_clearance_url' => $this->immigration_clearance_url,
            'immigration_clearance_link' => $this->immigration_clearance_link,

            'education_url' => $this->education_url,
            'training_url' => $this->training_url,
            'experience_url' => $this->experience_url,
            'driving_license_url' => $this->driving_license_url,
            'status' => $this->current_process_name ?? 'hiring_list',
            'application_status' => $this->application_status,
            'raw_application_status' => $this->getRawOriginal('application_status'),
            'remarks' => $this->remarks,
            'summary' => $this->summary,
            'discount_amount' => $this->discount_amount,

            'job_list_id' => $this->job_list_id,
            'job' => $this->jobList->name . ($this->jobList->job_code ? ' (' . substr($this->jobList->job_code, 4) . ')' : '') ?? null,
            'just_job' => $this->jobList->name ?? null,
            // 'job_code' => $this->jobList->job_code ?? null,

            'work_order_id' => substr($this->jobList->workOrder->work_order_id ?? '', 3), // Shortened work order ID
            'application_price' => $this->jobList->price ?? null,
            'payer' => JobListPayerHelper::resolveForApplication($this),
            'payer_label' => JobListPayerHelper::label(JobListPayerHelper::resolveForApplication($this)),
            'applied_through' => $this->applied_through ?? ($this->agent_id ? 'agent' : 'direct_candidate'),
            'payment_responsibility' => $this->payment_responsibility ?? [],
            'payment_responsibility_label' => JobListPayerHelper::label(
                $this->payment_responsibility ?? []
            ),

            // Show CLient Name MAx 20 characters
            'client' => strlen($this->jobList->workOrder->client->user->name ?? '') > 35 ? substr($this->jobList->workOrder->client->user->name, 0, 20) . '...' : $this->jobList->workOrder->client->user->name ?? null,
            'client_id' => $this->jobList?->workOrder?->client_id
                ?? $this->jobList?->workOrder?->client?->id,
            'country_name' => $this->jobList?->workOrder?->client?->country?->name,
            'agent_id' => $this->agent?->id ?? null,
            'agent_name' => $this->agent?->user?->name ?? null,
            'subject_id' => $this->subject_id,
            'subject' => $this->subject?->name,
            'qualification_id' => $this->qualification_id,
            'qualification' => $this->qualification?->name,
            'visa_status' => $this->embassySubmission?->ksa_visa_status ?? 'pending',
            'has_embassy_submission' => ($this->embassySubmission && $this->embassySubmission->mofa_no) ? 'yes' : 'no',
            'experiences' => $this->experiences->map(function ($experience) {
                return [
                    'company_name' => $experience->company_name,
                    'position' => $experience->position,
                    'from_date' => $experience->from_date,
                    'to_date' => $experience->to_date,
                    'is_current' => $experience->is_current,
                    'types' => $experience->types,
                    'responsibilities' => $experience->responsibilities,
                ];
            }),

            'embassy_submission' => [
                'id' => $this->embassySubmission->id ?? null,
                'religion' => $this->embassySubmission->religion ?? null,
                'visa_profession_en' => $this->embassySubmission->visa_profession_en ?? null,
                'visa_profession_ar' => $this->embassySubmission->visa_profession_ar ?? null,
                'visit_work_for_ar' => $this->embassySubmission->visit_work_for_ar ?? null,
                'visit_work_for_en' => $this->embassySubmission->visit_work_for_en ?? null,
                'mofa_no' => $this->embassySubmission->mofa_no ?? null,
                'police_clearance_no' => $this->embassySubmission->police_clearance_no ?? null,
                'alwakala_no' => $this->embassySubmission->alwakala_no ?? null,
                'date_of_submission' => $this->embassySubmission->date_of_submission ?? null,
            ],

          

            'created_at' => DateTimeFormatter::formatDateTime($this->created_at),
            'updated_at' => DateTimeFormatter::formatDateTime($this->updated_at),
            'created_by' => $this->createdBy ? $this->createdBy->name : null,
            'updated_by' => $this->updatedBy ? $this->updatedBy->name : null,
        ];
    }
}
