<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Modules\Principal\Mail\PrincipalReportMail;
use App\Modules\Principal\Models\Principal;
use App\Modules\Reports\Application\Repositories\ApplicationReportRepository;
use App\Modules\Setting\Models\Setting;

class SendPrincipalApplicationReportCommand extends Command
{
    protected $signature = 'report:send-principal-applications';

    protected $description = 'Send application reports to principals';

    public function __construct(
        private ApplicationReportRepository $repository
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $setting = Setting::first();
        $principals = Principal::query()
            ->with('user')
            ->where('send_notification', true)
            ->get();

        foreach ($principals as $principal) {

            $applications = $this->repository
                ->principalApplications($principal->id)
                ->with([
                    'jobList.workOrder.client.country',
                    'jobList.principal',
                    'agent.user',
                    'currentProcess.process',
                ])
                ->get();

            if ($applications->isEmpty()) {
                continue;
            }

             Mail::to($principal->user->email)
                ->queue(new PrincipalReportMail(
                    principal: $principal,
                    applications: $applications,
                    settings: $setting->toArray()
                ));

            $this->info(
                "Report sent to {$principal->user->email}"
            );
        }

        return self::SUCCESS;
    }
}
