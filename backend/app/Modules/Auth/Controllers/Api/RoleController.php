<?php

namespace App\Modules\Auth\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Models\Role;
use App\Modules\Auth\Requests\RoleRequest;
use App\Modules\Auth\Resources\RoleResource;
use App\Modules\Auth\Services\RoleService;
use App\Http\Requests\ApiIndexRequest;
use App\Modules\Agent\Services\AgentDataDbService;
use App\Modules\Client\Models\Client;
use App\Modules\Client\Services\ClientDataDbService;
use App\Modules\Employee\Services\EmployeeDataDbService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RoleController extends Controller
{
    public function __construct(
        private readonly RoleService $service,
        private readonly AgentDataDbService $agentDataDbService,
        private readonly ClientDataDbService $clientDataDbService,
        private readonly EmployeeDataDbService $employeeDataDbService
    ) {}

    public function index(ApiIndexRequest $request): AnonymousResourceCollection
    {
        $filters = $request->filters();

        $filters['with'] = ['permissions']; // Eager load permissions
        $filters['sort_direction'] = 'asc'; // Default sorting

        $roleData = $this->service->getPaginatedDataWithCache(
            $filters
        );

        return RoleResource::collection($roleData);
    }

    public function store(RoleRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['slug'] = \Str::slug($data['name']);
        $role = $this->service->createRole($data);
        $this->agentDataDbService->clearAgentDataCache();
        $this->clientDataDbService->clearClientDataCache();
        $this->employeeDataDbService->clearEmployeeDataCache();

        return apiSuccess(
            new RoleResource($role),
            'created'
        );
    }

    public function show(Role $role): RoleResource
    {
        return new RoleResource(
            $this->service->getRole($role)
        );
    }

    public function update(RoleRequest $request, Role $role): JsonResponse
    {
        $data = $request->validated();
        $data['slug'] = \Str::slug($data['name']);
        $role = $this->service->updateRole(
            $role,
            $data
        );
        $this->agentDataDbService->clearAgentDataCache();
        $this->clientDataDbService->clearClientDataCache();
        $this->employeeDataDbService->clearEmployeeDataCache();

        return apiSuccess(
            new RoleResource($role),
            'updated'
        );
    }

    public function updatePermissions(Request $request, Role $role): JsonResponse
    {

        $this->service->updateRolePermissions(
            $role,
            $request->permission_ids
        );

        $users = $role->users;
        $currentUserId = auth()->id();

        foreach ($users as $user) {

            if ($user->id !== $currentUserId) {
                $user->tokens()->delete();
            }
        }

        return apiSuccess(
            null,
            'permissions_updated'
        );
    }

    public function destroy(Role $role): JsonResponse
    {
        $this->service->deleteRole($role);

        return apiSuccess(
            null,
            'deleted'
        );
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        $this->service->bulkDelete($request->ids ?? []);

        return apiSuccess(
            null,
            'deleted',
            200,
            'Records'
        );
    }
}
