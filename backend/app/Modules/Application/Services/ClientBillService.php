<?php

namespace App\Modules\Application\Services;

use App\Modules\Application\Mails\ClientInvoiceMail;
use App\Modules\Application\Models\Transaction;
use App\Modules\Application\Repositories\ClientBillRepository;
use App\Modules\Application\Repositories\TransactionRepository;
use App\Modules\Setting\Models\Setting;
use App\Modules\Shared\Helpers\FileHelper;
use App\Services\BaseCachedService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class ClientBillService extends BaseCachedService
{

    public function __construct(
        protected ClientBillRepository $repository,
        protected readonly WorkOrderDbService $workOrderDbService,
        protected TransactionRepository $transactionRepository,
        protected TransactionService $transactionService,
        protected ClientTransactionService $clientTransactionService,
        protected ClientMailService $clientMailService,
        protected BillingCacheService $billingCacheService,
        protected JobListDbService $jobListDbService,
    ) {
        parent::__construct(new Transaction());
    }

    /* ==========================================================
     | Read Operations (Cached)
     |========================================================== */

    public function getPaginatedDataWithCache(array $filters = [])
    {
        return $this->remember(
            $this->filtersCacheKey($filters),
            function () use ($filters) {

                $data = $this->repository->getPaginatedData($filters);

                $billFilters = $this->repository->getBillFilters();

                return [
                    'data' => $data,
                    'bill_filters' => $billFilters,
                ];
            }
        );
    }

    public function getByBillNo(string $billNo, int $jobId = null)
    {
        $transactions = $this->transactionRepository->getByBillNo($billNo);
        if ($transactions->isEmpty()) {
            throw new \Exception('No client bills found for the provided bill number and job ID.');
        }
        $job = $this->jobListDbService->getJobById($jobId);
        if (!$job) {
            throw new \Exception('No job found for the provided job ID.');
        }
        $bill = [
            'bill_no' => $billNo,
            'job_id' => $jobId,
            'transactions' => $transactions,
            'job' => $job,
        ];
        return $this->remember(
            $this->byBillNoAndJobCacheKey($billNo, $jobId),
            fn() => $bill
        );
    }


    public function generateInvoice(array $data): string
    {
        return DB::transaction(function () use ($data) {

            $ids = $data['ids'] ?? [];
            $clientId = $data['client_id'] ?? null;

            $this->validateInvoiceGeneration($ids, $clientId);

            $clientBills = $this->transactionRepository->getByIds($ids);

            if ($clientBills->isEmpty()) {
                throw new \Exception('No client bills found for the provided IDs.');
            }
            $billNo = $this->generateBillNumber();
            $this->updateTransactionsWithBillNo($clientBills, $billNo);
            $this->createClientTransactionRecord($clientBills, $clientId, $billNo);
            $this->flushCache();
            return $billNo;
        });
    }


    public function updateInvoiceAndSendMail(Request $request): JsonResponse
    {
        $data = $request->validated();
        $storedFiles = [];
        try {
            return DB::transaction(function () use ($request, $data, &$storedFiles) {
                $storedPath = $this->storeInvoiceFileIfExists($request, $data);
                $storedFiles['invoice_path'] = $storedPath;
                $billNo = $data['bill_no'];
                $this->ensureBillExists($billNo);
                $this->markInvoiceAsSent($billNo);
                $clientMail = $this->clientMailService->createClientMail($data);
                $setting = Setting::first();
                if (env('APP_ENV') == 'production') {
                    Mail::to($data['to_mail'])
                        ->send(new ClientInvoiceMail($clientMail, $setting));
                } else {
                    \Log::info(
                        "Simulating email sending in non-production environment. Email content: "
                            . print_r($clientMail->toArray(), true)
                    );
                }
                $this->flushCache();
                return response()->json([
                    'success' => true,
                    'message' => 'Invoice sent successfully.',
                    'data' => null,
                ], 200);
            });
        } catch (\Throwable $error) {
            \Log::error('Invoice send failed: ' . $error->getMessage());
            // Delete any files that were stored before the error
            foreach ($storedFiles as $path) {
                FileHelper::delete($path, 'local');
            }
            return response()->json([
                'success' => false,
                'message' => 'Failed to send invoice.',
                'error' => $error->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function collectInvoiceService(string $billNo): JsonResponse
    {
        $this->ensureBillExists($billNo);
        $this->model::where('bill_no', $billNo)
            ->update(['status' => 'paid']);

        $this->clientTransactionService
            ->updateStatusByBillNo($billNo, 'paid');

        $this->flushCache();

        return response()->json([
            'success' => true,
            'message' => 'Invoice collected successfully.',
            'data' => null,
        ], 200);
    }

    public function cancelledInvoiceService(string $billNo): JsonResponse
    {
        $this->ensureBillExists($billNo);
        $this->model::where('bill_no', $billNo)
            ->update(['bill_no' => null, 'status' => 'bill-generated']);

        $this->clientTransactionService
            ->updateStatusByBillNo($billNo, 'cancelled');

        $this->flushCache();

        return response()->json([
            'success' => true,
            'message' => 'Invoice cancelled successfully.',
            'data' => null,
        ], 200);
    }

    private function validateInvoiceGeneration(array $ids, ?int $clientId): void
    {
        if (empty($ids)) {
            throw new \Exception('No client bill IDs provided.');
        }

        if (empty($clientId)) {
            throw new \Exception('Client ID is required.');
        }
    }

    private function generateBillNumber(): string
    {
        $payerShortForm = 'e';

        $prefix = $this->transactionService->getBillPrefix($payerShortForm);
        $lastNumber = $this->transactionRepository->getClientLastBillSequence($prefix);

        $sequence = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);

        return $prefix . $sequence;
    }

    private function updateTransactionsWithBillNo($clientBills, string $billNo): void
    {
        foreach ($clientBills as $transaction) {
            $transaction->update([
                'bill_no' => $billNo,
                'status' => 'invoice-generated',
            ]);
        }
    }

    private function createClientTransactionRecord($clientBills, int $clientId, string $billNo): void
    {
        $clientTransactionData = [
            'client_id' => $clientId,
            'bill_no' => $billNo,
            'amount' => $clientBills->sum('total_amount'),
            'status' => 'invoice-generated',
        ];
        $this->clientTransactionService->createClientTransaction($clientTransactionData);
    }

    private function storeInvoiceFileIfExists(Request $request, array &$data): ?string
    {

        if ($request->hasFile('invoice_path')) {
            $path = FileHelper::store(
                $request->file('invoice_path'),
                'invoices',
                'local'
            );
            $data['invoice_path'] = $path;
            return $path;
        }

        return null;
    }

    private function ensureBillExists(string $billNo): void
    {
        $transactions = $this->transactionRepository->getByBillNo($billNo);

        if ($transactions->isEmpty()) {
            throw new \Exception('No client bills found for the provided bill number.');
        }
    }

    private function markInvoiceAsSent(string $billNo): void
    {
        $this->model::where('bill_no', $billNo)
            ->update(['status' => 'invoice-sent']);

        $this->clientTransactionService
            ->updateStatusByBillNo($billNo, 'invoice-sent');
    }

    // Helper methods for caching

    protected function remember(string $key, \Closure $callback)
    {
        return Cache::tags($this->getCacheTag())
            ->remember($key, $this->getCacheTtl(), $callback);
    }

    public function flushCache(): void
    {
        $this->billingCacheService->flush();
    }
    protected function getCacheTag(): string
    {
        return 'ClientBill';
    }

    protected function byBillNoAndJobCacheKey(string $billNo, int $jobId): string
    {
        return "{$this->getCacheTag()}_by_bill_no_{$billNo}_and_job_{$jobId}";
    }
}
