<?php

namespace App\Modules\Application\Services;

use App\Modules\Application\Models\Transaction;
use App\Modules\Application\Repositories\TransactionRepository;
use App\Services\BaseCachedService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TransactionService extends BaseCachedService
{

    protected $applicationService;
    public function __construct(ApplicationService $applicationService,
    protected BillingCacheService $billingCacheService,
    protected TransactionRepository $repository)
    {
        parent::__construct(new Transaction());
        $this->applicationService = $applicationService;

    }

    /* ==========================================================
     | Read Operations (Cached)
     |========================================================== */

    public function getPaginatedDataWithCache(int $page, int $perPage = 10, array $filters = [])
    {
        return $this->remember(
            $this->filtersCacheKey($filters),
            fn() => $this->repository->getPaginatedData($filters)
        );
    }

    public function getApplicationTransactions(?string $search = null)
    {
        if ($search === null) {
            return [];
        }
        return $this->applicationService->getApplicationsWithSearch($search);
    }

    public function getById(int $id)
    {
        return $this->remember(
            $this->byIdCacheKey($id),
            fn() => $this->model->findOrFail($id)
        );
    }

    public function create(array $data, $application)
    {
        return DB::transaction(function () use ($data, $application) {

            $totalAmount = $this->repository->getTotalAmount($application);

            $this->repository->applyApplicationDiscountIfNeeded($application, $data, $totalAmount);

            $transactionDiscount = $this->repository->getTransactionDiscount($application);

            $transactionData = $this->repository->prepareTransactionData(
                $data,
                $application,
                $totalAmount,
                $transactionDiscount
            );
            $record = $this->model->create($transactionData);

            $this->flushCache();
            return $record;
        });
    }

    public function createTransactionsForApplications(array $applicationIds, float $totalAmount, string $payerShortForm): void
    {
        // 1️⃣ Prepare bill prefix
        $prefix = $this->getBillPrefix($payerShortForm);

        // 2️⃣ Get last sequence number for bill
        $lastNumber = $this->repository->getLastBillSequence($prefix);

        // 3️⃣ Prepare transaction records
        $transactions = [];
        foreach ($applicationIds as $index => $applicationId) {
            $sequence = str_pad($lastNumber + $index + 1, 3, '0', STR_PAD_LEFT);
            $billNo = $prefix . $sequence;

            $transactions[] = $this->repository->buildTransactionData($applicationId, $billNo, $totalAmount);
        }

        // 4️⃣ Insert all transactions in one query
        $this->repository->insertTransactions($transactions);
    }

    public function createTransactionForClient(int $applicationId, float $totalAmountUSD, string $payerShortForm='e'): void
    {
        $now = now();
        $transactionData = [
            'transaction_id' => $this->repository->generateTransactionId([
                'payment_date' => $now->toDateString(),
            ]),
            'bill_no' => null,
            'total_amount_usd' => $totalAmountUSD,
            'paid_amount_usd' => 0,
            'discount_amount' => 0,
            'payment_method' => 'pending',
            'status' => 'bill-generated',
            'payment_date' => $now->toDateString(),
            'payment_time' => $now->toTimeString(),
            'remarks' => 'Transaction created when on-boarding completed',
            'application_id' => $applicationId,
            'created_at' => $now,
            'updated_at' => $now,
        ];
        $this->repository->createClientTransaction($transactionData);
        $this->flushCache();
        $this->billingCacheService->flush();

    }

    public function getBillPrefix(string $payerShortForm): string
    {
        $year = date('y');
        return 'B' . strtoupper($payerShortForm) . '-' . $year . '-';
    }

    public function update(int $id, array $data)
    {
        return $this->mutate(fn() => tap($this->model->findOrFail($id))->update($data));
    }
    public function delete(int $id): bool
    {
        return $this->mutate(fn() => $this->model->findOrFail($id)->delete());
    }
    public function bulkDelete(array $ids): int
    {
        return $this->mutate(fn() => $this->model->whereIn('id', $ids)->delete());
    }
}
