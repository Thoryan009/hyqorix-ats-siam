<?php

namespace App\Modules\Accounts\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Accounts\Requests\IncomeStatementIndexRequest;
use App\Modules\Accounts\Services\IncomeStatementService;
use Illuminate\Http\JsonResponse;

class IncomeStatementController extends Controller
{
    public function __construct(
        private readonly IncomeStatementService $service
    ) {}

    public function index(IncomeStatementIndexRequest $request): JsonResponse
    {
        return apiSuccess($this->service->getReport($request->filters()));
    }
}
