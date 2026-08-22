@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold mb-0">Dashboard</h3>
    @if(!auth()->user()?->isKlien())
    <a href="{{ route('invoices.create') }}" class="btn text-white" style="background:#4f46e5;">
        <i class="bi bi-plus-lg"></i> Buat Invoice
    </a>
    @endif
</div>

<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card card-stat p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="text-muted mb-1 small">Total Invoice</p>
                    <h3 class="fw-bold mb-0">{{ $totalInvoices }}</h3>
                </div>
                <div class="icon-box" style="background: #4f46e5;"><i class="bi bi-file-text"></i></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stat p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="text-muted mb-1 small">Lunas</p>
                    <h3 class="fw-bold mb-0">{{ $paidInvoices }}</h3>
                </div>
                <div class="icon-box" style="background: #22c55e;"><i class="bi bi-check-circle"></i></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stat p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="text-muted mb-1 small">Belum Dibayar</p>
                    <h3 class="fw-bold mb-0">{{ $unpaidInvoices }}</h3>
                </div>
                <div class="icon-box" style="background: #f59e0b;"><i class="bi bi-clock"></i></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card card-stat p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="text-muted mb-1 small">Lewat Jatuh Tempo</p>
                    <h3 class="fw-bold mb-0">{{ $overdueInvoices }}</h3>
                </div>
                <div class="icon-box" style="background: #ef4444;"><i class="bi bi-exclamation-triangle"></i></div>
            </div>
        </div>
    </div>
</div>

<!-- Financial Summary -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card card-stat p-3 text-center">
            <p class="text-muted mb-1">Total Tagihan</p>
            <h4 class="fw-bold">Rp {{ number_format($totalAmount, 0, ',', '.') }}</h4>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-stat p-3 text-center">
            <p class="text-muted mb-1">Sudah Dibayar</p>
            <h4 class="fw-bold text-success">Rp {{ number_format($paidAmount, 0, ',', '.') }}</h4>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-stat p-3 text-center">
            <p class="text-muted mb-1">Belum Dibayar</p>
            <h4 class="fw-bold text-danger">Rp {{ number_format($unpaidAmount, 0, ',', '.') }}</h4>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Invoices -->
    <div class="col-lg-6 mb-4">
        <div class="table-container p-3">
            <h5 class="fw-bold mb-3"><i class="bi bi-clock-history text-muted me-2"></i>Invoice Terbaru</h5>
            @if($recentInvoices->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0 responsive-card-table">
                        <thead><tr><th>No. Invoice</th><th>Klien</th><th>Total</th><th>Status</th></tr></thead>
                        <tbody>
                            @foreach($recentInvoices as $inv)
                            <tr onclick="location.href='{{ route('invoices.show', $inv) }}'" style="cursor:pointer;">
                                <td class="fw-semibold" data-label="No. Invoice">{{ $inv->invoice_number }}</td>
                                <td data-label="Klien">{{ Str::limit($inv->client_name, 20) }}</td>
                                <td data-label="Total">Rp {{ number_format($inv->total, 0, ',', '.') }}</td>
                                <td data-label="Status"><span class="badge badge-status bg-{{ $inv->status_color }}">{{ $inv->status_label }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state py-3"><i class="bi bi-inbox"></i><p>Belum ada invoice</p></div>
            @endif
        </div>
    </div>

    <!-- Upcoming Due -->
    <div class="col-lg-6 mb-4">
        <div class="table-container p-3">
            <h5 class="fw-bold mb-3"><i class="bi bi-calendar-check text-muted me-2"></i>Segera Jatuh Tempo</h5>
            @if($upcomingDue->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0 responsive-card-table">
                        <thead><tr><th>No. Invoice</th><th>Klien</th><th>Jatuh Tempo</th><th>Total</th></tr></thead>
                        <tbody>
                            @foreach($upcomingDue as $inv)
                            <tr onclick="location.href='{{ route('invoices.show', $inv) }}'" style="cursor:pointer;">
                                <td class="fw-semibold" data-label="No. Invoice">{{ $inv->invoice_number }}</td>
                                <td data-label="Klien">{{ Str::limit($inv->client_name, 20) }}</td>
                                <td data-label="Jatuh Tempo">
                                    @if($inv->due_date->isToday())
                                        <span class="text-warning fw-bold">Hari ini!</span>
                                    @else
                                        {{ $inv->due_date->format('d/m/Y') }}
                                        <small class="text-muted">({{ $inv->due_date->diffInDays(now()) }} hari)</small>
                                    @endif
                                </td>
                                <td data-label="Total">Rp {{ number_format($inv->total, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state py-3"><i class="bi bi-check-circle"></i><p>Tidak ada invoice yang segera jatuh tempo</p></div>
            @endif
        </div>
    </div>
</div>

<!-- Monthly Stats -->
<div class="table-container p-4">
    <h5 class="fw-bold mb-3">Statistik Bulanan {{ date('Y') }}</h5>
    <div class="table-responsive">
        <table class="table table-bordered mb-0">
            <thead>
                <tr>
                    @for($m = 1; $m <= 12; $m++)
                        <th class="text-center small">{{ Carbon\Carbon::create()->month($m)->format('M') }}</th>
                    @endfor
                </tr>
            </thead>
            <tbody>
                <tr>
                    @for($m = 1; $m <= 12; $m++)
                        <td class="text-center">
                            @php $stat = $monthlyStats->get($m); @endphp
                            @if($stat)
                                <span class="fw-semibold">{{ $stat->count }}</span>
                                <br><small class="text-muted">Rp {{ number_format($stat->total, 0, ',', '.') }}</small>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    @endfor
                </tr>
            </tbody>
        </table>
    </div>
</div>

@endsection
