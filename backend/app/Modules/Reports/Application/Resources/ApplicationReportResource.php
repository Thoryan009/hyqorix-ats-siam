<?php

namespace App\Modules\Reports\Application\Resources;

use App\Modules\Application\Helpers\ApplicationPresenter;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Shared\Helpers\DateTimeFormatter;
use Illuminate\Support\Str;
use App\Modules\JobList\Helpers\JobListPayerHelper;
class ApplicationReportResource extends JsonResource
{
    public function toArray($request)
    {
        $status = "";
        if ($this->current_process_name && $this->current_process_name !== 'N/A') {

            if ($this->currentProcess?->process?->name === 'on_boarding' && $this->currentProcess?->status === 'completed') {
                $status = 'deployed';
            }else if($this->currentProcess?->status == 'rejected'){
                $status = 'rejected';
             } else if($this->currentProcess?->status == 'declined'){
                $status = 'declined';

            } else {
                $status = $this->current_process_name;
            }
        } else {
            $status = $this->application_status ?? 'hiring_list';
        }

        return [
            'job_name'          => $this->jobList->name,
            'job_code'          => $this->jobList->job_code,
            'name'              => ApplicationPresenter::fullName($this->given_name, $this->sur_name),
            'passport_no'       => $this->passport_no,
            'mobile'            => ApplicationPresenter::localMobile($this->mobile),
            'sex'               => strtolower($this->sex),
            'email'             => $this->email,
            'payer'             => JobListPayerHelper::resolveForApplication($this),
            'payer_label'       => JobListPayerHelper::label(JobListPayerHelper::resolveForApplication($this)),
            'client'            => $this->jobList?->workOrder?->client?->user?->name,
            'client2'           => Str::limit($this->jobList?->workOrder?->client?->user?->name, 20),
            'agent'             => $this->agent?->user?->name,
            'country'           => $this->jobList?->workOrder?->client?->country?->name,
            'principal'         => $this->jobList?->principal?->user?->name,
            'principal2'        => Str::limit($this->jobList?->principal?->user?->name, 20),
            'application_id'    => $this->application_id,
            'work_order_id'     => $this->jobList->workOrder->work_order_id ?? null,
            'remarks'           => $this->currentProcess->remarks ?? null,
            'status'            => $status,
            'days'              => $this->currentProcessDays() ?? null,
            'total_process_days' => $this->totalProcessDays() ?? null,
            'created_at'        => DateTimeFormatter::formatDateTime($this->created_at),
            'updated_at'        => DateTimeFormatter::formatDateTime($this->updated_at),
        ];
    }
}
