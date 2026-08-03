<?php

namespace App\Modules\Reports\Expiry\Mails;

// use Illuminate\Contracts\Mail\Mailable;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ExpiryReportMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $department;
    public $reportDate;
    public $rows;
    public $settings;

    public function __construct(
        $department,
        string $reportDate,
        $rows,
        $settings
    ) {
        $this->department = $department;
        $this->reportDate = $reportDate;
        $this->rows = $rows;
        $this->settings = $settings;
    }

    public function build()
    {
        $pdf = Pdf::loadView(
            'emails.expiry.expiry-report-pdf',
            [
                'department' => $this->department,
                'reportDate' => $this->reportDate,
                'rows' => $this->rows,
                'setting' => $this->settings,
            ]
        )->setPaper('a4', 'landscape');

        return $this
            ->subject(
                'Daily Expiry Report - ' .
                Carbon::parse($this->reportDate)->format('d M Y')
            )
            ->view('emails.expiry.report-mail-body')
            ->attachData(
                $pdf->output(),
                'expiry-report.pdf',
                [
                    'mime' => 'application/pdf',
                ]
            );
    }
}
