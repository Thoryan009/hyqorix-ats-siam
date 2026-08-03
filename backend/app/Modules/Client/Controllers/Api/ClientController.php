<?php

namespace App\Modules\Client\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Client\Requests\ClientRequest;
use App\Http\Requests\ApiIndexRequest;
use App\Modules\Client\Resources\SingleClientResource;
use App\Modules\Client\Contracts\ClientDataServiceInterface;
use App\Modules\Client\Resources\ClientResource;
use App\Modules\Client\Services\ClientService;
use App\Modules\WorkOrder\Services\WorkOrderDataDbService;
use App\Modules\Application\Services\ApplicationDataDbService;
use App\Modules\Application\Services\TransactionDataDbService;
use App\Modules\JobList\Services\AtsDataDbService;
use App\Modules\Reports\Services\ClientDbService;
use App\Modules\JobList\Services\JobListDataDbService;
use App\Modules\Shared\Helpers\FileHelper;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use PHPUnit\Util\PHP\Job;

class ClientController extends Controller
{
    public function __construct(
        private readonly ClientService $service,
        private readonly ClientDataServiceInterface $clientDataService,
        private readonly WorkOrderDataDbService $workOrderDataDbService,
        private readonly ApplicationDataDbService $applicationDataDbService,
        private readonly TransactionDataDbService $transactionDataDbService,
        private readonly JobListDataDbService $jobListDataDbService,
        private readonly AtsDataDbService $atsDataDbService,
        private readonly ClientDbService $clientDbService,


    ) {}

    public function index(ApiIndexRequest $request): AnonymousResourceCollection
    {
        $filters = $request->filters();
        $filters['country_id'] = $request->get('country_id', null);
        $data = $this->service->getPaginatedDataWithCache($filters);
        return ClientResource::collection($data);
    }

    public function store(ClientRequest $request): JsonResponse
    {
        $storedFiles = []; // Track uploaded files for rollback

        try {
            $data = $request->validated();

            // Handle file upload (example: client_image_path)
            if ($request->hasFile('client_image_path')) {
                $path = FileHelper::store($request->file('client_image_path'), 'clients');
                $data['client_image_path'] = $path;
                $storedFiles['client_image_path'] = $path;
            }

            // Create client
            $record = $this->service->create($data);

            // Clear caches
            $this->workOrderDataDbService->clearWorkOrderDataCache();
            $this->applicationDataDbService->clearApplicationDataCache();
            $this->transactionDataDbService->clearTransactionDataCache();
            $this->jobListDataDbService->clearJobListDataCache();
            $this->atsDataDbService->flushCache();
            $this->clientDbService->clearClientDataCache();

            return apiSuccess(
                new ClientResource($record),
                'created',
                201
            );
        } catch (\Exception $e) {
            // Rollback stored files if anything fails
            foreach ($storedFiles as $filePath) {
                FileHelper::delete($filePath);
            }

            throw $e; // Let global handler manage error
        }
    }

    public function show(int $id): SingleClientResource
    {
        return new SingleClientResource($this->service->getById($id));
    }

    public function update(ClientRequest $request, int $id): JsonResponse
    {
        $storedFiles = [];

        try {

            // Get existing client
            $client = $this->service->getById($id);

            $data = $request->validated();

            // -----------------------------
            // IMAGE UPDATE LOGIC
            // -----------------------------
            if ($request->hasFile('client_image_path')) {

                // delete old image if exists
                if (!empty($client->client_image_path)) {
                    FileHelper::delete($client->client_image_path);
                }

                // store new image
                $path = FileHelper::store(
                    $request->file('client_image_path'),
                    'clients'
                );

                $data['client_image_path'] = $path;
                $storedFiles['client_image_path'] = $path;
            }

            // -----------------------------
            // UPDATE CLIENT
            // -----------------------------
            $record = $this->service->update($id, $data);

            // -----------------------------
            // CACHE CLEAR
            // -----------------------------
            $this->workOrderDataDbService->clearWorkOrderDataCache();
            $this->applicationDataDbService->clearApplicationDataCache();
            $this->transactionDataDbService->clearTransactionDataCache();
            $this->jobListDataDbService->clearJobListDataCache();
            $this->atsDataDbService->flushCache();
            $this->clientDbService->clearClientDataCache();

            return apiSuccess(
                new ClientResource($record),
                'updated'
            );
        } catch (\Exception $e) {

            // rollback newly uploaded files
            foreach ($storedFiles as $filePath) {
                FileHelper::delete($filePath);
            }

            throw $e;
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            // -----------------------------
            // GET CLIENT (for file path)
            // -----------------------------
            $client = $this->service->getById($id);

            $filePath = $client->client_image_path;

            // -----------------------------
            // DELETE FROM DB
            // -----------------------------
            $this->service->delete($id);

            // -----------------------------
            // DELETE FILE (AFTER DB SUCCESS)
            // -----------------------------
            if (!empty($filePath)) {
                FileHelper::delete($filePath);
            }

            // -----------------------------
            // CLEAR CACHE
            // -----------------------------
            $this->workOrderDataDbService->clearWorkOrderDataCache();
            $this->applicationDataDbService->clearApplicationDataCache();
            $this->transactionDataDbService->clearTransactionDataCache();
            $this->jobListDataDbService->clearJobListDataCache();
            $this->atsDataDbService->flushCache();
            $this->clientDbService->clearClientDataCache();

            return apiSuccess(null, 'deleted');
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        try {
            $ids = $request->ids;

            // -----------------------------
            // GET FILE PATHS
            // -----------------------------
            $clients = $this->service->getByIds($ids);
            $filePaths = $clients->pluck('client_image_path')->filter()->toArray();

            // -----------------------------
            // DELETE FROM DB
            // -----------------------------
            $this->service->bulkDelete($ids);

            // -----------------------------
            // DELETE FILES
            // -----------------------------
            foreach ($filePaths as $filePath) {
                FileHelper::delete($filePath);
            }

            // -----------------------------
            // CLEAR CACHE
            // -----------------------------
            $this->workOrderDataDbService->clearWorkOrderDataCache();
            $this->applicationDataDbService->clearApplicationDataCache();
            $this->transactionDataDbService->clearTransactionDataCache();
            $this->jobListDataDbService->clearJobListDataCache();
            $this->atsDataDbService->flushCache();
            $this->clientDbService->clearClientDataCache();

            return apiSuccess(null, 'deleted', 200, 'Records');
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getClientCountries(): JsonResponse
    {
        $countries = $this->clientDataService->getClientData();
        return apiSuccess(
            $countries,
            'fetched',
            200,
            'Countries'
        );
    }
}
