<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPackage;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PackageController extends Controller
{
    public function index()
    {
        $this->requireSuperAdmin();
        $packages = SubscriptionPackage::withCount('companies')->orderBy('price_monthly')->get();

        return view('superadmin.packages.index', compact('packages'));
    }

    public function store(Request $request)
    {
        $this->requireSuperAdmin();
        $validated = $this->validated($request);

        SubscriptionPackage::create($validated + ['slug' => Str::slug($validated['name']) . '-' . substr(uniqid(), -5)]);

        ActivityLogger::log('package.create', 'Paket Subscription', 'Menambahkan paket "' . $validated['name'] . '"');

        return back()->with('success', 'Paket berhasil ditambahkan! ✅');
    }

    public function update(Request $request, SubscriptionPackage $package)
    {
        $this->requireSuperAdmin();
        $validated = $this->validated($request);

        $package->update($validated);

        ActivityLogger::log('package.update', 'Paket Subscription', 'Memperbarui paket "' . $package->name . '"');

        return back()->with('success', 'Paket berhasil diperbarui! ✅');
    }

    public function destroy(SubscriptionPackage $package)
    {
        $this->requireSuperAdmin();
        $package->delete();

        ActivityLogger::log('package.delete', 'Paket Subscription', 'Menghapus paket "' . $package->name . '"');

        return back()->with('success', 'Paket berhasil dihapus!');
    }

    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'price_monthly' => 'required|numeric|min:0',
            'price_yearly' => 'nullable|numeric|min:0',
            'max_users' => 'required|integer|min:1',
            'max_invoices' => 'required|integer|min:1',
            'max_clients' => 'required|integer|min:1',
            'features' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
        ]);

        // Fitur dipisah baris / koma → array
        $validated['features'] = array_values(array_filter(
            preg_split('/\r\n|\r|\n|,/', (string) $validated['features'])
        ));

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['price_yearly'] = $validated['price_yearly'] ?? 0;

        return $validated;
    }

    private function requireSuperAdmin(): void
    {
        abort_unless(auth()->user()?->canManageCompanies(), 403, 'Akses khusus Super Admin.');
    }
}
