<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MidtransSetting extends Model
{
    protected $table = 'midtrans_settings';

    protected $fillable = [
        'company_id',
        'is_active', 'is_production', 'merchant_id', 'client_key', 'server_key',
        'last_test_status', 'last_test_message', 'last_tested_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_production' => 'boolean',
        'last_tested_at' => 'datetime',
    ];

    protected $hidden = ['server_key', 'client_key'];

    /* ================= MULTI-TENANT (SaaS) ================= */

    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class);
    }

    /**
     * Resolusi per-perusahaan:
     * - User perusahaan : baris milik perusahaanNYA, fallback ke platform (company_id NULL)
     * - Super Admin/guest: baris platform
     */
    public static function current(): ?self
    {
        $cid = auth()->user()?->company_id;
        if ($cid !== null) {
            return self::where('company_id', $cid)->first()
                ?? self::whereNull('company_id')->first();
        }
        return self::whereNull('company_id')->first();
    }

    /** Kunci baris milik user yang login (updateOrCreate) — SA = platform, admin = perusahaan sendiri. */
    public static function ownKey(): array
    {
        return ['company_id' => auth()->user()?->company_id];
    }

    public static function isActive(): bool
    {
        $s = self::current();
        return (bool) ($s?->is_active && $s->server_key);
    }

    public function baseUrl(): string
    {
        return $this->is_production
            ? 'https://app.midtrans.com'
            : 'https://app.sandbox.midtrans.com';
    }

    /**
     * Base URL untuk API Core Midtrans (cek status, dsb).
     * PENTING: host-nya BERBEDA dari Snap API (api.* bukan app.*)
     */
    public function apiBaseUrl(): string
    {
        return $this->is_production
            ? 'https://api.midtrans.com'
            : 'https://api.sandbox.midtrans.com';
    }
}
