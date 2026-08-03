<?php

namespace App\Modules\PassportHandover\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApiIndexRequest;
use App\Modules\PassportHandover\Models\PassportHandover;
use App\Modules\PassportHandover\Requests\CollectPassportHandoverRequest;
use App\Modules\PassportHandover\Requests\PassportHandoverRequest;
use App\Modules\PassportHandover\Resources\PassportHandoverResource;
use App\Modules\PassportHandover\Resources\PassportHandoverSearchItemResource;
use App\Modules\PassportHandover\Services\PassportHandoverService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PassportHandoverController extends Controller
{
    public function __construct(
        private readonly PassportHandoverService $service
    ) {}

    public function index(ApiIndexRequest $request): AnonymousResourceCollection
    {
        $filters = $request->filters();
        $filters['type'] = $request->get('type');
        $filters['status'] = $request->get('status');
        $filters['passport_no'] = $request->get('passport_no');

        $data = $this->service->getPaginatedDataWithCache($filters);

        return PassportHandoverResource::collection($data);
    }

    public function searchByPassport(Request $request): JsonResponse
    {
        $request->validate([
            'passport_no' => ['required', 'string', 'min:1'],
        ]);

        $items = $this->service->searchByPassport($request->get('passport_no'));

        return apiSuccess(
            PassportHandoverSearchItemResource::collection($items),
            'Passport handover search results'
        );
    }

    public function store(PassportHandoverRequest $request): JsonResponse
    {
        $handover = $this->service->createPassportHandover($request->validated());

        return apiSuccess(
            new PassportHandoverResource($handover),
            'created'
        );
    }

    public function show(PassportHandover $passportHandover): PassportHandoverResource
    {
        return new PassportHandoverResource(
            $this->service->getPassportHandover($passportHandover)
        );
    }

    public function destroy(PassportHandover $passportHandover): JsonResponse
    {
        $this->service->deletePassportHandover($passportHandover);

        return apiSuccess(null, 'deleted');
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        $this->service->bulkDelete($request->ids);

        return apiSuccess(null, 'deleted', 200, 'Records');
    }

    public function collect(CollectPassportHandoverRequest $request, PassportHandover $passportHandover): JsonResponse
    {
        $handover = $this->service->collectPassports($passportHandover, $request->validated());

        return apiSuccess(
            new PassportHandoverResource($handover),
            'Passport collection recorded successfully'
        );
    }
}
