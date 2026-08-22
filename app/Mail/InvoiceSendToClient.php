<?php

namespace App\Mail;

use App\Models\Invoice;
use App\Models\BusinessProfile;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceSendToClient extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Invoice $invoice,
        public ?BusinessProfile $profile,
        public string $customSubject,
        public string $customMessage,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->customSubject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice-send-client',
            with: [
                'invoice' => $this->invoice,
                'profile' => $this->profile,
                'customMessage' => $this->customMessage,
            ],
        );
    }
}
