<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * SaaS guard: kunci akun perusahaan yang masa trial/langganannya HABIS.
 * User terkunci hanya boleh mengakses halaman aktivasi (token dari Super Admin)
 * dan logout. Customer (klien) & Super Admin tidak terpengaruh.
 */
class EnsureSubscriptionActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Belum login / customer / super admin / tanpa perusahaan → bebas
        if (!$user || $user->isKlien() || $user->isSuperAdmin() || !$user->company_id) {
            return $next($request);
        }

        // Sedang di halaman aktivasi → jangan redirect loop
        if ($request->routeIs('subscription.*', 'logout')) {
            return $next($request);
        }

        $company = $user->company;

        if ($company && $company->isLocked()) {
            return redirect()->route('subscription.locked')
                ->with('error', 'Masa ' . ($company->package?->trial_days > 0 && $company->subscription_expires_at?->isPast() ? 'TRIAL' : 'LANGGANAN') . ' Anda telah berakhir. Aktifkan paket dengan kode token dari Super Admin.');
        }

        return $next($request);
    }
}
