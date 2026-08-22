<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NotificationSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'wa_enabled',
        'email_enabled',
        'wa_number',
        'email',
        'days_before_due',
        'send_on_due_day',
        'send_at_hour',
    ];

    protected $casts = [
        'wa_enabled' => 'boolean',
        'email_enabled' => 'boolean',
        'send_on_due_day' => 'boolean',
        'days_before_due' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getNotificationDays(): array
    {
        $days = $this->days_before_due ?? [2]; // default H-2
        if ($this->send_on_due_day) {
            $days[] = 0; // H-0
        }
        sort($days);
        return $days;
    }
}
