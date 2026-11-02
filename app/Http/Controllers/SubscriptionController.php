<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\SubscriptionPackage;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    /**
     * Halaman kunci: muncul saat trial/langganan perusahaan habis.
     * User memasukkan token dari Super Admin + memilih paket berbayar.
     */
    public function locked()
    {
        $user = auth()->user();
        $company = $user?->company;

        // Tidak terkunci → tidak perlu di sini
        if (!$company || !$company->isLocked()) {
            return redirect()->route('dashboard');
        }

        $packages = SubscriptionPackage::where('is_active', true)
            ->where('price_monthly', '>', 0) // paket berbayar saja
            ->orderBy('price_monthly')
            ->get();

        return view('subscription.locked', [
            'company'  => $company,
            'package'  => $company->package,
            'packages' => $packages,
        ]);
    }

    /**
     * Aktivasi: validasi token → set paket baru → buka kunci.
     */
    public function activate(Request $request)
    {
        $user = auth()->user();
        $company = $user?->company;

        if (!$company) {
            return redirect()->route('dashboard');
        }

        $validated = $request->validate([
            'token'      => ['required', 'string', 'max:20'],
            'package_id' => ['required', 'integer', 'exists:subscription_packages,id'],
        ], [
            'token.required'      => 'Kode token aktivasi wajib diisi.',
            'package_id.required' => 'Silakan pilih paket berlangganan.',
        ]);

        $package = SubscriptionPackage::where('is_active', true)
            ->where('price_monthly', '>', 0)
            ->findOrFail($validated['package_id']);

        // Cek token (case-insensitive)
        $token = strtoupper(trim($validated['token']));
        if (!$company->activation_token || !hash_equals($company->activation_token, $token)) {
            return back()->with('error', 'Kode token tidak valid. Hubungi Super Admin untuk mendapatkan kode yang benar.');
        }

        $company->update([
            'package_id'             => $package->id,
            'subscription_expires_at' => now()->addDays($package->duration_days),
            'is_active'              => true,
            'activation_token'       => null, // token sekali pakai
        ]);

        ActivityLogger::log('company.activated', 'Perusahaan',
            'Perusahaan "' . $company->name . '" mengaktifkan paket ' . $package->name .
            ' s/d ' . $company->subscription_expires_at->format('d/m/Y'));

        return redirect()->route('dashboard')
            ->with('success', '🎉 Akun berhasil diaktifkan! Paket ' . $package->name .
                ' aktif sampai ' . $company->subscription_expires_at->format('d/m/Y') . '.');
    }
}
