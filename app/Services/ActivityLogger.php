<?php

namespace App\Services;

use App\Models\ActivityLog;

class ActivityLogger
{
    /**
     * Catat aktivitas user.
     *
     * Contoh: ActivityLogger::log('invoice.create', 'Invoice', 'Membuat invoice INV/2026/001');
     */
    public static function log(string $action, ?string $module = null, ?string $description = null): void
    {
        try {
            ActivityLog::create([
                'user_id'     => auth()->id(),
                'action'      => $action,
                'module'      => $module,
                'description' => $description,
                'ip_address'  => request()?->ip(),
                'user_agent'  => substr((string) request()?->userAgent(), 0, 500) ?: null,
            ]);
        } catch (\Throwable $e) {
            // Logging tidak boleh mematahkan proses utama aplikasi
            \Log::warning('Gagal mencatat activity log: ' . $e->getMessage());
        }
    }
}
