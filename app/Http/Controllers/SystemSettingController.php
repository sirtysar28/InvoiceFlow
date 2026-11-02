<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class SystemSettingController extends Controller
{
    public function index()
    {
        $this->requireSuperAdmin();

        $settings = [
            'app_name' => SystemSetting::get('app_name', config('app.name')),
            'support_whatsapp' => SystemSetting::get('support_whatsapp'),
            'support_email' => SystemSetting::get('support_email'),
            'registration_open' => SystemSetting::get('registration_open', '1'),
            'default_payment_due_days' => SystemSetting::get('default_payment_due_days', '14'),
            'maintenance_mode' => SystemSetting::get('maintenance_mode', '0'),
        ];

        return view('superadmin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $this->requireSuperAdmin();

        $validated = $request->validate([
            'app_name' => 'required|string|max:100',
            'support_whatsapp' => 'nullable|string|max:20',
            'support_email' => 'nullable|email|max:255',
            'registration_open' => 'nullable|in:0,1',
            'default_payment_due_days' => 'nullable|integer|min:1|max:120',
            'maintenance_mode' => 'nullable|in:0,1',
        ]);

        foreach ($validated as $key => $value) {
            SystemSetting::set($key, $value);
        }

        ActivityLogger::log('system.update', 'Pengaturan Sistem', 'Memperbarui pengaturan sistem');

        return back()->with('success', 'Pengaturan sistem berhasil disimpan! ✅');
    }

    private function requireSuperAdmin(): void
    {
        abort_unless(auth()->user()?->canManageCompanies(), 403, 'Akses khusus Super Admin.');
    }
}
