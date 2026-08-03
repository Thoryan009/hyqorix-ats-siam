<?php

namespace App\Modules\Application\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApiIndexRequest;
use App\Modules\Application\Requests\CancelledInvoiceRequest;
use App\Modules\Application\Requests\CollectInvoiceRequest;
use App\Modules\Application\Requests\GenerateInvoiceRequest;
use App\Modules\Application\Requests\SendMailRequest;
use App\Modules\Application\Resources\ClientBillResource;
use App\Modules\Application\Resources\SingleClientBillResource;
use App\Modules\Application\Services\ClientBillDataDbService;
use App\Modules\Application\Services\ClientBillService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientBillController extends Controller
{
    public function __construct(
        private ClientBillService $service,
        private readonly ClientBillDataDbService $clientBillDataDbService,
    ) {}
    public function index(ApiIndexRequest $request)
    {


        $filters = $request->filters();
        $filters['job_list_id'] = $request->get('job_id', null);
        $filters['bill_no'] = $request->get('bill_no', null);
        $filters['transaction_status'] = $request->get('transaction_status', null);

        $result = $this->service->getPaginatedDataWithCache($filters);

        return ClientBillResource::collection($result['data'])
            ->additional([
                'bill_filters' => $result['bill_filters'],
            ]);
    }


    public function getByBillNo($billNo, Request $request): SingleClientBillResource
    {
        $jobId = $request->query('job_id');
        $bill = $this->service->getByBillNo($billNo, $jobId);
        return new SingleClientBillResource($bill);
    }

    public function generateInvoice(GenerateInvoiceRequest $request): JsonResponse
    {

        $billNo = $this->service->generateInvoice($request->validated());
        $this->clientBillDataDbService->clearClientBillDataCache();
        return apiSuccess(
            ['bill_no' => $billNo],
            'Invoice generated successfully'
        );
    }

    public function collectInvoice(CollectInvoiceRequest $request): JsonResponse
    {
        $this->service->collectInvoiceService($request->validated()['bill_no']);
        $this->clientBillDataDbService->clearClientBillDataCache();
        return apiSuccess(null, 'Invoice collected successfully');
    }

    public function cancelledInvoice(CancelledInvoiceRequest $request): JsonResponse
    {
        $this->service->cancelledInvoiceService($request->validated()['bill_no']);
        $this->clientBillDataDbService->clearClientBillDataCache();
        return apiSuccess(null, 'Invoice cancelled successfully');
    }

    public function updateInvoiceAndSendMail(SendMailRequest $request): JsonResponse
    {
        $this->service->updateInvoiceAndSendMail($request);
        $this->clientBillDataDbService->clearClientBillDataCache();
        return apiSuccess(null, 'Invoice email sent successfully');
    }

    public function getClientBillData(): JsonResponse
    {
        $clientBillData = $this->clientBillDataDbService->getClientBillData();
        return apiSuccess(
            $clientBillData,
            'fetched'
        );
    }
}
