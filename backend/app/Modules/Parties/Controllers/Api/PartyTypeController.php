<?php

namespace App\Modules\Parties\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Parties\Models\PartyType;
use App\Modules\Parties\Requests\PartyTypeBulkDeleteRequest;
use App\Modules\Parties\Requests\PartyTypeIndexRequest;
use App\Modules\Parties\Requests\PartyTypeRequest;
use App\Modules\Parties\Resources\PartyTypeResource;
use App\Modules\Parties\Services\PartyTypeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PartyTypeController extends Controller
{
    public function __construct(
        private readonly PartyTypeService $service
    ) {}

    public function index(PartyTypeIndexRequest $request): AnonymousResourceCollection
    {
        return PartyTypeResource::collection(
            $this->service->getPaginatedData($request->filters())
        );
    }

    public function options(Request $request): JsonResponse
    {
        $options = $this->service->getOptions([
            'status' => $request->query('status', 'active'),
        ])->map(fn (PartyType $type) => [
            'id' => $type->code,
            'party_type_id' => $type->id,
            'name' => $type->name,
            'code' => $type->code,
            'status' => $type->status,
            'sort_order' => (int) $type->sort_order,
            'source_module' => $type->source_module,
        ])->values();

        return apiSuccess($options);
    }

    public function store(PartyTypeRequest $request): JsonResponse
    {
        $partyType = $this->service->create($request->validated());

        return apiSuccess(
            new PartyTypeResource($partyType),
            'created',
            201,
            'Party Type'
        );
    }

    public function show(PartyType $partyType): PartyTypeResource
    {
        return new PartyTypeResource(
            $this->service->getPartyType($partyType)
        );
    }

    public function update(PartyTypeRequest $request, PartyType $partyType): JsonResponse
    {
        $partyType = $this->service->update($partyType, $request->validated());

        return apiSuccess(
            new PartyTypeResource($partyType),
            'updated',
            200,
            'Party Type'
        );
    }

    public function destroy(PartyType $partyType): JsonResponse
    {
        $this->service->delete($partyType);

        return apiSuccess(null, 'deleted', 200, 'Party Type');
    }

    public function bulkDelete(PartyTypeBulkDeleteRequest $request): JsonResponse
    {
        $this->service->bulkDelete($request->validated('ids'));

        return apiSuccess(null, 'deleted', 200, 'Party Types');
    }
}
