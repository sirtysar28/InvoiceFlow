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
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

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
            return redirect()->intended(RouteServiceProvider::HOME);
        }

        return back()->withErrors([
            'email' => 'Email atau password tidak sesuai.',
        ])->onlyInput('email');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
