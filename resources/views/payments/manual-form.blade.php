<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Pembayaran - {{ $invoice->invoice_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --grad-brand: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); }
        * { font-family: 'Inter', sans-serif; }
        h1,h2,h3,h4,h5,h6 { font-family: 'Sora', sans-serif !important; }
        body { min-height: 100vh; display: flex; align-items: center; justify-content: center;
               background: linear-gradient(160deg,#0f0c29 0%, #1e1b4b 45%, #4338ca 100%); padding: 24px 14px; }
        .pay-card { width: 100%; max-width: 560px; background: #fff; border-radius: 24px; padding: 36px;
                    box-shadow: 0 40px 90px -20px rgba(0,0,0,.5); }
        .inv-badge { display:inline-flex; align-items:center; gap:8px; background:#eef2ff; color:#4338ca;
                     font-weight:700; padding:8px 18px; border-radius:30px; font-size:.9rem; }
        .total-box { background: var(--grad-brand); color:#fff; border-radius:16px; padding:18px 22px;
                     display:flex; justify-content:space-between; align-items:center; margin:18px 0 26px; }
        .total-box .amount { font-family:'Sora'; font-weight:800; font-size:1.5rem; }
        .btn-brand { background: var(--grad-brand); color:#fff; font-weight:700; border:none; border-radius:13px; padding:12px 22px; }
        .btn-brand:hover { color:#fff; filter:brightness(1.08); }
        .alert-pending { background:#fffbeb; border:1px solid #fde68a; color:#92400e; border-radius:14px; padding:16px 18px; }
    </style>
</head>
<body>
    <div class="pay-card">
        <div class="text-center mb-2">
            <i class="bi bi-receipt-cutoff" style="font-size:2.4rem; color:#6366f1;"></i>
        </div>
        <h4 class="text-center fw-bold mb-1">Konfirmasi Pembayaran</h4>
        <p class="text-center text-muted mb-3">Invoice <span class="inv-badge"><i class="bi bi-hash"></i>{{ $invoice->invoice_number }}</span></p>

        <div class="total-box">
            <div>
                <small style="opacity:.8;">Total Tagihan</small>
                <div class="amount">Rp {{ number_format($invoice->total, 0, ',', '.') }}</div>
            </div>
            <i class="bi bi-cash-coin" style="font-size:2rem; opacity:.85;"></i>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if($pending)
            <div class="alert-pending d-flex gap-3 align-items-start">
                <i class="bi bi-hourglass-split" style="font-size:1.5rem;"></i>
                <div>
                    <strong>Konfirmasi Anda sedang diverifikasi admin.</strong><br>
                    <small>Dikirim {{ $pending->created_at->format('d/m/Y H:i') }} · {{ $pending->bank_name }} · Rp {{ number_format($pending->amount, 0, ',', '.') }}</small>
                </div>
            </div>
            <a href="{{ route('invoices.public', $invoice->id) }}" class="btn btn-brand w-100 mt-3">
                <i class="bi bi-arrow-left"></i> Kembali ke Invoice
            </a>
        @else
            <form method="POST" action="{{ route('payment.manual.submit', $invoice->id) }}" enctype="multipart/form-data">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Bank Pengirim <span class="text-danger">*</span></label>
                        <input type="text" name="bank_name" class="form-control {{ $errors->has('bank_name') ? 'is-invalid' : '' }}" required placeholder="BCA / Mandiri / BNI...">
                        @error('bank_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nama Pengirim <span class="text-danger">*</span></label>
                        <input type="text" name="sender_name" class="form-control" required placeholder="Nama di rekening">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Tanggal Transfer <span class="text-danger">*</span></label>
                        <input type="date" name="transfer_date" class="form-control" required value="{{ old('transfer_date', now()->format('Y-m-d')) }}" max="{{ now()->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nominal Transfer <span class="text-danger">*</span></label>
                        <input type="number" name="amount" class="form-control" required step="0.01" min="0" value="{{ old('amount', $invoice->total) }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Bukti Transfer <small class="text-muted">(jpg/png/pdf, opsional)</small></label>
                        <input type="file" name="proof" class="form-control" accept="image/jpeg,image/png,application/pdf">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Catatan</label>
                        <textarea name="notes" rows="2" class="form-control" placeholder="Catatan tambahan untuk admin (opsional)">{{ old('notes') }}</textarea>
                    </div>
                </div>
                <button type="submit" class="btn btn-brand w-100 mt-4">
                    <i class="bi bi-send"></i> Kirim Konfirmasi Pembayaran
                </button>
            </form>
            <a href="{{ route('invoices.public', $invoice->id) }}" class="btn btn-outline-secondary w-100 mt-2">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        @endif
    </div>
</body>
</html>
