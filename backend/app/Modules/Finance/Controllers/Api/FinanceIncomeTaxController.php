<?php

namespace App\Modules\Finance\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApiIndexRequest;
use App\Modules\Finance\Models\FinanceIncomeTax;
use App\Modules\Finance\Requests\FinanceIncomeTaxRequest;
use App\Modules\Finance\Resources\FinanceIncomeTaxResource;
use App\Modules\Finance\Services\FinanceIncomeTaxService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FinanceIncomeTaxController extends Controller
{
    public function __construct(
        private readonly FinanceIncomeTaxService $service
    ) {}

    public function index(ApiIndexRequest $request): AnonymousResourceCollection
    {
        $filters = $request->filters();
        $filters['status'] = $request->get('status');
        $filters['year'] = $request->get('year');

        return FinanceIncomeTaxResource::collection(
            $this->service->getPaginatedDataWithCache($filters)
        );
    }

    public function yearContext(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'year' => ['required', 'integer', 'min:2000', 'max:' . ((int) date('Y') + 1)],
        ]);

        $context = $this->service->getYearContext((int) $validated['year']);

        return apiSuccess([
            'year' => $context['year'],
            'from_date' => $context['from_date'],
            'to_date' => $context['to_date'],
            'net_profit' => $context['net_profit'],
            'is_profit' => $context['is_profit'],
            'gross_profit' => $context['gross_profit'],
            'total_income' => $context['total_income'],
            'total_operating_expense' => $context['total_operating_expense'],
            'entry' => $context['entry']
                ? (new FinanceIncomeTaxResource($context['entry']))->resolve()
                : null,
        ]);
    }

    public function store(FinanceIncomeTaxRequest $request): JsonResponse
    {
        $entry = $this->service->createFinanceIncomeTax($request->validated());

        return apiSuccess(
            new FinanceIncomeTaxResource($entry->load(['createdBy', 'updatedBy'])),
            'created',
            200,
            'Income Tax'
        );
    }

    public function show(FinanceIncomeTax $financeIncomeTax): FinanceIncomeTaxResource
    {
        return new FinanceIncomeTaxResource(
            $this->service->getFinanceIncomeTax($financeIncomeTax)
        );
    }

    public function update(FinanceIncomeTaxRequest $request, FinanceIncomeTax $financeIncomeTax): JsonResponse
    {
        $entry = $this->service->updateFinanceIncomeTax(
            $financeIncomeTax,
            $request->validated()
        );

        return apiSuccess(
            new FinanceIncomeTaxResource($entry->load(['createdBy', 'updatedBy'])),
            'updated',
            200,
            'Income Tax'
        );
    }

    public function destroy(FinanceIncomeTax $financeIncomeTax): JsonResponse
    {
        $this->service->deleteFinanceIncomeTax($financeIncomeTax);

        return apiSuccess(null, 'deleted', 200, 'Income Tax');
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        $this->service->bulkDelete($request->ids);

        return apiSuccess(null, 'deleted', 200, 'Income Taxes');
    }
}
