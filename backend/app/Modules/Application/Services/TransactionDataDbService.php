<?php

namespace App\Modules\Application\Services;

use App\Modules\Application\Contracts\TransactionDataServiceInterface;
use App\Modules\Application\Models\Transaction;
use App\Modules\Client\Models\Client;
use App\Modules\JobList\Models\JobList;
use App\Modules\WorkOrder\Models\WorkOrder;
use Illuminate\Support\Facades\Cache;
use App\Modules\JobList\Helpers\JobListPayerHelper;

class TransactionDataDbService implements TransactionDataServiceInterface
{
    public function getTransactionData(): array
    {
       // Cache key
        $cacheKey = 'transaction_data';

        // Cache duration in seconds (e.g., 3600 = 1 hour)
        $cacheTTL = 2592000; // 30 days

        return Cache::remember($cacheKey, $cacheTTL, function () {
            $status = Transaction::select('status')
                ->whereNotNull('status')
                ->distinct()
                ->pluck('status')
                ->map(fn ($status) => [
                    'id'   => $status,
                    'name' => $status,
                ])
                ->values()
                ->toArray();

            $billNo = Transaction::select('bill_no')
                ->whereNotNull('bill_no')
                ->distinct()
                ->pluck('bill_no')
                ->map(fn ($billNo) => [
                    'id'   => $billNo,
                    'name' => $billNo,
                ])
                ->values()
                ->toArray();
            $payer = JobListPayerHelper::filterOptions();
            $jobList = JobList::select('id', 'name', 'job_code')
                ->whereNotNull('name')
                ->distinct()
                ->get()
                ->map(fn ($jobList) => [
                    'id'   => $jobList->id,
                    'name' => $jobList->name . ' ( ' . $jobList->job_code .')',
                ])
                ->values()
                ->toArray();

            $workOrder = WorkOrder::get()
            ->map(fn ($workOrder) => [
                'id'   => $workOrder->id,
                'name' => $workOrder->work_order_id,
            ])
            ->values()
            ->toArray();

            $client = Client::get()
            ->map(fn ($client) => [
                'id'   => $client->id,
                'name' => $client->client_id,
            ])
            ->values()
            ->toArray();

            return [
                'status' => $status,
                'bill_no' => $billNo,
                'job_list' => $jobList,
                'work_order' => $workOrder,
                'client' => $client,
                'payer' => $payer,
            ];
        });
    }

    public function clearTransactionDataCache(): void
    {
        Cache::forget('transaction_data');
    }
}
