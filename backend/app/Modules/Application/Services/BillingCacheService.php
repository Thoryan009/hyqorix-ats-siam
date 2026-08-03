<?php

namespace App\Modules\Application\Services;

use Illuminate\Support\Facades\Cache;

class BillingCacheService
{
    public function flush(): void
    {
        Cache::tags([
            'Transaction',
            'ClientBill'
        ])->flush();
    }
}
