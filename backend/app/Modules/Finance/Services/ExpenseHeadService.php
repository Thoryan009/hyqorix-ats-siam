<?php

namespace App\Modules\Finance\Services;

use App\Modules\Finance\Models\ExpenseCategory;
use App\Modules\Finance\Models\ExpenseHead;
use App\Modules\Finance\Repositories\ExpenseHeadRepository;
use App\Services\BaseCachedService;

class ExpenseHeadService extends BaseCachedService
{
    public function __construct(
        protected ExpenseHeadRepository $repository,
        protected FinanceAccountService $financeAccountService,
    ) {
        parent::__construct(new ExpenseHead());
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

    public function getExpenseHead(ExpenseHead $expenseHead): ExpenseHead
    {
        return $this->remember(
            $this->byIdCacheKey($expenseHead->id),
            fn () => $expenseHead->load('expenseCategory')
        );
    }

    public function createExpenseHead(array $data): ExpenseHead
    {
        return $this->mutate(function () use ($data) {
            $data = $this->normalizeBillsReceivableLink($data);
            $head = $this->model->create($data);

            if (!empty($data['is_bills_receivable_link'])) {
                $this->unlinkOtherBillsReceivableLinks($head->id);
            }

            $this->financeAccountService->ensureExpenseHeadAccount($head);

            return $head;
        });
    }

    public function updateExpenseHead(ExpenseHead $expenseHead, array $data): ExpenseHead
    {
        return $this->mutate(function () use ($expenseHead, $data) {
            $data = $this->normalizeBillsReceivableLink($data, $expenseHead);
            $expenseHead->update($data);

            if (!empty($data['is_bills_receivable_link'])) {
                $this->unlinkOtherBillsReceivableLinks($expenseHead->id);
            }

            $this->financeAccountService->ensureExpenseHeadAccount($expenseHead->fresh(['expenseCategory']));

            return $expenseHead;
        });
    }

    private function normalizeBillsReceivableLink(array $data, ?ExpenseHead $existing = null): array
    {
        $categoryId = (int) ($data['expense_category_id'] ?? $existing?->expense_category_id);
        $category = ExpenseCategory::query()->find($categoryId);

        if (!$category || $category->code !== 'operating_cost') {
            $data['is_bills_receivable_link'] = false;
        } else {
            $data['is_bills_receivable_link'] = (bool) ($data['is_bills_receivable_link'] ?? false);
        }

        return $data;
    }

    private function unlinkOtherBillsReceivableLinks(int $keepHeadId): void
    {
        $this->model
            ->newQuery()
            ->where('is_bills_receivable_link', true)
            ->where('id', '!=', $keepHeadId)
            ->update(['is_bills_receivable_link' => false]);
    }
}
