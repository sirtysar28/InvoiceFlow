<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <title>Konfigurasi SMTP Berhasil</title>
</head>
<body style="margin:0; padding:0; background-color:#eef2f7; font-family:'Inter','Segoe UI',Helvetica,Arial,sans-serif;">
    <div style="display:none; max-height:0; overflow:hidden;">Konfigurasi SMTP Anda berhasil — email ini dikirim sebagai pengujian.</div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#eef2f7; padding:32px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px; width:100%;">

                    <!-- Header gradient -->
                    <tr>
                        <td style="background:linear-gradient(135deg,#4f46e5 0%,#7c3aed 100%); background-color:#4f46e5; border-radius:20px 20px 0 0; padding:36px 40px; text-align:center;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding-bottom:18px;">
                                        <div style="width:64px; height:64px; margin:0 auto; background:rgba(255,255,255,.16); border:1px solid rgba(255,255,255,.3); border-radius:18px; line-height:64px; font-size:30px;">✅</div>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" style="color:#ffffff; font-size:22px; font-weight:700; font-family:'Sora','Inter',sans-serif; letter-spacing:-0.3px;">
                                        SMTP Berhasil Terhubung!
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" style="color:rgba(224,231,255,.85); font-size:14px; padding-top:8px;">
                                        Konfigurasi email Anda sudah siap digunakan
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="background:#ffffff; padding:36px 40px; border-left:1px solid #e2e8f0; border-right:1px solid #e2e8f0;">
                            <p style="margin:0 0 18px; color:#0f172a; font-size:15px; line-height:1.7;">
                                Selamat 🎉 — koneksi SMTP Anda <strong style="color:#166534;">berhasil diuji</strong>.
                                Mulai sekarang, semua notifikasi invoice dan pengiriman email ke klien
                                akan dikirim melalui server email berikut:
                            </p>

                            <!-- Detail konfigurasi -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:14px; margin:8px 0 20px;">
                                <tr>
                                    <td style="padding:18px 22px;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;">
                                            <tr>
                                                <td style="padding:7px 0; color:#64748b; width:130px;">SMTP Host</td>
                                                <td style="padding:7px 0; color:#0f172a; font-weight:600;">{{ $host }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:7px 0; color:#64748b; border-top:1px dashed #e2e8f0;">Port</td>
                                                <td style="padding:7px 0; color:#0f172a; font-weight:600; border-top:1px dashed #e2e8f0;">{{ $port }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:7px 0; color:#64748b; border-top:1px dashed #e2e8f0;">Enkripsi</td>
                                                <td style="padding:7px 0; color:#0f172a; font-weight:600; border-top:1px dashed #e2e8f0;">{{ $encryption }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:7px 0; color:#64748b; border-top:1px dashed #e2e8f0;">Waktu Uji</td>
                                                <td style="padding:7px 0; color:#0f172a; font-weight:600; border-top:1px dashed #e2e8f0;">{{ now()->format('d M Y, H:i') }} WIB</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0; color:#64748b; font-size:13px; line-height:1.7;">
                                Tips: simpan email ini sebagai referensi konfigurasi Anda.
                                Jika ada kendala pengiriman, periksa kembali pengaturan di
                                <strong>Pengaturan Notifikasi → SMTP</strong>.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background:#ffffff; border-top:1px solid #f1f5f9; border-radius:0 0 20px 20px; border-left:1px solid #e2e8f0; border-right:1px solid #e2e8f0; border-bottom:1px solid #e2e8f0; padding:24px 40px; text-align:center;">
                            <p style="margin:0 0 6px; color:#94a3b8; font-size:12px;">
                                Email otomatis dari <strong style="color:#64748b;">{{ config('app.name') }}</strong>
                            </p>
                            <p style="margin:0; color:#94a3b8; font-size:12px;">
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
