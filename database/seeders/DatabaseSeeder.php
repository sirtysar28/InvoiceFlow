<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\BusinessProfile;
use App\Models\NotificationSetting;
use App\Models\MessageTemplate;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ===== Admin (idempoten: aman dijalankan berulang kali) =====
        $user = User::updateOrCreate(
            ['email' => 'admin@invoiceflow.com'],
            [
                'name' => 'Admin InvoiceFlow',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,      // WAJIB: agar menu Master User, Pengaturan SMTP tampil
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        // ===== Business profile =====
        BusinessProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'business_name' => 'PT. Digital Kreatif Indonesia',
                'address' => 'Jl. Teknologi No. 88, Kawasan Bisnis\nJakarta Selatan, 12950',
                'phone' => '081234567890',
                'email' => 'info@digitalkreatif.com',
                'bank_name' => 'BCA',
                'bank_account_number' => '1234567890',
                'bank_account_name' => 'PT Digital Kreatif Indonesia',
            ]
        );

        // ===== Notification settings =====
        NotificationSetting::updateOrCreate(
            ['user_id' => $user->id],
            [
                'wa_enabled' => false,
                'email_enabled' => true,
                'email' => 'admin@invoiceflow.com',
                'days_before_due' => [2, 1],
                'send_on_due_day' => true,
                'send_at_hour' => 9,
            ]
        );

        // ===== Message templates =====
        MessageTemplate::updateOrCreate(
            ['user_id' => $user->id, 'type' => 'whatsapp'],
            [
                'name' => 'Template WA Default',
                'body' => "Halo {nama_klien},\n\nBerikut tagihan invoice Anda:\n📄 No. Invoice: {no_invoice}\n💰 Total: Rp {total}\n📅 Jatuh Tempo: {tanggal_jatuh_tempo}\n\n🔗 Link: {link_invoice}\n\nTerima kasih.",
            ]
        );

        MessageTemplate::updateOrCreate(
            ['user_id' => $user->id, 'type' => 'email'],
            [
                'name' => 'Template Email Default',
                'subject' => 'Invoice #{no_invoice} - {nama_klien}',
                'body' => "Halo {nama_klien},\n\nBerikut terlampir invoice dengan detail:\n\nNo. Invoice: {no_invoice}\nTotal: Rp {total}\nJatuh Tempo: {tanggal_jatuh_tempo}\n\nSilahkan melakukan pembayaran sebelum tanggal jatuh tempo.\n\nPembayaran dapat ditransfer ke:\nBCA 1234567890 a.n PT Digital Kreatif Indonesia\n\nLink Invoice: {link_invoice}\n\nTerima kasih.",
            ]
        );

        // ===== Sample invoices (hanya dibuat jika belum ada) =====
        $this->createSampleInvoice($user, [
            'invoice_number' => 'INV/202501/001',
            'client_name' => 'Budi Santoso',
            'client_email' => 'budi@email.com',
            'client_phone' => '081298765432',
            'description' => 'Jasa Pembuatan Website Company Profile',
            'quantity' => 1,
            'unit_price' => 5000000,
            'tax' => 11,
            'due_date' => now()->subDays(5),
        ]);

        $this->createSampleInvoice($user, [
            'invoice_number' => 'INV/202501/002',
            'client_name' => 'PT Maju Jaya Abadi',
            'client_email' => 'finance@majujaya.com',
            'client_phone' => '081345678901',
            'description' => 'Konsultasi IT Bulanan - Januari',
            'quantity' => 1,
            'unit_price' => 3500000,
            'tax' => 0,
            'due_date' => now()->addDays(3),
        ]);

        $paid = $this->createSampleInvoice($user, [
            'invoice_number' => 'INV/202412/003',
            'client_name' => 'CV Karya Mandiri',
            'client_email' => 'info@karyamandiri.com',
            'client_phone' => '081212345678',
            'description' => 'Pengembangan Aplikasi Mobile',
            'quantity' => 1,
            'unit_price' => 15000000,
            'tax' => 11,
            'due_date' => now()->subDays(30),
        ]);
        if (!$paid->is_paid) {
            $paid->markAsPaid();
        }

        $this->createSampleInvoice($user, [
            'invoice_number' => 'INV/202501/004',
            'client_name' => 'Ani Rahmawati',
            'client_email' => 'ani.rahmawati@email.com',
            'client_phone' => '085678901234',
            'description' => 'Desain Logo + Branding Kit',
            'quantity' => 1,
            'unit_price' => 2500000,
            'tax' => 0,
            'discount' => 10,
            'due_date' => now()->addDays(1),
        ]);

        $this->command?->info('✅ Seeder selesai — data admin & contoh invoice siap. Login: admin@invoiceflow.com / password');
    }

    private function createSampleInvoice(User $user, array $data): Invoice
    {
        // Lewati jika invoice dengan nomor tersebut sudah ada (idempoten)
        $existing = Invoice::where('invoice_number', $data['invoice_number'])->first();
        if ($existing) {
            return $existing;
        }

        $quantity = $data['quantity'];
        $unitPrice = $data['unit_price'];
        $subtotal = $quantity * $unitPrice;
        $taxPercent = $data['tax'] ?? 0;
        $taxAmount = $subtotal * ($taxPercent / 100);
        $discountPercent = $data['discount'] ?? 0;
        $discountAmount = $subtotal * ($discountPercent / 100);
        $total = $subtotal + $taxAmount - $discountAmount;

        $invoice = Invoice::create([
            'user_id' => $user->id,
            'invoice_number' => $data['invoice_number'],
            'client_name' => $data['client_name'],
            'client_email' => $data['client_email'],
            'client_phone' => $data['client_phone'] ?? null,
            'description' => $data['description'],
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'subtotal' => $subtotal,
            'tax' => $taxPercent,
            'tax_amount' => $taxAmount,
            'discount' => $discountPercent,
            'discount_amount' => $discountAmount,
            'total' => $total,
            'due_date' => $data['due_date'],
            'status' => 'unpaid',
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'description' => $data['description'],
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'amount' => $subtotal,
        ]);

        return $invoice;
    }
}
