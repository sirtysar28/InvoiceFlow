<?php

namespace App\Http\Controllers;

use App\Models\NotificationSetting;
use App\Models\SmtpSetting;
use App\Models\WhatsAppSetting;
use App\Models\MidtransSetting;
use Illuminate\Http\Request;

class NotificationSettingController extends Controller
{
    public function index()
    {
        $settings = NotificationSetting::where('user_id', auth()->id())->first();
        $smtp = SmtpSetting::current();
        $whatsapp = WhatsAppSetting::current();
        $midtrans = MidtransSetting::current();

        return view('notifications.settings', compact('settings', 'smtp', 'whatsapp', 'midtrans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'wa_enabled' => 'nullable|boolean',
            'email_enabled' => 'nullable|boolean',
            'wa_number' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'days_before_due' => 'nullable|array',
            'days_before_due.*' => 'integer',
            'send_on_due_day' => 'nullable|boolean',
            'send_at_hour' => 'nullable|integer|min:0|max:23',
        ]);

        $validated['wa_enabled'] = $request->has('wa_enabled');
        $validated['email_enabled'] = $request->has('email_enabled');
        $validated['send_on_due_day'] = $request->has('send_on_due_day');
        $validated['user_id'] = auth()->id();

        NotificationSetting::updateOrCreate(
            ['user_id' => auth()->id()],
            $validated
        );

        return back()->with('success', 'Pengaturan notifikasi berhasil disimpan! ✅');
    }
}
