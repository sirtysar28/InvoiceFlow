<!DOCTYPE html>
<html lang="id">
@php
    $pubProfile = \App\Models\BusinessProfile::whereNotNull('logo')->orderBy('id')->first()
        ?: \App\Models\BusinessProfile::orderBy('id')->first();
    $pubLogo = $pubProfile?->logo;
    $pubName = $pubProfile?->business_name ?: config('app.name');
@endphp
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    @if($pubLogo)
        <link rel="icon" type="image/png" href="{{ asset('storage/' . $pubLogo) }}">
    @else
        <link rel="icon" type="image/svg+xml" href="{{ asset('img/favicon.svg') }}">
    @endif
    {{-- ===== PWA ===== --}}
    <link rel="manifest" href="{{ route('pwa.manifest') }}">
    <meta name="theme-color" content="#6366f1">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="{{ $pubName }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f1f5f9; padding: 40px; }
        .invoice-card {
            background: #fff;
            border-radius: 16px;
            padding: 40px;
            max-width: 800px;
            margin: 0 auto;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        .invoice-header { border-bottom: 2px solid #4f46e5; padding-bottom: 20px; margin-bottom: 30px; }
        .invoice-header h2 { color: #4f46e5; font-weight: 800; }
        .badge-status { font-size: 0.9rem; padding: 8px 16px; border-radius: 20px; }
        .payment-section .pay-pending {
            display: flex; gap: 14px; align-items: flex-start;
            background: #fffbeb; border: 1px solid #fde68a; color: #92400e;
            border-radius: 14px; padding: 16px 18px;
        }
        .payment-section .pay-pending i { font-size: 1.5rem; }
        .pay-option {
            display: flex; align-items: center; gap: 14px;
            border: 1.5px solid #e2e8f0; border-radius: 16px; padding: 16px;
            text-decoration: none; color: inherit; background: #fff;
            transition: all .25s ease; height: 100%;
        }
        .pay-option:hover { border-color: #818cf8; box-shadow: 0 10px 26px -10px rgba(79,70,229,.35); transform: translateY(-2px); color: inherit; }
        .po-icon { width: 46px; height: 46px; border-radius: 13px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.2rem; }
        .po-midtrans { background: linear-gradient(135deg, #0ea5e9, #38bdf8); }
        .po-manual { background: linear-gradient(135deg, #f59e0b, #fbbf24); }
        .po-title { font-weight: 700; }
        .po-arrow { color: #cbd5e1; transition: transform .2s; }
        .pay-option:hover .po-arrow { transform: translateX(4px); color: #4f46e5; }
        .info-section { background: #f8fafc; border-radius: 12px; padding: 20px; margin-bottom: 20px; }
        .total-row { font-size: 1.3rem; font-weight: 700; color: #4f46e5; }
        .table td { overflow-wrap: anywhere; }
        @media (max-width: 768px) {
            body { padding: 16px; }
            .invoice-card { padding: 20px; }
        }
        @media (max-width: 575.98px) {
            .invoice-card {
                padding: 16px;
                border-radius: 12px;
            }
            .invoice-header {
                margin-bottom: 20px;
            }
            .invoice-header h2 {
                font-size: 1.35rem;
            }
            .info-section {
                padding: 14px;
            }
            .total-row {
                font-size: 1.05rem;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-card">
        <div class="invoice-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                @if($businessProfile)
                    <h2>{{ $businessProfile->business_name }}</h2>
                    @if($businessProfile->address)
                        <p class="text-muted mb-0">{!! nl2br(e($businessProfile->address)) !!}</p>
                    @endif
                @else
                    <h2>Invoice</h2>
                @endif
            </div>
            <span class="badge badge-status bg-{{ $invoice->status_color }}">
                {{ $invoice->status_label }}
            </span>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <small class="text-muted">No. Invoice</small>
                <h5 class="fw-bold">{{ $invoice->invoice_number }}</h5>
                <small class="text-muted">Tanggal</small>
                <h6>{{ $invoice->created_at->format('d M Y') }}</h6>
            </div>
            <div class="col-md-6 text-md-end">
                <small class="text-muted">Jatuh Tempo</small>
                <h5 class="fw-bold {{ $invoice->isOverdue() ? 'text-danger' : '' }}">
                    {{ $invoice->due_date->format('d M Y') }}
                </h5>
            </div>
        </div>

        <div class="info-section">
            <div class="row">
                <div class="col-md-6">
                    <strong>Klien:</strong><br>
                    {{ $invoice->client_name }}<br>
                    {{ $invoice->client_email }}
                    @if($invoice->client_phone)
                        <br>{{ $invoice->client_phone }}
                    @endif
                </div>
                <div class="col-md-6 text-md-end">
                    <strong>Dari:</strong><br>
                    {{ $businessProfile?->business_name ?? '-' }}<br>
                    {{ $businessProfile?->email ?? '' }}
                    @if($businessProfile?->phone)
                        <br>{{ $businessProfile->phone }}
                    @endif
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Deskripsi</th>
                        <th class="text-center">Jumlah</th>
                        <th class="text-end">Harga Satuan</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->items as $item)
                    <tr>
                        <td>{{ $item->description }}</td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-end">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($item->amount, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <td colspan="3" class="text-end">Subtotal</td>
                        <td class="text-end">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @if($invoice->tax > 0)
                    <tr>
                        <td colspan="3" class="text-end">Pajak ({{ $invoice->tax }}%)</td>
                        <td class="text-end">Rp {{ number_format($invoice->tax_amount, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    @if($invoice->discount > 0)
                    <tr>
                        <td colspan="3" class="text-end">Diskon ({{ $invoice->discount }}%)</td>
                        <td class="text-end text-danger">-Rp {{ number_format($invoice->discount_amount, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td colspan="3" class="text-end total-row">TOTAL</td>
                        <td class="text-end total-row">Rp {{ number_format($invoice->total, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        @if($invoice->notes)
        <div class="alert alert-warning">
            <strong>Catatan:</strong> {{ $invoice->notes }}
        </div>
        @endif

        @if($businessProfile && $businessProfile->bank_account_number)
        <div class="info-section mt-3">
            <h6 class="fw-bold mb-2">Informasi Pembayaran</h6>
            <p class="mb-1"><strong>Bank:</strong> {{ $businessProfile->bank_name }}</p>
            <p class="mb-1"><strong>No. Rekening:</strong> {{ $businessProfile->bank_account_number }}</p>
            <p class="mb-0"><strong>A.n:</strong> {{ $businessProfile->bank_account_name }}</p>
        </div>
        @endif

        @if(!$invoice->is_paid)
        <!-- ===== Pilihan Metode Pembayaran ===== -->
        <div class="payment-section mt-4">
            <h6 class="fw-bold mb-3"><i class="bi bi-credit-card-2-front"></i> Bayar Sekarang</h6>

            @if($pendingPayment)
                <div class="pay-pending">
                    @if($pendingPayment->method == 'manual')
                        <i class="bi bi-hourglass-split"></i>
                        <div>
                            <strong>Konfirmasi pembayaran Anda sedang diverifikasi admin.</strong><br>
                            <small>Dikirim {{ $pendingPayment->created_at->format('d/m/Y H:i') }}
                                · {{ $pendingPayment->bank_name }} · Rp {{ number_format($pendingPayment->amount, 0, ',', '.') }}
                            </small>
                        </div>
                    @else
                        <i class="bi bi-credit-card"></i>
                        <div>
                            <strong>Menunggu pembayaran via Midtrans.</strong><br>
                            <small>Setelah pembayaran berhasil, invoice <b>otomatis berstatus Lunas</b> — tanpa verifikasi admin.</small>
                            <div class="mt-2 d-flex flex-wrap gap-2">
                                <a href="{{ route('invoices.public', $invoice->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-arrow-clockwise"></i> Cek Status Pembayaran
                                </a>
                                @if($pendingPayment->snap_url)
                                    <a href="{{ $pendingPayment->snap_url }}" target="_blank" rel="noopener" class="btn btn-sm btn-primary text-white">
                                        <i class="bi bi-credit-card"></i> Lanjutkan Pembayaran
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            @else
                <div class="row g-3">
                    @if($midtransActive)
                    <div class="col-md-6">
                        <a href="{{ route('payment.midtrans', $invoice->id) }}" class="pay-option">
                            <div class="po-icon po-midtrans"><i class="bi bi-credit-card"></i></div>
                            <div class="flex-grow-1">
                                <div class="po-title">Bayar Online</div>
                                <small class="text-muted">VA, QRIS, E-Wallet, Kartu — via Midtrans</small>
                            </div>
                            <i class="bi bi-chevron-right po-arrow"></i>
                        </a>
                    </div>
                    @endif
                    <div class="{{ $midtransActive ? 'col-md-6' : 'col-12' }}">
                        <a href="{{ route('payment.manual', $invoice->id) }}" class="pay-option">
                            <div class="po-icon po-manual"><i class="bi bi-bank"></i></div>
                            <div class="flex-grow-1">
                                <div class="po-title">Transfer Manual</div>
                                <small class="text-muted">Transfer bank + konfirmasi di aplikasi</small>
                            </div>
                            <i class="bi bi-chevron-right po-arrow"></i>
                        </a>
                    </div>
                </div>
            @endif
        </div>
        @endif

        <div class="text-center text-muted mt-4 pt-3 border-top">
            <small>Invoice ini dibuat menggunakan InvoiceFlow</small>
        </div>
    </div>
</body>
<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
        navigator.serviceWorker.register('/sw.js').catch(function () {});
    });
}
</script>
</html>
