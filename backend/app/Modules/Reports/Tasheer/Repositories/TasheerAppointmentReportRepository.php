<?php

namespace App\Modules\Reports\Tasheer\Repositories;

use App\Modules\Agent\Models\Agent;
use App\Modules\Application\Models\Application;
use App\Modules\Client\Models\Client;
use App\Modules\JobList\Models\JobList;
use Illuminate\Database\Eloquent\Builder;

class TasheerAppointmentReportRepository
{
    public function baseQuery(array $filters = []): Builder
    {
        $query = Application::query()
            ->with([
                'embassySubmission:id,application_id,mofa_no',
            ])
            ->when(
                $filters['status'] ?? null,
                function (Builder $query, $status) {
                      if ($status !== 'all') {
                        $query->where('tasheer_status', $status);
                        }
                },
                function (Builder $query) {
                    $query->where('tasheer_status', 'pending');
                }
            )
            ->when(isset($filters['ids']), function (Builder $query) use ($filters) {
                $query->whereIn('id', $filters['ids']);
            })
            ->when($filters['client_id'] ?? null, function (Builder $query, $clientId) {
                $query->whereHas('jobList.workOrder.client', function (Builder $applicationQuery) use ($clientId) {
                    $applicationQuery->where('id', $clientId);
                });
            })
            ->when($filters['agent_id'] ?? null, function (Builder $query, $agentId) {
                $query->whereHas('agent', function (Builder $agentQuery) use ($agentId) {
                    $agentQuery->where('id', $agentId);
                });
            })
            ->when($filters['job_id'] ?? null, function (Builder $query, $jobId) {
                $query->whereHas('jobList', function (Builder $applicationQuery) use ($jobId) {
                    $applicationQuery->where('id', $jobId);
                });
            });

        $this->applyTasheerEligibleScope($query);

        return $query->latest('id');
    }

    public function getFilterData(): array
    {
        $embassySubmissionConstraint = function (Builder $query) {
            $query->whereNotNull('mofa_no')
                ->whereRaw("TRIM(mofa_no) != ''");
        };

        $clients = Client::query()
            ->with('user:id,name')
            ->select('clients.id', 'clients.user_id')
            ->whereHas('workOrders.jobLists.applications', function (Builder $query) use ($embassySubmissionConstraint) {
                $query->whereHas('embassySubmission', $embassySubmissionConstraint);
            })
            ->addSelect([
                'applications_count' => Application::query()
                    ->selectRaw('count(*)')
                    ->join('job_lists', 'applications.job_list_id', '=', 'job_lists.id')
                    ->join('work_orders', 'job_lists.work_order_id', '=', 'work_orders.id')
                    ->join('embasy_submissions', 'embasy_submissions.application_id', '=', 'applications.id')
                    ->whereColumn('work_orders.client_id', 'clients.id')
                    ->whereNotNull('embasy_submissions.mofa_no')
                    ->whereRaw("TRIM(embasy_submissions.mofa_no) != ''"),
            ])
            ->join('users', 'clients.user_id', '=', 'users.id')
            ->whereHas('user', fn (Builder $query) => $query->where('status', 1))
            ->orderBy('users.name', 'asc')
            ->get()
            ->map(fn ($client) => [
                'id' => $client->id,
                'applications_count' => (int) $client->applications_count,
                'name' => sprintf(
                    '%s (%d)',
                    optional($client->user)->name ?? 'N/A',
                    $client->applications_count
                ),
            ])
            ->toArray();

        $agents = Agent::query()
            ->select('agents.id', 'agents.user_id')
            ->with('user:id,name')
            ->whereHas('applications', function (Builder $query) use ($embassySubmissionConstraint) {
                $query->whereHas('embassySubmission', $embassySubmissionConstraint);
            })
            ->withCount([
                'applications as applications_count' => function (Builder $query) use ($embassySubmissionConstraint) {
                    $query->whereHas('embassySubmission', $embassySubmissionConstraint);
                },
            ])
            ->join('users', 'agents.user_id', '=', 'users.id')
            ->orderBy('users.name', 'asc')
            ->get()
            ->map(fn ($agent) => [
                'id' => $agent->id,
                'applications_count' => (int) $agent->applications_count,
                'name' => sprintf(
                    '%s (%d)',
                    optional($agent->user)->name ?? 'N/A',
                    $agent->applications_count
                ),
            ])
            ->toArray();

        $jobs = JobList::query()
            ->select('job_lists.id', 'job_lists.name', 'job_lists.job_code')
            ->whereHas('applications', function (Builder $query) use ($embassySubmissionConstraint) {
                $query->whereHas('embassySubmission', $embassySubmissionConstraint);
            })
            ->withCount([
                'applications as applications_count' => function (Builder $query) use ($embassySubmissionConstraint) {
                    $query->whereHas('embassySubmission', $embassySubmissionConstraint);
                },
            ])
            ->orderBy('job_lists.name', 'asc')
            ->get()
            ->map(fn ($job) => [
                'id' => $job->id,
                'job_name' => $job->name,
                'job_code' => $job->job_code,
                'applications_count' => (int) $job->applications_count,
                'name' => sprintf(
                    '%s (%s) (%d)',
                    $job->name,
                    $job->job_code,
                    $job->applications_count
                ),
            ])
            ->toArray();



        $statusCounts = Application::query()
            ->whereHas('embassySubmission', $embassySubmissionConstraint)
            ->selectRaw('tasheer_status, count(*) as count')
            ->groupBy('tasheer_status')
            ->pluck('count', 'tasheer_status');

        $statusLabels = [
        'pending' => 'Pending',
        'done' => 'Done',
        'cancel' => 'Cancelled',
        ];

        $totalCount = (int) $statusCounts->sum();
        $statuses = [];
        $statuses = [
                    [
                        'value' => 'all',
                        'label' => 'All',
                        'applications_count' => $totalCount,
                        'name' => sprintf('All (%d)', $totalCount),
                    ],
                ];




        foreach ($statusLabels as $value => $label) {
            $count = (int) ($statusCounts[$value] ?? 0);

            if ($count === 0) {
                continue;
            }

            $statuses[] = [
                'value' => $value,
                'label' => $label,
                'applications_count' => $count,
                'name' => sprintf('%s (%d)', $label, $count),
            ];
        }

        return [
            'clients' => $clients,
            'agents' => $agents,
            'jobs' => $jobs,
            'statuses' => $statuses,
        ];
    }

    public function bulkStatusUpdate(array $ids, string $status)
    {
        return Application::whereIn('id', $ids)->update(['tasheer_status' => $status]);
    }

    private function applyTasheerEligibleScope(Builder $query): void
    {
        $query->whereHas('embassySubmission', function (Builder $subQuery) {
            $subQuery->whereNotNull('mofa_no')
                ->whereRaw("TRIM(mofa_no) != ''");
        });
    }
}
