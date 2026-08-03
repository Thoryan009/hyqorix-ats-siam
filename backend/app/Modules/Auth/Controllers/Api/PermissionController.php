<?php

namespace App\Modules\Auth\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Models\Permission;
use App\Modules\Auth\Requests\PermissionModuleRequest;
use App\Modules\Auth\Requests\PermissionRequest;
use App\Modules\Auth\Resources\PermissionResource;
use App\Modules\Auth\Services\PermissionService;
use App\Http\Requests\ApiIndexRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PermissionController extends Controller
{
    public function __construct(
        private readonly PermissionService $service
    ) {}

    public function index(ApiIndexRequest $request): AnonymousResourceCollection
    {
        $filters = $request->filters();
        $filters['with'] = ['roles'];
        $filters['sort_by'] = 'id';
        $filters['sort_direction'] = 'desc';

        $permissionData = $this->service->getPaginatedDataWithCache(
            $filters
        );

        return PermissionResource::collection($permissionData);
    }

    public function store(PermissionRequest $request): JsonResponse
    {
        $permission = $this->service->createPermission($request->validated());

        return apiSuccess(
            new PermissionResource($permission),
            'created'
        );
    }

    public function storeModule(PermissionModuleRequest $request): JsonResponse
    {
        $result = $this->service->createModulePermissions($request->validated());

        return apiSuccess(
            [
                'created' => PermissionResource::collection(collect($result['created'])),
                'skipped' => PermissionResource::collection(collect($result['skipped'])),
                'created_count' => count($result['created']),
                'skipped_count' => count($result['skipped']),
            ],
            'created'
        );
    }

    public function show(Permission $permission): PermissionResource
    {
        return new PermissionResource(
            $this->service->getPermission($permission)
        );
    }

    public function update(PermissionRequest $request, Permission $permission): JsonResponse
    {
        $permission = $this->service->updatePermission(
            $permission,
            $request->validated()
        );

        return apiSuccess(
            new PermissionResource($permission),
            'updated'
        );
    }

    public function destroy(Permission $permission): JsonResponse
    {
        $this->service->deletePermission($permission);

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
