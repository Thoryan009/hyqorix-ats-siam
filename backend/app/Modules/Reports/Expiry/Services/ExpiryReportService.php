<?php

namespace App\Modules\Reports\Expiry\Services;

use App\Modules\Agent\Models\Agent;
use App\Modules\Client\Models\Client;
use App\Modules\JobList\Models\JobList;
use App\Modules\Reports\Expiry\Repositories\ExpiryReportRepository;
use App\Modules\Reports\Expiry\Resources\ExpiryReportResource;
use App\Modules\Application\Helpers\ApplicationPresenter;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ExpiryReportService
{
    private const MEDICAL_VALIDITY_WITH_MOFA = 90;

    private const PROCESS_CONFIG = [
        'medical_test' => [
            'document' => 'Medical',
            'date_key' => 'date_of_medical',
        ],
        'police_clearance' => [
            'document' => 'Police Clearance',
            'date_key' => 'date_of_apply',
        ],
        'embassy_submission' => [
            'document' => 'Visa',
            'date_key' => 'endorsment_date',
        ],
    ];

    public function __construct(
        protected ExpiryReportRepository $repository
    ) {}

    public function getExpiryReport(array $filters)
    {
        $rows = $this->getExpiryRows($filters);

        $page = max(1, (int) ($filters['page'] ?? 1));
        $perPage = max(1, (int) ($filters['per_page'] ?? 10));

        $paginated = $this->paginateCollection($rows, $page, $perPage);

        return ExpiryReportResource::collection($paginated);
    }

    public function getFilterData(array $filters): array
    {
        $rows = $this->getExpiryRows([
            'from_date' => $filters['from_date'],
            'to_date' => $filters['to_date'],
            'search' => $filters['search'] ?? null,
            'client_id' => $filters['client_id'] ?? null,
            'agent_id' => $filters['agent_id'] ?? null,
            'job_id' => $filters['job_id'] ?? null,
        ]);

        $processLabels = [
            'medical_test' => 'Medical',
            'embassy_submission' => 'Embassy Submission (Visa)',
            'police_clearance' => 'Police Clearance',
        ];

        $processCounts = $rows->groupBy('process_name')->map->count();

        $documents = [];

        foreach ($processLabels as $processId => $label) {
            $count = (int) ($processCounts[$processId] ?? 0);

            if ($count === 0) {
                continue;
            }

            $documents[] = [
                'id' => $processId,
                'applications_count' => $count,
                'name' => sprintf('%s (%d)', $label, $count),
            ];
        }

        $jobCounts = $rows->groupBy('job_list_id')->map->count();
        $jobIds = $jobCounts->keys()->filter()->values();

        $jobs = JobList::query()
            ->whereIn('id', $jobIds)
            ->orderBy('name')
            ->get()
            ->map(function ($job) use ($jobCounts) {
                $count = (int) ($jobCounts[$job->id] ?? 0);

                return [
                    'id' => $job->id,
                    'job_name' => $job->name,
                    'job_code' => $job->job_code,
                    'applications_count' => $count,
                    'name' => sprintf('%s (%s) (%d)', $job->name, $job->job_code, $count),
                ];
            })
            ->values()
            ->toArray();

        $clientCounts = $rows->groupBy('client_id')->map->count();
        $clientIds = $clientCounts->keys()->filter()->values();

        $clients = $clientIds->isEmpty()
            ? collect()
            : Client::query()
                ->with('user:id,name')
                ->whereIn('clients.id', $clientIds)
                ->join('users', 'clients.user_id', '=', 'users.id')
                ->orderBy('users.name', 'asc')
                ->select('clients.*')
                ->get();

        $clients = $clients
            ->map(function ($client) use ($clientCounts) {
                $count = (int) ($clientCounts[$client->id] ?? 0);

                return [
                    'id' => $client->id,
                    'applications_count' => $count,
                    'name' => sprintf('%s (%d)', optional($client->user)->name ?? 'N/A', $count),
                ];
            })
            ->values()
            ->toArray();

        $agentCounts = $rows->groupBy('agent_id')->map->count();
        $agentIds = $agentCounts->keys()->filter()->values();

        $agents = $agentIds->isEmpty()
            ? collect()
            : Agent::query()
                ->with('user:id,name')
                ->whereIn('agents.id', $agentIds)
                ->join('users', 'agents.user_id', '=', 'users.id')
                ->orderBy('users.name', 'asc')
                ->select('agents.*')
                ->get();

        $agents = $agents
            ->map(function ($agent) use ($agentCounts) {
                $count = (int) ($agentCounts[$agent->id] ?? 0);

                return [
                    'id' => $agent->id,
                    'applications_count' => $count,
                    'name' => sprintf('%s (%d)', optional($agent->user)->name ?? 'N/A', $count),
                ];
            })
            ->values()
            ->toArray();

        return [
            'documents' => $documents,
            'jobs' => $jobs,
            'clients' => $clients,
            'agents' => $agents,
        ];
    }

    public function getExpiryRows(array $filters): Collection
    {
        $fromDate = Carbon::parse($filters['from_date'])->startOfDay();
        $toDate = Carbon::parse($filters['to_date'])->startOfDay();

        if ($toDate->lt($fromDate)) {
            $toDate = $fromDate->copy();
        }

        return $this->repository
            ->baseQuery($filters)
            ->get()
            ->map(fn($applicationProcess) => $this->transformRow($applicationProcess, $toDate))
            ->filter(fn($row) => $row !== null && $this->isRowWithinNotifyWindow($row, $toDate))
            ->values();
    }

    private function transformRow($applicationProcess, Carbon $referenceDate): ?array
    {
        $processName = $applicationProcess->process?->name;
        if (!$processName || !isset(self::PROCESS_CONFIG[$processName])) {
            return null;
        }

        // Check if candidate should be excluded based on progression
        if ($this->isExcludedByProgression($applicationProcess, $processName)) {
            return null;
        }

        $config = self::PROCESS_CONFIG[$processName];
        $startedDate = data_get($applicationProcess->data, $config['date_key']);
        if (!$startedDate) {
            return null;
        }

        $validity = (int) ($applicationProcess->process?->validity ?? 0);

        if ($processName === 'medical_test' && $this->hasMofaNo($applicationProcess->application)) {
            $validity = self::MEDICAL_VALIDITY_WITH_MOFA;
        }

        $notifyBefore = (int) ($applicationProcess->process?->notify_before ?? 0);

        if ($validity <= 0) {
            return null;
        }

        $startedAt = Carbon::parse($startedDate)->startOfDay();
        $expiryDate = $startedAt->copy()->addDays($validity);
        $daysLeft = $referenceDate->diffInDays($expiryDate, false);

        $agent = $applicationProcess->application?->agent;
        $client = $applicationProcess->application?->jobList?->workOrder?->client?->user?->name;
        $job = $applicationProcess->application?->jobList?->name;

        return [
            'passport_no'       => $applicationProcess->application?->passport_no,
            'candidate_name'    => trim(($applicationProcess->application?->given_name ?? '') . ' ' . ($applicationProcess->application?->sur_name ?? '')),
            'mobile'            => ApplicationPresenter::localMobile($applicationProcess->application?->mobile),
            'agent_name'        => $agent?->user?->name,
            'agent_mobile_no'   => ApplicationPresenter::localMobile($agent?->user?->phone),
            'client_name'       => $client,
            'job_name'          => $job,
            'document'          => $config['document'],
            'process_name'      => $processName,
            'job_list_id'       => $applicationProcess->application?->job_list_id,
            'client_id'         => $applicationProcess->application?->jobList?->workOrder?->client_id,
            'agent_id'          => $applicationProcess->application?->agent_id,
            'expiry_date'       => $expiryDate->toDateString(),
            'expiry_date_formatted' => $expiryDate->format('d M Y'),
            'days_left'         => $daysLeft,
            'current_process'   => $this->formatProcessName($applicationProcess->application?->currentProcess?->process?->name),
            'notify_before'     => $notifyBefore,
        ];
    }

    private function hasMofaNo($application): bool
    {
        $mofaNo = $application?->embassySubmission?->mofa_no;

        return filled(trim((string) $mofaNo));
    }

    private function isExcludedByProgression($applicationProcess, string $processName): bool
    {
        $application = $applicationProcess->application;
        if (!$application || !$application->processes) {
            return false;
        }

        $processes = $application->processes->keyBy(fn($p) => $p->process?->name);

        // Exclusion 1: For medical_test and police_clearance
        // Exclude if candidate moved to embassy_submission with endorsment_date value
        if (in_array($processName, ['medical_test', 'police_clearance'])) {
            if (isset($processes['embassy_submission'])) {
                $embassyProcess = $processes['embassy_submission'];
                $endorsmentDate = data_get($embassyProcess->data, 'endorsment_date');
                if ($endorsmentDate) {
                    return true;
                }
            }
        }

        // Exclusion 2: For embassy_submission (visa)
        // Exclude if candidate moved to on_boarding with flight_status == 'departed'
        if ($processName === 'embassy_submission') {
            if (isset($processes['on_boarding'])) {
                $onboardingProcess = $processes['on_boarding'];
                $flightStatus = data_get($onboardingProcess->data, 'flight_status');
                if ($flightStatus === 'departed') {
                    return true;
                }
            }
        }

        return false;
    }

    private function isRowWithinNotifyWindow(?array $row, Carbon $referenceDate): bool
    {
        if (!$row) {
            return false;
        }

        $notifyBefore = (int) ($row['notify_before'] ?? 0);
        $daysLeft = (int) ($row['days_left'] ?? 0);

        // Keep listing once the notify window starts until process date is updated.
        return $daysLeft <= $notifyBefore;
    }

    private function formatProcessName(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        return ucwords(str_replace('_', ' ', $value));
    }

    private function paginateCollection(Collection $items, int $page, int $perPage): LengthAwarePaginator
    {
        return new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );
    }

        public function exportCSV(array $filters): string
        {
            // $report = $this->getExpiryRows($filters);
            $rows = $this->getExpiryRows($filters);

            $output = fopen('php://temp', 'r+');

            // CSV Header
            fputcsv($output, [
                'Passport No',
                'Candidate Name',
                'Mobile',
                'Agent Name',
                'Client Name',
                'Job Name',
                'Agent Mobile',
                'Document',
                'Expiry Date',
                'Days Left',
                'Current Process',
            ]);

            // CSV Rows
            foreach ($rows as $row) {
                fputcsv($output, [
                    $row['passport_no'] ?? '',
                    $row['candidate_name'] ?? '',
                    $this->csvText($row['mobile'] ?? ''),
                    $row['agent_name'] ?? '',
                    $row['client_name'] ?? '',
                    $row['job_name'] ?? '',
                    $this->csvText($row['agent_mobile_no'] ?? ''),
                    $row['document'] ?? '',
                    $row['expiry_date_formatted'] ?? '',
                    $row['days_left'] ?? '',
                    $row['current_process'] ?? '',
                ]);
            }

            rewind($output);

            $csv = stream_get_contents($output);

            fclose($output);

            return $csv;
        }

    /**
     * Keep leading zeros (01…) intact when Excel opens the CSV.
     */
    private function csvText(?string $value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return '';
        }

        return "\t".$value;
    }
}
