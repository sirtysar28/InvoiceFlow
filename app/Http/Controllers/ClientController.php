<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $isAdmin = auth()->user()?->isAdmin();
        $query = Client::query()->withCount('invoices');

        if (!$isAdmin) {
            $query->where('user_id', auth()->id());
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%");
            });
        }

        $clients = $query->latest()->paginate(15)->withQueryString();

        return view('clients.index', compact('clients'));
    }

    public function create()
    {
        return view('clients.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'create_user' => 'nullable|boolean',
            'password' => 'nullable|string|min:8|required_if:create_user,1',
        ]);

        $linkedUserId = null;

        // Optionally create user account for this client
        if ($request->boolean('create_user') && auth()->user()?->isAdmin()) {
            $existingUser = User::where('email', $validated['email'])->first();
            if ($existingUser) {
                $linkedUserId = $existingUser->id;
            } else {
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'role' => User::ROLE_KLIEN,
                    'status' => 'active',
                ]);
                $linkedUserId = $user->id;
            }
        }

        $validated['user_id'] = auth()->id();
        $validated['linked_user_id'] = $linkedUserId;

        $client = Client::create($validated);

        \App\Services\ActivityLogger::log('client.create', 'Klien', 'Menambahkan klien "' . $client->name . '"' . ($linkedUserId ? ' (dengan akun login)' : ''));

        return redirect()->route('clients.index')
            ->with('success', 'Klien berhasil ditambahkan! ' . ($linkedUserId ? 'Akun login juga sudah dibuat.' : ''));
    }

    public function show(Client $client)
    {
        $this->authorizeClient($client);

        $client->load(['linkedUser', 'invoices' => function ($q) {
            $q->latest()->limit(20);
        }]);

        $invoices = $client->invoices()->latest()->paginate(10);

        return view('clients.show', compact('client', 'invoices'));
    }

    public function edit(Client $client)
    {
        $this->authorizeClient($client);
        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        $this->authorizeClient($client);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ]);

        $client->update($validated);

        \App\Services\ActivityLogger::log('client.update', 'Klien', 'Memperbarui klien "' . $client->name . '"');

        return redirect()->route('clients.show', $client)
            ->with('success', 'Data klien berhasil diperbarui! ✅');
    }

    public function destroy(Client $client)
    {
        $this->authorizeClient($client);

        // Cek apakah ada invoice terkait
        if ($client->invoices()->exists()) {
            return back()->with('error', 'Klien tidak bisa dihapus karena masih memiliki invoice. Hapus invoice terkait terlebih dahulu.');
        }

        $client->delete();

        \App\Services\ActivityLogger::log('client.delete', 'Klien', 'Menghapus klien "' . $client->name . '"');

        return redirect()->route('clients.index')
            ->with('success', 'Klien berhasil dihapus!');
    }

    /**
     * API: Cari klien untuk autocomplete di form invoice
     */
    public function search(Request $request)
    {
        $q = $request->get('q', '');
        $isAdmin = auth()->user()?->isAdmin();

        $query = Client::where('name', 'like', "%{$q}%")
            ->orWhere('email', 'like', "%{$q}%")
            ->orWhere('company', 'like', "%{$q}%");

        if (!$isAdmin) {
            $query->where('user_id', auth()->id());
        }

        $clients = $query->limit(10)->get(['id', 'name', 'email', 'phone', 'company']);

        return response()->json($clients);
    }

    private function authorizeClient(Client $client): void
    {
        if (auth()->user()?->isAdmin()) {
            return;
        }

        if ($client->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }
    }
}
