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

            if ($response->successful()) {
                Log::info('WhatsApp notification sent successfully', [
                    'phone' => $phone,
                    'response' => $response->json(),
                ]);
                return ['status' => 'success', 'response' => $response->json()];
            }

            Log::error('WhatsApp API error', [
                'phone' => $phone,
                'response' => $response->body(),
            ]);
            return ['status' => 'error', 'message' => $response->body()];

        } catch (\Exception $e) {
            Log::error('WhatsApp send exception: ' . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
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
}
