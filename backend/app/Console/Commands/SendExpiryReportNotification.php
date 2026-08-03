<?php

namespace App\Console\Commands;

use App\Modules\Employee\Models\Department;
use App\Modules\Reports\Expiry\Services\ExpiryReportService;
use App\Modules\Setting\Models\Setting;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Modules\Reports\Expiry\Mails\ExpiryReportMail;
use Illuminate\Support\Facades\Mail;

class SendExpiryReportNotification extends Command
{
    protected $signature = 'app:send-expiry-report-notification {--date= : Report date in Y-m-d format}';

    protected $description = 'Send expiry report notification email to employees of configured department';

    public function handle(ExpiryReportService $expiryReportService): int
    {
        try {
            $setting = Setting::query()->first();
            $departmentId = $setting?->expiry_report_notify_department_id;

            if (!$departmentId) {
                Log::info('Expiry report notification skipped: no department configured in settings.');
                return self::SUCCESS;
            }

            $department = Department::query()
                ->with(['employees.user:id,email,name'])
                ->find($departmentId);

            if (!$department) {
                Log::warning("Expiry report notification skipped: department {$departmentId} not found.");
                return self::SUCCESS;
            }

            $emails = $department->employees
                ->pluck('user.email')
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (empty($emails)) {
                Log::info("Expiry report notification skipped: no employee emails in department {$department->name}.");
                return self::SUCCESS;
            }

            $dateInput = $this->option('date');
            $reportDate = $dateInput
                ? Carbon::parse($dateInput)->toDateString()
                : now()->toDateString();

            $rows = $expiryReportService->getExpiryRows([
                'from_date' => $reportDate,
                'to_date' => $reportDate,
                'process' => null,
            ]);

            // $subject = 'Daily Expiry Report - ' . Carbon::parse($reportDate)->format('d M Y');


            // $html = $this->buildEmailHtml($department->name, $reportDate, $rows->toArray(), $setting);

            // Mail::send([], [], function ($message) use ($emails, $subject, $html) {
            //     $message->to($emails)
            //         ->subject($subject)
            //         ->html($html);
            // });

            Mail::to($emails)
            ->queue(
                new ExpiryReportMail(
                    department: $department,
                    reportDate: $reportDate,
                    rows: $rows,
                    settings: $setting
                )
            );



            Log::info('Expiry report notification sent successfully.', [
                'department_id' => $department->id,
                'department_name' => $department->name,
                'recipients' => count($emails),
                'rows' => $rows->count(),
                'report_date' => $reportDate,
            ]);

            return self::SUCCESS;
        } catch (\Throwable $e) {
            Log::error('Expiry report notification failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return self::FAILURE;
        }
    }

    // private function buildEmailHtml(string $departmentName, string $reportDate, array $rows,  ?Setting $setting): string
    // {
    //     $header = "
    //             <div style='border-bottom:2px solid #000;padding-bottom:10px;margin-bottom:15px;'>

    //                 <table style='width:100%;'>
    //                     <tr>
    //                         <td style='width:100px;text-align:center;'>
    //                             <img src='" . asset('storage/' . ($setting?->company_logo_path ?? '')) . "'
    //                                 style='max-height:60px;'>
    //                         </td>
    //                         <td style='text-align:right;'>
    //                             <div style='font-size:20px;font-weight:bold;color:#000;'>
    //                                 {$this->escape($setting?->company_name ?? 'Company Name')}
    //                             </div>
    //                             <div style='font-size:10px;color:#4b5563;line-height:1.5;'>
    //                                 {$this->escape($setting?->company_address ?? '')}
    //                             </div>
    //                         </td>
    //                     </tr>
    //                 </table>
    //             </div>
    //             <div style='text-align:center;margin:15px 0;'>
    //                 <h2 style='margin:0;font-size:18px;'>Expiry Report</h2>
    //             </div>
    //             <div style='background:#f9fafb;border:1px solid #e5e7eb;padding:8px;margin-bottom:15px;'>
    //                 <table style='width:100%;'>
    //                     <tr>
    //                         <td width='50%'>
    //                             <strong>Department:</strong>
    //                             {$this->escape($departmentName)}
    //                         </td>
    //                         <td width='50%'>
    //                             <strong>Date:</strong>
    //                             {$this->escape(Carbon::parse($reportDate)->format('d M Y'))}
    //                         </td>
    //                     </tr>
    //                 </table>
    //             </div>
    //             ";

    //     if (empty($rows)) {
    //         return $header . "
    //             <p>No candidates matched the expiry notify condition for this date.</p>
    //             <div style='margin-top:30px;text-align:center;color:#6b7280;font-size:10px;border-top:2px solid #1e40af;padding-top:10px;'>
    //                 Generated automatically at " . now()->format('d M Y h:i A') . " by ATS System
    //             </div>
    //         ";
    //     }

    //     $tableRows = '';
    //     foreach ($rows as $row) {
    //         $tableRows .= '<tr>'
    //             . '<td style="border:1px solid #ddd;padding:8px;">' . $this->escape($row['passport_no'] ?? '-') . '</td>'
    //             . '<td style="border:1px solid #ddd;padding:8px;">' . $this->escape($row['candidate_name'] ?? '-') . '</td>'
    //             . '<td style="border:1px solid #ddd;padding:8px;">' . $this->escape($row['mobile'] ?? '-') . '</td>'
    //             . '<td style="border:1px solid #ddd;padding:8px;">' . $this->escape($row['agent_name'] ?? '-') . '</td>'
    //             . '<td style="border:1px solid #ddd;padding:8px;">' . $this->escape($row['client_name'] ?? '-') . '</td>'
    //             . '<td style="border:1px solid #ddd;padding:8px;">' . $this->escape($row['job_name'] ?? '-') . '</td>'
    //             . '<td style="border:1px solid #ddd;padding:8px;">' . $this->escape($row['agent_mobile_no'] ?? '-') . '</td>'
    //             . '<td style="border:1px solid #ddd;padding:8px;">' . $this->escape($row['document'] ?? '-') . '</td>'
    //             . '<td style="border:1px solid #ddd;padding:8px;">' . $this->escape($row['expiry_date_formatted'] ?? $row['expiry_date'] ?? '-') . '</td>'
    //             . '<td style="border:1px solid #ddd;padding:8px;">' . $this->escape((string) ($row['days_left'] ?? '-')) . '</td>'
    //             . '<td style="border:1px solid #ddd;padding:8px;">' . $this->escape($row['current_process'] ?? '-') . '</td>'
    //             . '</tr>';
    //     }

    //     return $header . "
    //         <table style='border-collapse:collapse;width:100%;'>
    //             <thead>
    //                 <tr style='background:#f3f4f6;'>
    //                     <th style='border:1px solid #ddd;padding:8px;text-align:left;'>Passport No</th>
    //                     <th style='border:1px solid #ddd;padding:8px;text-align:left;'>Candidate Name</th>
    //                     <th style='border:1px solid #ddd;padding:8px;text-align:left;'>Mobile</th>
    //                     <th style='border:1px solid #ddd;padding:8px;text-align:left;'>Agent Name</th>
    //                     <th style='border:1px solid #ddd;padding:8px;text-align:left;'>Client Name</th>
    //                     <th style='border:1px solid #ddd;padding:8px;text-align:left;'>Job Name</th>
    //                     <th style='border:1px solid #ddd;padding:8px;text-align:left;'>Agent Mobile No</th>
    //                     <th style='border:1px solid #ddd;padding:8px;text-align:left;'>Document</th>
    //                     <th style='border:1px solid #ddd;padding:8px;text-align:left;'>Expiry Date</th>
    //                     <th style='border:1px solid #ddd;padding:8px;text-align:left;'>Days Left</th>
    //                     <th style='border:1px solid #ddd;padding:8px;text-align:left;'>Current Process</th>
    //                 </tr>
    //             </thead>
    //             <tbody>
    //                 {$tableRows}
    //             </tbody>
    //         </table>
    //         <div style='margin-top:30px;text-align:center;color:#6b7280;font-size:10px;border-top:2px solid #1e40af;padding-top:10px;'>
    //             Generated automatically at " . now()->format('d M Y h:i A') . " by ATS System
    //         </div>
    //     ";
    // }

    // private function escape(?string $value): string
    // {
    //     return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
    // }
}
