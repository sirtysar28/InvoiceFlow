<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesOrder extends Model
{
    protected $fillable = [
        'user_id', 'order_number', 'quotation_id', 'client_id',
        'client_name', 'client_email', 'client_phone',
        'items', 'subtotal', 'discount_amount', 'tax_amount', 'total',
        'order_date', 'status', 'invoice_id', 'notes',
    ];

    protected $casts = [
        'items' => 'array',
        'order_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'new' => 'Baru',
            'processing' => 'Diproses',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            'invoiced' => 'Sudah Di-invoice',
            default => ucfirst($this->status),
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'new' => 'primary',
            'processing' => 'warning',
            'completed' => 'success',
            'cancelled' => 'danger',
            'invoiced' => 'info',
            default => 'secondary',
        };
    }
}
