@extends('layouts.app')
@section('title', 'Detail Klien')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold mb-0">👤 {{ $client->name }}</h3>
    <div class="d-flex gap-2">
        <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
        <a href="{{ route('clients.edit', $client) }}" class="btn btn-outline-warning">
            <i class="bi bi-pencil"></i> Edit
        </a>
        <a href="{{ route('invoices.create') }}?client_id={{ $client->id }}" class="btn text-white" style="background:#4f46e5;">
            <i class="bi bi-plus-circle"></i> Buat Invoice
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Info Klien -->
    <div class="col-lg-4">
        <div class="card card-stat p-4">
            <h6 class="fw-bold mb-3"><i class="bi bi-person-badge me-2"></i>Informasi Klien</h6>
            <table class="table table-sm mb-0">
                <tr>
                    <td class="text-muted" width="100">Nama</td>
                    <td class="fw-semibold">{{ $client->name }}</td>
                </tr>
                <tr>
                    <td class="text-muted">Email</td>
                    <td>{{ $client->email }}</td>
                </tr>
                @if($client->phone)
                <tr>
                    <td class="text-muted">No. HP</td>
                    <td>{{ $client->phone }}</td>
                </tr>
                @endif
                @if($client->company)
                <tr>
                    <td class="text-muted">Perusahaan</td>
                    <td>{{ $client->company }}</td>
                </tr>
                @endif
                @if($client->address)
                <tr>
                    <td class="text-muted">Alamat</td>
                    <td>{{ $client->address }}</td>
                </tr>
                @endif
                <tr>
                    <td class="text-muted">Terdaftar</td>
                    <td>{{ $client->created_at->format('d M Y') }}</td>
                </tr>
            </table>

            @if($client->linkedUser)
            <div class="bg-info bg-opacity-10 rounded-3 p-2 mt-3">
                <small class="text-muted"><i class="bi bi-link-45deg"></i> Terhubung ke Akun:</small>
                <p class="mb-0 small fw-semibold">{{ $client->linkedUser->name }} · {{ $client->linkedUser->email }}
                    <span class="badge bg-info ms-1">Klien</span>
                    @if($client->linkedUser->isPending()) <span class="badge bg-warning">Pending</span> @endif
                </p>
            </div>
            @endif

            @if($client->notes)
            <div class="bg-light rounded-3 p-2 mt-3">
                <small class="text-muted">Catatan:</small>
                <p class="mb-0 small">{{ $client->notes }}</p>
            </div>
            @endif
        </div>

        <!-- Ringkasan -->
        <div class="card card-stat p-4 mt-3">
            <h6 class="fw-bold mb-3"><i class="bi bi-bar-chart me-2"></i>Ringkasan Invoice</h6>
            @php
                $totalPaid = $client->invoices()->paid()->count();
                $totalUnpaid = $client->invoices()->unpaid()->count();
                $totalOverdue = $client->invoices()->overdue()->count();
                $totalAmount = $client->invoices()->sum('total');
                $unpaidAmount = $client->invoices()->where('is_paid', false)->sum('total');
            @endphp
            <div class="d-flex justify-content-between mb-2">
                <span>Total Invoice</span>
                <span class="fw-bold">{{ $client->invoices_count }}</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span><span class="badge bg-success">Lunas</span></span>
                <span>{{ $totalPaid }}</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span><span class="badge bg-secondary">Belum Dibayar</span></span>
                <span>{{ $totalUnpaid }}</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span><span class="badge bg-danger">Lewat Jatuh Tempo</span></span>
                <span>{{ $totalOverdue }}</span>
            </div>
            <hr>
            <div class="d-flex justify-content-between mb-1">
                <span class="fw-bold">Total Nominal</span>
                <span class="fw-bold">Rp {{ number_format($totalAmount, 0, ',', '.') }}</span>
            </div>
            <div class="d-flex justify-content-between">
                <span class="text-danger">Belum Dibayar</span>
                <span class="text-danger fw-semibold">Rp {{ number_format($unpaidAmount, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <!-- Riwayat Invoice -->
    <div class="col-lg-8">
        <div class="card card-stat p-4">
            <h6 class="fw-bold mb-3"><i class="bi bi-clock-history me-2"></i>Riwayat Invoice</h6>

            @if($invoices->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0 responsive-card-table">
                    <thead>
                        <tr>
                            <th>No. Invoice</th>
                            <th>Jatuh Tempo</th>
                            <th class="text-end">Total</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoices as $inv)
                        <tr>
                            <td class="fw-semibold" data-label="No. Invoice">{{ $inv->invoice_number }}</td>
                            <td data-label="Jatuh Tempo">{{ $inv->due_date->format('d M Y') }}</td>
                            <td class="text-end" data-label="Total">Rp {{ number_format($inv->total, 0, ',', '.') }}</td>
                            <td data-label="Status">
                                <span class="badge badge-status bg-{{ $inv->status_color }}">
                                    {{ $inv->status_label }}
                                </span>
                            </td>
                            <td class="text-end actions-cell" data-label="Aksi">
                                <a href="{{ route('invoices.show', $inv) }}" class="btn btn-sm btn-outline-info" title="Lihat">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $invoices->links() }}
            </div>
            @else
            <div class="empty-state">
                <i class="bi bi-file-text"></i>
                <h5>Belum ada invoice</h5>
                <p class="text-muted">Klien ini belum memiliki invoice.</p>
                <a href="{{ route('invoices.create') }}?client_id={{ $client->id }}" class="btn text-white" style="background:#4f46e5;">
                    <i class="bi bi-plus-circle"></i> Buat Invoice Pertama
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

@endsection
