@extends('layouts.app')
@section('title', 'Dashboard')

@push('styles')
<style>
    /* ===== Hero Banner ===== */
    .dash-hero {
        position: relative;
        overflow: hidden;
        border-radius: 20px;
        background: linear-gradient(120deg, #4338ca 0%, #6366f1 48%, #8b5cf6 100%);
        color: #fff;
        padding: 30px 32px;
        margin-bottom: 24px;
        box-shadow: 0 16px 40px -12px rgba(79, 70, 229, .5);
    }
    .dash-hero::before,
    .dash-hero::after {
        content: '';
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, .09);
    }
    .dash-hero::before { width: 300px; height: 300px; top: -140px; right: -80px; }
    .dash-hero::after  { width: 180px; height: 180px; bottom: -100px; right: 140px; }
    .dash-hero .hero-greet {
        font-family: 'Sora', 'Inter', sans-serif;
        font-size: clamp(1.4rem, 2.4vw, 1.9rem);
        font-weight: 700;
        letter-spacing: -0.02em;
        margin-bottom: 6px;
    }
    .dash-hero .hero-sub { color: rgba(255, 255, 255, .78); font-size: .92rem; margin: 0; }
    .dash-hero .btn-light {
        border-radius: 12px;
        font-weight: 600;
        padding: .6rem 1.3rem;
        box-shadow: 0 10px 24px -8px rgba(0, 0, 0, .35);
        position: relative;
        z-index: 1;
    }

    /* ===== Stat Cards (Clickable) ===== */
    .stat-card {
        display: flex;
        align-items: center;
        gap: 16px;
        background: #fff;
        border: 1px solid rgba(226, 232, 240, .9);
        border-radius: 18px;
        padding: 22px;
        text-decoration: none;
        color: inherit;
        box-shadow: 0 1px 3px rgba(15, 23, 42, .06);
        transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
        position: relative;
        overflow: hidden;
        height: 100%;
    }
    .stat-card::after {
        content: '';
        position: absolute;
        inset: 0 0 auto 0;
        height: 4px;
        opacity: 0;
        transition: opacity .25s ease;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 14px 32px -10px rgba(15, 23, 42, .18);
        color: inherit;
    }
    .stat-card:hover::after { opacity: 1; }
    .stat-card .stat-icon {
        flex: 0 0 52px;
        width: 52px;
        height: 52px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 23px;
        color: #fff;
        box-shadow: 0 8px 18px -6px rgba(15, 23, 42, .28);
    }
    .stat-card .stat-label {
        display: block;
        font-size: .78rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #94a3b8;
        margin-bottom: 4px;
    }
    .stat-card .stat-value {
        font-family: 'Sora', 'Inter', sans-serif;
        font-size: 1.75rem;
        font-weight: 700;
        line-height: 1.1;
        color: #0f172a;
    }
    .stat-card .stat-arrow {
        margin-left: auto;
        color: #cbd5e1;
        font-size: 1.1rem;
        transition: transform .25s ease, color .25s ease;
    }
    .stat-card:hover .stat-arrow { transform: translateX(4px); color: var(--primary); }
    .stat-card.accent-brand::after  { background: var(--grad-brand); }
    .stat-card.accent-success::after { background: var(--grad-success); }
    .stat-card.accent-warning::after { background: var(--grad-warning); }
    .stat-card.accent-danger::after  { background: var(--grad-danger); }

    /* ===== Collection Progress ===== */
    .collect-card {
        background: #fff;
        border: 1px solid rgba(226, 232, 240, .9);
        border-radius: 18px;
        padding: 26px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, .06);
        height: 100%;
    }
    .collect-card .collect-title {
        font-family: 'Sora', 'Inter', sans-serif;
        font-weight: 700;
        font-size: 1.05rem;
        color: #0f172a;
    }
    .collect-rate {
        font-family: 'Sora', 'Inter', sans-serif;
        font-size: 2.4rem;
        font-weight: 800;
        line-height: 1;
    }
    .progress-collect {
        height: 12px;
        border-radius: 20px;
        background: #eef2f7;
        overflow: hidden;
    }
    .progress-collect .bar {
        height: 100%;
        border-radius: 20px;
        background: var(--grad-success);
        transition: width .8s cubic-bezier(.22, 1, .36, 1);
    }
    .amount-chip {
        display: flex;
        flex-direction: column;
        gap: 2px;
        background: #f8fafc;
        border: 1px solid #eef2f7;
        border-radius: 14px;
        padding: 14px 16px;
        flex: 1 1 0;
        min-width: 0;
    }
    .amount-chip .chip-label {
        font-size: .74rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #94a3b8;
    }
    .amount-chip .chip-value {
        font-family: 'Sora', 'Inter', sans-serif;
        font-weight: 700;
        font-size: 1.02rem;
        color: #0f172a;
        overflow-wrap: anywhere;
    }

    /* ===== Panel & Tables ===== */
    .panel-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding-bottom: 14px;
        margin-bottom: 6px;
        border-bottom: 1px solid #eef2f7;
    }
    .panel-title {
        font-family: 'Sora', 'Inter', sans-serif;
        font-weight: 700;
        font-size: 1rem;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0;
    }
    .panel-title i { color: var(--primary); }
    .avatar-initial {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: #eef2ff;
        color: var(--primary);
        font-weight: 700;
        font-size: .8rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 34px;
    }
    .due-pill {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: .74rem;
        font-weight: 600;
        background: #fef3c7;
        color: #b45309;
        white-space: nowrap;
    }
    .due-pill.today { background: #fee2e2; color: #b91c1c; }
    .due-pill.soon  { background: #dcfce7; color: #15803d; }

    /* ===== Monthly Bar Chart ===== */
    .chart-wrap {
        display: flex;
        align-items: flex-end;
        gap: 8px;
        height: 190px;
        padding-top: 8px;
    }
    .chart-col {
        flex: 1 1 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        height: 100%;
        min-width: 0;
    }
    .chart-bar-area {
        flex: 1;
        width: 100%;
        display: flex;
        align-items: flex-end;
        justify-content: center;
    }
    .chart-bar {
        width: min(100%, 30px);
        border-radius: 8px 8px 3px 3px;
        background: var(--grad-brand);
        box-shadow: 0 6px 14px -6px rgba(99, 102, 241, .5);
        transition: filter .2s ease, transform .2s ease;
        animation: growBar .8s cubic-bezier(.22, 1, .36, 1) backwards;
    }
    .chart-col:hover .chart-bar { filter: brightness(1.12); transform: scaleX(1.08); }
    .chart-bar.empty { background: #e8edf4; box-shadow: none; }
    .chart-bar.current {
        background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);
        box-shadow: 0 6px 14px -6px rgba(245, 158, 11, .55);
    }
    .chart-month {
        margin-top: 8px;
        font-size: .7rem;
        font-weight: 600;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: .04em;
    }
    .chart-col:hover .chart-month { color: var(--primary); }
    .chart-legend {
        display: flex;
        gap: 18px;
        flex-wrap: wrap;
        font-size: .78rem;
        color: #64748b;
    }
    .chart-legend .dot {
        display: inline-block;
        width: 10px;
        height: 10px;
        border-radius: 4px;
        margin-right: 6px;
        vertical-align: middle;
    }
    @keyframes growBar { from { height: 0 !important; opacity: 0; } }

    @media (max-width: 575.98px) {
        .dash-hero { padding: 22px 20px; border-radius: 16px; }
        .stat-card { padding: 16px; border-radius: 15px; }
        .stat-card .stat-value { font-size: 1.4rem; }
        .collect-card { padding: 18px; }
        .chart-wrap { gap: 4px; height: 150px; }
        .chart-month { font-size: .6rem; }
    }
</style>
@endpush

@section('content')

@php
    $hour = now()->hour;
    $greeting = $hour < 11 ? 'Selamat pagi' : ($hour < 15 ? 'Selamat siang' : ($hour < 19 ? 'Selamat sore' : 'Selamat malam'));
    $collectRate = $totalAmount > 0 ? round($paidAmount / $totalAmount * 100) : 0;
    $maxMonthly = max(1, (float) ($monthlyStats->max('total') ?? 1));
@endphp

<!-- Hero Banner -->
<div class="dash-hero d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div>
        <p class="hero-greet mb-1">{{ $greeting }}, {{ auth()->user()->name }} 👋</p>
        <p class="hero-sub">{{ now()->translatedFormat('l, d F Y') }} · Ringkasan bisnis Anda hari ini</p>
    </div>
    @if(!auth()->user()?->isKlien())
    <a href="{{ route('invoices.create') }}" class="btn btn-light text-primary fw-semibold">
        <i class="bi bi-plus-lg"></i> Buat Invoice
    </a>
    @endif
</div>

<!-- Stat Cards (Clickable → data invoice terkait) -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <a href="{{ route('invoices.index') }}" class="stat-card accent-brand">
            <div class="stat-icon" style="background: var(--grad-brand);"><i class="bi bi-file-text"></i></div>
            <div>
                <span class="stat-label">Total Invoice</span>
                <span class="stat-value">{{ $totalInvoices }}</span>
            </div>
            <i class="bi bi-arrow-right stat-arrow"></i>
        </a>
    </div>
    <div class="col-xl-3 col-md-6">
        <a href="{{ route('invoices.index', ['status' => 'paid']) }}" class="stat-card accent-success">
            <div class="stat-icon" style="background: var(--grad-success);"><i class="bi bi-check-circle"></i></div>
            <div>
                <span class="stat-label">Lunas</span>
                <span class="stat-value">{{ $paidInvoices }}</span>
            </div>
            <i class="bi bi-arrow-right stat-arrow"></i>
        </a>
    </div>
    <div class="col-xl-3 col-md-6">
        <a href="{{ route('invoices.index', ['status' => 'unpaid']) }}" class="stat-card accent-warning">
            <div class="stat-icon" style="background: var(--grad-warning);"><i class="bi bi-clock"></i></div>
            <div>
                <span class="stat-label">Belum Dibayar</span>
                <span class="stat-value">{{ $unpaidInvoices }}</span>
            </div>
            <i class="bi bi-arrow-right stat-arrow"></i>
        </a>
    </div>
    <div class="col-xl-3 col-md-6">
        <a href="{{ route('invoices.index', ['status' => 'overdue']) }}" class="stat-card accent-danger">
            <div class="stat-icon" style="background: var(--grad-danger);"><i class="bi bi-exclamation-triangle"></i></div>
            <div>
                <span class="stat-label">Lewat Jatuh Tempo</span>
                <span class="stat-value">{{ $overdueInvoices }}</span>
            </div>
            <i class="bi bi-arrow-right stat-arrow"></i>
        </a>
    </div>
</div>

<!-- Financial Summary -->
<div class="row g-3 mb-4">
    <div class="col-lg-5 mb-3 mb-lg-0">
        <div class="collect-card d-flex flex-column justify-content-center">
            <div class="d-flex align-items-end justify-content-between mb-2">
                <div>
                    <p class="collect-title mb-1">Tingkat Penyelesaian</p>
                    <small class="text-muted">{{ $paidInvoices }} dari {{ $totalInvoices }} invoice telah lunas</small>
                </div>
                <div class="collect-rate text-success">{{ $collectRate }}%</div>
            </div>
            <div class="progress-collect mb-3">
                <div class="bar" style="width: {{ min(100, $collectRate) }}%;"></div>
            </div>
            <div class="d-flex gap-2">
                <div class="amount-chip">
                    <span class="chip-label">Total Tagihan</span>
                    <span class="chip-value">Rp {{ number_format($totalAmount, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="collect-card h-100 d-flex flex-column justify-content-center">
            <p class="collect-title mb-3">Ringkasan Keuangan</p>
            <div class="d-flex flex-column flex-sm-row gap-2">
                <div class="amount-chip">
                    <span class="chip-label">Sudah Dibayar</span>
                    <span class="chip-value text-success">Rp {{ number_format($paidAmount, 0, ',', '.') }}</span>
                </div>
                <div class="amount-chip">
                    <span class="chip-label">Belum Dibayar</span>
                    <span class="chip-value text-danger">Rp {{ number_format($unpaidAmount, 0, ',', '.') }}</span>
                </div>
                <div class="amount-chip">
                    <span class="chip-label">Overdue</span>
                    <span class="chip-value text-warning">{{ $overdueInvoices }} invoice</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Invoices -->
    <div class="col-lg-6 mb-4">
        <div class="table-container p-3 h-100">
            <div class="panel-head">
                <h5 class="panel-title"><i class="bi bi-clock-history"></i> Invoice Terbaru</h5>
                <a href="{{ route('invoices.index') }}" class="small fw-semibold text-decoration-none">Lihat semua <i class="bi bi-arrow-right"></i></a>
            </div>
            @if($recentInvoices->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0 responsive-card-table">
                        <thead><tr><th>No. Invoice</th><th>Klien</th><th>Total</th><th>Status</th></tr></thead>
                        <tbody>
                            @foreach($recentInvoices as $inv)
                            <tr onclick="location.href='{{ route('invoices.show', $inv) }}'" style="cursor:pointer;">
                                <td class="fw-semibold" data-label="No. Invoice">{{ $inv->invoice_number }}</td>
                                <td data-label="Klien">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="avatar-initial">{{ strtoupper(substr($inv->client_name, 0, 1)) }}</span>
                                        <span>{{ Str::limit($inv->client_name, 20) }}</span>
                                    </div>
                                </td>
                                <td class="fw-semibold" data-label="Total">Rp {{ number_format($inv->total, 0, ',', '.') }}</td>
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
        <div class="table-container p-3 h-100">
            <div class="panel-head">
                <h5 class="panel-title"><i class="bi bi-calendar-check"></i> Segera Jatuh Tempo</h5>
                <a href="{{ route('invoices.index', ['status' => 'today']) }}" class="small fw-semibold text-decoration-none">Lihat semua <i class="bi bi-arrow-right"></i></a>
            </div>
            @if($upcomingDue->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0 responsive-card-table">
                        <thead><tr><th>No. Invoice</th><th>Klien</th><th>Jatuh Tempo</th><th>Total</th></tr></thead>
                        <tbody>
                            @foreach($upcomingDue as $inv)
                            <tr onclick="location.href='{{ route('invoices.show', $inv) }}'" style="cursor:pointer;">
                                <td class="fw-semibold" data-label="No. Invoice">{{ $inv->invoice_number }}</td>
                                <td data-label="Klien">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="avatar-initial">{{ strtoupper(substr($inv->client_name, 0, 1)) }}</span>
                                        <span>{{ Str::limit($inv->client_name, 20) }}</span>
                                    </div>
                                </td>
                                <td data-label="Jatuh Tempo">
                                    @if($inv->due_date->isToday())
                                        <span class="due-pill today">Hari ini!</span>
                                    @else
                                        <span class="due-pill {{ $inv->due_date->diffInDays(now()) <= 3 ? 'today' : 'soon' }}">
                                            {{ $inv->due_date->format('d/m/Y') }} · {{ $inv->due_date->diffInDays(now()) }} hari
                                        </span>
                                    @endif
                                </td>
                                <td class="fw-semibold" data-label="Total">Rp {{ number_format($inv->total, 0, ',', '.') }}</td>
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

<!-- Monthly Stats (Bar Chart) -->
<div class="table-container p-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <h5 class="panel-title mb-0"><i class="bi bi-bar-chart"></i> Statistik Bulanan {{ date('Y') }}</h5>
        <div class="chart-legend">
            <span><span class="dot" style="background: var(--grad-brand);"></span>Total invoice per bulan</span>
            <span><span class="dot" style="background: linear-gradient(135deg, #f59e0b, #fbbf24);"></span>Bulan ini</span>
        </div>
    </div>
    <div class="chart-wrap">
        @for($m = 1; $m <= 12; $m++)
            @php $stat = $monthlyStats->get($m); @endphp
            <div class="chart-col"
                 title="{{ $stat ? $stat->count . ' invoice · Rp ' . number_format($stat->total, 0, ',', '.') : 'Belum ada data' }}">
                <div class="chart-bar-area">
                    <div class="chart-bar {{ $stat ? '' : 'empty' }} {{ $m == now()->month ? 'current' : '' }}"
                         style="height: {{ $stat ? max(5, (int) round(($stat->total / $maxMonthly) * 100)) : 4 }}%; animation-delay: {{ ($m - 1) * 60 }}ms;"></div>
                </div>
                <span class="chart-month">{{ Carbon\Carbon::create()->month($m)->format('M') }}</span>
            </div>
        @endfor
    </div>
</div>

@endsection
