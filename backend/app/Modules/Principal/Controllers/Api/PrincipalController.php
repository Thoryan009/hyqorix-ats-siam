<?php

namespace App\Modules\Principal\Controllers\Api;

use App\Http\Controllers\Controller;


use App\Http\Requests\ApiIndexRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Modules\Principal\Contracts\PrincipalDataServiceInterface;
use App\Modules\Principal\Models\Principal;
use App\Modules\Principal\Requests\PrincipalRequest;
use App\Modules\Principal\Resources\PrincipalResource;
use App\Modules\Principal\Services\PrincipalService;
use App\Modules\Reports\Services\PrincipalDbService;

class PrincipalController extends Controller
{
    public function __construct(private readonly PrincipalService $service, private readonly PrincipalDataServiceInterface $principalDataService, private readonly PrincipalDbService $principalDbService) {}

    public function index(ApiIndexRequest $request): AnonymousResourceCollection
    {
        $filters = $request->filters();
        $user = auth()->user();

        // 🔥 Force client isolation
        if ($user->type === 'principal') {
            $filters['principal_id'] = $user->principal->id;
        }

        $principalData = $this->service->getPaginatedDataWithCache($filters);

        return PrincipalResource::collection($principalData);
    }

    public function store(PrincipalRequest $request)
    {
        $principal = $this->service->createPrincipal($request->validated());

        $this->principalDataService->clearPrincipalDataCache();
        $this->principalDbService->clearPrincipalCache();

        return apiSuccess(new PrincipalResource($principal), 'created');
    }

    public function show(Principal $principal): PrincipalResource
    {
        $this->authorizePrincipalAccess($principal);

        return new PrincipalResource($this->service->getPrincipal($principal));
    }

    public function update(PrincipalRequest $request, int $id): JsonResponse
    {
        $principal = $this->service->getById($id);
        $this->authorizePrincipalAccess($principal);
        $record = $this->service->updatePrincipal($id, $request->validated());
        $this->principalDataService->clearPrincipalDataCache();
        $this->principalDbService->clearPrincipalCache();
        return apiSuccess(new PrincipalResource($record), 'updated');
    }

    public function destroy(int $id): JsonResponse
    {
        $principal = $this->service->getById($id);
        $this->authorizePrincipalAccess($principal);
        $this->service->deletePrincipal($id);
        $this->principalDataService->clearPrincipalDataCache();
        $this->principalDbService->clearPrincipalCache();
        return apiSuccess(null, 'deleted');
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        $this->service->bulkDelete($request->ids);
        $this->principalDataService->clearPrincipalDataCache();
        $this->principalDbService->clearPrincipalCache();
        return apiSuccess(null, 'deleted', 200, 'Records');
    }

    public function getPrincipalData(): JsonResponse
    {
        $data = $this->principalDataService->getPrincipalData();

        return apiSuccess($data, 'fetched');
    }

    private function authorizePrincipalAccess($principal): void
    {
        $user = auth()->user();

        if ($user->type === 'principal') {
            if (!$user->principal || $principal->id !== $user->principal->id) {
                abort(403, 'Unauthorized access');
            }
        }
    }
}
