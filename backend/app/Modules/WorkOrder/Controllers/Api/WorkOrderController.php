<?php

namespace App\Modules\WorkOrder\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\WorkOrder\Requests\WorkOrderRequest;
use App\Http\Requests\ApiIndexRequest;
use App\Modules\Application\Models\Application;
use App\Modules\Application\Models\Transaction;
use App\Modules\Application\Services\ApplicationDataDbService;
use App\Modules\Application\Services\TransactionDataDbService;
use App\Modules\JobList\Contracts\AtsDataServiceInterface;
use App\Modules\JobList\Services\JobListDataDbService;
use App\Modules\Reports\Services\WorkOrderDbService;
use App\Modules\WorkOrder\Services\WorkOrderDataDbService;
use App\Modules\WorkOrder\Resources\WorkOrderResource;
use App\Modules\WorkOrder\Services\ClientDbService;
use App\Modules\WorkOrder\Services\WorkOrderService;
use App\Modules\Shared\Helpers\FileHelper;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class WorkOrderController extends Controller
{
    public function __construct(private readonly WorkOrderService $service, private readonly ClientDbService $clientDbService, private readonly WorkOrderDataDbService $workOrderDataDbService, private readonly JobListDataDbService $jobListDataDbService, private readonly ApplicationDataDbService $applicationDataDbService, private readonly TransactionDataDbService $transactionDataDbService, private readonly WorkOrderDbService $workOrderDbService, private readonly AtsDataServiceInterface $atsDataService) {}

    public function index(ApiIndexRequest $request): AnonymousResourceCollection
    {
        $filters = $request->filters();

        $user = auth()->user();
        if ($user->type === 'client') {
            $filters['client_id'] = $user->client->id;
        } else {
            $filters['client_id'] = $request->get('client_id', null);
        }

        $filters['employee_id'] = $request->get('employee_id', null);
        $filters['has_jobs'] = $request->get('has_jobs');
        $filters['include_jobs_count'] = $request->get('include_jobs_count');
        $filters['with'] = ['client.user', 'employee.user'];
        $data = $this->service->getPaginatedDataWithCache($filters);

        return WorkOrderResource::collection($data);
    }

    public function store(WorkOrderRequest $request): JsonResponse
    {
        $storedFiles = []; // Keep track of stored files in case we need to rollback
        try {
            $data = $request->validated(); // Get validated input

            if ($request->hasFile('work_order_path')) {
                $path = FileHelper::store($request->file('work_order_path'), 'work_orders');
                $data['work_order_path'] = $path;
                $storedFiles['work_order_path'] = $path;
            }

            if ($request->has('client_id')) {
                $client = $this->clientDbService->getClientById($request->client_id);
            }
            $record = $this->service->create($data, $client);
            // Clear relevant caches after successful creation
            $this->jobListDataDbService->clearJobListDataCache();
            $this->applicationDataDbService->clearApplicationDataCache();
            $this->transactionDataDbService->clearTransactionDataCache();
            $this->workOrderDbService->clearWorkOrderCache();
            $this->atsDataService->flushCache();

            return apiSuccess(new WorkOrderResource($record), 'created');
        } catch (\Exception $e) {
            // Rollback any stored files if an error occurs
            foreach ($storedFiles as $path) {
                FileHelper::delete($path);
            }
            throw $e; // Re-throw the exception after cleanup
        }
    }

    public function show(int $id): WorkOrderResource
    {
        $workOrder = $this->service->getById($id);
        $this->authorizeClientAccess($workOrder);
        return new WorkOrderResource($workOrder);
    }

        public function update(WorkOrderRequest $request, int $id): JsonResponse
{
    $storedFiles = [];

    try {
        $workOrder = $this->service->getById($id);
        $this->authorizeClientAccess($workOrder);

        $data = $request->validated();

        // -----------------------------
        // FILE UPDATE LOGIC
        // -----------------------------
        if ($request->hasFile('work_order_path')) {

            // delete old file first (important)
            if (!empty($workOrder->work_order_path)) {
                FileHelper::delete($workOrder->work_order_path);
            }

            // store new file
            $path = FileHelper::store(
                $request->file('work_order_path'),
                'work_orders'
            );

            $data['work_order_path'] = $path;
            $storedFiles['work_order_path'] = $path;
        }

        // -----------------------------
        // UPDATE RECORD
        // -----------------------------
        $record = $this->service->update($id, $data);

        // -----------------------------
        // CACHE CLEAR
        // -----------------------------
        $this->jobListDataDbService->clearJobListDataCache();
        $this->applicationDataDbService->clearApplicationDataCache();
        $this->transactionDataDbService->clearTransactionDataCache();
        $this->workOrderDbService->clearWorkOrderCache();
        $this->atsDataService->flushCache();

        return apiSuccess(new WorkOrderResource($record), 'updated');

    } catch (\Exception $e) {

        // rollback newly uploaded file if error happens
        foreach ($storedFiles as $path) {
            FileHelper::delete($path);
        }

        throw $e;
    }
}

    public function destroy(int $id): JsonResponse
    {
        $workOrder = $this->service->getById($id);
        $this->authorizeClientAccess($workOrder);
        $this->service->delete($id);
        $this->workOrderDataDbService->clearWorkOrderDataCache();
        $this->jobListDataDbService->clearJobListDataCache();
        $this->applicationDataDbService->clearApplicationDataCache();
        $this->transactionDataDbService->clearTransactionDataCache();
        $this->workOrderDbService->clearWorkOrderCache();
        $this->atsDataService->flushCache();
        return apiSuccess(null, 'deleted');
    }

    public function bulkDelete(Request $request)
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
        $this->workOrderDataDbService->clearWorkOrderDataCache();
        $this->jobListDataDbService->clearJobListDataCache();
        $this->applicationDataDbService->clearApplicationDataCache();
        $this->transactionDataDbService->clearTransactionDataCache();
        $this->workOrderDbService->clearWorkOrderCache();
        $this->atsDataService->flushCache();
        return apiSuccess(null, 'deleted', 200, 'Records');
    }

    public function getWorkOrderClients(): JsonResponse
    {
        $clients = $this->clientDbService->getClients();
        return apiSuccess($clients);
    }

    public function getWorkOrderData(): JsonResponse
    {
        $workOrderData = $this->workOrderDataDbService->getWorkOrderData();
        return apiSuccess($workOrderData, 'fetched');
    }
    private function authorizeClientAccess($workOrder): void
    {
        $user = auth()->user();
        if ($user->type === 'client') {
            if (!$user->client || $workOrder->client_id !== $user->client->id) {
                abort(403, 'Unauthorized access');
            }
        }
    }
}
