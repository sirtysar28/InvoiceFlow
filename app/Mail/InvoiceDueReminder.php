<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceDueReminder extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Invoice $invoice,
        public int $daysBeforeDue
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->daysBeforeDue === 0
            ? "Pengingat: Invoice Jatuh Tempo Hari Ini - {$this->invoice->invoice_number}"
            : "Pengingat Invoice Jatuh Tempo (H-{$this->daysBeforeDue}) - {$this->invoice->invoice_number}";

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice-due-reminder',
            with: [
                'invoice' => $this->invoice,
                'daysBeforeDue' => $this->daysBeforeDue,
            ],
        );
    }
}
