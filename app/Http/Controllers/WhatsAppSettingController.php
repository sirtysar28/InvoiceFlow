<?php

namespace App\Http\Controllers;

use App\Models\WhatsAppSetting;
use App\Services\WhatsAppService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class WhatsAppSettingController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'is_active' => 'nullable|boolean',
            'api_url' => 'required|url|max:255',
            'api_key' => 'nullable|string|max:255',
        ], [
            'api_url.required' => 'API URL wajib diisi.',
            'api_url.url' => 'Format API URL tidak valid (harus diawali http:// atau https://).',
        ]);

        $validated['is_active'] = $request->has('is_active');

        // Jika API key kosong, pertahankan API key lama
        if (empty($validated['api_key'])) {
            unset($validated['api_key']);
        }

        WhatsAppSetting::updateOrCreate(WhatsAppSetting::ownKey(), $validated);
        WhatsAppSetting::applyToConfig();

        return back()->with('success', 'Pengaturan WhatsApp API berhasil disimpan! ✅');
    }

    /**
     * Uji kirim pesan WhatsApp tes (gunakan nilai form, fallback ke DB).
     */
    public function test(Request $request): RedirectResponse
    {
        $request->validate([
            'test_to' => 'required|string|max:20|regex:/^[0-9+\-\s]+$/',
        ], [
            'test_to.required' => 'Nomor WhatsApp tujuan wajib diisi.',
            'test_to.regex' => 'Format nomor WhatsApp tidak valid (hanya angka, +, dan -).',
        ]);

        $saved = WhatsAppSetting::current();
        $apiKey = $request->input('api_key') ?: $saved?->api_key ?: config('whatsapp.api_key');
        $apiUrl = $request->input('api_url', $saved?->api_url) ?: config('whatsapp.api_url');

        if (empty($apiKey)) {
            return back()->with('error', 'API Key belum diisi. Isi dulu pengaturan WhatsApp API di atas.');
        }

        // Terapkan konfigurasi sementara untuk pengujian
        Config::set('whatsapp.api_url', $apiUrl);
        Config::set('whatsapp.api_key', $apiKey);

        $result = app(WhatsAppService::class)->send(
            $request->input('test_to'),
            "✅ *Tes Koneksi WhatsApp*\n\nPesan tes dari *" . config('app.name') . "* berhasil terkirim! 🎉"
        );

        $target = $request->input('test_to');

        if (($result['status'] ?? '') === 'success') {
            if ($saved) {
                $saved->update([
                    'last_test_status' => 'success',
                    'last_test_message' => 'Koneksi berhasil — pesan tes terkirim.',
                    'last_tested_at' => now(),
                ]);
            }

            return back()->with('success', "Pesan tes berhasil terkirim ke {$target} 🎉");
        }

        $message = $result['message'] ?? 'Unknown error';

        if ($saved) {
            $saved->update([
                'last_test_status' => 'failed',
                'last_test_message' => mb_substr($message, 0, 250),
                'last_tested_at' => now(),
            ]);
        }

        return back()->with('error', 'Gagal mengirim pesan tes: ' . mb_substr($message, 0, 200));
    }
}
