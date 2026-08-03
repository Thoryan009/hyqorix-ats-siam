<?php

namespace App\Modules\Client\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;


class ClientReportMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */

    public $client;
    public $applications;
    public $settings;
    public $date;

    public function __construct(
         $client,
         $applications,
        array $settings
    ) {
        $this->client = $client;
        $this->applications = $applications;
        $this->date = date('Y-m-d');
        $this->settings = $settings;
    }

    /**
     * Get the message envelope.
     */

    public function build()
    {
        $pdf = Pdf::loadView(
            'emails.client-mails.client-report-mail',
            [
                'client' => $this->client,
                'applications' => $this->applications,
                'setting' => $this->settings,
            ]
        )->setPaper('a4', 'landscape');

        return $this
            ->subject('Applicant Report - ' . $this->date)
            ->view('emails.client-mails.report-mail-body')
            ->attachData(
                $pdf->output(),
                'application-report.pdf',
                [
                    'mime' => 'application/pdf',
                ]
            );
    }

}
