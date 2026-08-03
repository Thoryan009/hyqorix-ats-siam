<?php

namespace App\Modules\JobList\Controllers\Api;

use App\Traits\HandlesAgentFilter;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApiIndexRequest;
use App\Modules\Application\Services\ApplicationDataDbService;
use App\Modules\Application\Services\TransactionDataDbService;
use App\Modules\JobList\Contracts\AtsDataServiceInterface;
use App\Modules\JobList\Requests\JobListRequest;
use App\Modules\JobList\Services\JobListService;
use App\Modules\JobList\Resources\JobListResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Modules\JobList\Services\JobListDataDbService;
use App\Modules\Reports\Services\JobListDbService;
use App\Traits\HandlesClientFilter;

class JobListController extends Controller
{
    use HandlesClientFilter, HandlesAgentFilter;
    public function __construct(
        private readonly JobListService $service,
        private readonly JobListDataDbService $jobListDataDbService,
        private readonly AtsDataServiceInterface $atsDataDbService,
        private readonly  ApplicationDataDbService $applicationDataDbService,
        private readonly TransactionDataDbService $transactionDataDbService,
        private readonly JobListDbService $jobListDbService,

    ) {}

    public function index(ApiIndexRequest $request): AnonymousResourceCollection
    {
        $filters = $request->filters();

        $filters['work_order_id'] = $request->get('work_order_id');
        $filters['principal_id'] = $request->get('principal_id');
        $filters['status'] = $request->get('status');
        $filters['has_ats_applications'] = $request->get('has_ats_applications');
        $filters['include_ats_count'] = $request->get('include_ats_count');

        // reusable client filter
        $filters = $this->applyClientFilter($request, $filters);
        $filters = $this->applyAgentFilter($request, $filters);

        $data = $this->service->getPaginatedDataWithCache($filters);

        return JobListResource::collection($data);
    }


    public function store(JobListRequest $request): JsonResponse
    {
        $record = $this->service->create($request->validated());
        $this->atsDataDbService->flushCache();
        $this->applicationDataDbService->clearApplicationDataCache();
        $this->transactionDataDbService->clearTransactionDataCache();
        $this->jobListDbService->clearJobListCache();
        return apiSuccess(
            new JobListResource($record),
            'created'
        );
    }

    public function show(int $id): JobListResource
    {
        $jobList = $this->service->getById($id);
        $this->authorizeClientAccess($jobList);
        return new JobListResource(
            $jobList
        );
    }

    public function update(JobListRequest $request, int $id): JsonResponse
    {
        $jobList = $this->service->getById($id);
        $this->authorizeClientAccess($jobList);
        $record = $this->service->update($id, $request->validated());

        $this->atsDataDbService->flushCache();
        $this->applicationDataDbService->clearApplicationDataCache();
        $this->transactionDataDbService->clearTransactionDataCache();
        $this->jobListDbService->clearJobListCache();
        return apiSuccess(
            new JobListResource($record),
            'updated'
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $jobList = $this->service->getById($id);
        $this->authorizeClientAccess($jobList);
        $this->service->delete($id);
        $this->atsDataDbService->flushCache();
        $this->applicationDataDbService->clearApplicationDataCache();
        $this->transactionDataDbService->clearTransactionDataCache();
        $this->jobListDbService->clearJobListCache();

        return apiSuccess(
            null,
            'deleted'
        );
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        $user = auth()->user();
        if ($user->type === 'client') {
            $ids = $request->ids;
            $count = $this->service->countByIdsAndClient($ids, $user->client->id);
            if ($count !== count($ids)) {
                abort(403, 'Unauthorized bulk delete');
            }
        }

        $this->service->bulkDelete($request->ids);
        $this->atsDataDbService->flushCache();
        $this->applicationDataDbService->clearApplicationDataCache();
        $this->transactionDataDbService->clearTransactionDataCache();
        $this->jobListDbService->clearJobListCache();

        return apiSuccess(
            null,
            'deleted',
            200,
            'Records'
        );
    }

    public function getJobListData()
    {

        $workOrders = $this->jobListDataDbService->getJobListData();
        return apiSuccess(
            $workOrders
        );
    }

    private function authorizeClientAccess($jobList): void
    {
        $user = auth()->user();
        if ($user->type === 'client') {
            if (!$user->client || $jobList->workOrder->client_id !== $user->client->id) {
                abort(403, 'Unauthorized access');
            }
        }
    }
}
