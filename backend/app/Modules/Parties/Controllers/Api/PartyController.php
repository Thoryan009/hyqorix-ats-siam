<?php

namespace App\Modules\Parties\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Parties\Models\Party;
use App\Modules\Parties\Requests\PartyBulkDeleteRequest;
use App\Modules\Parties\Requests\PartyIndexRequest;
use App\Modules\Parties\Requests\PartyRequest;
use App\Modules\Parties\Resources\PartyResource;
use App\Modules\Parties\Services\PartyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PartyController extends Controller
{
    public function __construct(
        private readonly PartyService $service
    ) {}

    public function index(PartyIndexRequest $request): AnonymousResourceCollection
    {
        return PartyResource::collection(
            $this->service->getPaginatedData($request->filters())
        );
    }

    public function store(PartyRequest $request): JsonResponse
    {
        $party = $this->service->create($request->validated());

        return apiSuccess(
            new PartyResource($party),
            'created',
            201,
            'Party'
        );
    }

    public function show(Party $party): PartyResource
    {
        return new PartyResource(
            $this->service->getParty($party)
        );
    }

    public function update(PartyRequest $request, Party $party): JsonResponse
    {
        $party = $this->service->update($party, $request->validated());

        return apiSuccess(
            new PartyResource($party),
            'updated',
            200,
            'Party'
        );
    }

    public function destroy(Party $party): JsonResponse
    {
        $this->service->delete($party);

        return apiSuccess(null, 'deleted', 200, 'Party');
    }

    public function bulkDelete(PartyBulkDeleteRequest $request): JsonResponse
    {
        $this->service->bulkDelete($request->validated('ids'));

        return apiSuccess(null, 'deleted', 200, 'Records');
    }
}
