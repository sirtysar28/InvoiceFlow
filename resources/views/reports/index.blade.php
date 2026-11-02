@extends('layouts.app')
@section('title', 'Laporan')

@push('styles')
<style>
    .report-card {
        display: flex; align-items: flex-start; gap: 16px;
        background: #fff; border: 1px solid rgba(226,232,240,.9); border-radius: 18px;
        padding: 22px; text-decoration: none; color: inherit; height: 100%;
        box-shadow: 0 1px 3px rgba(15,23,42,.06);
        transition: transform .25s ease, box-shadow .25s ease;
    }
    .report-card:hover { transform: translateY(-5px); box-shadow: 0 14px 32px -10px rgba(15,23,42,.18); color: inherit; }
    .report-card .rc-icon {
        flex: 0 0 48px; width: 48px; height: 48px; border-radius: 14px;
        display: flex; align-items: center; justify-content: center; font-size: 1.3rem; color: #fff;
        background: var(--grad-brand);
    }
    .report-card:nth-child(6n+2) .rc-icon { background: var(--grad-success); }
    .report-card:nth-child(6n+3) .rc-icon { background: var(--grad-warning); }
    .report-card:nth-child(6n+4) .rc-icon { background: var(--grad-danger); }
    .report-card:nth-child(6n+5) .rc-icon { background: linear-gradient(135deg,#0ea5e9,#38bdf8); }
    .report-card:nth-child(6n+6) .rc-icon { background: linear-gradient(135deg,#8b5cf6,#c084fc); }
    .report-card h6 { font-family: 'Sora', sans-serif; font-weight: 700; margin-bottom: 4px; }
    .report-card p { font-size: .85rem; color: #64748b; margin: 0; }
    .report-card .rc-arrow { margin-left: auto; align-self: center; color: #cbd5e1; transition: transform .25s; }
    .report-card:hover .rc-arrow { transform: translateX(4px); color: var(--primary); }
</style>
@endpush

@section('content')

<div class="mb-4">
    <h3 class="fw-bold mb-1">📊 Pusat Laporan</h3>
    <p class="text-muted mb-0">Pilih jenis laporan — semua bisa di-export ke Excel (.xlsx) & PDF.</p>
</div>

<div class="row g-3">
    @foreach($types as $key => $meta)
    <div class="col-xl-3 col-md-6">
        <a href="{{ route('reports.show', $key) }}" class="report-card">
            <div class="rc-icon"><i class="bi {{ $meta['icon'] }}"></i></div>
            <div>
                <h6>{{ $meta['label'] }}</h6>
                <p>{{ $meta['desc'] }}</p>
            </div>
            <i class="bi bi-arrow-right rc-arrow"></i>
        </a>
    </div>
    @endforeach
</div>

@endsection
