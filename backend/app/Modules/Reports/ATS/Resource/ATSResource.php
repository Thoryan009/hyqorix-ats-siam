<?php

namespace App\Modules\Reports\ATS\Resource;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Shared\Helpers\DateTimeFormatter;
use App\Modules\JobList\Helpers\JobListPayerHelper;

class ATSResource extends JsonResource
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

        $payers = JobListPayerHelper::resolveForApplication($this);

        return [
            'job_name'              => $this->jobList?->name,
            'work_order'            => $this->jobList?->workOrder?->work_order_id,
            'client'                => $this->jobList?->workOrder?->client?->user?->name,
            'payer'                 => $payers,
            'payer_label'           => JobListPayerHelper::label($payers),
            'candidate'             => $this->sur_name . ' ' . $this->given_name,
            'phone'                 => $this->mobile,
            'passport_no'           => $this->passport_no,
            // 'process'               => $this->current_process_name ?? 'N/A',
            // 'duration'              => $this->currentProcess?->process?->duration,
            'remarks'               => $this->currentProcess?->remarks,
            'status'                => $status,
            'medical_test'          => $this->getProcessDataById(3, 'medical_fit'),

            'police_clearance'      => $this->getProcessStatus('police_clearance'),
            'trade_test'            => $this->getProcessDataById(5,'status'),
            'biometric_enrollment'  => $this->getProcessDataById(6, 'status'),
            'immigration_clearance' => $this->getProcessDataById(10, 'clearance_status'),

            // 🔥 JSON ভিত্তিক Fields
            'visa_endorsement'      => $this->getProcessDataById(7, 'endorsment_date'),
            'visa_expiry'           => $this->getProcessDataById(7, 'visa_expiry'),
            'flight_date'           => $this->getProcessDataById(12, 'flight_date'),
            'days'                  => $this->currentProcessDays() ?? null,
            'total_process_days'    => $this->totalProcessDays() ?? null,
            // 'duration'      => $this->currentProcess?->getDurationInDays() . ' ' . ($this->currentProcess?->getDurationInDays() <= 1 ? 'Day' : 'Days'),
            'started_at'            => DateTimeFormatter::formatDateTime(optional($this->currentProcess)->started_at),
            'completed_at'          => DateTimeFormatter::formatDateTime(optional($this->currentProcess)->completed_at),
            'created_at'            => DateTimeFormatter::formatDateTime($this->created_at),
            'updated_at'            => DateTimeFormatter::formatDateTime($this->updated_at),
        ];
    }

    private function getProcessStatus($processName)
    {
        $process = $this->processes
            ->firstWhere('process.name', $processName);

        return $process?->status;
    }
    private function getProcessDataById($processId, $key)
    {
        $process = $this->processes
            ->firstWhere('process_id', $processId);

        return data_get($process?->data, $key);
    }
}

