<?php

namespace App\Modules\Accounts\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Accounts\Requests\GrossProfitIndexRequest;
use App\Modules\Accounts\Services\GrossProfitService;
use Illuminate\Http\JsonResponse;

class GrossProfitController extends Controller
{
    public function __construct(
        private readonly GrossProfitService $service
    ) {}

    public function index(GrossProfitIndexRequest $request): JsonResponse
    {
        return apiSuccess($this->service->getReport($request->filters()));
    }
}
