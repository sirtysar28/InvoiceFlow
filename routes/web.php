<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BusinessProfileController;
use App\Http\Controllers\NotificationSettingController;
use App\Http\Controllers\SmtpSettingController;
use App\Http\Controllers\WhatsAppSettingController;
use App\Http\Controllers\CaptchaController;
use App\Http\Controllers\MessageTemplateController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\LandingContentController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\MidtransSettingController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\SalesOrderController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\SystemSettingController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Welcome / Landing Page (konten dikelola admin)
Route::get('/', function () {
    $sections = [];
    foreach (\App\Http\Controllers\LandingContentController::defaults() as $key => $default) {
        $sections[$key] = \App\Models\LandingContent::section($key, $default);
    }
    return view('landing.page', compact('sections'));
})->name('landing');

// Captcha (kode keamanan login)
Route::get('/captcha/login', [CaptchaController::class, 'login'])->name('captcha.login');

// Auth
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);

    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout')->middleware('auth');

// Public invoice view (klien lihat invoice & bayar)
Route::get('/invoice/{id}/public', [InvoiceController::class, 'publicView'])->name('invoices.public');

// Pembayaran oleh klien (dari halaman invoice publik)
Route::get('/payment/{invoiceId}/midtrans', [PaymentController::class, 'payWithMidtrans'])->name('payment.midtrans');
Route::get('/payment/{invoiceId}/manual', [PaymentController::class, 'manualForm'])->name('payment.manual');
Route::post('/payment/{invoiceId}/manual', [PaymentController::class, 'submitManual'])->name('payment.manual.submit');

// Webhook Midtrans (dipanggil server Midtrans — tanpa CSRF)
Route::post('/payments/midtrans/notification', [PaymentController::class, 'midtransNotification'])->name('payment.midtrans.webhook');

