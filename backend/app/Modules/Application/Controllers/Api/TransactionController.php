<?php

namespace App\Modules\Application\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApiIndexRequest;
use App\Modules\Application\Requests\TransactionRequest;
use App\Modules\Application\Services\TransactionService;
use App\Modules\Application\Resources\ApplicationTransactionResource;
use App\Modules\Application\Resources\TransactionResource;
use App\Modules\Application\Services\ApplicationService;
use App\Modules\Application\Services\TransactionDataDbService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Modules\Application\Resources\SingleTransactionResource;

class TransactionController extends Controller
{
    public function __construct(private readonly TransactionService $service, public ApplicationService $applicationService, public TransactionDataDbService $transactionDataDbService) {}

    public function index(ApiIndexRequest $request): AnonymousResourceCollection
    {
        $job_list_id = (int) $request->get('job_list_id', null);
        $work_order_id = (int) $request->get('work_order_id', default: null);
        $client_id = (int) $request->get('client_id', default: null);
        $status =  $request->get('status', default: null);
        $payer =  $request->get('payer', default: null);
        $bill_no = $request->get('bill_no', null);
        $filters = $request->filters();
        $filters['work_order_id'] = $work_order_id;
        $filters['bill_no'] = $bill_no;
        $filters['job_list_id'] = $job_list_id;
        $filters['client_id'] = $client_id;
        $filters['status'] = $status;
        $filters['payer'] = $payer;
        $data = $this->service->getPaginatedDataWithCache($filters['page'], $filters['per_page'], $filters);
        return TransactionResource::collection($data);
    }

    public function applicationTransactions(Request $request): AnonymousResourceCollection
    {
        $search = $request->get('search', null);
        $data = $this->service->getApplicationTransactions($search);
        return ApplicationTransactionResource::collection($data);
    }

    public function store(TransactionRequest $request): JsonResponse
    {
        $application = $this->applicationService->getById($request->validated()['application_id']);
        $record = $this->service->create($request->validated(), $application);
        return apiSuccess(new TransactionResource($record), 'created');
    }
    public function show(int $id): SingleTransactionResource
    {
        return new SingleTransactionResource($this->service->getById($id));
    }
    public function update(TransactionRequest $request, int $id): JsonResponse
    {
        $record = $this->service->update($id, $request->validated());
        return apiSuccess(new TransactionResource($record), 'updated');
    }
    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id);
        return apiSuccess(null, 'deleted');
    }
    public function bulkDelete(Request $request): JsonResponse
    {
        $this->service->bulkDelete($request->ids);
        return apiSuccess(null, 'deleted', 200, 'Records');
    }

    public function getTransactionData(): JsonResponse
    {
        $transactionData = $this->transactionDataDbService->getTransactionData();
        return apiSuccess($transactionData, 'fetched');
    }
}
