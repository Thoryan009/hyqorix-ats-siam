<?php

namespace App\Modules\Reports\ATS\Mail;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AtsSummaryReportMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public array $report,
        public array $settings = []
    ) {}

    public function build()
{
    $pdf = Pdf::loadView(
        'emails.ats-summary.ats-summary-report-pdf',
        [
            'rows' => $this->report['rows'],
            'cards' => $this->report['cards'],
            'grandTotal' => $this->report['grand_total'],
            'processes' => $this->report['processes'],
            'setting' => $this->settings,
        ]
    )->setPaper('a4', 'landscape');

    return $this
        ->subject('ATS Summary Report - ' . now()->format('d M Y'))
        ->view(
            'emails.ats-summary.ats-summary-mail-body',
            [
                'grandTotal' => $this->report['grand_total'],
                'settings' => $this->settings,
            ]
        )
        ->attachData(
            $pdf->output(),
            'ats-summary-report.pdf',
            [
                'mime' => 'application/pdf',
            ]
        );
}
}
