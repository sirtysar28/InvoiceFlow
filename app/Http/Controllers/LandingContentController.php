<?php

namespace App\Http\Controllers;

use App\Models\LandingContent;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class LandingContentController extends Controller
{
    /** Daftar section + struktur default (juga dipakai halaman landing). */
    public static function defaults(): array
    {
        return [
            'hero' => [
                'badge' => '✨ Manajemen Invoice Modern',
                'title' => 'Kelola Invoice Bisnis Anda dengan',
                'highlight' => 'Lebih Cepat & Rapi',
                'subtitle' => 'Buat, kirim, dan pantau invoice secara otomatis. Kirim via WhatsApp & Email, terima pembayaran lebih cepat tanpa ribet.',
                'btn_primary_text' => 'Mulai Gratis Sekarang',
                'btn_primary_link' => '#cta',
                'btn_secondary_text' => 'Lihat Fitur',
                'btn_secondary_link' => '#fitur',
                'stats' => [
                    ['value' => '500+', 'label' => 'Invoice Terkirim'],
                    ['value' => '98%', 'label' => 'Kepuasan Pengguna'],
                    ['value' => '24/7', 'label' => 'Akses Kapan Saja'],
                    ['value' => '5 mnt', 'label' => 'Rata-rata Buat Invoice'],
                ],
            ],
            'features' => [
                'heading' => 'Semua yang Anda Butuhkan',
                'subheading' => 'Fitur lengkap untuk mengelola penagihan bisnis Anda dalam satu aplikasi.',
                'items' => [
                    ['icon' => 'bi-receipt-cutoff', 'title' => 'Invoice Profesional', 'desc' => 'Buat invoice rapi dengan logo, pajak & diskon otomatis dalam hitungan detik.'],
                    ['icon' => 'bi-whatsapp', 'title' => 'Kirim via WhatsApp', 'desc' => 'Template pesan otomatis, kirim tagihan ke klien cukup satu klik.'],
                    ['icon' => 'bi-envelope-paper', 'title' => 'Notifikasi Email', 'desc' => 'Pengingat jatuh tempo otomatis agar pembayaran tidak terlambat.'],
                    ['icon' => 'bi-graph-up-arrow', 'title' => 'Laporan Lengkap', 'desc' => 'Ekspor laporan ke Excel & PDF, pantau kinerja bisnis real-time.'],
                    ['icon' => 'bi-people', 'title' => 'Master Klien', 'desc' => 'Kelola database klien beserta riwayat tagihan mereka.'],
                    ['icon' => 'bi-shield-check', 'title' => 'Akses Multi-User', 'desc' => 'Admin, karyawan, dan klien punya hak akses berbeda yang aman.'],
                    ['icon' => 'bi-credit-card-2-front', 'title' => 'Pembayaran Otomatis', 'desc' => 'Bayar via QRIS/VA/e-wallet/kartu — integrasi Midtrans. Lunas otomatis, tanpa konfirmasi manual.'],
                    ['icon' => 'bi-phone-fill', 'title' => 'Bisa Di-install (PWA)', 'desc' => 'Pasang seperti aplikasi mobile di HP & desktop — tanpa perlu Play Store.'],
                ],
            ],
            'payments' => [
                'badge' => '💳 Payment Gateway',
                'heading' => 'Pembayaran Otomatis via Midtrans',
                'subheading' => 'Klien bayar online lewat metode favoritnya — invoice langsung LUNAS otomatis. Tanpa konfirmasi manual, tanpa nunggu admin.',
                'methods' => [
                    ['icon' => 'bi-qr-code', 'label' => 'QRIS'],
                    ['icon' => 'bi-bank', 'label' => 'Virtual Account'],
                    ['icon' => 'bi-wallet2', 'label' => 'GoPay / OVO / DANA'],
                    ['icon' => 'bi-credit-card', 'label' => 'Kartu Kredit/Debit'],
                ],
                'steps' => [
                    ['title' => 'Klien Klik "Bayar Sekarang"', 'desc' => 'Dari link invoice yang Anda kirim — tampilan rapi, mobile-friendly.'],
                    ['title' => 'Bayar via Midtrans', 'desc' => 'Pilih QRIS, VA, e-wallet, atau kartu — semua dalam satu halaman aman.'],
                    ['title' => 'Invoice Otomatis LUNAS ✅', 'desc' => 'Status berubah sendiri begitu pembayaran berhasil — real-time, tanpa konfirmasi admin.'],
                ],
            ],
            'pwa' => [
                'badge' => '📱 Installable App',
                'heading' => 'Install Seperti Aplikasi Mobile',
                'subheading' => 'InvoiceFlow berbasis PWA — pasang di homescreen HP & desktop, buka layaknya aplikasi native. Tanpa Play Store, tanpa update manual.',
                'features' => [
                    ['icon' => 'bi-download', 'title' => 'Sekali Klik Install', 'desc' => 'Tombol "Install Aplikasi" langsung dari browser — Chrome, Edge, & Android.'],
                    ['icon' => 'bi-lightning-charge', 'title' => 'Terbuka Instan', 'desc' => 'App-like: full screen, icon sendiri di homescreen, loading lebih cepat.'],
                    ['icon' => 'bi-wifi-off', 'title' => 'Tetap Buka Offline', 'desc' => 'Halaman terakhir tetap bisa dibuka walau sinyal hilang sejenak.'],
                ],
                'btn_text' => 'Install Aplikasi',
            ],
            'steps' => [
                'heading' => 'Mulai dalam 3 Langkah Mudah',
                'subheading' => 'Tidak perlu install apa pun. Langsung pakai dari browser.',
                'items' => [
                    ['title' => 'Daftar & Lengkapi Profil', 'desc' => 'Isi nama bisnis, logo, dan rekening bank untuk invoice Anda.'],
                    ['title' => 'Buat & Kirim Invoice', 'desc' => 'Pilih klien, isi item tagihan, kirim via WhatsApp atau email.'],
                    ['title' => 'Pantau & Terima Pembayaran', 'desc' => 'Tandai lunas, lihat laporan, dan kejar tagihan lewat jatuh tempo.'],
                ],
            ],
            'testimonials' => [
                'heading' => 'Dipercaya banyak Bisnis',
                'subheading' => 'Apa kata mereka yang sudah pakai InvoiceFlow.',
                'items' => [
                    ['name' => 'Andi Pratama', 'role' => 'Owner Digital Agency', 'quote' => 'InvoiceFlow bikin penagihan klien jadi jauh lebih cepat. Laporan bulanannya sangat membantu.'],
                    ['name' => 'Sari Wulandari', 'role' => 'Freelance Designer', 'quote' => 'Tampilannya bagus, gampang dipakai. Kirim invoice via WhatsApp sekali klik, langsung dibayar!'],
                    ['name' => 'PT Maju Bersama', 'role' => 'Kontraktor', 'quote' => 'Fitur pengingat jatuh tempo sangat membantu tim kami menagih tepat waktu.'],
                ],
            ],
            'faq' => [
                'heading' => 'Pertanyaan yang Sering Diajukan',
                'subheading' => 'Belum menemukan jawaban? Hubungi kami via WhatsApp.',
                'items' => [
                    ['q' => 'Apakah bisa dipakai gratis?', 'a' => 'Ya! Anda bisa mendaftar dan langsung menggunakan seluruh fitur dasar InvoiceFlow.'],
                    ['q' => 'Apakah data saya aman?', 'a' => 'Data tersimpan di server dengan akses terproteksi. Setiap user hanya melihat data miliknya sendiri.'],
                    ['q' => 'Bisa kirim invoice lewat WhatsApp?', 'a' => 'Bisa. Tersedia template pesan otomatis yang bisa dikirim sekali klik via WhatsApp atau Email.'],
                    ['q' => 'Apakah bisa ekspor laporan?', 'a' => 'Laporan bisa diekspor ke format Excel (.xlsx) dan PDF kapan saja.'],
                    ['q' => 'Bagaimana jika klien bayar lewat Midtrans, apakah perlu konfirmasi manual?', 'a' => 'Tidak perlu! Pembayaran via Midtrans (QRIS, VA, e-wallet, kartu) terverifikasi otomatis oleh payment gateway. Begitu pembayaran berhasil, invoice langsung berstatus LUNAS real-time — tanpa konfirmasi admin.'],
                    ['q' => 'Apakah InvoiceFlow bisa di-install di HP?', 'a' => 'Bisa! InvoiceFlow adalah PWA (Progressive Web App). Buka website lewat Chrome/Edge/Safari lalu pilih "Install Aplikasi" atau "Add to Home Screen" — ikhtisar aplikasi muncul di homescreen seperti aplikasi biasa, tanpa install dari Play Store.'],
                ],
            ],
            'cta' => [
                'title' => 'Siap Rapikan Penagihan Bisnis Anda?',
                'subtitle' => 'Gabung sekarang, buat invoice pertama Anda dalam 5 menit.',
                'btn_text' => 'Daftar Sekarang',
                'btn_link' => '#',
            ],
            'contact' => [
                'phone' => '(021) 555-0123',
                'email' => 'halo@invoiceflow.id',
                'address' => 'Jakarta, Indonesia',
                'whatsapp' => '6281234567890',
            ],
        ];
    }

    public function index()
    {
        $sections = [];
        foreach (self::defaults() as $key => $default) {
            $sections[$key] = LandingContent::section($key, $default);
        }

        return view('landing.manage', compact('sections'));
    }

    public function update(Request $request, string $section)
    {
        $allowed = array_keys(self::defaults());

        if (!in_array($section, $allowed)) {
            abort(404);
        }

        $data = $request->input('data', []);

        // Sanitasi items: buang baris kosong
        if (isset($data['items']) && is_array($data['items'])) {
            $data['items'] = array_values(array_filter($data['items'], function ($item) {
                if (!is_array($item)) {
                    return false;
                }
                $firstValue = trim((string) ($item['title'] ?? $item['name'] ?? $item['q'] ?? ''));
                return $firstValue !== '';
            }));
        }
        if (isset($data['stats']) && is_array($data['stats'])) {
            $data['stats'] = array_values(array_filter($data['stats'], fn ($s) => is_array($s) && trim((string) ($s['value'] ?? '')) !== ''));
        }

        LandingContent::put($section, $data);

        ActivityLogger::log('landing.update', 'Landing Page', 'Memperbarui konten section: ' . $section);

        return back()->with('success', 'Konten section "' . $section . '" berhasil disimpan! ✅');
    }
}
