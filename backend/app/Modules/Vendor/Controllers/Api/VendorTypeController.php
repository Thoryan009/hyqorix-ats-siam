<?php

namespace App\Modules\Vendor\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApiIndexRequest;
use App\Modules\Vendor\Models\VendorType;
use App\Modules\Vendor\Requests\VendorTypeRequest;
use App\Modules\Vendor\Resources\VendorTypeResource;
use App\Modules\Vendor\Services\VendorTypeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class VendorTypeController extends Controller
{
    public function __construct(
        private readonly VendorTypeService $service
    ) {}

    public function index(ApiIndexRequest $request): AnonymousResourceCollection
    {
        $filters = $request->filters();
        $filters['status'] = $request->get('status');

        return VendorTypeResource::collection(
            $this->service->getPaginatedDataWithCache($filters)
        );
    }

    public function store(VendorTypeRequest $request): JsonResponse
    {
        $vendorType = $this->service->createVendorType($request->validated());

        return apiSuccess(
            new VendorTypeResource($vendorType),
            'created'
        );
    }

    public function show(VendorType $vendorType): VendorTypeResource
    {
        return new VendorTypeResource(
            $this->service->getVendorType($vendorType)
        );
    }

    public function update(VendorTypeRequest $request, VendorType $vendorType): JsonResponse
    {
        $vendorType = $this->service->updateVendorType($vendorType, $request->validated());

        return apiSuccess(
            new VendorTypeResource($vendorType),
            'updated'
        );
    }

    public function destroy(VendorType $vendorType): JsonResponse
    {
        $this->service->deleteVendorType($vendorType);

        return apiSuccess(null, 'deleted');
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        $this->service->bulkDelete($request->ids);

        return apiSuccess(null, 'deleted', 200, 'Records');
    }
}
