<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\SubscriptionPackage;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index()
    {
        $this->requireSuperAdmin();
        $companies = Company::with('package')->withCount('users')->latest()->paginate(20);
        $packages = SubscriptionPackage::where('is_active', true)->get();

        return view('superadmin.companies.index', compact('companies', 'packages'));
    }

    public function store(Request $request)
    {
        $this->requireSuperAdmin();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'address' => 'nullable|string|max:500',
            'package_id' => 'nullable|exists:subscription_packages,id',
            'subscription_expires_at' => 'nullable|date',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        Company::create($validated);

        ActivityLogger::log('company.create', 'Perusahaan', 'Menambahkan perusahaan "' . $validated['name'] . '"');

        return back()->with('success', 'Perusahaan berhasil ditambahkan! ✅');
    }

    public function update(Request $request, Company $company)
    {
        $this->requireSuperAdmin();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'address' => 'nullable|string|max:500',
            'package_id' => 'nullable|exists:subscription_packages,id',
            'subscription_expires_at' => 'nullable|date',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $company->update($validated);

        ActivityLogger::log('company.update', 'Perusahaan', 'Memperbarui perusahaan "' . $company->name . '"');

        return back()->with('success', 'Perusahaan berhasil diperbarui! ✅');
    }

    public function destroy(Company $company)
    {
        $this->requireSuperAdmin();
        $company->delete();

        ActivityLogger::log('company.delete', 'Perusahaan', 'Menghapus perusahaan "' . $company->name . '"');

        return back()->with('success', 'Perusahaan berhasil dihapus!');
    }

    /**
     * Generate kode token aktivasi (untuk customer yang trial-nya habis).
     * Token dikasih ke customer — mereka memasukkannya di halaman kunci
     * + memilih paket berbayar → akun aktif kembali.
     */
    public function generateToken(Company $company)
    {
        $this->requireSuperAdmin();

        do {
            $token = strtoupper(\Illuminate\Support\Str::random(8));
        } while (Company::where('activation_token', $token)->exists());

        $company->update(['activation_token' => $token]);

        ActivityLogger::log('company.token', 'Perusahaan',
            'Membuat token aktivasi untuk "' . $company->name . '" (' . $token . ')');

        return back()->with('success',
            '🔑 Token aktivasi untuk "' . $company->name . '": ' . $token .
            ' — Sampaikan kode ini ke pemilik perusahaan (berlaku sekali pakai).');
    }

    /** Kunci / buka akses perusahaan secara manual. */
    public function toggle(Company $company)
    {
        $this->requireSuperAdmin();

        $company->update(['is_active' => !$company->is_active]);

        ActivityLogger::log('company.toggle', 'Perusahaan',
            ($company->is_active ? 'MEMBUKA' : 'MENGUNCI') . ' akses perusahaan "' . $company->name . '"');

        return back()->with('success',
            'Perusahaan "' . $company->name . '" berhasil di-' . ($company->is_active ? 'buka' : 'kunci') . '.');
    }

    private function requireSuperAdmin(): void
    {
        abort_unless(auth()->user()?->canManageCompanies(), 403, 'Akses khusus Super Admin.');
    }
}
