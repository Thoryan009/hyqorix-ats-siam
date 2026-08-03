<?php
namespace App\Modules\Reports\Services;
use App\Modules\Reports\Contracts\ProcessServiceInterface;
use App\Modules\Application\Models\Process;
use Illuminate\Support\Facades\Cache;

class ProcessDbService implements ProcessServiceInterface
{
    public function getProcesses(): array
    {
        $cacheKey = 'processes';
        $cacheTTL = 2592000; // 30 days

        return Cache::remember($cacheKey, $cacheTTL, function () {
            $processes = Process::select('id', 'name')
                ->get()
                ->map(
                    fn($process) => [
                        'id' => $process->id,
                        'name' => $process->name,
                    ],
                )
                ->toArray();

            // ✅ Get from config/app.php
            $extraProcesses = [
                [
                    'id' => (int) config('app.process_rejected_id'),
                    'name' => 'rejected',
                ],
                [
                    'id' => (int) config('app.process_declined_id'),
                    'name' => 'declined',
                ],
                [
                    'id' => (int) config('app.process_deployed_id'),
                    'name' => 'deployed',
                ],
            ];

            // ✅ Merge + avoid duplicate IDs
            return collect($processes)->merge($extraProcesses)->unique('id')->values()->toArray();
        });
    }

}
