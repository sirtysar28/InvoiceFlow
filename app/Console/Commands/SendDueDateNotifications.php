<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\NotificationLog;
use App\Models\NotificationSetting;
use App\Services\WhatsAppService;
use App\Services\EmailNotificationService;
use Illuminate\Console\Command;
use Carbon\Carbon;

class SendDueDateNotifications extends Command
{
    protected $signature = 'invoiceflow:send-due-reminders';
    protected $description = 'Kirim notifikasi invoice jatuh tempo via WA dan Email';

    public function handle(
        WhatsAppService $waService,
        EmailNotificationService $emailService,
    ): int {
        $this->info('Memulai pengecekan notifikasi jatuh tempo...');

        $notificationSettings = NotificationSetting::with('user')->get();

        foreach ($notificationSettings as $settings) {
            $this->processUserNotifications($settings, $waService, $emailService);
        }

        $this->info('Selesai.');

        return self::SUCCESS;
    }

    private function processUserNotifications(
        NotificationSetting $settings,
        WhatsAppService $waService,
        EmailNotificationService $emailService,
    ): void {
        $notifyDays = $settings->getNotificationDays();

        if (empty($notifyDays)) {
            return;
        }

        foreach ($notifyDays as $daysBeforeDue) {
            $targetDate = Carbon::now()->addDays($daysBeforeDue)->toDateString();

            // Cari invoice yang jatuh tempo pada targetDate, belum dibayar, dan belum dinotifikasi
            $invoices = Invoice::where('user_id', $settings->user_id)
                ->where('is_paid', false)
                ->whereDate('due_date', $targetDate)
                ->get();

            foreach ($invoices as $invoice) {
                $this->sendNotificationForInvoice(
                    $invoice,
                    $settings,
                    $daysBeforeDue,
                    $waService,
                    $emailService,
                );
            }
        }
    }

    private function sendNotificationForInvoice(
        Invoice $invoice,
        NotificationSetting $settings,
        int $daysBeforeDue,
        WhatsAppService $waService,
        EmailNotificationService $emailService,
    ): void {
        // Cegah duplikasi
        $alreadySent = NotificationLog::where('invoice_id', $invoice->id)
            ->where('days_before_due', $daysBeforeDue)
            ->exists();

        if ($alreadySent) {
            $this->line("Notifikasi sudah dikirim: Invoice #{$invoice->invoice_number} H-{$daysBeforeDue}");
            return;
        }

        $label = $daysBeforeDue === 0 ? 'Hari H' : "H-{$daysBeforeDue}";
        $this->info("Mengirim notifikasi: Invoice #{$invoice->invoice_number} ({$label})");

        // Kirim via WhatsApp
        if ($settings->wa_enabled && $waService->isConfigured()) {
            $phone = $settings->wa_number ?? $invoice->user->phone ?? null;
            if ($phone) {
                $waMessage = $this->buildWaMessage($invoice, $daysBeforeDue);
                $result = $waService->send($phone, $waMessage);

                NotificationLog::create([
                    'invoice_id' => $invoice->id,
                    'user_id' => $settings->user_id,
                    'channel' => 'whatsapp',
                    'type' => 'due_reminder',
                    'days_before_due' => $daysBeforeDue,
                    'sent_at' => now(),
                    'status' => $result['status'] === 'success' ? 'sent' : 'failed',
                    'message' => $waMessage,
                ]);

                if ($result['status'] === 'success') {
                    $this->info("  ✅ WhatsApp terkirim ke {$phone}");
                } else {
                    $this->error("  ❌ WhatsApp gagal ke {$phone}: " . ($result['message'] ?? 'Unknown'));
                }
            }
        }

        // Kirim via Email
        if ($settings->email_enabled) {
            $sent = $emailService->sendDueReminder($invoice, $settings, $daysBeforeDue);

            NotificationLog::create([
                'invoice_id' => $invoice->id,
                'user_id' => $settings->user_id,
                'channel' => 'email',
                'type' => 'due_reminder',
                'days_before_due' => $daysBeforeDue,
                'sent_at' => now(),
                'status' => $sent ? 'sent' : 'failed',
                'message' => "Pengingat Invoice {$invoice->invoice_number}",
            ]);

            $recipient = $settings->email ?? $invoice->user->email;
            if ($sent) {
                $this->info("  ✅ Email terkirim ke {$recipient}");
            } else {
                $this->error("  ❌ Email gagal ke {$recipient}");
            }
        }
    }

    private function buildWaMessage(Invoice $invoice, int $daysBeforeDue): string
    {
        if ($daysBeforeDue === 0) {
            $msg = "🔔 *PENGINGAT JATUH TEMPO*\n\n";
            $msg .= "Invoice *{$invoice->invoice_number}* untuk *{$invoice->client_name}* ";
            $msg .= "sebesar *Rp " . number_format($invoice->total, 0, ',', '.') . "* ";
            $msg .= "jatuh tempo *HARI INI* (" . $invoice->due_date->format('d/m/Y') . ").\n\n";
            $msg .= "Segera tagih! 💰";
        } else {
            $msg = "⏰ *PENGINGAT INVOICE*\n\n";
            $msg .= "Invoice *{$invoice->invoice_number}* untuk *{$invoice->client_name}* ";
            $msg .= "sebesar *Rp " . number_format($invoice->total, 0, ',', '.') . "* ";
            $msg .= "jatuh tempo dalam *{$daysBeforeDue} hari* lagi ";
            $msg .= "(" . $invoice->due_date->format('d/m/Y') . ").\n\n";
            $msg .= "Jangan lupa tagih ya! 📋";
        }

        return $msg;
    }
}
