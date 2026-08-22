<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->user()?->isAdmin()) {
                abort(403, 'Hanya Admin yang bisa mengakses halaman ini.');
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $query = User::withCount('invoices');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $users = $query->latest()->paginate(15)->withQueryString();
        $roles = User::roles();

        return view('users.index', compact('users', 'roles'));
    }

    public function create()
    {
        $roles = User::roles();
        return view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,karyawan,klien',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'status' => 'active', // Admin yg buat, langsung active
        ]);

        // Jika role klien, auto-create Client record
        if ($user->isKlien()) {
            Client::create([
                'user_id' => auth()->id(),
                'linked_user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]);
        }

        return redirect()->route('users.index')
            ->with('success', 'User berhasil ditambahkan!');
    }

    public function edit(User $user)
    {
        $roles = User::roles();
        return view('users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|in:admin,karyawan,klien',
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ];

        if ($validated['password']) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        return redirect()->route('users.index')
            ->with('success', 'User berhasil diperbarui!');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak bisa menghapus akun sendiri.');
        }

        // Reassign invoices ke admin sebelum hapus
        if ($user->invoices()->exists()) {
            $admin = User::where('role', 'admin')->first();
            if ($admin) {
                $user->invoices()->update(['user_id' => $admin->id]);
            }
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User berhasil dihapus!');
    }

    public function show(User $user)
    {
        return redirect()->route('users.edit', $user);
    }

    public function approve(User $user)
    {
        $user->update(['status' => 'active']);

        // Auto-create Client jika belum ada (klien yg daftar via register)
        if ($user->isKlien() && !Client::where('linked_user_id', $user->id)->exists()) {
            Client::create([
                'user_id' => $user->id,
                'linked_user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]);
        }

        return back()->with('success', 'Akun ' . $user->name . ' telah disetujui! ✅');
    }

    public function reject(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak bisa menolak akun sendiri.');
        }
        $user->update(['status' => 'rejected']);
        return back()->with('success', 'Akun ' . $user->name . ' telah ditolak.');
    }
}
