<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <title>Invoice</title>
</head>
<body style="margin:0; padding:0; background-color:#eef2f7; font-family:'Inter','Segoe UI',Helvetica,Arial,sans-serif;">
    <div style="display:none; max-height:0; overflow:hidden;">Invoice {{ $invoice->invoice_number }} — Rp {{ number_format($invoice->total, 0, ',', '.') }}</div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#eef2f7; padding:32px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px; width:100%;">

                    <!-- Header -->
                    <tr>
                        <td style="background:linear-gradient(135deg,#4f46e5 0%,#7c3aed 100%); background-color:#4f46e5; border-radius:20px 20px 0 0; padding:30px 40px 26px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="color:#ffffff; font-size:19px; font-weight:700; font-family:'Sora','Inter',sans-serif; letter-spacing:-0.3px;">
                                        {{ $profile?->business_name ?? '⧉ InvoiceFlow' }}
                                    </td>
                                    <td align="right">
                                        <span style="display:inline-block; background:rgba(255,255,255,.18); border:1px solid rgba(255,255,255,.3); color:#ffffff; font-size:12px; font-weight:700; padding:6px 14px; border-radius:999px;">INVOICE</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="background:#ffffff; padding:36px 40px; border-left:1px solid #e2e8f0; border-right:1px solid #e2e8f0;">
                            <p style="margin:0 0 10px; color:#475569; font-size:14.5px; line-height:1.8; white-space:pre-line;">
                                {!! nl2br(e($customMessage)) !!}
                            </p>

                            <!-- Kartu total -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:24px 0;">
                                <tr>
                                    <td style="background:#eef2ff; border:1px solid #c7d2fe; border-radius:14px; padding:20px 24px; text-align:center;">
                                        <div style="color:#4f46e5; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:1px; padding-bottom:4px;">Total Tagihan</div>
                                        <div style="color:#1e1b4b; font-size:28px; font-weight:800; font-family:'Sora','Inter',sans-serif;">
                                            Rp {{ number_format($invoice->total, 0, ',', '.') }}
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <!-- Detail invoice -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:14px; margin-bottom:20px;">
                                <tr>
                                    <td style="padding:18px 22px;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;">
                                            <tr>
                                                <td style="padding:7px 0; color:#64748b; width:140px;">No. Invoice</td>
                                                <td style="padding:7px 0; color:#0f172a; font-weight:700;">{{ $invoice->invoice_number }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:7px 0; color:#64748b; border-top:1px dashed #e2e8f0;">Kepada</td>
                                                <td style="padding:7px 0; color:#0f172a; font-weight:600; border-top:1px dashed #e2e8f0;">{{ $invoice->client_name }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:7px 0; color:#64748b; border-top:1px dashed #e2e8f0;">Jatuh Tempo</td>
                                                <td style="padding:7px 0; color:#0f172a; font-weight:600; border-top:1px dashed #e2e8f0;">{{ $invoice->due_date->format('d M Y') }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <!-- Info pembayaran -->
                            @if($profile && $profile->bank_account_number)
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:26px;">
                                <tr>
                                    <td style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:14px; padding:18px 22px;">
                                        <div style="color:#166534; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:1px; margin-bottom:10px;">💳 Informasi Pembayaran</div>
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;">
                                            <tr>
                                                <td style="padding:4px 0; color:#64748b; width:100px;">Bank</td>
                                                <td style="padding:4px 0; color:#0f172a; font-weight:600;">{{ $profile->bank_name }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:4px 0; color:#64748b;">No. Rek</td>
                                                <td style="padding:4px 0; color:#0f172a; font-weight:700; letter-spacing:.5px;">{{ $profile->bank_account_number }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:4px 0; color:#64748b;">a.n.</td>
                                                <td style="padding:4px 0; color:#0f172a; font-weight:600;">{{ $profile->bank_account_name }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            @endif

                            <!-- CTA -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center">
                                        <a href="{{ route('invoices.public', $invoice->id) }}"
                                           style="display:inline-block; background:linear-gradient(135deg,#4f46e5 0%,#7c3aed 100%); background-color:#4f46e5; color:#ffffff; font-size:15px; font-weight:700; text-decoration:none; padding:14px 38px; border-radius:12px; box-shadow:0 8px 20px rgba(79,70,229,.3);">
                                            🧾 Lihat Invoice
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background:#ffffff; border-top:1px solid #f1f5f9; border-radius:0 0 20px 20px; border-left:1px solid #e2e8f0; border-right:1px solid #e2e8f0; border-bottom:1px solid #e2e8f0; padding:22px 40px 26px; text-align:center;">
                            <p style="margin:0 0 6px; color:#94a3b8; font-size:12px; line-height:1.6;">
                                Invoice ini dikirim melalui {{ $profile?->business_name ?? 'InvoiceFlow' }}.
                            </p>
                            <p style="margin:6px 0 0; color:#94a3b8; font-size:12px;">
                                Developed by
                                <a href="https://digimagine.web.id" target="_blank" rel="noopener noreferrer" style="color:#4f46e5; font-weight:600; text-decoration:none;">Digital Imagine Indonesia</a>
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
