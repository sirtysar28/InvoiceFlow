<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\NotificationSetting;
use App\Mail\InvoiceDueReminder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailNotificationService
{
    public function sendDueReminder(Invoice $invoice, NotificationSetting $settings, int $daysBeforeDue): bool
    {
        try {
            $recipientEmail = $settings->email ?? $invoice->user->email;

            Mail::to($recipientEmail)->send(
                new InvoiceDueReminder($invoice, $daysBeforeDue)
            );

            Log::info('Email due reminder sent', [
                'invoice_id' => $invoice->id,
                'to' => $recipientEmail,
                'days_before' => $daysBeforeDue,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send email reminder: ' . $e->getMessage(), [
                'invoice_id' => $invoice->id,
            ]);
            return false;
        }
    }
}
