@extends('layouts.app')
@section('title', 'Quotation')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-0">📝 Quotation (Penawaran)</h3>
        <small class="text-muted">Buat penawaran, konversi ke order atau invoice saat disetujui.</small>
    </div>
    <a href="{{ route('quotations.create') }}" class="btn btn-brand text-white">
        <i class="bi bi-plus-lg"></i> Buat Penawaran
    </a>
</div>

<!-- Filter -->
<form method="GET" class="table-container p-3 mb-4">
    <div class="row g-2 align-items-end">
        <div class="col-md-3 col-6">
            <label class="form-label small fw-semibold">Status</label>
            <select name="status" class="form-select">
                <option value="">Semua</option>
                @foreach(['draft' => 'Draft', 'sent' => 'Terkirim', 'accepted' => 'Disetujui', 'rejected' => 'Ditolak', 'converted' => 'Dikonversi'] as $val => $lbl)
                    <option value="{{ $val }}" {{ request('status') == $val ? 'selected' : '' }}>{{ $lbl }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-9 col-12">
            <label class="form-label small fw-semibold">Cari</label>
            <div class="d-flex gap-2">
                <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="no. penawaran / klien...">
                <button class="btn btn-brand text-white"><i class="bi bi-search"></i></button>
            </div>
        </div>
    </div>
</form>

<div class="table-container p-3">
    @if($quotations->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle responsive-card-table">
            <thead>
                <tr><th>No. Penawaran</th><th>Klien</th><th class="text-end">Total</th><th>Berlaku S/D</th><th>Status</th><th class="text-end">Aksi</th></tr>
            </thead>
            <tbody>
                @foreach($quotations as $q)
                <tr>
                    <td class="fw-semibold" data-label="No. Penawaran">
                        <a href="{{ route('quotations.show', $q) }}" class="text-decoration-none">{{ $q->quote_number }}</a>
                        <div><small class="text-muted">{{ $q->created_at->format('d/m/Y') }}</small></div>
                    </td>
                    <td data-label="Klien">{{ Str::limit($q->client_name, 25) }}</td>
                    <td class="text-end fw-semibold" data-label="Total">Rp {{ number_format($q->total, 0, ',', '.') }}</td>
                    <td data-label="Berlaku"><small>{{ optional($q->valid_until)->format('d/m/Y') ?? '—' }}</small></td>
                    <td data-label="Status">
                        <span class="badge badge-status bg-{{ $q->statusColor() }}">{{ $q->statusLabel() }}</span>
                        @if($q->isExpired())<div><small class="text-danger">kedaluwarsa</small></div>@endif
                    </td>
                    <td class="text-end actions-cell" data-label="Aksi">
                        <a href="{{ route('quotations.show', $q) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
                        @if(!in_array($q->status, ['accepted', 'converted']))
                        <a href="{{ route('quotations.edit', $q) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="p-3 d-flex justify-content-center">{{ $quotations->links() }}</div>
    @else
    <div class="empty-state"><i class="bi bi-file-earmark-text"></i><h5>Belum ada penawaran</h5><p>Buat penawaran pertama untuk klien Anda.</p></div>
    @endif
</div>

@endsection
