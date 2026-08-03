<?php

namespace App\Modules\System\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\System\Models\ActivityLog;
use App\Modules\System\Requests\ActivityLogRequest;
use App\Modules\System\Resources\ActivityLogResource;
use App\Modules\System\Services\ActivityLogService;
use App\Http\Requests\ApiIndexRequest;
use App\Modules\Shared\Services\FilterResolver;
use App\Modules\System\Contracts\ActivityLogDataServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ActivityLogController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $service,
        private readonly ActivityLogDataServiceInterface $activityLogDataDbService,
        private readonly FilterResolver $filterResolver
    ) {}

    public function index(ApiIndexRequest $request)
    {
        $activityLogMetaData = $this->activityLogDataDbService->getActivityLogData();

        $availableFilters = ActivityLog::availableFilters($activityLogMetaData ?? []);

        $filters = $this->filterResolver->resolve($request, $availableFilters);

        $activityLogData = $this->service->getPaginatedDataWithCache(
            $filters
        );

        return ActivityLogResource::collection($activityLogData->appends(request()->query()))
            ->additional([
                'extra_data' => $activityLogMetaData,
                'table_meta' => [
                    'columns' => ActivityLog::tableColumns(),
                    'filters' => $availableFilters,
                ],
            ]);
    }

    public function store(ActivityLogRequest $request): JsonResponse
    {
        $activityLog = $this->service->createActivityLog($request->validated());

        return apiSuccess(
            new ActivityLogResource($activityLog),
            'created'
        );
    }

    public function show(ActivityLog $activityLog): ActivityLogResource
    {
        return new ActivityLogResource(
            $this->service->getActivityLog($activityLog)
        );
    }

    public function update(ActivityLogRequest $request, ActivityLog $activityLog): JsonResponse
    {
        $activityLog = $this->service->updateActivityLog(
            $activityLog,
            $request->validated()
        );

        return apiSuccess(
            new ActivityLogResource($activityLog),
            'updated'
        );
    }

    public function destroy(ActivityLog $activityLog): JsonResponse
    {
        $this->service->deleteActivityLog($activityLog);

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
