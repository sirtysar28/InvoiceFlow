<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Payment extends Model
{
    protected $table = 'payments';

    protected $fillable = [
        'invoice_id', 'user_id', 'method', 'amount', 'status',
        'bank_name', 'sender_name', 'transfer_date', 'proof_path', 'notes',
        'order_id', 'transaction_id', 'payment_type', 'snap_url', 'payload',
        'confirmed_by', 'confirmed_at', 'admin_note', 'paid_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'transfer_date' => 'date',
        'confirmed_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function confirmer()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function getStatusLabelAttribute(): string
    {
        // Midtrans TIDAK diverifikasi admin — bedakan labelnya biar gak membingungkan
        if ($this->status === 'pending' && $this->method === 'midtrans') {
            return 'Menunggu Midtrans';
        }

        return match ($this->status) {
            'success' => 'Berhasil',
            'pending' => 'Menunggu Verifikasi',
            'rejected' => 'Ditolak',
            'failed' => 'Gagal',
            default => ucfirst($this->status),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'success' => 'success',
            'pending' => 'warning',
            'rejected', 'failed' => 'danger',
            default => 'secondary',
        };
    }

    public function getMethodLabelAttribute(): string
    {
        return $this->method === 'midtrans' ? 'Midtrans (Online)' : 'Transfer Manual';
    }

    public function proofUrl(): ?string
    {
        return $this->proof_path ? Storage::url($this->proof_path) : null;
    }
}
