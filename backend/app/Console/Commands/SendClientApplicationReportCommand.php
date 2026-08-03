<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Modules\Client\Mail\ClientReportMail;
use App\Modules\Client\Models\Client;
use App\Modules\Reports\Application\Repositories\ApplicationReportRepository;
use App\Modules\Setting\Models\Setting;

class SendClientApplicationReportCommand extends Command
{
    protected $signature = 'report:send-client-applications';

    protected $description = 'Send application reports to clients';

    public function __construct(
        private ApplicationReportRepository $repository
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $setting = Setting::first();
        $clients = Client::query()
            ->with('user')
            ->where('send_notification', true)
            ->get();

        foreach ($clients as $client) {

            $applications = $this->repository
                ->clientApplications($client->id)
                ->with([
                    'jobList.workOrder.client.country',
                    'jobList.workOrder.client',
                    'agent.user',
                    'currentProcess.process',
                ])
                ->get();

            if ($applications->isEmpty()) {
                continue;
            }

             Mail::to($client->user->email)
                ->queue(new ClientReportMail(
                    client: $client,
                    applications: $applications,
                    settings: $setting->toArray()
                ));

            $this->info(
                "Report sent to {$client->user->email}"
            );
        }

        return self::SUCCESS;
    }
}
