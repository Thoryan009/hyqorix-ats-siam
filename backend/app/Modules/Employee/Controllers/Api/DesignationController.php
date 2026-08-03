<?php

namespace App\Modules\Employee\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Modules\Employee\Requests\DesignationRequest;
use App\Modules\Employee\Services\DesignationService;
use App\Modules\Employee\Resources\DesignationResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Http\Requests\ApiIndexRequest;
use App\Modules\Employee\Services\EmployeeDataDbService;

class DesignationController extends Controller
{
    public function __construct(
        private DesignationService $service,
        private readonly EmployeeDataDbService $employeeDataDbService,
    ) {}
    public function index(ApiIndexRequest $request): AnonymousResourceCollection
    {
         $filters = $request->filters();
         $data = $this->service->getPaginatedDataWithCache($filters);
         return DesignationResource::collection($data);
    }

    public function store(DesignationRequest $request)
    {
        $designation = $this->service->create(
            $request->validated()
        );
        $this->employeeDataDbService->clearEmployeeDataCache();
        return apiSuccess(
            new DesignationResource($designation),
            'created'
        );
    }

    public function show($id)
    {
        return new DesignationResource($this->service->getById($id));
    }

    public function update(DesignationRequest $request, $id)
    {
        $record = $this->service->update(
            $id,
            $request->validated()
        );
        $this->employeeDataDbService->clearEmployeeDataCache();

         return apiSuccess(
            new DesignationResource($record),
            'updated'
        );
    }

    public function destroy($id)
    {
        $this->service->delete($id);
        $this->employeeDataDbService->clearEmployeeDataCache();
         return apiSuccess(
            null,
            'deleted'
        );
    }

    public function bulkDelete(Request $request)
    {
        $this->service->bulkDelete($request->ids);
        $this->employeeDataDbService->clearEmployeeDataCache();
        return apiSuccess(
            null,
            'deleted',
            200,
            'Records'
        );
    }
}
