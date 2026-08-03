<?php

namespace App\Modules\Application\Repositories;

use App\Modules\Application\Models\ClientTransaction;
use App\Modules\Application\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;
use App\Repositories\BaseRepository;
use App\Modules\JobList\Helpers\JobListPayerHelper;

class TransactionRepository extends BaseRepository
{
    public function __construct(Transaction $model)
    {
        $this->model = $model;
    }

    protected array $transactionSearchFields = [
        'bill_no',
        'transaction_id',
    ];

    protected array $applicationSearchFields = [
        'sur_name',
        'given_name',
        'email',
        'mobile',
        'application_id',
    ];

    public function getAllTransantion()
    {
        return $this->model
            ->with(['application', 'billTransactions'])
            ->orderByDesc('id');
    }

    public function getByIds(array $ids)
    {
        return $this->model
            ->whereIn('id', $ids)
            ->get();
    }


    public function getByBillNo(string $billNo)
    {
        return $this->model
            ->with([
                'application.jobList.workOrder.client.user',
                'application.jobList.workOrder.client.country',
                'application.jobList.jobListDetails.jobListDetailsHead.jobListDetailsCategory',
            ])
            ->where('bill_no', $billNo)
            ->get();
    }

    public function deleteByIds(array $ids): void
    {
        $this->model
            ->whereIn('id', $ids)
            ->delete();
    }

    protected function filtersCacheKey(array $filters): string
    {
        ksort($filters);
        return 'transactions:' . http_build_query($filters);
    }


    public function applyFilters(Builder $query, array $filters): void
    {
        $this->applySearch($query, $filters['search'] ?? null);
        $this->applyBillNoFilter($query, $filters['bill_no'] ?? null);
        $this->applyJobListIdFilter($query, $filters['job_list_id'] ?? null);
        $this->applyWorkOrderIdFilter($query, $filters['work_order_id'] ?? null);
        $this->applyClientIdFilter($query, $filters['client_id'] ?? null);
        $this->applyStatusFilter($query, $filters['status'] ?? null);
        $this->applyPayerFilter($query, $filters['payer'] ?? null);
        $this->applyDateFilter($query, $filters);
    }

    protected function applyBillNoFilter(Builder $query, ?string $billNo): void
    {
        if ($billNo) {
            $query->where('bill_no', 'like', "%{$billNo}%");
        }
    }

    protected function applyJobListIdFilter(Builder $query, ?int $jobListId): void
    {
        if ($jobListId) {
            $query->whereHas('application.jobList', function ($q) use ($jobListId) {
                $q->where('id', $jobListId);
            });
        }
    }

    protected function applyWorkOrderIdFilter(Builder $query, ?int $workOrderId): void
    {
        if ($workOrderId) {
                $query->whereHas('application.jobList.workOrder', function ($q) use ($workOrderId) {
                    $q->where('id', $workOrderId);
                });
        }
    }

    protected function applyClientIdFilter(Builder $query, ?int $clientId): void
    {
        if ($clientId) {
                $query->whereHas('application.jobList.workOrder.client', function ($q) use ($clientId) {
                    $q->where('id', $clientId);
                });
        }
    }

    protected function applyStatusFilter(Builder $query, ?string $status): void
    {
        if ($status) {
            $query->where('status', $status);
        }
    }

    protected function applyPayerFilter(Builder $query, ?string $payer): void
    {
        if ($payer) {
            $query->whereHas('application', function ($q) use ($payer) {
                JobListPayerHelper::scopeWhereApplicationResponsible($q, $payer);
            });
        }
    }

    protected function applySearch(Builder $query, ?string $search): void
    {
        if (!$search) {
            return;
        }

        $query->where(function ($q) use ($search) {

            // 🔹 Transaction search
            $q->where(function ($subQ) use ($search) {
                foreach ($this->transactionSearchFields as $field) {
                    $subQ->orWhere($field, 'like', "%{$search}%");
                }
            });

            // 🔹 Application search
            $q->orWhereHas('application', function ($appQ) use ($search) {
                $appQ->where(function ($subAppQ) use ($search) {
                    foreach ($this->applicationSearchFields as $field) {
                        $subAppQ->orWhere($field, 'like', "%{$search}%");
                    }
                });
            });
        });
    }

    public function getTotalAmount($application): float
    {
        return $application->jobList->price;
    }

    public function applyApplicationDiscountIfNeeded(
        $application,
        array $data,
        float $totalAmount
    ): void {
        if ($application->discount_amount !== null || empty($data['discount_amount'])) {
            return;
        }

        $application->discount_amount = $this->validateDiscountAmount(
            $totalAmount,
            $data['discount_amount']
        );

        $application->save();
        $application->refresh();
    }

    protected function validateDiscountAmount($totalAmount, $discountAmount)
    {
        if ($discountAmount > $totalAmount) {
            throw new \InvalidArgumentException('Discount amount exceeds total amount.');
        }
        return $discountAmount;
    }

    public function getTransactionDiscount($application): float
    {
        $alreadyUsed = $application->transactions()
            ->where('discount_amount', '>', 0)
            ->exists();
        return $alreadyUsed ? 0 : (float) $application->discount_amount;
    }

