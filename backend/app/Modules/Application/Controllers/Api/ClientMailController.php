<?php

namespace App\Modules\Application\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Application\Models\ClientMail;
use App\Modules\Application\Requests\ClientMailRequest;
use App\Modules\Application\Resources\ClientMailResource;
use App\Modules\Application\Services\ClientMailService;
use App\Http\Requests\ApiIndexRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ClientMailController extends Controller
{
    public function __construct(
        private readonly ClientMailService $service
    ) {}

    public function index(ApiIndexRequest $request): AnonymousResourceCollection
    {
        $filters = $request->filters();

        $clientMailData = $this->service->getPaginatedDataWithCache(
            $filters
        );

        return ClientMailResource::collection($clientMailData);
    }

    public function store(ClientMailRequest $request): JsonResponse
    {
        $clientMail = $this->service->createClientMail($request->validated());

        return apiSuccess(
            new ClientMailResource($clientMail),
            'created'
        );
    }

    public function show(ClientMail $clientMail): ClientMailResource
    {
        return new ClientMailResource(
            $this->service->getClientMail($clientMail)
        );
    }

    public function update(ClientMailRequest $request, ClientMail $clientMail): JsonResponse
    {
        $clientMail = $this->service->updateClientMail(
            $clientMail,
            $request->validated()
        );

        return apiSuccess(
            new ClientMailResource($clientMail),
            'updated'
        );
    }

    public function destroy(ClientMail $clientMail): JsonResponse
    {
        $this->service->deleteClientMail($clientMail);

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
