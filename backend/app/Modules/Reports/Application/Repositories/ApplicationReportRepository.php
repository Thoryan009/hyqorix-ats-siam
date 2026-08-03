<?php

namespace App\Modules\Reports\Application\Repositories;

use App\Modules\Application\Models\Application;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ApplicationReportRepository
{
    public function baseQuery(array $filters): Builder
    {
        $query = Application::query()
            ->with([
                'jobList.workOrder.client.country',
            ])
            ->when($filters['country_id'] ?? null, function ($query, $countryId) {
                $query->whereHas('jobList.workOrder.client.country', function ($q) use ($countryId) {
                    $q->where('id', $countryId);
                });
            })
            ->when($filters['client_id'] ?? null, function ($query, $clientId) {
                $query->whereHas('jobList.workOrder.client', function ($q) use ($clientId) {
                    $q->where('id', $clientId);
                });
            })
            ->when($filters['agent_id'] ?? null, function ($query, $agentId) {
                $query->whereHas('agent', function ($q) use ($agentId) {
                    $q->where('id', $agentId);
                });
            })
            ->when($filters['principal_id'] ?? null, function ($query, $principalId) {
                $query->whereHas('jobList.principal', function ($q) use ($principalId) {
                    $q->where('id', $principalId);
                });
            })
            ->when($filters['work_order_id'] ?? null, function ($query, $workOrderId) {
                $query->whereHas('jobList.workOrder', function ($q) use ($workOrderId) {
                    $q->where('id', $workOrderId);
                });
            })
            ->when($filters['job_list_id'] ?? null, function ($query, $jobListId) {
                $query->whereHas('jobList', function ($q) use ($jobListId) {
                    $q->where('id', $jobListId);
                });
            });

        $this->applyRecruitmentAndProcessFilters($query, $filters);

        return $query
            ->when(
                $filters['from_date'] ?? null,
                fn ($q, $from) => $q->whereDate('created_at', '>=', $from)
            )
            ->when(
                $filters['to_date'] ?? null,
                fn ($q, $to) => $q->whereDate('created_at', '<=', $to)
            )
            ->when(!$this->isFilteringForSpecialStatuses($filters), function ($q) {
                $q->where(DB::raw('LOWER(application_status)'), '!=', 'rejected_list')
                    ->where(function ($subQuery) {
                        $subQuery
                            ->whereDoesntHave('currentProcess')
                            ->orWhereHas('currentProcess', function ($processQuery) {
                                $processQuery
                                    ->whereNotIn('status', ['rejected', 'declined'])
                                    ->where(function ($inner) {
                                        $inner
                                            ->whereHas('process', function ($p) {
                                                $p->where('name', '!=', 'on_boarding');
                                            })
                                            ->orWhere('status', '!=', 'completed');
                                    });
                            });
                    });
            })
            ->join('job_lists', 'job_lists.id', '=', 'applications.job_list_id')
            ->orderBy('job_lists.job_code', 'desc')
            ->select('applications.*');
    }

    private function applyRecruitmentAndProcessFilters(Builder $query, array $filters): void
    {
        $statusFilter = $filters['application_status'] ?? null;
        $processFilter = $filters['process_id'] ?? null;

        $hasStatus = $this->hasApplicationStatusFilter($statusFilter);
        $hasProcess = $this->hasProcessFilter($processFilter);

        if ($hasStatus && $hasProcess) {
            $query->where(function ($combined) use ($statusFilter, $processFilter) {
                $combined
                    ->where(function ($statusQuery) use ($statusFilter) {
                        $this->applyApplicationStatusFilter($statusQuery, $statusFilter);
                    })
                    ->orWhere(function ($processQuery) use ($processFilter) {
                        $this->applyProcessFilter($processQuery, $processFilter);
                    });
            });

            return;
        }

        if ($hasStatus) {
            $this->applyApplicationStatusFilter($query, $statusFilter);
        }

        if ($hasProcess) {
            $this->applyProcessFilter($query, $processFilter);
        }
    }

    private function hasApplicationStatusFilter(mixed $status): bool
    {
        if (empty($status)) {
            return false;
        }

        $statuses = is_array($status) ? $status : [$status];
        $statuses = array_values(array_filter(array_map(static fn ($item) => strtolower((string) $item), $statuses)));

        return !empty($statuses);
    }

    private function hasProcessFilter(mixed $processId): bool
    {
        if (empty($processId)) {
            return false;
        }

        $processIds = is_array($processId)
            ? array_values(array_filter(array_map(static fn ($item) => (int) $item, $processId)))
            : [(int) $processId];

        return !empty($processIds);
    }

    private function applyApplicationStatusFilter(Builder $query, mixed $status): void
    {
        $statuses = is_array($status) ? $status : [$status];
        $statuses = array_values(array_filter(array_map(static fn ($item) => strtolower((string) $item), $statuses)));

        if (empty($statuses)) {
            return;
        }

        $query->whereIn(DB::raw('LOWER(application_status)'), $statuses);
    }

    private function applyProcessFilter(Builder $query, mixed $processId): void
    {
        $processIds = is_array($processId)
            ? array_values(array_filter(array_map(static fn ($item) => (int) $item, $processId)))
            : [(int) $processId];

        if (empty($processIds)) {
            return;
        }

        $rejectedId = (int) config('app.process_rejected_id');
        $declinedId = (int) config('app.process_declined_id');
        $deployedId = (int) config('app.process_deployed_id');

        $query->where(function ($processQuery) use ($processIds, $rejectedId, $declinedId, $deployedId) {
            $hasClause = false;

            if (in_array($rejectedId, $processIds, true)) {
                $processQuery->whereHas('currentProcess', function ($q) {
                    $q->where('status', 'rejected');
                });
                $hasClause = true;
            }

            if (in_array($declinedId, $processIds, true)) {
                $method = $hasClause ? 'orWhereHas' : 'whereHas';
                $processQuery->{$method}('currentProcess', function ($q) {
                    $q->where('status', 'declined');
                });
                $hasClause = true;
            }

            if (in_array($deployedId, $processIds, true)) {
                $method = $hasClause ? 'orWhereHas' : 'whereHas';
                $processQuery->{$method}('currentProcess', function ($q) {
                    $q->where('status', 'completed')
                        ->whereHas('process', function ($qq) {
                            $qq->where('name', 'on_boarding');
                        });
                });
                $hasClause = true;
            }

            $normalProcessIds = array_values(array_filter(
                $processIds,
                static fn ($id) => !in_array($id, [$rejectedId, $declinedId, $deployedId], true)
            ));

            if (!empty($normalProcessIds)) {
                $method = $hasClause ? 'orWhereHas' : 'whereHas';
                $processQuery->{$method}('currentProcess.process', function ($q) use ($normalProcessIds) {
                    $q->whereIn('id', $normalProcessIds);
                });
            }
        });
    }

    /**
     * Check if user is explicitly filtering for rejected/declined applications
     */
    private function isFilteringForSpecialStatuses(array $filters): bool
    {
        $processIds = $filters['process_id'] ?? null;

        $rejectedStatus = ($filters['application_status'] ?? null) == 'rejected_list'
            || (is_array($filters['application_status'] ?? null)
                && in_array('rejected_list', $filters['application_status'] ?? []));

        if ($rejectedStatus) {
            return true;
        }

        if (!$processIds) {
            return false;
        }

        $processIds = is_array($processIds)
            ? array_map('intval', $processIds)
            : [(int) $processIds];

        return collect($processIds)->contains(
            fn ($id) => in_array(
                $id,
                [
                    (int) config('app.process_rejected_id'),
                    (int) config('app.process_declined_id'),
                    (int) config('app.process_deployed_id'),
                ],
                true
            )
        );
    }

    public function filterByApplication(Builder $query, string $application): Builder
    {
        return $query;
    }

    public function principalApplications(int $principalId): Builder
    {
        $filters = [
            'principal_id' => $principalId,
            'application_status' => ['hiring_list', 'ATS'],
        ];

        return $this->baseQuery($filters);
    }

    public function clientApplications(int $clientId): Builder
    {
        $filters = [
            'client_id' => $clientId,
            'application_status' => ['hiring_list', 'ATS'],
        ];

        return $this->baseQuery($filters);
    }
}
