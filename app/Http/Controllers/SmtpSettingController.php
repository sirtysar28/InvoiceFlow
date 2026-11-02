<?php

namespace App\Http\Controllers;

use App\Models\SmtpSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class SmtpSettingController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'is_active' => 'nullable|boolean',
            'mail_host' => 'required|string|max:255',
            'mail_port' => 'required|integer|min:1|max:65535',
            'mail_encryption' => 'required|in:tls,ssl,none',
            'mail_username' => 'nullable|string|max:255',
            'mail_password' => 'nullable|string|max:255',
            'mail_from_address' => 'nullable|email|max:255',
            'mail_from_name' => 'nullable|string|max:255',
        ], [
            'mail_host.required' => 'SMTP Host wajib diisi.',
            'mail_port.required' => 'Port wajib diisi.',
            'mail_from_address.email' => 'Format alamat email pengirim tidak valid.',
        ]);

        $validated['is_active'] = $request->has('is_active');

        // Jika password kosong, pertahankan password lama
        if (empty($validated['mail_password'])) {
            unset($validated['mail_password']);
        }

        SmtpSetting::updateOrCreate(SmtpSetting::ownKey(), $validated);
        SmtpSetting::applyToConfig();

        return back()->with('success', 'Pengaturan SMTP berhasil disimpan! ✅');
    }

    /**
     * Uji koneksi SMTP + kirim email tes dengan format HTML.
     */
    public function test(Request $request): RedirectResponse
    {
        $request->validate([
            'test_to' => 'required|email',
        ], [
            'test_to.required' => 'Alamat email tujuan wajib diisi.',
            'test_to.email' => 'Format email tujuan tidak valid.',
        ]);

        // Gunakan nilai dari form (tanpa harus simpan dulu), fallback ke DB
        $saved = SmtpSetting::current();
        $host = $request->input('mail_host', $saved?->mail_host);
        $port = (int) $request->input('mail_port', $saved?->mail_port ?? 587);
        $encryption = $request->input('mail_encryption', $saved?->mail_encryption ?? 'tls');
        $username = $request->input('mail_username', $saved?->mail_username);
        $password = $request->input('mail_password') ?: $saved?->mail_password;
        $fromAddress = $request->input('mail_from_address', $saved?->mail_from_address) ?: config('mail.from.address');
        $fromName = $request->input('mail_from_name', $saved?->mail_from_name) ?: config('app.name');

        if (empty($host)) {
            return back()->with('error', 'SMTP Host belum diisi. Isi dulu pengaturan SMTP di bawah.');
        }

        // Terapkan konfigurasi sementara untuk pengujian
        Config::set('mail.default', 'smtp');
        Config::set('mail.mailers.smtp', [
            'transport' => 'smtp',
            'host' => $host,
            'port' => $port,
            'encryption' => $encryption === 'none' ? null : $encryption,
            'username' => $username,
            'password' => $password,
            'timeout' => 15,
        ]);
        Config::set('mail.from', [
            'address' => $fromAddress,
            'name' => $fromName,
        ]);

        try {
            Mail::to($request->input('test_to'))->send(
                new \App\Mail\SmtpTestMail($host, $port, strtoupper((string) $encryption))
            );

            if ($saved) {
                $saved->update([
                    'last_test_status' => 'success',
                    'last_test_message' => 'Koneksi berhasil — email tes terkirim.',
                    'last_tested_at' => now(),
                ]);
            }

            return back()->with('success', "Email tes berhasil terkirim ke {$request->input('test_to')} 🎉");
        } catch (\Throwable $e) {
            if ($saved) {
                $saved->update([
                    'last_test_status' => 'failed',
                    'last_test_message' => mb_substr($e->getMessage(), 0, 250),
                    'last_tested_at' => now(),
                ]);
            }

            return back()->with('error', 'Gagal mengirim email tes: ' . mb_substr($e->getMessage(), 0, 200));
        }
    }
}
