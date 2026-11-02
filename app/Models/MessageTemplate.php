<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MessageTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_id',
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

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /** Template untuk tipe tertentu (whatsapp/email): milik perusahaan user login, fallback platform. */
    public static function forType(string $type, ?int $companyId = null): ?self
    {
        $companyId ??= auth()->user()?->company_id;

        return $companyId
            ? (self::where('company_id', $companyId)->where('type', $type)->first()
                ?? self::whereNull('company_id')->where('type', $type)->first())
            : self::whereNull('company_id')->where('type', $type)->first();
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

        $body = str_replace(array_keys($replacements), array_values($replacements), $this->body);

        return $this->normalizeNewlines($body);
    }

    /**
     * Anti "ngaco": template lama/seeder pernah menyimpan newline sebagai
     * literal %0A (percent-encoded). Saat controller men-urlencode() pesan,
     * %0A ikut ter-encode lagi jadi %250A sehingga WhatsApp menampilkan
     * teks "%0A" mentah. Normalisasi ke newline asli di sini.
     */
    private function normalizeNewlines(string $body): string
    {
        // %0D%0A / %0A / %0D literal → newline asli
        $body = str_replace(['%0D%0A', '%0d%0a', '%0A', '%0a', '%0D', '%0d'], "\n", $body);
        // Samakan CRLF → LF
        return str_replace(["\r\n", "\r"], "\n", $body);
    }
}
