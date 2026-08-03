<?php

namespace App\Modules\Client\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Shared\Helpers\DateTimeFormatter;
use App\Modules\Application\Helpers\ApplicationPresenter;

class SingleClientResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,

            // Client-specific
            'client_id' => $this->client_id,

            'name' => $this->user->name,
            'email' => $this->user->email,
            'phone' => $this->user->phone,
            'type' => $this->user->type,
            'country' => $this->country->name,
            'country_id' => $this->country_id,
            'work_orders' => $this->workOrders->map(function ($workOrder) {
                return [
                    'id' => $workOrder->id,
                    'work_order_id' => $workOrder->work_order_id,
                    'candidates' => $workOrder->candidates,
                    'end_date' => $workOrder->end_date,
                    'end_date_formatted' => DateTimeFormatter::formatDate($workOrder->end_date),
                    'price' => $workOrder->price,
                    'price_usd' => $workOrder->price_usd,
                    'client_id' => $workOrder->client_id,
                    'client' => $workOrder->client->user->name,
                    'created_at' => DateTimeFormatter::formatDateTime($workOrder->created_at),
                    'updated_at' => DateTimeFormatter::formatDateTime($workOrder->updated_at),
                    'created_by' => $workOrder->createdBy ? $workOrder->createdBy->name : null,
                    'updated_by' => $workOrder->updatedBy ? $workOrder->updatedBy->name : null,
                ];
            }),

            'job_lists' => $this->workOrders->flatMap(function ($workOrder) {
                return $workOrder->jobLists->map(function ($jobList) {
                    return [
                        'id' => $jobList->id,
                        'name' => $jobList->name,
                        'job_code' => $jobList->job_code,
                        'vacancy' => $jobList->vacancy,
                        'experience' => $jobList->experience,
                        'min_age' => $jobList->min_age,
                        'max_age' => $jobList->max_age,
                        'price' => $jobList->price,
                        'client_commission_per_candidate' => $jobList->client_commission_per_candidate,
                        'contract_length' => $jobList->contract_length,
                        'description' => $jobList->description,
                        'qualification' => $jobList->qualification,
                        'language' => $jobList->language,
                        'salary' => $jobList->salary,
                        'deadline' => $jobList->deadline,
                        'interview_date' => $jobList->interview_date,
                        'status' => $jobList->status,
                        'work_order_id' => $jobList->work_order_id,
                        'work_order' => $jobList->workOrder->work_order_id ?? null,
                        'created_at' => DateTimeFormatter::formatDateTime($jobList->created_at),
                        'updated_at' => DateTimeFormatter::formatDateTime($jobList->updated_at),
                        'created_by' => $jobList->createdBy ? $jobList->createdBy->name : null,
                        'updated_by' => $jobList->updatedBy ? $jobList->updatedBy->name : null,
                    ];
                });
            }),

            'applications' => $this->workOrders->flatMap(function ($workOrder) {
                return $workOrder->jobLists->flatMap(function ($jobList) {
                    return $jobList->applications->map(function ($application) {
                        return [
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

                            'driving_license_url' => $this->driving_license_url,
                            'driving_license_link' => $this->driving_license_link,

                            'visa_copy_url' => $this->visa_copy_url,
                            'visa_copy_link' => $this->visa_copy_link,

                            'immigration_clearance_url' => $this->immigration_clearance_url,
                            'immigration_clearance_link' => $this->immigration_clearance_link,
                            
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
                });
            }),
            'client_mails' => $this->clientMails
                ? $this->clientMails->map(function ($mail) {
                    return [
                        'id' => $mail->id,
                        'bill_no' => $mail->bill_no,
                        'to_mail' => $mail->to_mail,
                        'subject' => $mail->subject,
                        'body' => $mail->body,
                        'invoice_path' => $mail->invoice_path,
                        'sent_at' => DateTimeFormatter::formatDateTime($mail->sent_at),
                        'created_at' => DateTimeFormatter::formatDateTime($mail->created_at),
                        'updated_at' => DateTimeFormatter::formatDateTime($mail->updated_at),
                        'created_by' => $mail->createdBy ? $mail->createdBy->name : null,
                        'updated_by' => $mail->updatedBy ? $mail->updatedBy->name : null,
                    ];
                })
                : null,

            'client_transactions' => $this->clientTransactions
                ? $this->clientTransactions->map(function ($transaction) {
                    return [
                        'id' => $transaction->id,
                        'bill_no' => $transaction->bill_no,
                        'amount' => $transaction->amount,
                        'status' => $transaction->status,
                        'created_at' => DateTimeFormatter::formatDateTime($transaction->created_at),
                        'updated_at' => DateTimeFormatter::formatDateTime($transaction->updated_at),
                        'created_by' => $transaction->createdBy ? $transaction->createdBy->name : null,
                        'updated_by' => $transaction->updatedBy ? $transaction->updatedBy->name : null,
                    ];
                })
                : null,

            'created_at' => DateTimeFormatter::formatDateTime($this->created_at),
            'updated_at' => DateTimeFormatter::formatDateTime($this->updated_at),
            'created_by' => $this->createdBy ? $this->createdBy->name : null,
            'updated_by' => $this->updatedBy ? $this->updatedBy->name : null,
        ];
    }
}