    public function prepareTransactionData(
        array $data,
        $application,
        float $totalAmount,
        float $transactionDiscount
    ): array {
        return [
            'transaction_id' => $this->generateTransactionId($data),
            'bill_no' => $this->generateBillNo(
                $this->getPayerShortForm($application),
                $application
            ),
            'total_amount' => $totalAmount,
            'paid_amount' => $this->validatePaidAmount(
                $application,
                $totalAmount,
                $data['paid_amount']
            ),
            'discount_amount' => $transactionDiscount,
            'payment_method' => $data['payment_method'],
            'status' => $this->getTransactionStatus(
                $data['status'],
                $application,
                $totalAmount,
                $data['paid_amount']
            ),
            'payment_date' => $data['payment_date'],
            'payment_time' => $data['payment_time'],
            'remarks' => $data['remarks'] ?? null,
            'application_id' => $application->id,
        ];
    }
    public function generateTransactionId(array $data): string
    {
        $datePart = date('ymd', strtotime($data['payment_date']));
        $random = strtoupper(substr(base_convert(random_int(0, 36 ** 4 - 1), 10, 36), 0, 3));

        return 'T' . $datePart . str_pad($random, 3, '0', STR_PAD_LEFT);
    }


    public function generateBillNo(string $payer_short_form, $application = null): string
    {
        // If bill_no already exists, return it
        if ($application->billNo()) {
            return $application->billNo();
        }
        // Fixed prefix 'B' + dynamic payer short form
        $prefix = 'B' . strtoupper($payer_short_form) . '-';

        // Current year in two digits
        $year = date('y');
        $prefixWithYear = $prefix . $year . '-';

        // Get the last transaction's bill_no that starts with this prefix
        $lastTransaction = $this->model->where('bill_no', 'like', $prefixWithYear . '%')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastTransaction) {
            // Extract the last sequence number
            $lastBillNo = $lastTransaction->bill_no; // e.g., BCS-26-005
            $lastNumber = (int) substr($lastBillNo, strrpos($lastBillNo, '-') + 1);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1; // First bill of this payer in current year
        }

        // Format the sequence as 3-digit number
        $sequence = str_pad($newNumber, 3, '0', STR_PAD_LEFT);
        return $prefixWithYear . $sequence;
    }

    private function getTransactionStatus($status, $application, $totalAmount, $paidAmount)
    {
        // Draft transactions are always 'draft'
        if ($status == 'draft') {
            return 'draft';
        }

        // 1️⃣ Validate paid amount first (throws if too high)
        $validPaidAmount = $this->validatePaidAmount($application, $totalAmount, $paidAmount);
        $currentPaidAmount = $application->totalPaidAmount() + $validPaidAmount;
        $discountAmount = $application->fresh()->discount_amount;

        if ($discountAmount > 0) {
            $totalAmount -= $discountAmount;
        }
        // 3️⃣ Return status
        if ($currentPaidAmount == $totalAmount) {
            return 'paid';
        }
        return 'due';
    }

    private function validatePaidAmount($application, $totalAmount, $paidAmount)
    {
        $totalPaidAmount = $application->totalPaidAmount();
        $currentPaidAmount = $totalPaidAmount + $paidAmount;
        if ($currentPaidAmount > $totalAmount) {
            throw new \InvalidArgumentException('Paid amount exceeds total amount.');
        }
        return $paidAmount;
    }

    protected function getPayerShortForm($application): string
    {
        return JobListPayerHelper::has(
            JobListPayerHelper::resolveForApplication($application),
            JobListPayerHelper::CANDIDATE
        )
            ? 'c'
            : 'e';
    }



    public function buildTransactionData(int $applicationId, string $billNo, float $totalAmount): array
    {
        $now = now();
        return [
            'transaction_id' => $this->generateTransactionId([
                'payment_date' => $now->toDateString(),
            ]),
            'bill_no' => $billNo,
            'total_amount' => $totalAmount,
            'paid_amount' => 0,
            'discount_amount' => 0,
            'payment_method' => 'pending',
            'status' => 'bill-generated',
            'payment_date' => $now->toDateString(),
            'payment_time' => $now->toTimeString(),
            'remarks' => 'Transaction created when offer extension',
            'application_id' => $applicationId,
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }
    public function insertTransactions(array $transactions): void
    {
        Transaction::insert($transactions);
    }

    // createClientTransaction
    public function createClientTransaction(array $transactionData): void
    {
        Transaction::create($transactionData);
    }


    public function getLastBillSequence(string $prefix): int
    {
        $lastTransaction = Transaction::query()
            ->where('bill_no', 'like', $prefix . '%')
            ->orderByDesc(
                \DB::raw('CAST(SUBSTRING_INDEX(bill_no, "-", -1) AS UNSIGNED)')
            )
            ->first();

        return $lastTransaction
            ? (int) substr($lastTransaction->bill_no, strrpos($lastTransaction->bill_no, '-') + 1)
            : 0;
    }
    public function getClientLastBillSequence(string $prefix): int
    {
        $lastTransaction = ClientTransaction::query()
            ->where('bill_no', 'like', $prefix . '%')
            ->orderByDesc(
                \DB::raw('CAST(SUBSTRING_INDEX(bill_no, "-", -1) AS UNSIGNED)')
            )
            ->first();

        return $lastTransaction
            ? (int) substr($lastTransaction->bill_no, strrpos($lastTransaction->bill_no, '-') + 1)
            : 0;
    }
}
