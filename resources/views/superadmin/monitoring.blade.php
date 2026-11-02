@extends('layouts.app')
@section('title', 'Monitoring Tenant')

@section('content')

<div class="mb-4">
    <h3 class="fw-bold mb-1">🛰️ Monitoring Tenant</h3>
    <p class="text-muted mb-0">Ringkasan seluruh perusahaan & aktivitas platform.</p>
</div>

<!-- Statistik Global -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card card-stat p-3 h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div><p class="text-muted mb-1 small">Total Perusahaan</p><h3 class="fw-bold mb-0">{{ $totalCompanies }}</h3></div>
                <div class="icon-box" style="background: var(--grad-brand);"><i class="bi bi-building"></i></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stat p-3 h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div><p class="text-muted mb-1 small">Langganan Aktif</p><h3 class="fw-bold mb-0 text-success">{{ $activeCompanies }}</h3></div>
                <div class="icon-box" style="background: var(--grad-success);"><i class="bi bi-check-circle"></i></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stat p-3 h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div><p class="text-muted mb-1 small">Total User</p><h3 class="fw-bold mb-0">{{ $totalUsers }}</h3></div>
                <div class="icon-box" style="background: var(--grad-warning);"><i class="bi bi-people"></i></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stat p-3 h-100">
            <div class="d-flex justify-content-between align-items-center">
                <div><p class="text-muted mb-1 small">Total Invoice</p><h3 class="fw-bold mb-0">{{ $totalInvoices }}</h3></div>
                <div class="icon-box" style="background: var(--grad-danger);"><i class="bi bi-file-text"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card card-stat p-3 h-100">
            <p class="text-muted mb-1 small">Total Pendapatan Platform (invoice lunas)</p>
            <h3 class="fw-bold text-success mb-0">Rp {{ number_format($totalPaid, 0, ',', '.') }}</h3>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card card-stat p-3 h-100">
            <p class="text-muted mb-1 small">Pembayaran Menunggu Verifikasi</p>
            <h3 class="fw-bold text-warning mb-0">{{ $totalPendingPayments }} transaksi</h3>
        </div>
    </div>
</div>

<!-- Statistik per Perusahaan -->
<div class="table-container p-4 mb-4">
    <div class="panel-head">
        <h5 class="panel-title"><i class="bi bi-bar-chart-steps"></i> Performa Per Perusahaan</h5>
        <a href="{{ route('companies.index') }}" class="small fw-semibold text-decoration-none">Kelola Perusahaan <i class="bi bi-arrow-right"></i></a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 responsive-card-table">
            <thead>
                <tr><th>Perusahaan</th><th>Paket</th><th class="text-center">User</th><th class="text-center">Invoice</th><th class="text-center">Lunas</th><th class="text-end">Pendapatan</th></tr>
            </thead>
            <tbody>
                @foreach($companies as $c)
                <tr>
                    <td class="fw-semibold" data-label="Perusahaan">
                        {{ $c->name }}
                        <div><small class="text-muted">{{ $c->email }}</small></div>
                    </td>
                    <td data-label="Paket"><span class="badge bg-primary-subtle text-primary">{{ $c->package?->name ?? 'Tanpa Paket' }}</span></td>
                    <td class="text-center" data-label="User">{{ $c->users_count }}</td>
                    <td class="text-center" data-label="Invoice">{{ $c->invoices_count }}</td>
                    <td class="text-center text-success" data-label="Lunas">{{ $c->paid_count }}</td>
                    <td class="text-end fw-semibold" data-label="Pendapatan">Rp {{ number_format($c->revenue, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Distribusi Paket -->
<div class="table-container p-4">
    <h5 class="panel-title mb-3"><i class="bi bi-box-seam"></i> Distribusi Paket Subscription</h5>
    <div class="row g-3">
        @foreach($packages as $pkg)
        <div class="col-md-3 col-6">
            <div class="sum-box p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-bold">{{ $pkg->name }}</div>
                        <small class="text-muted">{{ $pkg->formattedMonthly() }}</small>
                    </div>
                    <h4 class="mb-0 text-primary fw-bold">{{ $pkg->companies_count }}</h4>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

@endsection
