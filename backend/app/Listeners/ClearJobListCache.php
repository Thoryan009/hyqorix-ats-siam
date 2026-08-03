<?php

namespace App\Listeners;

use App\Events\ApplicationProcessCreated;
use App\Modules\JobList\Services\JobListService;

class ClearJobListCache
{
    /**
     * Create the event listener.
     */
    public function __construct(
        protected JobListService $service
    ) {}

    /**
     * Handle the event.
     */
    public function handle(ApplicationProcessCreated $event): void
    {
        $this->service->clearApplicationProcessCache();
    }
}
