<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'name', 'email', 'phone', 'address', 'package_id',
        'subscription_expires_at', 'is_active', 'activation_token',
    ];

    protected $casts = [
        'subscription_expires_at' => 'date',
        'is_active' => 'boolean',
    ];

    public function package()
    {
        return $this->belongsTo(SubscriptionPackage::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function usersCount(): int
    {
        return $this->users()->count();
    }

    public function invoicesCount(): int
    {
        $userIds = $this->users()->pluck('id');
        return Invoice::whereIn('user_id', $userIds)->count();
    }

    public function revenueTotal(): float
    {
        $userIds = $this->users()->pluck('id');
        return (float) Invoice::whereIn('user_id', $userIds)->where('is_paid', true)->sum('total');
    }

    public function subscriptionStatus(): string
    {
        if (!$this->is_active) {
            return 'Suspended';
        }
        if ($this->subscription_expires_at && $this->subscription_expires_at->isPast()) {
            return 'Expired';
        }
        return 'Active';
    }

    /** True jika masa langganan (trial/berbayar) sudah lewat atau dikunci super admin. */
    public function isLocked(): bool
    {
        return !$this->is_active
            || ($this->subscription_expires_at && $this->subscription_expires_at->isPast());
    }

    public function subscriptionColor(): string
    {
        return match ($this->subscriptionStatus()) {
            'Active' => 'success',
            'Expired' => 'danger',
            default => 'secondary',
        };
    }
}
