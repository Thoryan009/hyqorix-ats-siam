<?php

namespace App\Modules\Vendor\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApiIndexRequest;
use App\Modules\Shared\Helpers\FileHelper;
use App\Modules\Vendor\Contracts\VendorDataServiceInterface;
use App\Modules\Vendor\Requests\VendorRequest;
use App\Modules\Vendor\Resources\SingleVendorResource;
use App\Modules\Vendor\Resources\VendorResource;
use App\Modules\Vendor\Services\VendorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class VendorController extends Controller
{
    public function __construct(
        private readonly VendorService $service,
        private readonly VendorDataServiceInterface $vendorDataService,
    ) {}

    public function index(ApiIndexRequest $request): AnonymousResourceCollection
    {
        $filters = $request->filters();
        $filters['status'] = $request->get('status');

        return VendorResource::collection(
            $this->service->getPaginatedDataWithCache($filters)
        );
    }

    public function store(VendorRequest $request): JsonResponse
    {
        $storedFiles = [];

        try {
            $data = $request->validated();

            if ($request->hasFile('vendor_image_path')) {
                $path = FileHelper::store($request->file('vendor_image_path'), 'vendors');
                $data['vendor_image_path'] = $path;
                $storedFiles['vendor_image_path'] = $path;
            }

            $record = $this->service->create($data);
            $this->vendorDataService->clearVendorDataCache();

            return apiSuccess(new VendorResource($record), 'created', 201);
        } catch (\Exception $e) {
            foreach ($storedFiles as $filePath) {
                FileHelper::delete($filePath);
            }

            throw $e;
        }
    }

    public function show(int $id): SingleVendorResource
    {
        return new SingleVendorResource($this->service->getById($id));
    }

    public function update(VendorRequest $request, int $id): JsonResponse
    {
        $storedFiles = [];

        try {
            $vendor = $this->service->getById($id);
            $data = $request->validated();

            if ($request->hasFile('vendor_image_path')) {
                if (!empty($vendor->vendor_image_path)) {
                    FileHelper::delete($vendor->vendor_image_path);
                }

                $path = FileHelper::store($request->file('vendor_image_path'), 'vendors');
                $data['vendor_image_path'] = $path;
                $storedFiles['vendor_image_path'] = $path;
            }

            $record = $this->service->update($id, $data);
            $this->vendorDataService->clearVendorDataCache();

            return apiSuccess(new VendorResource($record), 'updated');
        } catch (\Exception $e) {
            foreach ($storedFiles as $filePath) {
                FileHelper::delete($filePath);
            }

            throw $e;
        }
    }

    public function destroy(int $id): JsonResponse
    {
        $vendor = $this->service->getById($id);
        $filePath = $vendor->vendor_image_path;

        $this->service->delete($id);
        $this->vendorDataService->clearVendorDataCache();

        if (!empty($filePath)) {
            FileHelper::delete($filePath);
        }

        return apiSuccess(null, 'deleted');
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        $ids = $request->ids;
        $vendors = $this->service->getByIds($ids);
        $filePaths = $vendors->pluck('vendor_image_path')->filter()->toArray();

        $this->service->bulkDelete($ids);
        $this->vendorDataService->clearVendorDataCache();

        foreach ($filePaths as $filePath) {
            FileHelper::delete($filePath);
        }

        return apiSuccess(null, 'deleted', 200, 'Records');
    }

    public function getVendorData(): JsonResponse
    {
        return apiSuccess($this->vendorDataService->getVendorData(), 'fetched', 200, 'Vendor data');
    }
}
