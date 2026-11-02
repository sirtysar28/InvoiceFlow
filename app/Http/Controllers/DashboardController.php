<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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
        // Admin, Super Admin & Finance lihat semua invoice
        elseif ($user->isAdmin() || $user->isFinance()) {
            // tanpa filter
        }
        // Sales & karyawan lihat invoice buatannya sendiri
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

        // Monthly stats (current year, by month) — kompatibel MySQL & SQLite
        $monthExpr = DB::connection()->getDriverName() === 'mysql'
            ? "MONTH(created_at)"
            : "CAST(strftime('%m', created_at) AS INTEGER)";

        $monthlyStats = (clone $baseQuery)
            ->whereYear('created_at', now()->year)
            ->selectRaw("{$monthExpr} as month, COUNT(*) as count, SUM(total) as total")
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
