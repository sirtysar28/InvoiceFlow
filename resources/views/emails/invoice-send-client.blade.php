<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"></head>
<body style="font-family:'Helvetica','Arial',sans-serif; background:#f8fafc; margin:0; padding:40px;">
    <div style="max-width:600px; margin:0 auto; background:#fff; border-radius:12px; padding:30px; box-shadow:0 2px 10px rgba(0,0,0,0.05);">
        <div style="text-align:center; border-bottom:2px solid #4f46e5; padding-bottom:20px; margin-bottom:25px;">
            <h2 style="color:#4f46e5; margin:0;">{{ $profile?->business_name ?? 'InvoiceFlow' }}</h2>
            <p style="color:#64748b; font-size:14px;">Invoice</p>
        </div>

        <div style="white-space:pre-line; color:#1e293b; line-height:1.6;">
            {!! nl2br(e($customMessage)) !!}
        </div>

        <div style="background:#f1f5f9; border-radius:8px; padding:20px; margin:20px 0;">
            <table style="width:100%; border-collapse:collapse;">
                <tr>
                    <td style="padding:8px 0; color:#64748b;">No. Invoice</td>
                    <td style="padding:8px 0; font-weight:bold;">{{ $invoice->invoice_number }}</td>
                </tr>
                <tr>
                    <td style="padding:8px 0; color:#64748b;">Total</td>
                    <td style="padding:8px 0; font-weight:bold; font-size:18px;">Rp {{ number_format($invoice->total, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td style="padding:8px 0; color:#64748b;">Jatuh Tempo</td>
                    <td style="padding:8px 0;">{{ $invoice->due_date->format('d M Y') }}</td>
                </tr>
            </table>
        </div>

        @if($profile && $profile->bank_account_number)
        <div style="background:#f8fafc; border-radius:8px; padding:15px; margin:15px 0;">
            <strong>Informasi Pembayaran:</strong><br>
            Bank: {{ $profile->bank_name }}<br>
            No. Rek: {{ $profile->bank_account_number }}<br>
            A.n: {{ $profile->bank_account_name }}
        </div>
        @endif

        <div style="text-align:center; margin:25px 0;">
            <a href="{{ route('invoices.public', $invoice->id) }}" style="background:#4f46e5; color:#fff; padding:12px 30px; border-radius:8px; text-decoration:none; font-weight:600;">
                🔗 Lihat Invoice
            </a>
        </div>

        <p style="color:#94a3b8; font-size:12px; text-align:center; margin-top:20px;">
            Invoice ini dikirim melalui InvoiceFlow.
        </p>
    </div>
</body>
</html>
