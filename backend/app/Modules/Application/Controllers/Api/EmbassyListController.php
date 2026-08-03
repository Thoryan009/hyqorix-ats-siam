<?php

namespace App\Modules\Application\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApiIndexRequest;
use App\Modules\Application\Models\EmbassyList;
use App\Modules\Application\Requests\EmbassyListRequest;
use App\Modules\Application\Resources\EmbassyListResource;
use App\Modules\Application\Services\EmbassyListService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class EmbassyListController extends Controller
{
    public function __construct(
        private readonly EmbassyListService $service
    ) {}

    public function index(ApiIndexRequest $request): AnonymousResourceCollection
    {
        $filters = $request->filters();

        $data = $this->service->getPaginatedDataWithCache($filters);

        return EmbassyListResource::collection($data);
    }

    public function store(EmbassyListRequest $request): JsonResponse
    {
        $embassyList = $this->service->createEmbassyList($request->validated());

        return apiSuccess(
            new EmbassyListResource($embassyList),
            'created'
        );
    }

    public function show(EmbassyList $embassyList): EmbassyListResource
    {
        return new EmbassyListResource(
            $this->service->getEmbassyList($embassyList)
        );
    }

    public function update(EmbassyListRequest $request, EmbassyList $embassyList): JsonResponse
    {
        $embassyList = $this->service->updateEmbassyList(
            $embassyList,
            $request->validated()
        );

        return apiSuccess(
            new EmbassyListResource($embassyList),
            'updated'
        );
    }

    public function destroy(EmbassyList $embassyList): JsonResponse
    {
        $this->service->deleteEmbassyList($embassyList);

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
}
