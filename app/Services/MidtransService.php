<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\MidtransSetting;
use App\Models\Payment;
use App\Services\ActivityLogger;
use GuzzleHttp\Client;
use Illuminate\Support\Str;

class MidtransService
{
    public static function setting(): ?MidtransSetting
    {
        return MidtransSetting::current();
    }

    /**
     * Setting Midtrans milik PERUSAHAAN pemilik payment (via invoice → staff).
     * Dipakai webhook/sync: user tidak login, jadi resolusinya dari data payment.
     */
    public static function settingForPayment(Payment $payment): ?MidtransSetting
    {
        $cid = $payment->invoice?->user?->company_id;

        return $cid
            ? (MidtransSetting::where('company_id', $cid)->first()
                ?? MidtransSetting::whereNull('company_id')->first())
            : MidtransSetting::whereNull('company_id')->first();
    }

    public static function enabled(): bool
    {
        return MidtransSetting::isActive();
    }

    /**
     * Buat transaksi Snap & URL pembayaran untuk sebuah invoice.
     * Return: [payment, redirectUrl] atau throw.
     */
    public static function createSnapTransaction(Invoice $invoice): array
    {
        $setting = self::setting();
        if (!$setting || !$setting->is_active || !$setting->server_key) {
            throw new \Exception('Payment gateway Midtrans belum aktif. Silakan hubungi penjual invoice.');
        }

        // Cegah transaksi ganda: pakai payment pending yang masih valid
        $existing = Payment::where('invoice_id', $invoice->id)
            ->where('method', 'midtrans')
            ->where('status', 'pending')
            ->whereNotNull('snap_url')
            ->latest()
            ->first();

        if ($existing) {
            return [$existing, $existing->snap_url];
        }

        $orderId = 'INVFLOW-' . $invoice->id . '-' . strtoupper(Str::random(8));

        $payment = Payment::create([
            'invoice_id' => $invoice->id,
            'user_id'    => $invoice->user_id,
            'method'     => 'midtrans',
            'amount'     => $invoice->total,
            'status'     => 'pending',
            'order_id'   => $orderId,
        ]);

        $itemDetails = $invoice->items->map(fn ($item) => [
            'id'       => 'ITEM-' . $item->id,
            'price'    => (int) round($item->unit_price),
            'quantity' => (int) $item->quantity,
            'name'     => Str::limit($item->description ?: 'Item', 50),
        ])->values()->all();

        // Penyesuaian (pajak/diskon) sebagai item tambahan agar total cocok
        if ($invoice->discount_amount > 0) {
            $itemDetails[] = [
                'id' => 'DISCOUNT', 'price' => -(int) round($invoice->discount_amount),
                'quantity' => 1, 'name' => 'Diskon',
            ];
        }

        $body = [
            'transaction_details' => [
                'order_id'     => $orderId,
                'gross_amount' => (int) round($invoice->total),
            ],
            'item_details'        => $itemDetails,
            'customer_details'    => [
                'first_name' => $invoice->client_name,
                'email'      => $invoice->client_email,
                'phone'      => $invoice->client_phone,
            ],
            'metadata' => ['invoice_id' => $invoice->id],
            // Setelah selesai bayar di Snap, browser kembali ke halaman invoice
            // → publicView otomatis cek status → invoice langsung LUNAS.
            'callbacks' => ['finish' => route('invoices.public', $invoice->id)],
        ];

        $client = new Client(['timeout' => 30]);

        $response = $client->post($setting->baseUrl() . '/snap/v1/transactions', [
            'headers' => [
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json',
                'Authorization' => 'Basic ' . base64_encode($setting->server_key . ':'),
            ],
            'json' => $body,
        ]);

        $result = json_decode((string) $response->getBody(), true);
        $redirectUrl = $result['redirect_url'] ?? null;

        if (!$redirectUrl) {
            $payment->update(['status' => 'failed', 'payload' => $result]);
            throw new \Exception('Midtrans tidak mengembalikan URL pembayaran.');
        }

        $payment->update([
            'snap_url' => $redirectUrl,
            'payload'  => $result,
        ]);

        return [$payment, $redirectUrl];
    }

