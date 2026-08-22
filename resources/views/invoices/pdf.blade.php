<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 14px; color: #1e293b; margin: 0; padding: 40px; }
        .header { border-bottom: 2px solid #4f46e5; padding-bottom: 20px; margin-bottom: 30px; }
        .header .title { font-size: 28px; font-weight: 800; color: #4f46e5; }
        .header .invoice-number { font-size: 16px; color: #64748b; margin-top: 4px; }
        .row { display: table; width: 100%; margin-bottom: 25px; }
        .col { display: table-cell; width: 50%; vertical-align: top; }
        .label { font-size: 11px; text-transform: uppercase; color: #94a3b8; letter-spacing: 1px; font-weight: 600; }
        .value { font-size: 15px; font-weight: 500; margin-top: 2px; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 25px; margin-bottom: 20px; }
        table.items th { background: #f1f5f9; padding: 12px; text-align: left; font-size: 12px; text-transform: uppercase; }
        table.items td { padding: 12px; border-bottom: 1px solid #e2e8f0; }
        table.items .text-right { text-align: right; }
        .summary { width: 300px; margin-left: auto; }
        .summary td { padding: 6px 0; }
        .summary .total { font-size: 22px; font-weight: 800; color: #4f46e5; }
        .footer { position: fixed; bottom: 40px; left: 40px; right: 40px; text-align: center; font-size: 11px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 15px; }
        .badge { display: inline-block; padding: 4px 12px; border-radius: 12px; color: #fff; font-weight: 600; font-size: 12px; }
        .badge-paid { background: #22c55e; }
        .badge-unpaid { background: #f59e0b; }
    </style>
</head>
<body>
    @if($businessProfile)
    <div class="header">
        <div class="row">
            <div class="col">
                @if($businessProfile->logo)
                    <img src="{{ storage_path('app/public/' . $businessProfile->logo) }}" style="max-height:50px;">
                @endif
                <div class="title">{{ $businessProfile->business_name }}</div>
            </div>
            <div class="col" style="text-align:right;">
                <span class="badge {{ $invoice->is_paid ? 'badge-paid' : 'badge-unpaid' }}">
                    {{ $invoice->status_label }}
                </span>
            </div>
        </div>
    </div>
    @endif

    <div class="row">
        <div class="col">
            <div class="label">Invoice Number</div>
            <div class="value">{{ $invoice->invoice_number }}</div>
        </div>
        <div class="col" style="text-align:right;">
            <div class="label">Tanggal</div>
            <div class="value">{{ $invoice->created_at->format('d M Y') }}</div>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <div class="label">Dari</div>
            <div class="value">{{ $businessProfile?->business_name ?? 'Bisnis Anda' }}</div>
            @if($businessProfile?->address)<div style="font-size:12px;color:#64748b;">{!! nl2br(e($businessProfile->address)) !!}</div>@endif
        </div>
        <div class="col" style="text-align:right;">
            <div class="label">Kepada</div>
            <div class="value">{{ $invoice->client_name }}</div>
            <div style="font-size:12px;color:#64748b;">{{ $invoice->client_email }}</div>
            @if($invoice->client_phone)<div style="font-size:12px;color:#64748b;">{{ $invoice->client_phone }}</div>@endif
        </div>
    </div>

    <div style="margin-top:25px;">
        <div class="label">Jatuh Tempo</div>
        <div class="value" style="{{ $invoice->isOverdue() ? 'color:#ef4444;' : '' }}">
            {{ $invoice->due_date->format('d M Y') }}
        </div>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th>Deskripsi</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Harga</th>
                <th class="text-right">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
            <tr>
                <td>{{ $item->description }}</td>
                <td class="text-right">{{ $item->quantity }}</td>
                <td class="text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($item->amount, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="summary">
        <tr>
            <td>Subtotal</td>
            <td class="text-right">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</td>
        </tr>
        @if($invoice->tax > 0)
        <tr>
            <td>Pajak ({{ $invoice->tax }}%)</td>
            <td class="text-right">Rp {{ number_format($invoice->tax_amount, 0, ',', '.') }}</td>
        </tr>
        @endif
        @if($invoice->discount > 0)
        <tr>
            <td>Diskon ({{ $invoice->discount }}%)</td>
            <td class="text-right">-Rp {{ number_format($invoice->discount_amount, 0, ',', '.') }}</td>
        </tr>
        @endif
        <tr style="border-top:2px solid #1e293b;">
            <td class="total">TOTAL</td>
            <td class="text-right total">Rp {{ number_format($invoice->total, 0, ',', '.') }}</td>
        </tr>
    </table>

    @if($invoice->notes)
    <div style="margin-top:30px; padding:15px; background:#fef9c3; border-radius:8px;">
        <div class="label">Catatan</div>
        <div style="margin-top:4px;">{{ $invoice->notes }}</div>
    </div>
    @endif

    @if($businessProfile && $businessProfile->bank_account_number)
    <div style="margin-top:30px;">
        <div class="label">Informasi Pembayaran</div>
        <div style="margin-top:8px; padding:15px; background:#f1f5f9; border-radius:8px;">
            <strong>Bank:</strong> {{ $businessProfile->bank_name }}<br>
            <strong>No. Rekening:</strong> {{ $businessProfile->bank_account_number }}<br>
            <strong>A.n:</strong> {{ $businessProfile->bank_account_name }}
        </div>
    </div>
    @endif

    <div class="footer">
        Invoice ini dibuat oleh InvoiceFlow &middot; {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
