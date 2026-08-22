<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }}</title>
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

        <div class="text-center text-muted mt-4 pt-3 border-top">
            <small>Invoice ini dibuat menggunakan InvoiceFlow</small>
        </div>
    </div>
</body>
</html>
