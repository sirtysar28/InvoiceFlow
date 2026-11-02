<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'captcha' => ['required', 'string'],
        ]);

        // Kredensial murni email + password (tanpa captcha,
        // agar Auth::validate bekerja benar)
        $credentials = $request->only('email', 'password');

        // Validasi CAPTCHA (case-insensitive)
        $sessionCaptcha = strtoupper((string) session('login_captcha'));
        if ($sessionCaptcha === '' || strtoupper(trim($request->input('captcha'))) !== $sessionCaptcha) {
            return back()->withErrors([
                'captcha' => 'Kode CAPTCHA tidak sesuai. Silakan coba lagi.',
            ])->onlyInput('email');
        }

        // Cek apakah kredensial valid dulu
        if (Auth::validate($credentials)) {
            $user = \App\Models\User::where('email', $request->email)->first();

            // Cek apakah user pending
            if ($user && $user->isPending()) {
                return back()->withErrors([
                    'email' => 'Akun Anda belum disetujui oleh admin. Silakan tunggu persetujuan.',
                ])->onlyInput('email');
            }

            // Login
            Auth::attempt($credentials, $request->boolean('remember'));
            $request->session()->regenerate();
            $request->session()->forget('login_captcha');

            \App\Services\ActivityLogger::log('login', 'Auth', $user->name . ' berhasil masuk');

            return redirect()->intended(RouteServiceProvider::HOME);
        }

        \App\Services\ActivityLogger::log('login.failed', 'Auth', 'Percobaan login gagal untuk email: ' . $request->email);

        return back()->withErrors([
            'email' => 'Email atau password tidak sesuai.',
        ])->onlyInput('email');
    }

    public function destroy(Request $request): RedirectResponse
    {
        \App\Services\ActivityLogger::log('logout', 'Auth', optional(auth()->user())->name . ' keluar dari aplikasi');

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
