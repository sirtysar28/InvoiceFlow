<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_UNPAID = 'unpaid';
    const STATUS_PAID = 'paid';
    const STATUS_OVERDUE = 'overdue';

    protected $fillable = [
        'user_id',
        'client_id',
        'invoice_number',
        'client_name',
        'client_email',
        'client_phone',
        'description',
        'amount',
        'unit_price',
        'quantity',
        'subtotal',
        'tax',
        'tax_amount',
        'discount',
        'discount_amount',
        'total',
        'due_date',
        'status',
        'notes',
        'is_sent',
        'sent_at',
        'is_paid',
        'paid_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'sent_at' => 'datetime',
        'paid_at' => 'datetime',
        'is_sent' => 'boolean',
        'is_paid' => 'boolean',
        'amount' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    protected $appends = ['status_label', 'status_color'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function notificationLogs()
    {
        return $this->hasMany(NotificationLog::class);
    }

    public function markAsPaid()
    {
        $this->is_paid = true;
        $this->status = self::STATUS_PAID;
        $this->paid_at = now();
        $this->save();
    }

    public function markAsSent()
    {
        $this->is_sent = true;
        $this->sent_at = now();
        $this->save();
    }

    public function isOverdue(): bool
    {
        return !$this->is_paid && $this->due_date && $this->due_date->isPast();
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->is_paid) return 'Lunas';
        if ($this->isOverdue()) return 'Lewat Jatuh Tempo';
        if ($this->due_date && $this->due_date->isToday()) return 'Jatuh Tempo';
        return 'Belum Dibayar';
    }

    public function getStatusColorAttribute(): string
    {
        if ($this->is_paid) return 'success';
        if ($this->isOverdue()) return 'danger';
        if ($this->due_date && $this->due_date->isToday()) return 'warning';
        return 'secondary';
    }

    public function scopeUnpaid($query)
    {
        return $query->where('is_paid', false)->whereDate('due_date', '>=', now());
    }

    public function scopePaid($query)
    {
        return $query->where('is_paid', true);
    }

    public function scopeOverdue($query)
    {
        return $query->where('is_paid', false)->whereDate('due_date', '<', now());
    }
}
