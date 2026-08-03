<?php

namespace App\Modules\JobList\Listeners;

use App\Modules\Application\Services\ApplicationProcessService;
use App\Modules\JobList\Events\BulkNextProcessCreated;

class CreateBulkNextProcess
{
    /**
     * Create the event listener.
     */
    public function __construct(
        protected ApplicationProcessService $service
    ) {}
    /**
     * Handle the event.
     */
    public function handle(BulkNextProcessCreated $event): void
    {
        \Log::info('Handling Bulk Next Process Creation:', ['event_data' => $event->data]);
        $this->service->createBulkNextProcess($event->data);
    }
}
