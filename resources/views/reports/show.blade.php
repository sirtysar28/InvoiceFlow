@extends('layouts.app')
@section('title', $report['title'])

@push('styles')
<style>
    .summary-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 12px; }
    .sum-box { background: #f8fafc; border: 1px solid #eef2f7; border-radius: 14px; padding: 13px 16px; }
    .sum-box .lbl { font-size: .72rem; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; color: #94a3b8; }
    .sum-box .val { font-family: 'Sora', sans-serif; font-weight: 700; font-size: 1.05rem; color: #0f172a; overflow-wrap: anywhere; }
    @media print {
        .no-print { display: none !important; }
        .sidebar, .bottom-nav, .wa-fab, .mobile-header { display: none !important; }
        .main-content { margin: 0 !important; padding: 0 !important; }
    }
</style>
@endpush

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <a href="{{ route('reports.index') }}" class="text-muted text-decoration-none small no-print"><i class="bi bi-arrow-left"></i> Semua Laporan</a>
        <h3 class="fw-bold mb-0 mt-1"><i class="bi {{ $report['icon'] }} text-primary"></i> {{ $report['title'] }}</h3>
    </div>
    <div class="d-flex gap-2 no-print">
        <a href="{{ route('reports.xlsx', request()->route('type')) . '?' . http_build_query(request()->query()) }}" class="btn btn-success text-white">
            <i class="bi bi-file-earmark-excel"></i> Excel
        </a>
        <a href="{{ route('reports.pdf', request()->route('type')) . '?' . http_build_query(request()->query()) }}" class="btn btn-danger text-white">
            <i class="bi bi-file-earmark-pdf"></i> PDF
        </a>
    </div>
</div>

<!-- Filter -->
<form method="GET" class="table-container p-3 mb-4 no-print">
    <div class="row g-2 align-items-end">
        <div class="col-md-3 col-6">
            <label class="form-label small fw-semibold">Dari Tanggal</label>
            <input type="date" name="from" class="form-control" value="{{ request('from') }}">
        </div>
        <div class="col-md-3 col-6">
            <label class="form-label small fw-semibold">Sampai Tanggal</label>
            <input type="date" name="to" class="form-control" value="{{ request('to') }}">
        </div>
        @if(request()->route('type') === 'invoice')
        <div class="col-md-3 col-6">
            <label class="form-label small fw-semibold">Status</label>
            <select name="status" class="form-select">
                <option value="all" {{ request('status') == 'all' || !request('status') ? 'selected' : '' }}>Semua</option>
                <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Lunas</option>
                <option value="unpaid" {{ request('status') == 'unpaid' ? 'selected' : '' }}>Belum Dibayar</option>
                <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
            </select>
        </div>
        <div class="col-md-3 col-6">
            <label class="form-label small fw-semibold">Klien</label>
            <select name="client_id" class="form-select">
                <option value="">Semua Klien</option>
                @foreach(\App\Models\Client::orderBy('name')->get(['id','name']) as $c)
                    <option value="{{ $c->id }}" {{ request('client_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <div class="col-12">
            <button class="btn btn-brand text-white"><i class="bi bi-funnel"></i> Terapkan</button>
            <a href="{{ route('reports.show', request()->route('type')) }}" class="btn btn-outline-secondary">Reset</a>
        </div>
    </div>
</form>

<!-- Ringkasan -->
<div class="table-container p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-1">
        <h5 class="panel-title mb-0"><i class="bi bi-clipboard-data"></i> Ringkasan</h5>
        <small class="text-muted">{{ $report['period_label'] }}</small>
    </div>
    <div class="summary-grid">
        @foreach($report['summary'] as $label => $value)
        <div class="sum-box"><span class="lbl">{{ $label }}</span><div class="val">{{ $value }}</div></div>
        @endforeach
    </div>
</div>

<!-- Tabel -->
<div class="table-container p-4">
    <h5 class="panel-title mb-3"><i class="bi bi-table"></i> Data</h5>
    @if(count($report['rows']) > 0)
    <div class="table-responsive">
        <table class="table table-hover mb-0 responsive-card-table">
            <thead><tr>@foreach($report['headers'] as $h)<th>{{ $h }}</th>@endforeach</tr></thead>
            <tbody>
                @foreach($report['rows'] as $row)
                <tr>
                    @foreach($row as $cell)
                        @if(Str::startsWith($cell, 'BADGE:'))
                            @php [$_, $color, $label] = explode(':', $cell, 3); @endphp
                            <td data-label="{{ $report['headers'][$loop->index] }}"><span class="badge badge-status bg-{{ $color }}">{{ $label }}</span></td>
                        @else
                            <td data-label="{{ $report['headers'][$loop->index] }}" {{ $loop->index > 0 ? '' : 'class="fw-semibold"' }}>{{ $cell }}</td>
                        @endif
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="empty-state py-4"><i class="bi bi-inbox"></i><p>Tidak ada data pada periode & filter ini</p></div>
    @endif
</div>

@endsection
