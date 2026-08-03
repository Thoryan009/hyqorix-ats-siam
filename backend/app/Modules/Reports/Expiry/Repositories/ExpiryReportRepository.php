<?php

namespace App\Modules\Reports\Expiry\Repositories;

use App\Modules\Application\Models\ApplicationProcess;
use Illuminate\Database\Eloquent\Builder;

class ExpiryReportRepository
{
    public function baseQuery(array $filters = []): Builder
    {
        $supportedProcesses = [
            'medical_test',
            'police_clearance',
            'embassy_submission',
        ];

        $processFilter = $filters['process'] ?? null;

        $query = ApplicationProcess::query()
            ->with([
                'process:id,name,validity,notify_before',
                'application:id,given_name,sur_name,passport_no,mobile,agent_id,job_list_id',
                'application.embassySubmission:id,application_id,mofa_no',
                'application.agent.user:id,name,phone',
                'application.jobList:id,name,work_order_id',
                'application.jobList.workOrder:id,client_id',
                'application.jobList.workOrder.client:id,user_id',
                'application.jobList.workOrder.client.user:id,name',
                'application.currentProcess',
                'application.currentProcess.process:id,name',
                'application.processes:id,application_id,process_id,data',
                'application.processes.process:id,name',
            ])
            ->whereIn('process_id', function ($query) use ($supportedProcesses) {
                $query->select('id')
                    ->from('processes')
                    ->whereIn('name', $supportedProcesses);
            })
            ->when($processFilter, function (Builder $query, string $processName) {
                $query->whereHas('process', function (Builder $processQuery) use ($processName) {
                    $processQuery->where('name', $processName);
                });
            })
            ->when($filters['client_id'] ?? null, function (Builder $query, $clientId) {
                $query->whereHas('application.jobList.workOrder.client', function (Builder $applicationQuery) use ($clientId) {
                    $applicationQuery->where('id', $clientId);
                });
            })
            ->when($filters['agent_id'] ?? null, function (Builder $query, $agentId) {
                $query->whereHas('application.agent', function (Builder $agentQuery) use ($agentId) {
                    $agentQuery->where('id', $agentId);
                });
            })
            ->when($filters['job_id'] ?? null, function (Builder $query, $jobId) {
                $query->whereHas('application.jobList', function (Builder $applicationQuery) use ($jobId) {
                    $applicationQuery->where('id', $jobId);
                });
            })
            ->whereNotNull('data')
            ->whereIn('id', function ($query) {
                $query->selectRaw('MAX(latest.id)')
                    ->from('application_processes as latest')
                    ->whereColumn('latest.application_id', 'application_processes.application_id')
                    ->whereColumn('latest.process_id', 'application_processes.process_id');
            })
            ->whereHas('application');

        $this->excludeInactiveApplications($query);
        $this->applySearch($query, $filters['search'] ?? null);

        return $query;
    }

    protected function excludeInactiveApplications(Builder $query): void
    {
        $query->whereHas('application', function (Builder $applicationQuery) {
            $applicationQuery
                ->whereDoesntHave('currentProcess', function (Builder $currentProcessQuery) {
                    $currentProcessQuery->whereIn('status', ['rejected', 'declined']);
                })
                ->whereDoesntHave('currentProcess', function (Builder $currentProcessQuery) {
                    $currentProcessQuery
                        ->where('status', 'completed')
                        ->whereHas('process', function (Builder $processQuery) {
                            $processQuery->where('name', 'on_boarding');
                        });
                });
        });
    }

    protected function applySearch(Builder $query, ?string $search): void
    {
        if (!$search) {
            return;
        }

        $search = trim($search);

        $query->whereHas('application', function (Builder $applicationQuery) use ($search) {
            $applicationQuery->where(function ($q) use ($search) {
                $columns = [
                    'sur_name',
                    'given_name',
                    'email',
                    'mobile',
                    'application_id',
                    'passport_no',
                    'nid_no',
                ];

                foreach ($columns as $column) {
                    $q->orWhere($column, 'like', "%{$search}%");
                }

                $q->orWhereHas('jobList', function ($jobQuery) use ($search) {
                    $jobQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('job_code', 'like', "%{$search}%");
                });

                $q->orWhereHas('agent.user', function ($agentQuery) use ($search) {
                    $agentQuery->where('name', 'like', "%{$search}%");
                });

                $q->orWhereHas('jobList.workOrder.client.user', function ($clientQuery) use ($search) {
                    $clientQuery->where('name', 'like', "%{$search}%");
                });
            });
        });
    }


}
