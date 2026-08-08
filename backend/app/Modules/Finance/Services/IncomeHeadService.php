<?php

namespace App\Modules\Finance\Services;

use App\Modules\Finance\Models\IncomeCategory;
use App\Modules\Finance\Models\IncomeHead;
use App\Modules\Finance\Repositories\IncomeHeadRepository;
use App\Services\BaseCachedService;

class IncomeHeadService extends BaseCachedService
{
    public function __construct(
        protected IncomeHeadRepository $repository,
        protected FinanceAccountService $financeAccountService,
    ) {
        parent::__construct(new IncomeHead());
    }

    public function getPaginatedDataWithCache(array $filters = [])
    {
        return $this->remember(
            $this->filtersCacheKey($filters),
            fn () => $this->repository->getPaginatedData($filters)
        );
    }

    public function getSummary(array $filters = []): array
    {
        return $this->remember(
            $this->filtersCacheKey([...$filters, '_summary' => true]),
            fn () => $this->repository->getSummary($filters)
        );
    }

    public function getIncomeHead(IncomeHead $incomeHead): IncomeHead
    {
        return $this->remember(
            $this->byIdCacheKey($incomeHead->id),
            fn () => $incomeHead->load('incomeCategory')
        );
    }

    public function createIncomeHead(array $data): IncomeHead
    {
        return $this->mutate(function () use ($data) {
            $data = $this->normalizeBillsPayableLink($data);
            $head = $this->model->create($data);

            if (!empty($data['is_bills_payable_link'])) {
                $this->unlinkOtherBillsPayableLinks($head->id);
            }

            $this->financeAccountService->ensureIncomeHeadAccount($head);

            return $head;
        });
    }

    public function updateIncomeHead(IncomeHead $incomeHead, array $data): IncomeHead
    {
        return $this->mutate(function () use ($incomeHead, $data) {
            $data = $this->normalizeBillsPayableLink($data, $incomeHead);
            $incomeHead->update($data);

            if (!empty($data['is_bills_payable_link'])) {
                $this->unlinkOtherBillsPayableLinks($incomeHead->id);
            }

            $this->financeAccountService->ensureIncomeHeadAccount($incomeHead->fresh(['incomeCategory']));

            return $incomeHead;
        });
    }

    private function normalizeBillsPayableLink(array $data, ?IncomeHead $existing = null): array
    {
        $categoryId = (int) ($data['income_category_id'] ?? $existing?->income_category_id);
        $category = IncomeCategory::query()->find($categoryId);

        if (!$category || $category->code !== 'other_income') {
            $data['is_bills_payable_link'] = false;
        } else {
            $data['is_bills_payable_link'] = (bool) ($data['is_bills_payable_link'] ?? false);
        }

        return $data;
    }

    private function unlinkOtherBillsPayableLinks(int $keepHeadId): void
    {
        $this->model
            ->newQuery()
            ->where('is_bills_payable_link', true)
            ->where('id', '!=', $keepHeadId)
            ->update(['is_bills_payable_link' => false]);
    }
}
