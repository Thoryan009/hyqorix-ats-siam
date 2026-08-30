<?php

namespace App\Modules\Accounts\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Accounts\Requests\TrialBalanceIndexRequest;
use App\Modules\Accounts\Services\TrialBalanceService;
use Illuminate\Http\JsonResponse;

class TrialBalanceController extends Controller
{
    public function __construct(
        private readonly TrialBalanceService $service
    ) {}

    public function index(TrialBalanceIndexRequest $request): JsonResponse
    {
        return apiSuccess($this->service->getReport($request->filters()));
    }
}
