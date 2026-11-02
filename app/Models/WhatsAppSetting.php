<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class WhatsAppSetting extends Model
{
    protected $table = 'whatsapp_settings';

    protected $fillable = [
        'company_id',
        'is_active',
        'api_url',
        'api_key',
        'last_test_status',
        'last_test_message',
        'last_tested_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_tested_at' => 'datetime',
    ];

    protected $hidden = ['api_key'];

    /**
     * Baris konfigurasi WhatsApp gateway tunggal (singleton).
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

    /** Nilai bawaan dari config/.env, disimpan saat pemanggilan pertama. */
    protected static ?array $defaults = null;

    /**
     * Terapkan konfigurasi WhatsApp dari database ke runtime Laravel.
     * Jika nonaktif / kosong, kembalikan ke nilai bawaan file .env.
     */
    public static function applyToConfig(): void
    {
        if (static::$defaults === null) {
            static::$defaults = [
                'api_url' => config('whatsapp.api_url', 'https://api.fonnte.com/send'),
                'api_key' => config('whatsapp.api_key', ''),
            ];
        }

        $setting = static::current();

        if (!$setting || !$setting->is_active || !$setting->api_key) {
            Config::set('whatsapp.api_url', static::$defaults['api_url']);
            Config::set('whatsapp.api_key', static::$defaults['api_key']);
            return;
        }

        Config::set('whatsapp.api_url', $setting->api_url ?: static::$defaults['api_url']);
        Config::set('whatsapp.api_key', $setting->api_key);
    }
}
