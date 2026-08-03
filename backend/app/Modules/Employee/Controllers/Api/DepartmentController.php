<?php

namespace App\Modules\Employee\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Employee\Models\Department;
use App\Modules\Employee\Requests\DepartmentRequest;
use App\Modules\Employee\Resources\DepartmentResource;
use App\Modules\Employee\Services\DepartmentService;
use App\Http\Requests\ApiIndexRequest;
use App\Modules\Shared\Services\FilterResolver;
use App\Modules\Employee\Contracts\DepartmentDataServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DepartmentController extends Controller
{
    public function __construct(
        private readonly DepartmentService $service,
        private readonly DepartmentDataServiceInterface $departmentDataDbService,
        private readonly FilterResolver $filterResolver
    ) {}

    public function index(ApiIndexRequest $request)
    {
        $departmentMetaData = $this->departmentDataDbService->getDepartmentData();

        $availableFilters = Department::availableFilters($departmentMetaData ?? []);

        $filters = $this->filterResolver->resolve($request, $availableFilters);

        $departmentData = $this->service->getPaginatedDataWithCache(
            $filters
        );

          return DepartmentResource::collection($departmentData->appends(request()->query()))
            ->additional([
                'extra_data' => $departmentMetaData,
                'table_meta' => [
                    'columns' => Department::tableColumns(),
                    'filters' => $availableFilters,
                ],
            ]);
    }

    public function store(DepartmentRequest $request): JsonResponse
    {
        $department = $this->service->createDepartment($request->validated());

        return apiSuccess(
            new DepartmentResource($department),
            'created'
        );
    }

    public function show(Department $department): DepartmentResource
    {
        return new DepartmentResource(
            $this->service->getDepartment($department)
        );
    }

    public function update(DepartmentRequest $request, Department $department): JsonResponse
    {
        $department = $this->service->updateDepartment(
            $department,
            $request->validated()
        );

        return apiSuccess(
            new DepartmentResource($department),
            'updated'
        );
    }

    public function destroy(Department $department): JsonResponse
    {
        $this->service->deleteDepartment($department);

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
