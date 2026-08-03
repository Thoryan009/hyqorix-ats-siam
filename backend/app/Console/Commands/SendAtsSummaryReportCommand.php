<?php

namespace App\Console\Commands;

use App\Modules\Employee\Models\Employee;
use App\Modules\Reports\ATS\Repositories\AtsSummaryReportRepository ;
use App\Modules\Reports\ATS\Mail\AtsSummaryReportMail;
use App\Modules\Setting\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendAtsSummaryReportCommand extends Command
{
    protected $signature = 'report:send-ats-summary';

    protected $description = 'Send ATS Summary Report';

    public function __construct(
        private AtsSummaryReportRepository $repository
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $emails = Employee::query()
            ->with('user:id,email')
            ->where('show_ats_summary', 1)
            ->get()
            ->pluck('user.email')
            ->filter()
            ->unique()
            ->values();

        if ($emails->isEmpty()) {
            $this->warn('No ATS summary recipients found.');
            return self::SUCCESS;
        }

        $report = $this->repository->getSummary($filters = []); // You can add filters if needed

        if (empty($report['rows'])) {
            $this->warn('No ATS summary data found.');
            return self::SUCCESS;
        }

        $setting = Setting::first();

        foreach ($emails as $email) {

            Mail::to($email)
                ->queue(
                    new AtsSummaryReportMail(
                        report: $report,
                        settings: $setting?->toArray() ?? []
                    )
                );

            $this->info(
                "ATS Summary Report sent to {$email}"
            );
        }

        return self::SUCCESS;
    }
}
