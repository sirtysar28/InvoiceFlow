<?php

namespace App\Http\Controllers;

use App\Models\BusinessProfile;
use Illuminate\Http\Request;

class BusinessProfileController extends Controller
{
    public function index()
    {
        $profile = BusinessProfile::current();
        return view('profile.business', compact('profile'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'bank_name' => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_account_name' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('logos', 'public');
            $validated['logo'] = $path;
        }

        $validated['user_id'] = auth()->id();

        $profile = BusinessProfile::updateOrCreate(
            // SaaS: 1 profil per perusahaan (SA = platform)
            ['company_id' => auth()->user()?->company_id],
            $validated
        );

        \App\Services\ActivityLogger::log('profile.update', 'Profil Bisnis', 'Memperbarui profil bisnis "' . $validated['business_name'] . '"');

        return back()->with('success', 'Profil bisnis berhasil disimpan! ✅');
    }
}
