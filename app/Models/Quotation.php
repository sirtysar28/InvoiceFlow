<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    protected $fillable = [
        'user_id', 'quote_number', 'client_id', 'client_name', 'client_email', 'client_phone',
        'items', 'subtotal', 'discount', 'discount_amount', 'tax', 'tax_amount', 'total',
        'valid_until', 'status', 'notes',
    ];

    protected $casts = [
        'items' => 'array',
        'valid_until' => 'date',
    ];

    public const STATUS_DRAFT     = 'draft';
    public const STATUS_SENT      = 'sent';
    public const STATUS_ACCEPTED  = 'accepted';
    public const STATUS_REJECTED  = 'rejected';
    public const STATUS_CONVERTED = 'converted';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function salesOrders()
    {
        return $this->hasMany(SalesOrder::class);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'sent' => 'Terkirim',
            'accepted' => 'Disetujui',
            'rejected' => 'Ditolak',
            'converted' => 'Jadi Order/Invoice',
            'expired' => 'Kedaluwarsa',
            default => ucfirst($this->status),
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'draft' => 'secondary',
            'sent' => 'primary',
            'accepted' => 'success',
            'rejected', 'expired' => 'danger',
            'converted' => 'info',
            default => 'secondary',
        };
    }

    public function isExpired(): bool
    {
        return $this->valid_until && $this->valid_until->isPast() && $this->status === 'sent';
    }
}
