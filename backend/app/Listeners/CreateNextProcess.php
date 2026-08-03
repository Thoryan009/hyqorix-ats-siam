<?php

namespace App\Listeners;

use App\Events\NextProcessCreated;
use App\Modules\Application\Services\ApplicationProcessService;


class CreateNextProcess
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
    public function handle(NextProcessCreated $event): void
    {
        $this->service->createNextProcess($event->data);
    }
}
