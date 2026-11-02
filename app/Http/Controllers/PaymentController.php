<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\MidtransSetting;
use App\Services\ActivityLogger;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    /**
     * Klien memilih bayar via Midtrans → redirect ke Snap.
     * Route publik (invoice publik).
     */
    public function payWithMidtrans(Request $request, $invoiceId)
    {
        $invoice = Invoice::with('items')->findOrFail($invoiceId);

        if ($invoice->is_paid) {
            return redirect()->route('invoices.public', $invoice->id)
                ->with('error', 'Invoice ini sudah lunas. Terima kasih!');
        }

        try {
            [$payment, $redirectUrl] = MidtransService::createSnapTransaction($invoice);

            ActivityLogger::log('payment.midtrans-init', 'Pembayaran',
                'Klien memulai pembayaran Midtrans untuk ' . $invoice->invoice_number . ' (Order: ' . $payment->order_id . ')');

            return redirect()->away($redirectUrl);
        } catch (\Throwable $e) {
            return redirect()->route('invoices.public', $invoice->id)
                ->with('error', 'Gagal membuat transaksi: ' . $e->getMessage());
        }
    }

    /**
     * Form konfirmasi pembayaran manual (transfer bank).
     */
    public function manualForm($invoiceId)
    {
        $invoice = Invoice::findOrFail($invoiceId);

        if ($invoice->is_paid) {
            return redirect()->route('invoices.public', $invoice->id);
        }

        $pending = Payment::where('invoice_id', $invoice->id)
            ->where('status', 'pending')->latest()->first();

        return view('payments.manual-form', compact('invoice', 'pending'));
    }

    /**
     * Simpan konfirmasi manual → menunggu verifikasi admin.
     */
    public function submitManual(Request $request, $invoiceId)
    {
        $invoice = Invoice::findOrFail($invoiceId);

        if ($invoice->is_paid) {
            return redirect()->route('invoices.public', $invoice->id);
        }

        $validated = $request->validate([
            'bank_name'    => 'required|string|max:100',
            'sender_name'  => 'required|string|max:150',
            'transfer_date'=> 'required|date',
            'amount'       => 'required|numeric|min:0',
            'proof'        => 'nullable|image|mimes:jpg,jpeg,png,pdf|max:3072',
            'notes'        => 'nullable|string|max:1000',
        ], [
            'bank_name.required'     => 'Nama bank pengirim wajib diisi.',
            'sender_name.required'   => 'Nama pengirim wajib diisi.',
            'transfer_date.required' => 'Tanggal transfer wajib diisi.',
            'amount.required'        => 'Nominal transfer wajib diisi.',
        ]);

        // Cegah spam konfirmasi: 1 pending manual aktif
        $exists = Payment::where('invoice_id', $invoice->id)
            ->where('method', 'manual')->where('status', 'pending')->exists();
        if ($exists) {
            return redirect()->route('invoices.public', $invoice->id)
                ->with('error', 'Konfirmasi pembayaran Anda sedang diverifikasi admin.');
        }

        $proofPath = null;
        if ($request->hasFile('proof')) {
            $proofPath = $request->file('proof')->store('payment-proofs', 'public');
        }

        Payment::create([
            'invoice_id'   => $invoice->id,
            'user_id'      => $invoice->user_id,
            'method'       => 'manual',
            'amount'       => $validated['amount'],
            'status'       => 'pending',
            'bank_name'    => $validated['bank_name'],
            'sender_name'  => $validated['sender_name'],
            'transfer_date'=> $validated['transfer_date'],
            'proof_path'   => $proofPath,
            'notes'        => $validated['notes'] ?? null,
        ]);

        ActivityLogger::log('payment.manual-submit', 'Pembayaran',
            'Klien mengirim konfirmasi manual untuk ' . $invoice->invoice_number);

        return redirect()->route('invoices.public', $invoice->id)
            ->with('success', 'Konfirmasi pembayaran terkirim! Menunggu verifikasi admin.');
    }

    /**
     * Webhook notifikasi Midtrans (tanpa auth & CSRF).
     * Settlement/capture → invoice OTOMATIS LUNAS, tanpa verifikasi admin.
     */
    public function midtransNotification(Request $request)
    {
        $data = $request->all();

        $payment = Payment::where('order_id', $data['order_id'] ?? '')->first();
        if (!$payment) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        // Verifikasi pakai SERVER KEY milik perusahaan pemilik invoice (SaaS multi-tenant)
        if (!MidtransService::verifySignature($data, MidtransService::settingForPayment($payment))) {
            ActivityLogger::log('payment.webhook-invalid', 'Pembayaran', 'Signature Midtrans tidak valid: ' . ($data['order_id'] ?? '-'));
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        MidtransService::applyStatus($payment, $data);

        return response()->json(['message' => 'OK']);
    }

    /* ================= ADMIN ================= */

    /** Daftar konfirmasi pembayaran (admin & finance). */
    public function adminIndex(Request $request)
    {
        $this->requireVerifier();

        // ===== AUTO-SYNC Midtrans =====
        // Tiap halaman ini dibuka, payment Midtrans yang pending otomatis dicek
        // langsung ke server Midtrans. Jika settlement → invoice OTOMATIS LUNAS.
        // (Lapisan ke-3 selain webhook & auto-sync di halaman invoice publik)
        $syncedCount = 0;
        $lunasCount = 0;
        if (MidtransService::enabled()) {
            Payment::where('status', 'pending')
                ->where('method', 'midtrans')
                ->whereNotNull('order_id')
                ->orderBy('id')
                ->limit(5)
                ->get()
                ->each(function ($p) use (&$syncedCount, &$lunasCount) {
                    $data = MidtransService::checkStatus($p, 8);
                    if (!$data) {
                        return; // lewati jika Midtrans tak terjangkau
                    }

                    $before = $p->status;
                    $status = MidtransService::applyStatus($p, $data);

                    if ($status !== $before) {
                        $syncedCount++;
                        if ($status === 'success') {
                            $lunasCount++;
                            ActivityLogger::log('payment.midtrans-sync', 'Pembayaran',
                                'Auto-sync: pembayaran Midtrans BERHASIL untuk ' . optional($p->invoice)->invoice_number .
                                ' (' . $p->order_id . ') → invoice LUNAS');
                        }
                    }
                });
        }

        if ($syncedCount > 0) {
            session()->flash('success',
                "🔄 {$syncedCount} pembayaran Midtrans disinkronkan otomatis" .
                ($lunasCount > 0 ? " — {$lunasCount} invoice menjadi LUNAS ✅" : ''));
        }

        $query = Payment::with(['invoice', 'confirmer'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('method')) {
            $query->where('method', $request->method);
        }
        if ($request->filled('q')) {
            $q = $request->q;
            $query->whereHas('invoice', function ($inv) use ($q) {
                $inv->where('invoice_number', 'like', "%{$q}%")
                    ->orWhere('client_name', 'like', "%{$q}%");
            });
        }

        $payments = $query->paginate(20)->withQueryString();

        return view('payments.index', compact('payments'));
    }

    /** Setujui konfirmasi manual → invoice lunas. */
    public function approve(Request $request, Payment $payment)
    {
        $this->requireVerifier();

        if ($payment->status !== 'pending') {
            return back()->with('error', 'Konfirmasi ini sudah diproses.');
        }

        $payment->update([
            'status'      => 'success',
            'paid_at'     => now(),
            'confirmed_by'=> auth()->id(),
            'confirmed_at'=> now(),
            'admin_note'  => $request->input('admin_note'),
        ]);

        if (!$payment->invoice->is_paid) {
            $payment->invoice->markAsPaid();
        }

        ActivityLogger::log('payment.approve', 'Pembayaran',
            'Menyetujui pembayaran manual untuk ' . $payment->invoice->invoice_number);

        return back()->with('success', 'Pembayaran disetujui & invoice ditandai lunas! ✅');
    }

    /**
     * Sinkron manual: cek status transaksi LANGSUNG ke API Midtrans.
     * Untuk payment Midtrans yang nyangkut pending (webhook belum terpasang/telat).
     * Jika settlement → payment success + invoice OTOMATIS LUNAS.
     */
    public function syncMidtrans(Payment $payment)
    {
        $this->requireVerifier();

        if ($payment->method !== 'midtrans') {
            return back()->with('error', 'Sinkron hanya untuk pembayaran Midtrans.');
        }

        if ($payment->status !== 'pending') {
            return back()->with('error', 'Pembayaran ini sudah diproses (' . $payment->status_label . ').');
        }

        $data = MidtransService::checkStatus($payment);

        if (!$data) {
            $detail = MidtransService::$lastStatusError
                ? ' Detail: ' . \Illuminate\Support\Str::limit(MidtransService::$lastStatusError, 160)
                : '';

            // Petunjuk spesifik dari kode HTTP
            if (str_contains(MidtransService::$lastStatusError ?? '', '401')) {
                $detail .= ' — Server Key salah/beda dengan transaksi ini. Cek Pengaturan Midtrans.';
            } elseif (str_contains(MidtransService::$lastStatusError ?? '', '404')) {
                $detail .= ' — Transaksi tidak ditemukan. Kemungkinan mode Sandbox/Production di pengaturan BERBEDA dengan saat transaksi dibuat.';
            }

            return back()->with('error',
                'Tidak bisa menghubungi Midtrans. Cek: kunci server di Pengaturan Midtrans, koneksi server, atau order_id transaksi.' . $detail);
        }

        $status = MidtransService::applyStatus($payment, $data);
        $payment->refresh();

        ActivityLogger::log('payment.midtrans-sync', 'Pembayaran',
            'Sinkron manual status Midtrans untuk ' . optional($payment->invoice)->invoice_number .
            ' (' . $payment->order_id . ') → ' . $payment->status_label);

        if ($payment->status === 'success') {
            return back()->with('success',
                '✅ Terkonfirmasi Lunas via Midtrans (' . ($data['payment_type'] ?? '-') . '). Invoice ' .
                $payment->invoice->invoice_number . ' otomatis berstatus LUNAS!');
        }

        if ($payment->status === 'failed') {
            return back()->with('error',
                'Midtrans melaporkan transaksi ' . ($data['transaction_status'] ?? 'gagal') . ' — pembayaran tidak berhasil.');
        }

        return back()->with('error',
            'Midtrans masih melaporkan "' . ($data['transaction_status'] ?? 'pending') . '" untuk transaksi ini. ' .
            'Jika klien yakin sudah bayar (VA/QRIS bisa muncul delayed), coba lagi beberapa menit lagi.');
    }

    /** Tolak konfirmasi manual. */
    public function reject(Request $request, Payment $payment)
    {
        $this->requireVerifier();

        if ($payment->status !== 'pending') {
            return back()->with('error', 'Konfirmasi ini sudah diproses.');
        }

        $validated = $request->validate(['admin_note' => 'required|string|max:500'], [
            'admin_note.required' => 'Alasan penolakan wajib diisi.',
        ]);

        $payment->update([
            'status'      => 'rejected',
            'confirmed_by'=> auth()->id(),
            'confirmed_at'=> now(),
            'admin_note'  => $validated['admin_note'],
        ]);

        ActivityLogger::log('payment.reject', 'Pembayaran',
            'Menolak pembayaran manual untuk ' . $payment->invoice->invoice_number . ' — ' . $validated['admin_note']);

        return back()->with('success', 'Konfirmasi pembayaran ditolak.');
    }

    private function requireVerifier(): void
    {
        abort_unless(auth()->user()?->canVerifyPayments(), 403, 'Akses khusus Admin & Finance.');
    }
}
