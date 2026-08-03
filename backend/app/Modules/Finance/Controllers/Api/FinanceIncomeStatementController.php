<?php

namespace App\Modules\Finance\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Finance\Services\FinanceIncomeStatementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FinanceIncomeStatementController extends Controller
{
    public function __construct(
        private readonly FinanceIncomeStatementService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = [
            'from_date' => $request->get('from_date'),
            'to_date' => $request->get('to_date'),
        ];

        return apiSuccess($this->service->getStatement($filters));
    }
}
