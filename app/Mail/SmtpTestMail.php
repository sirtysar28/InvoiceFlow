<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SmtpTestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $host,
        public int $port,
        public string $encryption
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '✅ Konfigurasi SMTP Berhasil — ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.smtp-test',
            with: [
                'host' => $this->host,
                'port' => $this->port,
                'encryption' => $this->encryption,
            ],
        );
    }
}
