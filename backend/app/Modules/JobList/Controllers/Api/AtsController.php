<?php

namespace App\Modules\JobList\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApiIndexRequest;
use App\Modules\JobList\Contracts\AtsDataServiceInterface;
use App\Modules\JobList\Models\JobList;
use App\Modules\JobList\Requests\BulkNextProcessRequest;
use App\Modules\JobList\Services\ProcessDbService;
use App\Modules\JobList\Requests\JobListAtsRequest;
use App\Modules\JobList\Requests\NextProcessRequest;
use App\Modules\JobList\Resources\AtsResource;
use App\Modules\JobList\Resources\SingleAtsResource;
use App\Modules\JobList\Services\AtsService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Modules\JobList\Services\WorkOrderDbService;
use App\Modules\JobList\Services\ClientDbService;
use App\Modules\Application\Models\Application;
use App\Traits\HandlesClientFilter;

class AtsController extends Controller
{

    use HandlesClientFilter;

    public function __construct(
        private readonly AtsService $service,
        private readonly ProcessDbService $processDbService,
        private readonly WorkOrderDbService $workOrderDbService,
        private readonly ClientDbService $clientDbService,
        private readonly AtsDataServiceInterface $atsDataDbService,

    ) {
    }


    public function index(ApiIndexRequest $request)
    {
         $filters = $request->filters();

        // Add work_order_id filter
        $filters['job_list_id'] = $request->get('job_list_id');
        $filters['work_order_id'] = $request->get('work_order_id');

        $user = auth()->user();
        if ($user && $user->type === 'agent') {
            $agentJobIds = $user->agent?->applications()->pluck('job_list_id')->toArray() ?? [];
            $filters['job_list_ids'] = $agentJobIds;
        }

        // Apply reusable client filter
        $filters = $this->applyClientFilter($request, $filters);

        // Fetch data
        $data = $this->service->getPaginatedDataWithCache($filters);
        \Log::info('ATS Index Data', ['filters' => $filters, 'data_count' => $data->count(), 'data' => $data->toArray()]);

        // Fetch processes and attach to each job
        $processes = $this->processDbService->getProcesses();
        $data->each(fn($job) => $job->setRelation('master_processes', collect($processes)));

        return AtsResource::collection($data);
    }



    public function show(Request $request): AnonymousResourceCollection
    {

        $job_id = $request->get('job_id');
        $user = auth()->user();
        if ($user && $user->type === 'agent') {
            $agentJobIds = $user->agent?->applications()->pluck('job_list_id')->toArray() ?? [];

            if (!in_array($job_id, $agentJobIds)) {
                abort(403, 'Unauthorized job access');
            }
        }

        $process_id = $request->get('process_id');
        $user = auth()->user();
        if ($user && $user->type === 'client') {
            $clientId = $user->client?->id;
            $jobBelongsToClient = JobList::where('id', $job_id)
                ->whereHas('workOrder', function ($q) use ($clientId) {
                    $q->where('client_id', $clientId);
                })
                ->exists();
            if (!$jobBelongsToClient) {
                abort(403, 'Unauthorized job access');
            }
        }
        
        $application_id = $request->get('application_id');
        $data = $this->service->getJobAtsByJobIdAndProcessId($job_id, $process_id, $application_id);
        $processes = $this->processDbService->getProcesses();
        $data->each(fn($job) => $job->setRelation('master_processes', collect($processes)));

        return SingleAtsResource::collection($data);
    }

    public function updateJobProcessForm(JobListAtsRequest $request)
    {
        $record = $this->service->updateJobProcess($request->validated());
        return apiSuccess(
            $record,
            'updated',
            200,
            "Job Process"
        );
    }


    public function createNextProcess(NextProcessRequest $request): JsonResponse
    {
        $records = $this->service->createNextProcess($request->validated());

        return apiSuccess(
            $records,
            'created',
            200,
            "Next Process"
        );
    }

    public function createBulkNextProcess(BulkNextProcessRequest $request): JsonResponse
    {
        $records = $this->service->createBulkNextProcess($request->validated());

        return apiSuccess(
            $records,
            'created',
            200,
            "Bulk Next Process"
        );
    }




    public function getAtsClients(): JsonResponse
    {
        $clients = $this->clientDbService->getAtsClients();

        return apiSuccess(
            $clients
        );
    }

