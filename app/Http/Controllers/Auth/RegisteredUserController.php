<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\BusinessProfile;
use App\Models\Client;
use App\Models\Company;
use App\Models\SubscriptionPackage;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Services\ActivityLogger;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        // Paket aktif untuk pilihan tenant (perusahaan pembuat invoice)
        $packages = SubscriptionPackage::where('is_active', true)->orderBy('price_monthly')->get();

        return view('auth.register', compact('packages'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'account_type'  => ['required', 'in:company,customer'],
            'name'          => ['required', 'string', 'max:255'],
            'email'         => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password'      => ['required', 'confirmed', Rules\Password::defaults()],
            // Khusus perusahaan
            'business_name' => ['required_if:account_type,company', 'nullable', 'string', 'max:255'],
            'package_id'    => ['required_if:account_type,company', 'nullable', 'integer', 'exists:subscription_packages,id'],
        ], [
            'account_type.required'  => 'Pilih jenis akun terlebih dahulu.',
            'business_name.required_if' => 'Nama perusahaan/bisnis wajib diisi.',
            'package_id.required_if'    => 'Silakan pilih paket langganan.',
        ]);

        $isCompany = $validated['account_type'] === 'company';
        $company = null;

        // ===== 1. Buat Perusahaan + langganan trial =====
        if ($isCompany) {
            $package = SubscriptionPackage::where('is_active', true)
                ->findOrFail($validated['package_id']);

            // Trial 15 hari untuk paket trial; paket berbayar: durasi paket (lakukan pembayaran ke super admin)
            $days = $package->trial_days > 0 ? $package->trial_days : $package->duration_days;

            $company = Company::create([
                'name'                    => $validated['business_name'],
                'email'                   => $validated['email'],
                'package_id'              => $package->id,
                'subscription_expires_at' => now()->addDays($days),
                'is_active'               => true,
            ]);
        }

        // ===== 2. Buat user =====
        $user = User::create([
            'name'       => $validated['name'],
            'email'      => $validated['email'],
            'password'   => Hash::make($validated['password']),
            'role'       => $isCompany ? User::ROLE_ADMIN : User::ROLE_KLIEN,
            'status'     => 'pending',
            'company_id' => $company?->id,
        ]);

        event(new Registered($user));

        // ===== 3. Data pendukung =====
        if ($isCompany) {
            // Profil bisnis untuk invoice & branding
            BusinessProfile::create([
                'user_id'       => $user->id,
                'business_name' => $validated['business_name'],
                'email'         => $validated['email'],
            ]);

            ActivityLogger::log('register', 'Auth',
                'Pendaftaran TENANT baru: ' . $company->name . ' (paket ' . $company->package->name .
                ', s/d ' . $company->subscription_expires_at->format('d/m/Y') . ') — menunggu persetujuan');
        } else {
            Client::create([
                'user_id'       => $user->id,
                'linked_user_id'=> $user->id,
                'name'          => $user->name,
                'email'         => $user->email,
            ]);

            ActivityLogger::log('register', 'Auth',
                'Pendaftaran CUSTOMER baru: ' . $user->name . ' (' . $user->email . ') — menunggu persetujuan');
        }

        $msg = $isCompany
            ? 'Pendaftaran perusahaan berhasil! Paket Anda: ' . $company->package->name .
              ' (trial hingga ' . $company->subscription_expires_at->format('d/m/Y') .
              '). Akun menunggu persetujuan admin sebelum bisa login.'
            : 'Pendaftaran berhasil! Akun Anda sedang menunggu persetujuan admin. Anda akan dapat login setelah disetujui.';

        return redirect()->route('login')->with('success', $msg);
    }
}
