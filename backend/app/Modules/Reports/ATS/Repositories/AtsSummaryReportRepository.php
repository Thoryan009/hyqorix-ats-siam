<?php

namespace App\Modules\Reports\Ats\Repositories;

use App\Modules\Application\Models\Application;

class AtsSummaryReportRepository
{
    protected array $processes = [
        'hiring_list',
        'offer_extended',
        'visa_authorization',
        'medical_test',
        'police_clearance',
        'trade_test',
        'biometric_enrollment',
        'embassy_submission',
        'bmet_training',
        'bmet_biometric_enrollment',
        'immigration_clearance',
        'pta_request',
        'tra_process',
        'on_boarding',
    ];

    public function getSummary($filters): array
    {
        $applications = Application::query()
            ->with([
               'jobList:id,name,job_code,work_order_id',
                'jobList.workOrder:id,client_id',
                'jobList.workOrder.client:id,user_id',
                'jobList.workOrder.client.user:id,name',
                'currentProcess',
                'currentProcess.process:id,name',
            ])
            ->whereIn('application_status', [
                'ATS',
                'hiring_list',
            ])
            ->when($filters['client_id'] ?? null, function ($query, $clientId) {
                $query->whereHas('jobList.workOrder.client', function ($q) use ($clientId) {
                    $q->where('id', $clientId);
                });
            })
            ->when($filters['job_id'] ?? null, function ($query, $jobId) {
                $query->whereHas('jobList', function ($q) use ($jobId) {
                    $q->where('id', $jobId);
                });
                // or simply:

            })
            ->get();

        $rows = [];

        $clients = [];

        $cards = array_fill_keys($this->processes, 0);

        foreach ($applications as $application) {

            if ($this->shouldSkip($application)) {
                continue;
            }

            $jobList = $application->jobList;

            if (!$jobList) {
                continue;
            }

            $jobId = $jobList->id;


           if (!isset($rows[$jobId])) {

                $clientId = $jobList->workOrder?->client?->id;


            if ($clientId) {
                $clients[$clientId] = [
                    'id' => $clientId,
                    'name' => $jobList->workOrder?->client?->user?->name,
                ];
            }

                $rows[$jobId] = [
                    'client_name' => $jobList->workOrder?->client?->user?->name,
                    'job_name' => $jobList->name . (!empty($jobList->job_code) ? " ({$jobList->job_code})" : ''),
                    'job_code' => $jobList->job_code,
                    'job_list_id' => $jobId,
                ];

                foreach ($this->processes as $process) {
                    $rows[$jobId][$process] = 0;
                }
            }

            $bucket = $this->resolveBucket($application);

            if (!$bucket) {
                continue;
            }

            $rows[$jobId][$bucket]++;
            $cards[$bucket]++;
                }


        $rows = collect($rows)
            ->sort(function ($a, $b) {

                $clientCompare = strcmp(
                    strtolower($a['client_name'] ?? ''),
                    strtolower($b['client_name'] ?? '')
                );

                if ($clientCompare !== 0) {
                    return $clientCompare;
                }

                return strcmp(
                    strtolower($a['job_name'] ?? ''),
                    strtolower($b['job_name'] ?? '')
                );
            })
            ->values();

        return [
            'rows' => $rows,
            'cards' => $cards,
            'clients' => array_values($clients),
            'grand_total' => collect($cards)->sum(),
            'processes' => $this->processes,
        ];
    }

    protected function shouldSkip(Application $application): bool
    {
        $status = $application->getRawOriginal('application_status');

        if (in_array($status, [
            'waiting_list',
            'short_list',
            'rejected_list',
        ])) {
            return true;
        }

        $currentProcess = $application->currentProcess;

        if (!$currentProcess) {
            return false;
        }

        if (in_array($currentProcess->status, [
            'rejected',
            'declined',
        ])) {
            return true;
        }

        if (
            $currentProcess->process?->name === 'on_boarding'
            && $currentProcess->status === 'completed'
        ) {
            return true;
        }

        return false;
    }

    protected function resolveBucket(Application $application): ?string
    {
        $status = $application->getRawOriginal('application_status');

        if ($status === 'hiring_list') {
            return 'hiring_list';
        }

        if ($status === 'ATS') {
            return $application->currentProcess?->process?->name;
        }

        return null;
    }
}
