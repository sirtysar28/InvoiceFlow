<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'company_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // ===== Role constants =====
    const ROLE_SUPERADMIN = 'superadmin';
    const ROLE_ADMIN      = 'admin';      // Admin/Owner perusahaan
    const ROLE_FINANCE    = 'finance';
    const ROLE_SALES      = 'sales';
    const ROLE_KARYAWAN   = 'karyawan';   // legacy (kompatibilitas data lama)
    const ROLE_KLIEN      = 'klien';      // Customer

    public static function roles(): array
    {
        return [
            self::ROLE_SUPERADMIN => 'Super Admin',
            self::ROLE_ADMIN      => 'Admin / Owner',
            self::ROLE_FINANCE    => 'Finance',
            self::ROLE_SALES      => 'Sales',
            self::ROLE_KLIEN      => 'Customer',
        ];
    }

    public function roleLabel(): string
    {
        return self::roles()[$this->role] ?? ucfirst($this->role);
    }

    public function roleColor(): string
    {
        return match ($this->role) {
            self::ROLE_SUPERADMIN => 'dark',
            self::ROLE_ADMIN      => 'primary',
            self::ROLE_FINANCE    => 'info',
            self::ROLE_SALES      => 'success',
            self::ROLE_KLIEN      => 'secondary',
            default               => 'warning',
        };
    }

    // ===== Cek role =====

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPERADMIN;
    }

    /** Admin group: superadmin + admin (Super Admin bisa buka semua menu admin). */
    public function isAdmin(): bool
    {
        return in_array($this->role, [self::ROLE_SUPERADMIN, self::ROLE_ADMIN]);
    }

    public function isFinance(): bool
    {
        return $this->role === self::ROLE_FINANCE;
    }

    public function isSales(): bool
    {
        return $this->role === self::ROLE_SALES;
    }

    public function isKaryawan(): bool
    {
        return $this->role === self::ROLE_KARYAWAN;
    }

    public function isKlien(): bool
    {
        return $this->role === self::ROLE_KLIEN;
    }

    /** Semua staf internal (bukan customer). */
    public function isStaff(): bool
    {
        return !$this->isKlien();
    }

    // ===== Permission per menu =====

    public function canManageCompanies(): bool
    {
        return $this->isSuperAdmin();
    }

    public function canManageCustomers(): bool
    {
        return $this->isAdmin() || $this->isSales();
    }

    public function canManageProducts(): bool
    {
        return $this->isAdmin();
    }

    public function canManageQuotations(): bool
    {
        return $this->isAdmin() || $this->isSales();
    }

    public function canManageOrders(): bool
    {
        return $this->isAdmin() || $this->isSales();
    }

    public function canManageInvoices(): bool
    {
        return $this->isStaff(); // semua staf internal
    }

    public function canVerifyPayments(): bool
    {
        return $this->isAdmin() || $this->isFinance();
    }

    public function canViewReports(): bool
    {
        return $this->isAdmin() || $this->isFinance();
    }

    public function canManageSettings(): bool
    {
        return $this->isAdmin();
    }

    // ===== Kompatibilitas lama =====

    public function canDelete(): bool
    {
        return $this->isAdmin();
    }

    public function canManageUsers(): bool
    {
        return $this->isAdmin();
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    // ===== Relasi =====

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function businessProfile()
    {
        return $this->hasOne(BusinessProfile::class);
    }

    public function notificationSetting()
    {
        return $this->hasOne(NotificationSetting::class);
    }

    public function messageTemplates()
    {
        return $this->hasMany(MessageTemplate::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