    public function getAtsWorkOrders(Request $request): JsonResponse
    {

        $client_id = $request->get('client_id');
        $workOrders = $this->workOrderDbService->getWorkOrders($client_id);
        return apiSuccess(
            $workOrders
        );
    }
    public function quickSearch(Request $request): JsonResponse
    {
        $search = trim($request->get('search', ''));

        if (strlen($search) < 2) {
            return apiSuccess([]);
        }

        $user = auth()->user();

        $normalizedSearch = strtolower($search);
        $normalizedPassport = $this->normalizePassport($search);
        $looksLikePassport = (bool) preg_match('/^[a-z0-9-]+$/i', $search) && preg_match('/\d/', $search);

        $query = Application::query()
            ->with(['jobList.workOrder.client.user', 'currentProcess.process'])
            ->whereNotNull('job_list_id')
            ->where(function ($q) use ($normalizedSearch, $normalizedPassport, $looksLikePassport) {
                if ($looksLikePassport) {
                    $q->where(function ($passportQuery) use ($normalizedSearch, $normalizedPassport) {
                        $passportQuery
                            ->whereRaw(
                                "LOWER(REPLACE(REPLACE(TRIM(passport_no), ' ', ''), '-', '')) = ?",
                                [$normalizedPassport]
                            )
                            ->orWhereRaw('LOWER(TRIM(passport_no)) = ?', [$normalizedSearch]);
                    });
                    return;
                }

                $q->whereRaw('LOWER(given_name) LIKE ?', ["%{$normalizedSearch}%"])
                    ->orWhereRaw('LOWER(sur_name) LIKE ?', ["%{$normalizedSearch}%"])
                    ->orWhereRaw("LOWER(CONCAT(given_name, ' ', sur_name)) LIKE ?", ["%{$normalizedSearch}%"]);
            });

        if ($user && $user->type === 'agent') {
            $query->where('agent_id', $user->agent?->id);
        } elseif ($user && $user->type === 'client') {
            $clientId = $user->client?->id;
            $query->whereHas('jobList.workOrder', fn ($q) => $q->where('client_id', $clientId));
        }

        $applications = $query
            ->orderByDesc('id')
            ->limit(15)
            ->get()
            ->map(function (Application $application) {
                $job = $application->jobList;
                $jobName = $job?->name ?? 'N/A';
                $jobCode = $job?->job_code ?? 'N/A';
                $canOpenAts = $this->canOpenAts($application);

                return [
                    'application_id' => $application->id,
                    'full_name' => trim(($application->given_name ?? '') . ' ' . ($application->sur_name ?? '')),
                    'passport_no' => $application->passport_no ?? 'N/A',
                    'sex' => $application->sex ?? 'N/A',
                    'worker_image_url' => $application->worker_image_url,
                    'job_list_id' => $application->job_list_id,
                    'job_name' => $jobName,
                    'job_code' => $jobCode,
                    'job_name_with_code' => sprintf('%s (%s)', $jobName, $jobCode),
                    'client_name' => $job?->workOrder?->client?->user?->name ?? 'N/A',
                    'application_status' => $this->formatApplicationStatus($application),
                    'can_open_ats' => $canOpenAts,
                    'process_id' => $canOpenAts ? $this->resolveNavigationProcessId($application) : null,
                ];
            })
            ->values()
            ->toArray();

        return apiSuccess($applications);
    }

    private function normalizePassport(string $value): string
    {
        return strtolower(preg_replace('/[\s\-]/', '', trim($value)));
    }

    private function canOpenAts(Application $application): bool
    {
        $rawStatus = strtoupper($application->getRawOriginal('application_status') ?? '');

        return $rawStatus === 'ATS' && $application->currentProcess !== null;
    }

    private function formatApplicationStatus(Application $application): string
    {
        $rawStatus = $application->getRawOriginal('application_status');

        if (strtoupper($rawStatus ?? '') === 'ATS') {
            return 'ATS';
        }

        return str($rawStatus ?? 'application_list')
            ->replace('_', ' ')
            ->title()
            ->toString();
    }

    private function resolveNavigationProcessId(Application $application): ?int
    {
        $current = $application->currentProcess;

        if (!$current) {
            return null;
        }

        if ($current->status === 'rejected') {
            return (int) config('app.process_rejected_id', 15);
        }

        if ($current->status === 'declined') {
            return (int) config('app.process_declined_id', 16);
        }

        if ($current->status === 'completed' && $current->process?->name === 'on_boarding') {
            return (int) config('app.process_deployed_id', 17);
        }

        return $current->process_id;
    }

    public function getAtsData(Request $request): JsonResponse
    {
        $client_id = $request->get('client_id');

        $user = auth()->user();
        $jobIds = [];
        if ($user && $user->type === 'agent') {
            $jobIds = $user->agent?->applications()->pluck('job_list_id')->toArray() ?? [];
        } else if ($user && $user->type === 'client') {
            $clientId = $user->client?->id;
            $clientJobIds = JobList::whereHas('workOrder', function ($q) use ($clientId) {
                $q->where('client_id', $clientId);
            })->pluck('id')->toArray();
            $jobIds = array_merge($jobIds, $clientJobIds);
        }

        $data = $this->atsDataDbService->getAtsData($client_id, $jobIds);

        return apiSuccess(
            $data
        );
    }

    public function deleteCurrentApplicationProcess(Request $request): JsonResponse
    {
        $applicationProcessId = $request->query('application_process_id');
        $this->atsDataDbService->deleteCurrentApplicationProcess($applicationProcessId);
        $this->service->flushCache(); // Flush cache after deletion to ensure data consistency

        return apiSuccess(
            null,
            'deleted',
            200,
            "Application Process"
        );
    }
}