    /**
     * Verifikasi signature_key notifikasi Midtrans:
     * sha512(order_id + status_code + gross_amount + serverKey)
     */
    public static function verifySignature(array $data, ?MidtransSetting $setting = null): bool
    {
        $setting ??= self::setting();
        if (!$setting || !$setting->server_key) {
            return false;
        }

        $expected = hash('sha512',
            ($data['order_id'] ?? '') .
            ($data['status_code'] ?? '') .
            ($data['gross_amount'] ?? '') .
            $setting->server_key
        );

        return hash_equals($expected, (string) ($data['signature_key'] ?? ''));
    }

    /**
     * Mapping status Midtrans → status payment internal.
     */
    public static function mapStatus(string $transactionStatus, ?string $fraudStatus = null): string
    {
        if ($fraudStatus === 'challenge') {
            return 'pending';
        }

        return match ($transactionStatus) {
            'capture', 'settlement' => 'success',
            'pending'               => 'pending',
            'deny', 'cancel', 'expire', 'failure' => 'failed',
            default => 'pending',
        };
    }

    /** Pesan error terakhir dari checkStatus (untuk diagnosis). */
    public static ?string $lastStatusError = null;

    /**
     * Cek status transaksi LANGSUNG ke API Midtrans (GET /v2/{order_id}/status).
     * Dipakai sebagai lapisan cadangan bila webhook terlambat/belum terpasang.
     * Return: array response Midtrans atau null jika gagal.
     */
    public static function checkStatus(Payment $payment, int $timeout = 15): ?array
    {
        self::$lastStatusError = null;

        $setting = self::settingForPayment($payment);
        if (!$setting || !$setting->is_active || !$setting->server_key || !$payment->order_id) {
            self::$lastStatusError = 'Pengaturan Midtrans tidak aktif / server key kosong / order_id kosong';
            return null;
        }

        try {
            $client = new Client(['timeout' => $timeout]);
            $response = $client->get(
                // PENTING: Status API ada di host api.* — BUKAN app.* (Snap)
                $setting->apiBaseUrl() . '/v2/' . $payment->order_id . '/status',
                [
                    'headers' => [
                        'Accept'        => 'application/json',
                        'Authorization' => 'Basic ' . base64_encode($setting->server_key . ':'),
                    ],
                ]
            );

            return json_decode((string) $response->getBody(), true);
        } catch (\Throwable $e) {
            self::$lastStatusError = $e->getMessage();
            \Illuminate\Support\Facades\Log::warning('Midtrans status check gagal [' . $payment->order_id . ']: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Terapkan status Midtrans ke payment + invoice.
     * Dipakai BERSAMA oleh webhook & cek status manual:
     * settlement/capture → payment success → invoice OTOMATIS LUNAS (tanpa verifikasi admin).
     */
    public static function applyStatus(Payment $payment, array $data): string
    {
        $status = self::mapStatus(
            $data['transaction_status'] ?? 'pending',
            $data['fraud_status'] ?? null
        );

        if ($status !== $payment->status || $data['transaction_status'] ?? null) {
            $payment->update([
                'status'         => $status,
                'transaction_id' => $data['transaction_id'] ?? $payment->transaction_id,
                'payment_type'   => $data['payment_type'] ?? $payment->payment_type,
                'paid_at'        => $status === 'success' ? ($payment->paid_at ?? now()) : $payment->paid_at,
                'payload'        => $data + (array) $payment->payload,
            ]);
        }

        if ($status === 'success' && !$payment->invoice->is_paid) {
            $payment->invoice->markAsPaid();
            ActivityLogger::log('payment.midtrans-success', 'Pembayaran',
                'Pembayaran Midtrans BERHASIL (otomatis) untuk ' . $payment->invoice->invoice_number .
                ' (' . ($data['payment_type'] ?? '-') . ', Rp ' . number_format($payment->amount, 0, ',', '.') . ')');
        }

        return $status;
    }
}
