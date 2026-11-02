<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class SmtpSetting extends Model
{
    protected $fillable = [
        'company_id',
        'is_active',
        'mail_host',
        'mail_port',
        'mail_encryption',
        'mail_username',
        'mail_password',
        'mail_from_address',
        'mail_from_name',
        'last_test_status',
        'last_test_message',
        'last_tested_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_tested_at' => 'datetime',
    ];

    protected $hidden = ['mail_password'];

    /**
     * Baris konfigurasi SMTP tunggal (singleton).
     */
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

    /**
     * Terapkan konfigurasi SMTP dari database ke runtime Laravel Mail.
     */
    public static function applyToConfig(): void
    {
        $setting = static::current();

        if (!$setting || !$setting->is_active || !$setting->mail_host) {
            return;
        }

        Config::set('mail.default', 'smtp');
        Config::set('mail.mailers.smtp', [
            'transport' => 'smtp',
            'host' => $setting->mail_host,
            'port' => $setting->mail_port,
            'encryption' => $setting->mail_encryption === 'none' ? null : $setting->mail_encryption,
            'username' => $setting->mail_username,
            'password' => $setting->mail_password,
            'timeout' => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN', parse_url(env('APP_URL', 'http://localhost'), PHP_URL_HOST)),
        ]);

        if ($setting->mail_from_address) {
            Config::set('mail.from', [
                'address' => $setting->mail_from_address,
                'name' => $setting->mail_from_name ?: config('app.name'),
            ]);
        }
    }
}
