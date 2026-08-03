<?php

namespace App\Modules\Principal\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;

class PrincipalReportMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $principal;
    public $applications;
    public $settings;
    // today's date
    public $date;

    public function __construct($principal, $applications, array $settings = [])
    {
        $this->principal = $principal;
        $this->applications = $applications;
        $this->date = date('Y-m-d');
        $this->settings = $settings;
    }

    public function build()
    {
        $pdf = Pdf::loadView(
            'emails.principal-mails.principal-report-mail',
            [
                'principal' => $this->principal,
                'applications' => $this->applications,
                'setting' => $this->settings,
            ]
        )->setPaper('a4', 'landscape');

        return $this
            ->subject('Applicant Report - ' . $this->date)
            ->view('emails.principal-mails.report-mail-body')
            ->attachData(
                $pdf->output(),
                'application-report.pdf',
                [
                    'mime' => 'application/pdf',
                ]
            );
    }
}
