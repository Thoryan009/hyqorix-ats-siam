<?php

namespace App\Modules\Reports\Services;
use App\Modules\Reports\Contracts\TransactionServiceInterface;
use App\Modules\Application\Models\Transaction;
use Illuminate\Support\Facades\Cache;

class TransactionDbService implements TransactionServiceInterface
{
    public function getPaymentMethods(): array
    {
          // Cache key
        $cacheKey = 'report_payment_methods';
        $cacheTTL = 2592000; // 30 days in second

        return Cache::remember($cacheKey, $cacheTTL, function () {
            return Transaction::query()
                ->select('payment_method')
                ->whereNotNull('payment_method')
                ->distinct()
                ->pluck('payment_method') // faster than get()->map()
                ->map(fn ($method) => [
                    'id'   => $method,
                    'name' => $method,
                ])
                ->values()
                ->toArray();
        });
    }

    public function getStatuses(): array
    {
       // Cache key
        $cacheKey = 'report_payment_status';

        // Cache duration in seconds (e.g., 3600 = 1 hour)
        $cacheTTL = 2592000; // 30 days

        return Cache::remember($cacheKey, $cacheTTL, function () {
            return Transaction::select('status')
                ->whereNotNull('status')
                ->distinct()
                ->pluck('status')
                ->map(fn ($status) => [
                    'id'   => $status,
                    'name' => $status,
                ])
                ->values()
                ->toArray();
        });
    }
}

