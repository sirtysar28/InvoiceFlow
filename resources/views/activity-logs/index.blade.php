@extends('layouts.app')
@section('title', 'Aktivitas Log')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h3 class="fw-bold mb-0">🕘 Aktivitas Log</h3>
    <span class="badge bg-secondary-subtle text-secondary fs-6">{{ $logs->total() }} aktivitas</span>
</div>

<!-- Filter -->
<form method="GET" class="table-container p-3 mb-4">
    <div class="row g-2 align-items-end">
        <div class="col-md-4 col-6">
            <label class="form-label small fw-semibold">User</label>
            <select name="user_id" class="form-select">
                <option value="">Semua User</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 col-6">
            <label class="form-label small fw-semibold">Jenis Aksi</label>
            <select name="action" class="form-select">
                <option value="">Semua Aksi</option>
                @foreach($actions as $a)
                    <option value="{{ $a }}" {{ request('action') == $a ? 'selected' : '' }}>{{ $a }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4 col-12">
            <label class="form-label small fw-semibold">Cari</label>
            <div class="d-flex gap-2">
                <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="deskripsi / modul...">
                <button class="btn btn-brand text-white"><i class="bi bi-search"></i></button>
            </div>
        </div>
    </div>
</form>

<!-- Log Table -->
<div class="table-container p-3">
    @if($logs->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle responsive-card-table">
            <thead>
                <tr><th>Waktu</th><th>User</th><th>Aksi</th><th>Deskripsi</th><th>IP Address</th></tr>
            </thead>
            <tbody>
                @foreach($logs as $log)
                <tr>
                    <td data-label="Waktu">
                        <div class="fw-semibold">{{ $log->created_at->format('d/m/Y') }}</div>
                        <small class="text-muted">{{ $log->created_at->format('H:i:s') }}</small>
                    </td>
                    <td data-label="User">
                        @if($log->user)
                            <div class="d-flex align-items-center gap-2">
                                <span class="avatar-initial">{{ strtoupper(substr($log->user->name, 0, 1)) }}</span>
                                <div>
                                    <div class="fw-semibold" style="font-size:.9rem;">{{ $log->user->name }}</div>
                                    <small class="text-muted">{{ $log->user->role }}</small>
                                </div>
                            </div>
                        @else
                            <span class="text-muted">System</span>
                        @endif
                    </td>
                    <td data-label="Aksi">
                        <span class="badge badge-status bg-{{ $log->color }}">
                            <i class="bi {{ $log->icon }}"></i> {{ $log->action }}
                        </span>
                    </td>
                    <td data-label="Deskripsi">
                        <span class="fw-semibold" style="font-size:.85rem;">{{ $log->module }}</span> —
                        {{ $log->description }}
                    </td>
                    <td data-label="IP"><small class="text-muted">{{ $log->ip_address }}</small></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="p-3 d-flex justify-content-center">
        {{ $logs->links() }}
    </div>
    @else
    <div class="empty-state">
        <i class="bi bi-clock-history"></i>
        <h5>Belum ada aktivitas</h5>
        <p>Aktivitas user akan terekam otomatis di sini.</p>
    </div>
    @endif
</div>

@endsection