// ===== PWA Manifest (dinamis — nama & logo dari Profil Bisnis) =====
Route::get('/manifest.webmanifest', function () {
    $profile = \App\Models\BusinessProfile::whereNotNull('logo')->orderBy('id')->first()
        ?: \App\Models\BusinessProfile::orderBy('id')->first();

    $name = $profile?->business_name ?: config('app.name');

    // Pakai logo bisnis jika ada, fallback ke icon bawaan
    $icon192 = $profile?->logo ? asset('storage/' . $profile->logo) : asset('img/icon-192.png');
    $icon512 = $profile?->logo ? asset('storage/' . $profile->logo) : asset('img/icon-512.png');

    return response()->json([
        'name' => $name,
        'short_name' => \Illuminate\Support\Str::limit($name, 12, ''),
        'description' => 'Aplikasi manajemen invoice — buat, kirim, & terima pembayaran otomatis.',
        'start_url' => url('/'),
        'scope' => url('/'),
        'display' => 'standalone',
        'orientation' => 'portrait-primary',
        'background_color' => '#0f0c29',
        'theme_color' => '#6366f1',
        'icons' => [
            ['src' => $icon192, 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
            ['src' => $icon512, 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
            ['src' => $icon512, 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
        ],
    ])->header('Content-Type', 'application/manifest+json');
})->name('pwa.manifest');

// ===== Langganan SaaS: halaman kunci trial & aktivasi token =====
Route::middleware('auth')->group(function () {
    Route::get('/subscription/locked', [\App\Http\Controllers\SubscriptionController::class, 'locked'])->name('subscription.locked');
    Route::post('/subscription/activate', [\App\Http\Controllers\SubscriptionController::class, 'activate'])->name('subscription.activate');
});

// Authenticated Routes (+ kunci trial/langganan untuk tenant)
Route::middleware(['auth', 'subscription.active'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Invoices
    Route::resource('invoices', InvoiceController::class)->except(['index']);
    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');

    // Invoice Actions
    Route::post('/invoices/{invoice}/mark-paid', [InvoiceController::class, 'markAsPaid'])->name('invoices.mark-paid');
    Route::post('/invoices/{invoice}/mark-unpaid', [InvoiceController::class, 'markAsUnpaid'])->name('invoices.mark-unpaid');
    Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'downloadPdf'])->name('invoices.pdf');
    Route::get('/invoices/{invoice}/send-whatsapp', [InvoiceController::class, 'sendWhatsApp'])->name('invoices.send-wa');
    Route::post('/invoices/{invoice}/send-email', [InvoiceController::class, 'sendEmail'])->name('invoices.send-email');

    // Clients (Admin & Sales)
    Route::middleware('can:manage-customers')->group(function () {
        Route::resource('clients', ClientController::class);
        Route::get('/api/clients/search', [ClientController::class, 'search'])->name('clients.search');
    });

    // Users (Admin only)
    Route::resource('users', UserController::class);
    Route::post('/users/{user}/approve', [UserController::class, 'approve'])->name('users.approve');
    Route::post('/users/{user}/reject', [UserController::class, 'reject'])->name('users.reject');

    // Business Profile
    Route::get('/profile/business', [BusinessProfileController::class, 'index'])->name('profile.business');
    Route::post('/profile/business', [BusinessProfileController::class, 'store'])->name('profile.business.store');

    // Notification Settings
    Route::get('/notifications', [NotificationSettingController::class, 'index'])->name('notifications.index');
    Route::post('/notifications', [NotificationSettingController::class, 'store'])->name('notifications.store');

    // SMTP Settings (Admin)
    Route::middleware('can:admin')->group(function () {
        Route::post('/notifications/smtp', [SmtpSettingController::class, 'update'])->name('smtp.store');
        Route::post('/notifications/smtp/test', [SmtpSettingController::class, 'test'])->name('smtp.test');

        Route::post('/notifications/whatsapp', [WhatsAppSettingController::class, 'update'])->name('whatsapp.store');
        Route::post('/notifications/whatsapp/test', [WhatsAppSettingController::class, 'test'])->name('whatsapp.test');

        // Payment Gateway Midtrans
        Route::post('/settings/midtrans', [MidtransSettingController::class, 'update'])->name('midtrans.store');
        Route::post('/settings/midtrans/test', [MidtransSettingController::class, 'test'])->name('midtrans.test');
    });

    // Message Templates
    Route::resource('templates', MessageTemplateController::class)->except(['show', 'edit', 'create']);

    // Laporan (Admin & Finance)
    Route::middleware('can:view-reports')->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/{type}', [ReportController::class, 'show'])->name('reports.show');
        Route::get('/reports/{type}/export/xlsx', [ReportController::class, 'exportXlsx'])->name('reports.xlsx');
        Route::get('/reports/{type}/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.pdf');
    });

    // Pembayaran (Admin & Finance)
    Route::middleware('can:verify-payments')->group(function () {
        Route::get('/payments', [PaymentController::class, 'adminIndex'])->name('payments.index');
        Route::post('/payments/{payment}/sync-midtrans', [PaymentController::class, 'syncMidtrans'])->name('payments.sync');
        Route::post('/payments/{payment}/approve', [PaymentController::class, 'approve'])->name('payments.approve');
        Route::post('/payments/{payment}/reject', [PaymentController::class, 'reject'])->name('payments.reject');
    });

    // Produk/Jasa (Admin & Super Admin)
    Route::middleware('can:manage-products')->group(function () {
        Route::resource('products', ProductController::class)->except(['create', 'show', 'edit']);
    });

    // Quotation (Admin & Sales)
    Route::middleware('can:manage-quotations')->group(function () {
        Route::resource('quotations', QuotationController::class);
        Route::post('/quotations/{quotation}/mark-status', [QuotationController::class, 'markStatus'])->name('quotations.mark-status');
        Route::post('/quotations/{quotation}/convert-order', [QuotationController::class, 'convertToOrder'])->name('quotations.convert-order');
        Route::post('/quotations/{quotation}/convert-invoice', [QuotationController::class, 'convertToInvoice'])->name('quotations.convert-invoice');
    });

    // Sales Order (Admin & Sales)
    Route::middleware('can:manage-orders')->group(function () {
        Route::resource('orders', SalesOrderController::class);
        Route::post('/orders/{order}/mark-status', [SalesOrderController::class, 'markStatus'])->name('orders.mark-status');
        Route::post('/orders/{order}/convert-invoice', [SalesOrderController::class, 'convertToInvoice'])->name('orders.convert-invoice');
    });

    // Riwayat transaksi Customer (klien)
    Route::get('/riwayat-transaksi', function () {
        $user = auth()->user();
        $payments = \App\Models\Payment::whereHas('invoice', fn ($q) => $q->where('client_email', $user->email))
            ->with('invoice')->latest()->paginate(20);
        return view('customers.history', compact('payments'));
    })->name('customers.history');

    // Admin-only
    Route::middleware('can:admin')->group(function () {
        // Aktivitas Log
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    });

    // Super Admin-only
    Route::middleware('can:manage-companies')->group(function () {
        // Landing Page = aset PLATFORM (seluruh tenant) → hanya Super Admin
        Route::get('/landing/manage', [LandingContentController::class, 'index'])->name('landing.manage');
        Route::post('/landing/manage/{section}', [LandingContentController::class, 'update'])->name('landing.update');

        Route::get('/superadmin/monitoring', [MonitoringController::class, 'index'])->name('superadmin.monitoring');
        Route::resource('companies', CompanyController::class)->except(['create', 'show', 'edit']);
        Route::post('/companies/{company}/generate-token', [CompanyController::class, 'generateToken'])->name('companies.token');
        Route::post('/companies/{company}/toggle', [CompanyController::class, 'toggle'])->name('companies.toggle');
        Route::resource('packages', PackageController::class)->except(['create', 'show', 'edit']);
        Route::get('/system-settings', [SystemSettingController::class, 'index'])->name('system-settings.index');
        Route::post('/system-settings', [SystemSettingController::class, 'update'])->name('system-settings.update');
    });
});
