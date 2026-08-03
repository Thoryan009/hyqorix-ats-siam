<?php

namespace App\Modules\Application\Controllers\Api;

use App\Http\Requests\ApiIndexRequest;
use App\Http\Controllers\Controller;
use App\Modules\Application\Services\CandidateBillService;
use App\Modules\Application\Requests\CandidateBillRequest;

use App\Modules\Application\Resources\CandidateBillResource;
use App\Modules\Application\Services\JobListDbService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\JsonResponse;

class CandidateBillController extends Controller
{
    public function __construct(
        private CandidateBillService $service,
        private readonly JobListDbService $jobListDbService
    ) {}
    public function index(ApiIndexRequest $request): AnonymousResourceCollection
    {
        $filters = $request->filters();
        $filters['job_id'] = $request->get('job_id', null);
        $data = $this->service->getPaginatedDataWithCache($filters);
        return CandidateBillResource::collection($data);
    }

    public function getCandidateBillJobLists(): JsonResponse
    {
        $jobList = $this->jobListDbService->getCandidateBillJobLists();
        return apiSuccess(
            $jobList,
            'fetched'
        );
    }
}
