<?php
namespace App\Modules\Reports\Services;

use App\Modules\Client\Models\Client;
use Illuminate\Support\Facades\Cache;
use App\Modules\Country\Models\Country;
use App\Modules\Reports\Contracts\CountryServiceInterface;
use App\Modules\WorkOrder\Models\WorkOrder;

class CountryDbService implements CountryServiceInterface
{
    public function getCountries(): array
{
    $cacheKey = 'countries_' . auth()->id(); // ✅ FIXED
    $cacheTTL = 2592000;

    $agent = auth()->user()->agent;
    $client = auth()->user()->client;
    $principal = auth()->user()->principal; // ✅ NEW

    $agentCountryIds = [];
    $clientCountryIds = [];
    $principalCountryIds = []; // ✅ NEW

    // ✅ Agent Logic (UNCHANGED)
    if ($agent) {
        $agentCountryIds = Client::query()
            ->join('work_orders', 'clients.id', '=', 'work_orders.client_id')
            ->join('job_lists', 'work_orders.id', '=', 'job_lists.work_order_id')
            ->join('applications', 'job_lists.id', '=', 'applications.job_list_id')
            ->where('applications.agent_id', $agent->id)
            ->pluck('clients.country_id')
            ->unique()
            ->toArray();
    }

    // ✅ Client Logic (UNCHANGED)
    if ($client) {
        $clientCountryIds = WorkOrder::query()
            ->join('clients', 'work_orders.client_id', '=', 'clients.id')
            ->where('work_orders.client_id', $client->id)
            ->pluck('clients.country_id')
            ->unique()
            ->toArray();
    }

    // ✅ Principal Logic (NEW)
    if ($principal) {
        $principalCountryIds = WorkOrder::query()
            ->join('job_lists', 'work_orders.id', '=', 'job_lists.work_order_id')
            ->join('clients', 'work_orders.client_id', '=', 'clients.id')
            ->where('job_lists.principal_id', $principal->id)
            ->pluck('clients.country_id')
            ->unique()
            ->toArray();
    }

    // ✅ Merge all
    $mergeCountryIds = array_merge(
        $agentCountryIds,
        $clientCountryIds,
        $principalCountryIds
    );

    $mergeCountryIds = array_unique($mergeCountryIds);

    return Cache::remember($cacheKey, $cacheTTL, function () use ($mergeCountryIds) {
        return Country::query()
            ->select('id', 'name')
            ->when(!empty($mergeCountryIds), function ($query) use ($mergeCountryIds) {
                $query->whereIn('id', $mergeCountryIds);
            })
            ->orderBy('name', 'asc')
            ->get()
            ->map(fn($country) => [
                'id'   => $country->id,
                'name' => $country->name,
            ])
            ->toArray();
    });
}
}
