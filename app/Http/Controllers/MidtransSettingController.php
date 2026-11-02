<?php

namespace App\Http\Controllers;

use App\Models\MidtransSetting;
use App\Services\ActivityLogger;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class MidtransSettingController extends Controller
{
    public function update(Request $request)
    {
        $validated = $request->validate([
            'is_active'     => 'nullable|boolean',
            'is_production' => 'nullable|boolean',
            'merchant_id'   => 'nullable|string|max:100',
            'client_key'    => 'nullable|string|max:200',
            'server_key'    => 'nullable|string|max:200',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_production'] = $request->boolean('is_production');

        MidtransSetting::updateOrCreate(MidtransSetting::ownKey(), $validated);

        ActivityLogger::log('midtrans.update', 'Pengaturan', 'Memperbarui pengaturan payment gateway Midtrans');

        return back()->with('success', 'Pengaturan Midtrans berhasil disimpan! ✅');
    }

    /** Tes koneksi ke Midtrans (cek merchant & server key valid). */
    public function test(Request $request)
    {
        $request->validate([
            'merchant_id' => 'nullable|string|max:100',
            'server_key'  => 'nullable|string|max:200',
        ]);

        $setting = MidtransSetting::current();
        $merchantId = $request->input('merchant_id') ?: $setting?->merchant_id;
        $serverKey  = $request->input('server_key') ?: $setting?->server_key;
        $isProduction = $setting?->is_production ?? false;

        if (!$merchantId || !$serverKey) {
            return back()->with('error', 'Merchant ID dan Server Key wajib diisi untuk tes koneksi.');
        }

        $base = $isProduction ? 'https://api.midtrans.com' : 'https://api.sandbox.midtrans.com';

        try {
            $client = new Client(['timeout' => 15, 'http_errors' => false]);

            // Endpoint ringan untuk validasi kredensial (snap transactions list dibatasi)
            $response = $client->get($base . '/v2/' . $merchantId . '/status', [
                'headers' => [
                    'Accept'        => 'application/json',
                    'Authorization' => 'Basic ' . base64_encode($serverKey . ':'),
                ],
            ]);

            $code = $response->getStatusCode();
            // 404 = merchant ada tapi order tidak ditemukan → kredensial valid
            // 401/403 = kredensial salah
            $ok = in_array($code, [200, 404]);
            $message = $ok
                ? 'Koneksi ke Midtrans BERHASIL — kredensial dikenali. (HTTP ' . $code . ')'
                : 'Kredensial ditolak Midtrans (HTTP ' . $code . '). Cek Server Key & Merchant ID.';

            if ($setting) {
                $setting->update([
                    'last_test_status'  => $ok ? 'success' : 'failed',
                    'last_test_message' => $message,
                    'last_tested_at'    => now(),
                ]);
            }

            return back()->with($ok ? 'success' : 'error', $message);
        } catch (\Throwable $e) {
            $message = 'Gagal terhubung ke Midtrans: ' . $e->getMessage();
            if ($setting) {
                $setting->update([
                    'last_test_status'  => 'failed',
                    'last_test_message' => $message,
                    'last_tested_at'    => now(),
                ]);
            }
            return back()->with('error', $message);
        }
    }
}
