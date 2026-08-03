<?php

namespace App\Modules\Employee\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApiIndexRequest;
use App\Modules\Employee\Contracts\EmployeeDataServiceInterface;
use App\Modules\Employee\Models\Employee;
use App\Modules\Employee\Requests\EmployeeRequest;
use App\Modules\Employee\Resources\DesignationResource;
use App\Modules\Employee\Resources\EmployeeResource;
use App\Modules\Employee\Services\DesignationService;
use App\Modules\Employee\Services\EmployeeService;
use App\Modules\Shared\Services\FilterResolver;
use App\Modules\WorkOrder\Services\WorkOrderDataDbService;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function __construct(private readonly EmployeeService $service, private readonly EmployeeDataServiceInterface $employeeDataDbService, private readonly DesignationService $designationService, private readonly FilterResolver $filterResolver, private readonly WorkOrderDataDbService $workOrderDataDbService) {}
    public function index(ApiIndexRequest $request)
    {
        $employeeMetaData = $this->employeeDataDbService->getEmployeeData();

        $availableFilters = Employee::availableFilters($employeeMetaData ?? []);

        $filters = $this->filterResolver->resolve($request, $availableFilters);
        $filters['with'] = ['designation', 'user'];

        $employeeData = $this->service->getPaginatedDataWithCache($filters);

        return EmployeeResource::collection($employeeData->appends(request()->query()))->additional([
            'extra_data' => $employeeMetaData,
            'table_meta' => [
                'columns' => Employee::tableColumns(),
                'filters' => $availableFilters,
            ],
        ]);
    }

    public function store(EmployeeRequest $request)
    {
        $employee = $this->service->create($request);
        $this->workOrderDataDbService->clearWorkOrderDataCache();
        $this->employeeDataDbService->clearEmployeeDataCache();
        return apiSuccess(new EmployeeResource($employee), 'created');
    }

    public function show($id)
    {
        return new EmployeeResource($this->service->getById($id));
    }

    public function update(EmployeeRequest $request, $id)
    {
        $record = $this->service->update($id, $request);
        $this->workOrderDataDbService->clearWorkOrderDataCache();
        $this->employeeDataDbService->clearEmployeeDataCache();
        return apiSuccess(new EmployeeResource($record), 'updated');
    }

    public function destroy($id)
    {
        $this->service->delete($id);
        $this->workOrderDataDbService->clearWorkOrderDataCache();
        $this->employeeDataDbService->clearEmployeeDataCache();
        return apiSuccess(null, 'deleted');
    }

    public function bulkDelete(Request $request)
    {
        $this->service->bulkDelete($request->ids);
        $this->workOrderDataDbService->clearWorkOrderDataCache();
        $this->employeeDataDbService->clearEmployeeDataCache();
        return apiSuccess(null, 'deleted', 200, 'Records');
    }

    public function getEmployeeDesignations()
    {
        $designations = $this->designationService->getAll();
        return apiSuccess(DesignationResource::collection($designations), 'Designation list');
    }

    public function getApprovalManagers()
    {
        return apiSuccess(
            EmployeeResource::collection($this->service->getApprovalManagers()),
            'Approval manager list'
        );
    }

    public function getAllEmployeesWithPoints()
    {
        $employees = $this->service->getAllEmployeesWithPoints();
        return apiSuccess(EmployeeResource::collection($employees), 'Employee list with points');
    }

    public function getTopEmployeeByPoints()
    {
        $employee = $this->service->getTopEmployeeByPoints();

        if (!$employee) {
            return apiSuccess(null, 'fetched', 200, 'Top employee');
        }

        return apiSuccess(new EmployeeResource($employee), 'fetched', 200, 'Top employee');
    }
}
