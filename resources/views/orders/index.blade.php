@extends('layouts.app')
@section('title', 'Sales Order')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-0">📋 Sales Order</h3>
        <small class="text-muted">Kelola order, konversi ke invoice saat siap ditagih.</small>
    </div>
    <a href="{{ route('orders.create') }}" class="btn btn-brand text-white">
        <i class="bi bi-plus-lg"></i> Buat Order
    </a>
</div>

<form method="GET" class="table-container p-3 mb-4">
    <div class="row g-2 align-items-end">
        <div class="col-md-3 col-6">
            <label class="form-label small fw-semibold">Status</label>
            <select name="status" class="form-select">
                <option value="">Semua</option>
                @foreach(['new' => 'Baru', 'processing' => 'Diproses', 'completed' => 'Selesai', 'invoiced' => 'Di-invoice', 'cancelled' => 'Dibatalkan'] as $val => $lbl)
                    <option value="{{ $val }}" {{ request('status') == $val ? 'selected' : '' }}>{{ $lbl }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-9 col-12">
            <label class="form-label small fw-semibold">Cari</label>
            <div class="d-flex gap-2">
                <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="no. order / klien...">
                <button class="btn btn-brand text-white"><i class="bi bi-search"></i></button>
            </div>
        </div>
    </div>
</form>

<div class="table-container p-3">
    @if($orders->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle responsive-card-table">
            <thead>
                <tr><th>No. Order</th><th>Klien</th><th class="text-end">Total</th><th>Tanggal</th><th>Status</th><th class="text-end">Aksi</th></tr>
            </thead>
            <tbody>
                @foreach($orders as $o)
                <tr>
                    <td class="fw-semibold" data-label="No. Order">
                        <a href="{{ route('orders.show', $o) }}" class="text-decoration-none">{{ $o->order_number }}</a>
                        @if($o->quotation_id)<div><small class="text-muted">dari quotation</small></div>@endif
                    </td>
                    <td data-label="Klien">{{ Str::limit($o->client_name, 25) }}</td>
                    <td class="text-end fw-semibold" data-label="Total">Rp {{ number_format($o->total, 0, ',', '.') }}</td>
                    <td data-label="Tanggal"><small>{{ optional($o->order_date)->format('d/m/Y') ?? '—' }}</small></td>
                    <td data-label="Status"><span class="badge badge-status bg-{{ $o->statusColor() }}">{{ $o->statusLabel() }}</span></td>
                    <td class="text-end actions-cell" data-label="Aksi">
                        <a href="{{ route('orders.show', $o) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
                        @if($o->status !== 'invoiced')
                            <a href="{{ route('orders.edit', $o) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="p-3 d-flex justify-content-center">{{ $orders->links() }}</div>
    @else
    <div class="empty-state"><i class="bi bi-clipboard-check"></i><h5>Belum ada order</h5><p>Buat order dari penawaran yang disetujui, atau langsung buat baru.</p></div>
    @endif
</div>

@endsection
