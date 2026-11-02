<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id', 'action', 'module', 'description', 'ip_address', 'user_agent',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Ikon bootstrap sesuai aksi */
    public function getIconAttribute(): string
    {
        return match ($this->action) {
            'login'  => 'bi-box-arrow-in-right',
            'logout' => 'bi-box-arrow-right',
            default  => str_contains($this->action, '.delete') ? 'bi-trash'
                : (str_contains($this->action, '.create') ? 'bi-plus-circle'
                : (str_contains($this->action, '.update') ? 'bi-pencil'
                : 'bi-activity')),
        };
    }

    /** Warna badge sesuai aksi */
    public function getColorAttribute(): string
    {
        return match (true) {
            $this->action === 'login' => 'primary',
            $this->action === 'logout' => 'secondary',
            str_contains($this->action, '.delete') => 'danger',
            str_contains($this->action, '.create') => 'success',
            str_contains($this->action, '.update') => 'warning',
            str_contains($this->action, 'send') => 'info',
            default => 'primary',
        };
    }
}
