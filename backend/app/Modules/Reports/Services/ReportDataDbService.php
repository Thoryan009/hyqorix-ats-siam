<?php
namespace App\Modules\Reports\Services;

use App\Modules\Agent\Models\Agent;
use App\Modules\Reports\Contracts\ReportDataServiceInterface;
use App\Modules\Country\Models\Country;
use App\Modules\Client\Models\Client;
use App\Modules\JobList\Models\JobList;
use App\Modules\Application\Models\Process;
use App\Modules\WorkOrder\Models\WorkOrder;
use App\Modules\Application\Models\Transaction;
use Illuminate\Support\Facades\Cache;

class ReportDataDbService implements ReportDataServiceInterface
{
    public function getReportData(): array
    {
        // Cache key
        $cacheKey = 'report_datas';
        $cacheTTL = 2592000; // 30 days in second

        return Cache::remember($cacheKey, $cacheTTL, function () {
            $countries = Country::query()
                ->select('id', 'name')
                ->get()
                ->map(
                    fn($country) => [
                        'id' => $country->id,
                        'name' => $country->name,
                    ],
                )
                ->toArray();

            $clients = Client::query()
                ->select('id', 'user_id')
                ->with('user:id,name') // eager load user
                ->get()
                ->map(
                    fn($client) => [
                        'id' => $client->id,
                        'name' => optional($client->user)->name,
                    ],
                )
                ->toArray();

                $agents = Agent::query()
                ->select('id', 'user_id')
                ->with('user:id,name') // eager load user
                ->get()
                ->map(
                    fn($agent) => [
                        'id' => $agent->id,
                        'name' => optional($agent->user)->name,
                    ],
                )
                ->toArray();

            $workOrders = WorkOrder::select('id', 'work_order_id')
                ->get()
                ->map(
                    fn($workOrder) => [
                        'id' => $workOrder->id,
                        'name' => $workOrder->work_order_id,
                    ],
                )
                ->toArray();

            $jobLists = JobList::query()
                ->where('status', 'open') // only open jobs
                ->get()
                ->map(function ($job) {
                    return [
                        'id' => $job->id,
                        'name' => $job->name . ' (' . $job->job_code . ')',
                    ];
                })
                ->toArray();

            $processes = Process::query()
                ->get()
                ->map(function ($process) {
                    return [
                        'id' => $process->id,
                        'name' => $process->name,
                    ];
                })
                ->toArray();

            $transactionStatus = Transaction::select('status')
                ->whereNotNull('status')
                ->distinct()
                ->pluck('status')
                ->map(
                    fn($status) => [
                        'id' => $status,
                        'name' => $status,
                    ],
                )
                ->values()
                ->toArray();

            $transactionPaymentMethods = Transaction::select('payment_method')
                ->whereNotNull('payment_method')
                ->distinct()
                ->pluck('payment_method')
                ->map(
                    fn($method) => [
                        'id' => $method,
                        'name' => $method,
                    ],
                )
                ->values()
                ->toArray();

            return [
                'countries' => $countries,
                'clients' => $clients,
                'agents' => $agents,
                'work_orders' => $workOrders,
                'job_lists' => $jobLists,
                'processes' => $processes,
                'transaction_payment_methods' => $transactionPaymentMethods,
                'transaction_status' => $transactionStatus,
            ];
        });
    }
}
