<?php

namespace App\Modules\Accounts\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Accounts\Requests\BalanceSheetIndexRequest;
use App\Modules\Accounts\Services\BalanceSheetService;
use Illuminate\Http\JsonResponse;

class BalanceSheetController extends Controller
{
    public function __construct(
        private readonly BalanceSheetService $service
    ) {}

    public function index(BalanceSheetIndexRequest $request): JsonResponse
    {
        return apiSuccess($this->service->getReport($request->filters()));
    }
}
