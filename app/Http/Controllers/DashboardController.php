<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $query = Invoice::query();

        // Klien hanya lihat invoice dengan emailnya
        if ($user->isKlien()) {
            $query->where('client_email', $user->email);
        }
        // Karyawan lihat invoice buatannya sendiri
        elseif ($user->isKaryawan()) {
            $query->where('user_id', $user->id);
        }
        // Admin lihat semua (no filter)
        else {
            $query->where('user_id', $user->id);
        }

        $baseQuery = clone $query;

        $totalInvoices = (clone $baseQuery)->count();
        $paidInvoices = (clone $baseQuery)->paid()->count();
        $overdueInvoices = (clone $baseQuery)->overdue()->count();
        $unpaidInvoices = (clone $baseQuery)->unpaid()->count();

        $totalAmount = (clone $baseQuery)->sum('total');
        $paidAmount = (clone $baseQuery)->where('is_paid', true)->sum('total');
        $unpaidAmount = (clone $baseQuery)->where('is_paid', false)->sum('total');

        // Recent invoices
        $recentInvoices = (clone $baseQuery)
            ->latest()
            ->take(5)
            ->get();

        // Upcoming due dates
        $upcomingDue = (clone $baseQuery)
            ->where('is_paid', false)
            ->whereDate('due_date', '>=', now())
            ->orderBy('due_date')
            ->take(5)
            ->get();

        // Monthly stats (current year, by month)
        $monthlyStats = (clone $baseQuery)
            ->whereYear('created_at', now()->year)
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as count, SUM(total) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        return view('dashboard', compact(
            'totalInvoices', 'paidInvoices', 'overdueInvoices', 'unpaidInvoices',
            'totalAmount', 'paidAmount', 'unpaidAmount',
            'recentInvoices', 'upcomingDue', 'monthlyStats'
        ));
    }
}
