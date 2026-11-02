<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <title>Pengingat Invoice Jatuh Tempo</title>
</head>
<body style="margin:0; padding:0; background-color:#eef2f7; font-family:'Inter','Segoe UI',Helvetica,Arial,sans-serif;">
    <div style="display:none; max-height:0; overflow:hidden;">
        @if($daysBeforeDue === 0)
            Invoice {{ $invoice->invoice_number }} jatuh tempo HARI INI!
        @else
            Invoice {{ $invoice->invoice_number }} akan jatuh tempo dalam {{ $daysBeforeDue }} hari.
        @endif
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#eef2f7; padding:32px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px; width:100%;">

                    <!-- Header -->
                    <tr>
                        <td style="background:linear-gradient(135deg,#4f46e5 0%,#7c3aed 100%); background-color:#4f46e5; border-radius:20px 20px 0 0; padding:30px 40px 26px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="color:#ffffff; font-size:20px; font-weight:700; font-family:'Sora','Inter',sans-serif; letter-spacing:-0.3px;">
                                        ⧉ InvoiceFlow
                                    </td>
                                    <td align="right">
                                        @if($daysBeforeDue === 0)
                                            <span style="display:inline-block; background:#fca5a5; color:#7f1d1d; font-size:12px; font-weight:700; padding:6px 14px; border-radius:999px;">⚠️ JATUH TEMPO HARI INI</span>
                                        @else
                                            <span style="display:inline-block; background:#fcd34d; color:#78350f; font-size:12px; font-weight:700; padding:6px 14px; border-radius:999px;">⏰ H-{{ $daysBeforeDue }}</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="background:#ffffff; padding:36px 40px; border-left:1px solid #e2e8f0; border-right:1px solid #e2e8f0;">
                            <h1 style="margin:0 0 12px; color:#0f172a; font-size:20px; font-weight:700; font-family:'Sora','Inter',sans-serif; letter-spacing:-0.3px;">
                                @if($daysBeforeDue === 0)
                                    Invoice ini jatuh tempo <span style="color:#dc2626;">hari ini</span>!
                                @else
                                    Invoice jatuh tempo dalam <span style="color:#d97706;">{{ $daysBeforeDue }} hari</span>
                                @endif
                            </h1>
                            <p style="margin:0 0 24px; color:#475569; font-size:14px; line-height:1.7;">
                                @if($daysBeforeDue === 0)
                                    Segera lakukan penagihan agar pembayaran diterima tepat waktu.
                                @else
                                    Berikut rincian invoice yang akan segera jatuh tempo. Persiapkan penagihan Anda.
                                @endif
                            </p>

                            <!-- Kartu total -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
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
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:14px; margin-bottom:26px;">
                                <tr>
                                    <td style="padding:18px 22px;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;">
                                            <tr>
                                                <td style="padding:7px 0; color:#64748b; width:140px;">No. Invoice</td>
                                                <td style="padding:7px 0; color:#0f172a; font-weight:700;">{{ $invoice->invoice_number }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:7px 0; color:#64748b; border-top:1px dashed #e2e8f0;">Klien</td>
                                                <td style="padding:7px 0; color:#0f172a; font-weight:600; border-top:1px dashed #e2e8f0;">{{ $invoice->client_name }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:7px 0; color:#64748b; border-top:1px dashed #e2e8f0;">Tanggal Jatuh Tempo</td>
                                                <td style="padding:7px 0; border-top:1px dashed #e2e8f0; {{ $daysBeforeDue === 0 ? 'color:#dc2626; font-weight:700;' : 'color:#0f172a; font-weight:600;' }}">
                                                    {{ $invoice->due_date->format('d M Y') }}
                                                    @if($daysBeforeDue === 0) ⚠️ @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding:7px 0; color:#64748b; border-top:1px dashed #e2e8f0;">Status</td>
                                                <td style="padding:7px 0; border-top:1px dashed #e2e8f0;">
                                                    <span style="display:inline-block; background:#fef3c7; color:#92400e; font-size:12px; font-weight:700; padding:4px 12px; border-radius:999px;">Belum Dibayar</span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <!-- CTA -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center">
                                        <a href="{{ route('invoices.public', $invoice->id) }}"
                                           style="display:inline-block; background:linear-gradient(135deg,#4f46e5 0%,#7c3aed 100%); background-color:#4f46e5; color:#ffffff; font-size:15px; font-weight:700; text-decoration:none; padding:14px 38px; border-radius:12px; box-shadow:0 8px 20px rgba(79,70,229,.3);">
                                            🔗 Lihat Invoice
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
                                Email ini dikirim otomatis oleh InvoiceFlow.<br>
                                Invoice yang sudah lunas tidak akan menerima pengingat lagi.
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
