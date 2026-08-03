<?php
namespace App\Modules\JobList\Services;

use App\Modules\JobList\Models\JobList;
use App\Modules\Application\Models\Process;
use Illuminate\Support\Facades\Cache;
use App\Modules\JobList\Contracts\ProcessServiceInterface;

class ProcessDbService implements ProcessServiceInterface
{
    public function getProcesses(): array
    {

           // Cache key
        $cacheKey = 'processes_all';

        // Cache duration in seconds (e.g., 3600 = 1 hour)
        $cacheTTL = 3600;

        // Use Redis cache
        return Cache::remember($cacheKey, $cacheTTL, function () {
            $processes = Process::all(); // fetch from DB

            return $processes->map(function ($process) {
                return [
                    'id' => $process->id,
                    'name' => $process->name,
                    'duration' => $process->duration,
                ];
            })->toArray();
        });
    }

    

}
