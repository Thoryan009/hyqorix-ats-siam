<?php
namespace App\Modules\Reports\Services;
use App\Modules\Client\Models\Client;
use App\Modules\Reports\Contracts\PrincipalServiceInterface;
use App\Modules\Principal\Models\Principal;
use Illuminate\Support\Facades\Cache;

class PrincipalDbService implements PrincipalServiceInterface
{
    public $cacheKey = 'principals';
    public function getPrincipals(): array
    {
        if(auth()->user()->client || auth()->user()->agent || auth()->user()->principal) {
            return [];
        }
       // Cache key
        $cacheKey = 'principals';
        $cacheTTL = 2592000; // 30 days in second

        return Cache::remember($cacheKey, $cacheTTL, function () {
            return Principal::query()
                ->select('principals.id', 'principals.user_id')
                ->with('user:id,name')
                ->join('users', 'principals.user_id', '=', 'users.id')
                ->orderBy('users.name', 'asc')
                ->get()
                ->map(fn($principal) => [
                    'id' => $principal->id,
                    'name' => optional($principal->user)->name,
                ])->toArray();
        });

    }

    public function clearPrincipalCache(): void
    {
        Cache::forget($this->cacheKey);
    }
}
