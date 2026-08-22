<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MessageTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'type',
        'subject',
        'body',
    ];

    const TYPE_WHATSAPP = 'whatsapp';
    const TYPE_EMAIL = 'email';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getParsedBody(Invoice $invoice): string
    {
        $replacements = [
            '{no_invoice}' => $invoice->invoice_number,
            '{nama_klien}' => $invoice->client_name,
            '{total}' => number_format($invoice->total, 0, ',', '.'),
            '{tanggal_jatuh_tempo}' => $invoice->due_date ? $invoice->due_date->format('d/m/Y') : '-',
            '{link_invoice}' => route('invoices.public', $invoice->id),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $this->body);
    }
}
