<?php

namespace App\Modules\Finance\Services;

use App\Modules\Finance\Models\ExpenseCategory;
use App\Modules\Finance\Repositories\ExpenseCategoryRepository;
use App\Services\BaseCachedService;

class ExpenseCategoryService extends BaseCachedService
{
    public function __construct(protected ExpenseCategoryRepository $repository)
    {
        parent::__construct(new ExpenseCategory());
    }

    public function getPaginatedDataWithCache(array $filters = [])
    {
        return $this->remember(
            $this->filtersCacheKey($filters),
            fn () => $this->repository->getPaginatedData($filters)
        );
    }

    public function getExpenseCategory(ExpenseCategory $expenseCategory): ExpenseCategory
    {
        return $this->remember(
            $this->byIdCacheKey($expenseCategory->id),
            fn () => $expenseCategory->loadCount('expenseHeads')
        );
    }

    public function createExpenseCategory(array $data): ExpenseCategory
    {
        return $this->mutate(fn () => $this->model->create($data));
    }

    public function updateExpenseCategory(ExpenseCategory $expenseCategory, array $data): ExpenseCategory
    {
        return $this->mutate(fn () => tap($expenseCategory)->update([
            'description' => $data['description'] ?? null,
        ]));
    }
}
