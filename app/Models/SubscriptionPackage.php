<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPackage extends Model
{
    protected $fillable = [
        'name', 'slug', 'price_monthly', 'price_yearly',
        'max_users', 'max_invoices', 'max_clients', 'features', 'is_active',
        'trial_days', 'duration_days',
    ];

    protected $casts = [
        'features' => 'array',
        'is_active' => 'boolean',
        'price_monthly' => 'float',
        'price_yearly' => 'float',
    ];

    public function companies()
    {
        return $this->hasMany(Company::class, 'package_id');
    }

    public function companiesCount(): int
    {
        return $this->companies()->count();
    }

    public function formattedMonthly(): string
    {
        return $this->price_monthly > 0
            ? 'Rp ' . number_format($this->price_monthly, 0, ',', '.') . '/bln'
            : 'Gratis';
    }
}
