<?php

namespace App\Providers;

use App\Models\SmtpSetting;
use App\Models\WhatsAppSetting;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Pagination: pakai markup Bootstrap 5 (bukan default Tailwind)
        // Tanpa ini, panah SVG Tailwind tampil BESAR karena tak ada CSS Tailwind.
        Paginator::useBootstrapFive();

        // Terapkan konfigurasi SMTP dari database (Pengaturan → SMTP)
        // ke runtime mailer Laravel. Aman diabaikan bila tabel belum ada.
        try {
            if (Schema::hasTable('smtp_settings')) {
                SmtpSetting::applyToConfig();
            }
        } catch (\Throwable $e) {
            // Database belum siap (mis. saat migrasi) — pakai default .env
        }

        // Terapkan konfigurasi WhatsApp API dari database (Pengaturan → WhatsApp)
        try {
            if (Schema::hasTable('whatsapp_settings')) {
                WhatsAppSetting::applyToConfig();
            }
        } catch (\Throwable $e) {
            // Database belum siap — pakai default .env
        }
    }
}
