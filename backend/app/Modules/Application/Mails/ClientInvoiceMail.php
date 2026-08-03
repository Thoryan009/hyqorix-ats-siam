<?php

namespace App\Modules\Application\Mails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class ClientInvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public $clientMail, $setting;

    public function __construct($clientMail, $setting)
    {
        $this->clientMail = $clientMail;
        $this->setting = $setting;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->clientMail->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.client-mails.client-invoice-mail',
            with: [
                'body' => $this->clientMail->body,
                'billNo' => $this->clientMail->bill_no,
                'setting' => $this->setting,
            ],
        );
    }

    public function attachments(): array
    {
        if (!$this->clientMail->invoice_path) {
            return [];
        }
        $path = storage_path('app/private/' . $this->clientMail->invoice_path);
        return [
            Attachment::fromPath($path)
                ->as('Invoice-' . $this->clientMail->bill_no . '.pdf')
                ->withMime('application/pdf'),
        ];
    }

}
