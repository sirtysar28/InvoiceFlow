<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use App\Models\SubscriptionPackage;

class MonitoringController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()?->canManageCompanies(), 403, 'Akses khusus Super Admin.');

        $totalCompanies = Company::count();
        $activeCompanies = Company::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('subscription_expires_at')
                  ->orWhere('subscription_expires_at', '>=', now());
            })->count();
        $totalUsers = User::count();
        $totalInvoices = Invoice::count();
        $totalPaid = (float) Invoice::where('is_paid', true)->sum('total');
        $totalPendingPayments = Payment::where('status', 'pending')->count();

        // Statistik per perusahaan
        $companies = Company::with('package')->withCount('users')->get()
            ->map(function ($c) {
                $userIds = $c->users()->pluck('id');
                $c->invoices_count = Invoice::whereIn('user_id', $userIds)->count();
                $c->paid_count = Invoice::whereIn('user_id', $userIds)->where('is_paid', true)->count();
                $c->revenue = (float) Invoice::whereIn('user_id', $userIds)->where('is_paid', true)->sum('total');
                return $c;
            })->sortByDesc('revenue');

        $packages = SubscriptionPackage::withCount('companies')->get();

        return view('superadmin.monitoring', compact(
            'totalCompanies', 'activeCompanies', 'totalUsers', 'totalInvoices',
            'totalPaid', 'totalPendingPayments', 'companies', 'packages'
        ));
    }
}
