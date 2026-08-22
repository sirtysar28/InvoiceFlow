<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NotificationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'user_id',
        'channel',
        'type',
        'days_before_due',
        'sent_at',
        'status',
        'message',
    ];

    protected $casts = [
        'days_before_due' => 'integer',
        'sent_at' => 'datetime',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function wasNotificationSent(int $invoiceId, int $daysBeforeDue): bool
    {
        return self::where('invoice_id', $invoiceId)
            ->where('days_before_due', $daysBeforeDue)
            ->exists();
    }
}
