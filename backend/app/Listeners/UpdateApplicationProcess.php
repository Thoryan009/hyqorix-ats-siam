<?php

namespace App\Listeners;

use App\Events\JobProcessSubmitted;
use App\Modules\Application\Services\ApplicationProcessService;

class UpdateApplicationProcess
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
    public function handle(JobProcessSubmitted $event): void
    {
        $this->service->updateProcess($event->data);
    }
}
