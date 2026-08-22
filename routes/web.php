<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BusinessProfileController;
use App\Http\Controllers\NotificationSettingController;
use App\Http\Controllers\MessageTemplateController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Welcome / Landing
Route::get('/', function () {
    return view('welcome');
});

// Auth
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);

    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout')->middleware('auth');

// Public invoice view (klien lihat invoice)
Route::get('/invoice/{id}/public', [InvoiceController::class, 'publicView'])->name('invoices.public');

// Authenticated Routes
Route::middleware('auth')->group(function () {

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

    // Clients
    Route::resource('clients', ClientController::class);
    Route::get('/api/clients/search', [ClientController::class, 'search'])->name('clients.search');

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

    // Message Templates
    Route::resource('templates', MessageTemplateController::class)->except(['show', 'edit', 'create']);
});
