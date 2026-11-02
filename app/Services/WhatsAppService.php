<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected string $apiUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->apiUrl = config('whatsapp.api_url', 'https://api.fonnte.com/send');
        $this->apiKey = config('whatsapp.api_key', '');
    }

    public function send(string $phone, string $message): array
    {
        if (empty($this->apiKey)) {
            Log::warning('WhatsApp API key not configured.');
            return ['status' => 'error', 'message' => 'WhatsApp API key not configured'];
        }

        $phone = $this->formatPhone($phone);

        try {
            $response = Http::withHeaders([
                'Authorization' => $this->apiKey,
            ])->post($this->apiUrl, [
                'target' => $phone,
                'message' => $message,
                'countryCode' => '62',
            ]);

            $data = $response->json();

            // Fonnte mengembalikan HTTP 200 walaupun pengiriman gagal,
            // status keberhasilan sebenarnya ada di body: {"status": true/false, "reason": "..."}
            $fonnteOk = $response->successful()
                && is_array($data)
                && !empty($data['status'])
                && (string) ($data['reason'] ?? 'success') === 'success';

            if ($fonnteOk) {
                Log::info('WhatsApp notification sent successfully', [
                    'phone' => $phone,
                    'response' => $data,
                ]);
                return ['status' => 'success', 'response' => $data];
            }

            // Ambil alasan kegagalan dari body Fonnte (jika ada)
            $reason = is_array($data) && isset($data['reason'])
                ? $this->translateReason((string) $data['reason'])
                : 'HTTP ' . $response->status() . ': ' . mb_substr($response->body(), 0, 200);

            Log::error('WhatsApp API error', [
                'phone' => $phone,
                'reason' => $reason,
                'response' => $response->body(),
            ]);
            return ['status' => 'error', 'message' => $reason];

        } catch (\Exception $e) {
            Log::error('WhatsApp send exception: ' . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /** Terjemahkan alasan gagal dari Fonnte agar mudah dipahami. */
    private function translateReason(string $reason): string
    {
        $map = [
            'invalid token' => 'Token/API Key tidak valid — periksa kembali token Fonnte Anda.',
            'wrong token' => 'Token/API Key salah — periksa kembali token Fonnte Anda.',
            'token not found' => 'Token tidak ditemukan — periksa kembali token Fonnte Anda.',
            'device not connected' => 'Device Fonnte terputus — buka app.fonnte.com lalu scan ulang QR agar statusnya connected.',
            'disconnected' => 'Device Fonnte terputus — buka app.fonnte.com lalu scan ulang QR agar statusnya connected.',
            'connect first' => 'Device Fonnte belum terhubung — scan QR dulu di app.fonnte.com.',
            'no destination' => 'Nomor tujuan tidak terdaftar di WhatsApp — pastikan nomor benar & aktif di WhatsApp.',
            'destination not found' => 'Nomor tujuan tidak terdaftar di WhatsApp — pastikan nomor benar & aktif di WhatsApp.',
            'no message' => 'Pesan kosong.',
            'insufficient credit' => 'Kredit Fonnte tidak cukup.',
        ];

        $lower = strtolower(trim($reason));

        foreach ($map as $key => $pesan) {
            if (str_contains($lower, $key)) {
                return $pesan;
            }
        }

        return 'Fonnte: ' . $reason;
    }

    private function formatPhone(string $phone): string
    {
        // Remove non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // If starts with '0', replace with '62'
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        // If starts with '8', add '62'
        if (str_starts_with($phone, '8')) {
            $phone = '62' . $phone;
        }

        return $phone;
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Cek status device di Fonnte (GET /device).
     * Berguna untuk diagnosis: device harus "connected" agar pesan bisa terkirim.
     */
    public function deviceStatus(): array
    {
        if (empty($this->apiKey)) {
            return ['status' => 'error', 'message' => 'API key belum dikonfigurasi'];
        }

        try {
            $response = Http::withHeaders(['Authorization' => $this->apiKey])
                ->get('https://api.fonnte.com/device');
            $data = $response->json();

            if (is_array($data) && !empty($data['status'])) {
                $device = $data['device_status'] ?? 'unknown';
                return [
                    'status' => 'success',
                    'device_status' => $device,
                    'message' => $device === 'connected'
                        ? 'Device Fonnte terhubung (connected).'
                        : "Device Fonnte status: {$device} — pesan tidak akan terkirim. Scan ulang QR di app.fonnte.com.",
                ];
            }

            return [
                'status' => 'error',
                'message' => is_array($data) && isset($data['reason'])
                    ? $this->translateReason((string) $data['reason'])
                    : 'HTTP ' . $response->status(),
            ];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
