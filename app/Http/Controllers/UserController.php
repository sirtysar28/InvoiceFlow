<?php

namespace App\Http\Controllers;

use App\Models\Company;
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

    /* ================= HELPERS ISOLASI MULTI-TENANT ================= */

    private function isSuperAdmin(): bool
    {
        return (bool) auth()->user()?->isSuperAdmin();
    }

    /**
     * Batasi query user sesuai hak akses:
     * - Super Admin : SEMUA user (seluruh tenant)
     * - Admin       : hanya user perusahaanNYA + customer yang dia kelola + dirinya.
     *                 Akun Super Admin TIDAK PERNAH terlihat.
     */
    private function scopeUsers($query)
    {
        $me = auth()->user();
        if ($this->isSuperAdmin()) {
            return $query;
        }

        $clientUserIds = Client::where('user_id', $me->id)->pluck('linked_user_id');

        return $query
            ->where('role', '!=', User::ROLE_SUPERADMIN)
            ->where(function ($q) use ($me, $clientUserIds) {
                $q->where('company_id', $me->company_id)      // staff satu perusahaan
                  ->orWhereIn('id', $clientUserIds)            // customer milik saya
                  ->orWhere('id', $me->id);                    // akun sendiri
            });
    }

    /** Pastikan user target boleh dikelola oleh user yang login. */
    private function authorizeTarget(User $user): void
    {
        if ($this->isSuperAdmin()) {
            return;
        }

        if ($user->isSuperAdmin()) {
            abort(403, 'Akun Super Admin terkunci — hanya Super Admin yang bisa mengelolanya.');
        }

        $me = auth()->user();
        $allowed = $user->company_id === $me->company_id
            || Client::where('user_id', $me->id)->where('linked_user_id', $user->id)->exists()
            || $user->id === $me->id;

        abort_unless($allowed, 403, 'User ini bukan bagian dari perusahaan Anda.');
    }

    /** Daftar role yang boleh dipilih (Super Admin dihapus utk non-SA). */
    private function assignableRoles(): array
    {
        $roles = User::roles();
        if (!$this->isSuperAdmin()) {
            unset($roles[User::ROLE_SUPERADMIN]);
        }
        return $roles;
    }

    /* ================= CRUD ================= */

    public function index(Request $request)
    {
        $query = $this->scopeUsers(User::withCount('invoices')->with('company'));

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
        $roles = $this->assignableRoles();

        return view('users.index', compact('users', 'roles'));
    }

    public function create()
    {
        $roles = $this->assignableRoles();
        $companies = $this->isSuperAdmin() ? Company::orderBy('name')->get() : collect();

        return view('users.create', compact('roles', 'companies'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => ['required', 'in:superadmin,admin,finance,sales,klien,karyawan', function ($attribute, $value, $fail) {
                if ($value === 'superadmin' && !auth()->user()->isSuperAdmin()) {
                    $fail('Role Super Admin terkunci — hanya Super Admin yang bisa menetapkannya.');
                }
            }],
            'company_id' => ['nullable', 'integer', 'exists:companies,id', function ($attribute, $value, $fail) {
                if ($value && !auth()->user()->isSuperAdmin()) {
                    $fail('Hanya Super Admin yang bisa menetapkan perusahaan.');
                }
            }],
        ]);

        // Perusahaan user baru:
        // - Super Admin boleh pilih manual
        // - Admin: staff ikut perusahaan dia; klien tanpa perusahaan
        $me = auth()->user();
        $companyId = $this->isSuperAdmin()
            ? ($validated['company_id'] ?? null)
            : (in_array($validated['role'], ['admin', 'finance', 'sales', 'karyawan']) ? $me->company_id : null);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'status' => 'active', // Admin yg buat, langsung active
            'company_id' => $companyId,
        ]);

        // Jika role klien, auto-create Client record
        if ($user->isKlien()) {
            Client::create([
                'user_id' => $me->id,
                'linked_user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]);
        }

        \App\Services\ActivityLogger::log('user.create', 'User', 'Menambahkan user ' . $user->name . ' (' . $user->role . ')');

        return redirect()->route('users.index')
            ->with('success', 'User berhasil ditambahkan!');
    }

    public function edit(User $user)
    {
        $this->authorizeTarget($user);

        $roles = $this->assignableRoles();
        $companies = $this->isSuperAdmin() ? Company::orderBy('name')->get() : collect();

        // Jangan biarkan role saat ini hilang dari opsi (superadmin di-superadmin)
        if (!isset($roles[$user->role])) {
            $roles[$user->role] = $user->roleLabel();
        }

        return view('users.edit', compact('user', 'roles', 'companies'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorizeTarget($user);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => ['required', 'in:superadmin,admin,finance,sales,klien,karyawan', function ($attribute, $value, $fail) use ($user) {
                if ($value === 'superadmin' && !auth()->user()->isSuperAdmin()) {
                    $fail('Role Super Admin terkunci — hanya Super Admin yang bisa menetapkannya.');
                }
                // Admin tidak boleh "menurunkan" akun Super Admin
                if ($user->isSuperAdmin() && $value !== 'superadmin' && !auth()->user()->isSuperAdmin()) {
                    $fail('Akun Super Admin tidak bisa diubah oleh Admin perusahaan.');
                }
            }],
            'company_id' => ['nullable', 'integer', 'exists:companies,id', function ($attribute, $value, $fail) {
                if ($value && !auth()->user()->isSuperAdmin()) {
                    $fail('Hanya Super Admin yang bisa memindahkan perusahaan user.');
                }
            }],
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ];

        if ($validated['password']) {
            $data['password'] = Hash::make($validated['password']);
        }

        // Perusahaan hanya bisa diubah Super Admin
        if ($this->isSuperAdmin()) {
            $data['company_id'] = $validated['company_id'] ?? null;
        }

        $user->update($data);

        \App\Services\ActivityLogger::log('user.update', 'User', 'Memperbarui user ' . $user->name . ' (' . $user->role . ')');

        return redirect()->route('users.index')
            ->with('success', 'User berhasil diperbarui!');
    }

    public function destroy(User $user)
    {
        $this->authorizeTarget($user);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak bisa menghapus akun sendiri.');
        }

        // Reassign invoices ke admin SEPERUSAHAAN sebelum hapus
        if ($user->invoices()->exists()) {
            $admin = User::where('role', 'admin')
                ->where('company_id', $user->company_id)
                ->where('id', '!=', $user->id)
                ->first()
                ?? User::where('role', 'superadmin')->first();
            if ($admin) {
                $user->invoices()->update(['user_id' => $admin->id]);
            }
        }

        $user->delete();

        \App\Services\ActivityLogger::log('user.delete', 'User', 'Menghapus user ' . $user->name);

        return redirect()->route('users.index')
            ->with('success', 'User berhasil dihapus!');
    }

    public function show(User $user)
    {
        $this->authorizeTarget($user);
        return redirect()->route('users.edit', $user);
    }

    public function approve(User $user)
    {
        $this->authorizeTarget($user);

        $user->update(['status' => 'active']);

        // Auto-create Client jika belum ada (klien yg daftar via register)
        if ($user->isKlien() && !Client::where('linked_user_id', $user->id)->exists()) {
            Client::create([
                'user_id' => auth()->id(),
                'linked_user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]);
        }

        \App\Services\ActivityLogger::log('user.approve', 'User', 'Menyetujui pendaftaran akun ' . $user->name);

        return back()->with('success', 'Akun ' . $user->name . ' telah disetujui! ✅');
    }

    public function reject(User $user)
    {
        $this->authorizeTarget($user);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak bisa menolak akun sendiri.');
        }
        $user->update(['status' => 'rejected']);
        \App\Services\ActivityLogger::log('user.reject', 'User', 'Menolak pendaftaran akun ' . $user->name);
        return back()->with('success', 'Akun ' . $user->name . ' telah ditolak.');
    }
}
