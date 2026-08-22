@extends('layouts.app')
@section('title', 'Master Data Klien')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold mb-0">👥 Master Data Klien</h3>
    <a href="{{ route('clients.create') }}" class="btn text-white" style="background:#4f46e5;">
        <i class="bi bi-plus-lg"></i> Tambah Klien
    </a>
</div>

<!-- Search -->
<div class="card card-stat p-3 mb-3">
    <form method="GET" action="{{ route('clients.index') }}" class="row g-2 align-items-center">
        <div class="col-md-8">
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control" placeholder="Cari nama, email, atau perusahaan..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-md-4 d-flex gap-2">
            <button type="submit" class="btn text-white" style="background:#4f46e5;"><i class="bi bi-search"></i> Cari</button>
            @if(request('search'))
            <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary">Reset</a>
            @endif
        </div>
    </form>
</div>

<!-- Client List -->
<div class="card card-stat p-0">
    <div class="table-responsive">
        <table class="table table-hover mb-0 responsive-card-table">
            <thead>
                <tr>
                    <th>Nama Klien</th>
                    <th>Email</th>
                    <th>Perusahaan</th>
                    <th>No. HP</th>
                    <th class="text-center">Total Invoice</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clients as $client)
                <tr>
                    <td data-label="Nama Klien">
                        <a href="{{ route('clients.show', $client) }}" class="fw-semibold text-decoration-none">
                            {{ $client->name }}
                        </a>
                        @if($client->linked_user_id)
                            <small class="text-muted"><i class="bi bi-person-check"></i></small>
                        @endif
                    </td>
                    <td data-label="Email">{{ $client->email }}</td>
                    <td data-label="Perusahaan">{{ $client->company ?: '-' }}</td>
                    <td data-label="No. HP">{{ $client->phone ?: '-' }}</td>
                    <td class="text-center" data-label="Total Invoice">
                        <span class="badge bg-primary">{{ $client->invoices_count }}</span>
                    </td>
                    <td class="text-end actions-cell" data-label="Aksi">
                        <div class="responsive-actions">
                            <a href="{{ route('clients.show', $client) }}" class="btn btn-sm btn-outline-info" title="Detail">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('clients.edit', $client) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="{{ route('invoices.create') }}?client_id={{ $client->id }}" class="btn btn-sm btn-outline-success" title="Buat Invoice">
                                <i class="bi bi-plus-circle"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <i class="bi bi-people"></i>
                            <h5>Belum ada data klien</h5>
                            <p class="text-muted">Tambahkan klien untuk mempermudah pembuatan invoice.</p>
                            <a href="{{ route('clients.create') }}" class="btn text-white" style="background:#4f46e5;">
                                <i class="bi bi-plus-lg"></i> Tambah Klien Pertama
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3">
        {{ $clients->links() }}
    </div>
</div>

@endsection
