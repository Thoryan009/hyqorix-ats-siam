<?php

namespace App\Modules\Application\Services;

use App\Modules\Application\Contracts\ClientBillDataServiceInterface;
use App\Modules\Application\Models\Transaction;
use App\Modules\JobList\Models\JobList;
use App\Modules\WorkOrder\Models\WorkOrder;
use Illuminate\Support\Facades\Cache;

class ClientBillDataDbService implements ClientBillDataServiceInterface
{

    public function getClientBillData(): array
    {
        $cacheKey = 'client_bill_data';
        $cacheTTL = 3600; // 1 hour in seconds

        return Cache::remember($cacheKey, $cacheTTL, function () {
            // $workOrders = WorkOrder::with(['client.user'])->get();
            $jobLists = JobList::get();
            $uniqueBillNos = Transaction::whereNotNull('bill_no')
                ->whereHas('clientTransaction.client', function ($query) {
                    $query->whereNotNull('user_id');
                })
                ->with('clientTransaction.client.user')
                ->pluck('bill_no')
                ->unique()
                ->toArray();

            $billNos = array_map(function ($billNo) {
                return [
                    'id' => $billNo,
                    'name' => $billNo . ' - ' . optional(optional(Transaction::where('bill_no', $billNo)->first())->clientTransaction?->client?->user)->name ?? 'N/A',
                    'status' => Transaction::where('bill_no', $billNo)->first()->status ?? 'N/A',
                ];
            }, $uniqueBillNos);

            $jobs = $jobLists->map(function ($job) {
                return [
                    'id' => $job->id,
                    'name' => $job->name . " (" . ($job->job_code) . ")",
                ];
            })->toArray();

            return [
                'job_lists' => $jobs,
                'bill_nos' => $billNos,
            ];
        });
    }

    public function clearClientBillDataCache(): void
    {
        Cache::forget('client_bill_data');
    }
}
