<?php

namespace App\Modules\Finance\Services;

use App\Modules\Finance\Models\FinanceIncomeTax;
use App\Modules\Finance\Repositories\FinanceIncomeTaxRepository;
use App\Services\BaseCachedService;
use Illuminate\Validation\ValidationException;

class FinanceIncomeTaxService extends BaseCachedService
{
    public function __construct(
        protected FinanceIncomeTaxRepository $repository,
        private readonly FinanceIncomeStatementService $incomeStatementService
    ) {
        parent::__construct(new FinanceIncomeTax());
    }

    public function getPaginatedDataWithCache(array $filters = [])
    {
        return $this->remember(
            $this->filtersCacheKey($filters),
            fn () => $this->repository->getPaginatedData($filters)
        );
    }

    public function getFinanceIncomeTax(FinanceIncomeTax $financeIncomeTax): FinanceIncomeTax
    {
        return $this->remember(
            $this->byIdCacheKey($financeIncomeTax->id),
            fn () => $financeIncomeTax->load(['createdBy', 'updatedBy'])
        );
    }

    public function createFinanceIncomeTax(array $data): FinanceIncomeTax
    {
        $this->assertUniqueYear((int) $data['year']);

        $data = $this->withStatementSnapshot($data);

        return $this->mutate(fn () => $this->model->create($data));
    }

    public function updateFinanceIncomeTax(FinanceIncomeTax $financeIncomeTax, array $data): FinanceIncomeTax
    {
        if (isset($data['year'])) {
            $this->assertUniqueYear((int) $data['year'], $financeIncomeTax->id);
        }

        $data = $this->withStatementSnapshot($data, (int) ($data['year'] ?? $financeIncomeTax->year));

        return $this->mutate(fn () => tap($financeIncomeTax)->update($data));
    }

    public function deleteFinanceIncomeTax(FinanceIncomeTax $financeIncomeTax): bool
    {
        return $this->mutate(fn () => (bool) $financeIncomeTax->delete());
    }

    public function bulkDelete(array $ids): int
    {
        return $this->mutate(fn () => $this->model->whereIn('id', $ids)->delete());
    }

    public function getYearContext(int $year): array
    {
        $statement = $this->incomeStatementService->getStatement([
            'from_date' => sprintf('%d-01-01', $year),
            'to_date' => sprintf('%d-12-31', $year),
        ]);

        $netProfitBeforeTax = round((float) ($statement['summary']['net_profit_before_tax'] ?? 0), 2);
        $entry = $this->model->query()
            ->with(['createdBy', 'updatedBy'])
            ->where('year', $year)
            ->first();

        return [
            'year' => $year,
            'from_date' => sprintf('%d-01-01', $year),
            'to_date' => sprintf('%d-12-31', $year),
            'net_profit' => $netProfitBeforeTax,
            'is_profit' => $netProfitBeforeTax >= 0,
            'gross_profit' => round((float) ($statement['summary']['gross_profit'] ?? 0), 2),
            'total_income' => round((float) ($statement['summary']['total_income'] ?? 0), 2),
            'total_operating_expense' => round(
                (float) ($statement['summary']['total_operating_expense'] ?? 0),
                2
            ),
            'entry' => $entry,
        ];
    }

    private function withStatementSnapshot(array $data, ?int $year = null): array
    {
        $year = $year ?? (int) ($data['year'] ?? 0);

        if ($year <= 0) {
            return $data;
        }

        $statement = $this->incomeStatementService->getStatement([
            'from_date' => sprintf('%d-01-01', $year),
            'to_date' => sprintf('%d-12-31', $year),
        ]);

        $data['net_profit'] = round((float) ($statement['summary']['net_profit_before_tax'] ?? 0), 2);

        if (!array_key_exists('tax_rate', $data) || $data['tax_rate'] === null || $data['tax_rate'] === '') {
            $taxAmount = (float) ($data['tax_amount'] ?? 0);
            $netProfit = (float) $data['net_profit'];
            $data['tax_rate'] = $netProfit > 0
                ? round(($taxAmount / $netProfit) * 100, 4)
                : null;
        }

        if (!isset($data['status']) || $data['status'] === '') {
            $data['status'] = 'active';
        }

        return $data;
    }

    private function assertUniqueYear(int $year, ?int $ignoreId = null): void
    {
        $exists = $this->model->query()
            ->where('year', $year)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'year' => ['An income tax entry already exists for this year. Only one entry per year is allowed.'],
            ]);
        }
    }
}
