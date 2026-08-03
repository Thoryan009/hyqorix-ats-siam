<?php

namespace App\Modules\Application\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Application\Models\ClientTransaction;
use App\Modules\Application\Requests\ClientTransactionRequest;
use App\Modules\Application\Resources\ClientTransactionResource;
use App\Modules\Application\Services\ClientTransactionService;
use App\Http\Requests\ApiIndexRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ClientTransactionController extends Controller
{
    public function __construct(
        private readonly ClientTransactionService $service
    ) {}

    public function index(ApiIndexRequest $request): AnonymousResourceCollection
    {
        $filters = $request->filters();

        $clientTransactionData = $this->service->getPaginatedDataWithCache(
            $filters
        );

        return ClientTransactionResource::collection($clientTransactionData);
    }

    public function store(ClientTransactionRequest $request): JsonResponse
    {
        $clientTransaction = $this->service->createClientTransaction($request->validated());

        return apiSuccess(
            new ClientTransactionResource($clientTransaction),
            'created'
        );
    }

    public function show(ClientTransaction $clientTransaction): ClientTransactionResource
    {
        return new ClientTransactionResource(
            $this->service->getClientTransaction($clientTransaction)
        );
    }

    public function update(ClientTransactionRequest $request, ClientTransaction $clientTransaction): JsonResponse
    {
        $clientTransaction = $this->service->updateClientTransaction(
            $clientTransaction,
            $request->validated()
        );

        return apiSuccess(
            new ClientTransactionResource($clientTransaction),
            'updated'
        );
    }

    public function destroy(ClientTransaction $clientTransaction): JsonResponse
    {
        $this->service->deleteClientTransaction($clientTransaction);

        return apiSuccess(
            null,
            'deleted'
        );
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        $this->service->bulkDelete($request->ids);

        return apiSuccess(
            null,
            'deleted',
            200,
            'Records'
        );
    }
}
