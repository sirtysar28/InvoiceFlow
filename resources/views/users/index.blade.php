@extends('layouts.app')
@section('title', 'Master User')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold mb-0">👤 Master Data User</h3>
    <a href="{{ route('users.create') }}" class="btn text-white" style="background:#4f46e5;">
        <i class="bi bi-plus-lg"></i> Tambah User
    </a>
</div>

<!-- Filter -->
<div class="card card-stat p-3 mb-3">
    <form method="GET" action="{{ route('users.index') }}" class="row g-2 align-items-center">
        <div class="col-md-4">
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control" placeholder="Cari nama atau email..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-md-2">
            <select name="role" class="form-select">
                <option value="">Semua Role</option>
                @foreach($roles as $key => $label)
                <option value="{{ $key }}" {{ request('role') == $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select">
                <option value="">Semua Status</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
            </select>
        </div>
        <div class="col-md-4 d-flex gap-2">
            <button type="submit" class="btn text-white" style="background:#4f46e5;"><i class="bi bi-funnel"></i> Filter</button>
            @if(request('search') || request('role') || request('status'))
            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Reset</a>
            @endif
        </div>
    </form>
</div>

<div class="card card-stat p-0">
    <div class="table-responsive">
        <table class="table table-hover mb-0 responsive-card-table">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Role</th>
                    @if(auth()->user()->isSuperAdmin())<th>Perusahaan</th>@endif
                    <th>Status</th>
                    <th class="text-center">Invoice</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $u)
                <tr>
                    <td class="fw-semibold" data-label="Nama">{{ $u->name }}</td>
                    <td data-label="Email">{{ $u->email }}</td>
                    <td data-label="Role">
                        <span class="badge bg-{{ $u->roleColor() }}" {{ $u->isSuperAdmin() ? 'style="background:#0f172a !important;"' : '' }}>
                            {{ $u->isSuperAdmin() ? '🛡️ ' : '' }}{{ $u->roleLabel() }}
                        </span>
                    </td>
                    @if(auth()->user()->isSuperAdmin())
                    <td data-label="Perusahaan"><small>{{ $u->company?->name ?? '—' }}</small></td>
                    @endif
                    <td data-label="Status">
                        @if($u->isPending())
                            <span class="badge bg-secondary">Pending</span>
                            <div class="mt-1 d-flex gap-1">
                                <form action="{{ route('users.approve', $u) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-success" title="Setujui">
                                        <i class="bi bi-check"></i>
                                    </button>
                                </form>
                                <form action="{{ route('users.reject', $u) }}" method="POST" class="d-inline" onsubmit="return confirm('Tolak akun {{ $u->name }}?')">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-danger" title="Tolak">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </form>
                            </div>
                        @elseif($u->status === 'rejected')
                            <span class="badge bg-danger">Ditolak</span>
                        @else
                            <span class="badge bg-success">Aktif</span>
                        @endif
                    </td>
                    <td class="text-center" data-label="Invoice">{{ $u->invoices_count }}</td>
                    <td class="text-end actions-cell" data-label="Aksi">
                        <div class="responsive-actions">
                            {{-- Akun Super Admin terkunci: hanya bisa diubah Super Admin --}}
                            @if($u->isSuperAdmin() && !auth()->user()->isSuperAdmin())
                                <span class="badge bg-dark"><i class="bi bi-lock-fill"></i> Terkunci</span>
                            @else
                                <a href="{{ route('users.edit', $u) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @if($u->id !== auth()->id())
                                <form action="{{ route('users.destroy', $u) }}" method="POST" onsubmit="return confirm('Yakin hapus user {{ $u->name }}?')" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endif
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ auth()->user()->isSuperAdmin() ? 7 : 6 }}">
                        <div class="empty-state">
                            <i class="bi bi-person-x"></i>
                            <h5>Tidak ada data user</h5>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $users->links() }}</div>
</div>

@endsection
