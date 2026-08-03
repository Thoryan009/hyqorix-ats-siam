<?php
namespace App\Modules\Reports\Services;
use App\Modules\Reports\Contracts\AgentServiceInterface;
use App\Modules\Client\Models\Client;
use App\Modules\Agent\Models\Agent;
use Illuminate\Support\Facades\Cache;

class AgentDbService implements AgentServiceInterface
{
    public $cacheKey = 'agents';
    public function getAgents(): array
    {
        if(auth()->user()->type === 'agent' || auth()->user()->type === 'principal') {
            return [];
        }
       // Cache key
        $cacheKey = 'agents';
        $cacheTTL = 2592000; // 30 days in second

        return Cache::remember($cacheKey, $cacheTTL, function () {
            return Agent::query()
                ->select('agents.id', 'agents.user_id')
                ->with('user:id,name')
                ->join('users', 'agents.user_id', '=', 'users.id')
                ->withCount('applications')
                ->orderBy('users.name', 'asc')
                ->get()
                ->map(fn($agent) => [
                    'id' => $agent->id,
                    'applications_count' => $agent->applications_count,
                    'name' => sprintf(
                        '%s (%d)',
                        optional($agent->user)->name ?? 'N/A',
                        $agent->applications_count
                    ),
                ])
                ->toArray();
        });

    }

    public function clearAgentCache(): void
    {
        Cache::forget($this->cacheKey);
    }
}
