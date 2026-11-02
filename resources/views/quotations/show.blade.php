@extends('layouts.app')
@section('title', 'Penawaran ' . $quotation->quote_number)

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <a href="{{ route('quotations.index') }}" class="text-muted text-decoration-none small"><i class="bi bi-arrow-left"></i> Semua Penawaran</a>
        <h3 class="fw-bold mb-0 mt-1">📝 {{ $quotation->quote_number }}</h3>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @if(!in_array($quotation->status, ['accepted', 'converted']))
            <form method="POST" action="{{ route('quotations.mark-status', $quotation) }}">
                @csrf <input type="hidden" name="status" value="sent">
                <button class="btn btn-outline-primary"><i class="bi bi-send"></i> Tandai Terkirim</button>
            </form>
            <form method="POST" action="{{ route('quotations.mark-status', $quotation) }}">
                @csrf <input type="hidden" name="status" value="accepted">
                <button class="btn btn-success text-white"><i class="bi bi-check-lg"></i> Disetujui Klien</button>
            </form>
            <form method="POST" action="{{ route('quotations.mark-status', $quotation) }}">
                @csrf <input type="hidden" name="status" value="rejected">
                <button class="btn btn-outline-danger"><i class="bi bi-x-lg"></i> Ditolak</button>
            </form>
        @endif
        @if($quotation->status === 'accepted')
            <form method="POST" action="{{ route('quotations.convert-order', $quotation) }}">
                @csrf
                <button class="btn btn-warning text-white"><i class="bi bi-arrow-right-circle"></i> Jadikan Order</button>
            </form>
            <form method="POST" action="{{ route('quotations.convert-invoice', $quotation) }}">
                @csrf
                <button class="btn btn-brand text-white"><i class="bi bi-receipt"></i> Jadikan Invoice</button>
            </form>
        @endif
        @if($quotation->status !== 'converted')
            <a href="{{ route('quotations.edit', $quotation) }}" class="btn btn-outline-secondary"><i class="bi bi-pencil"></i> Edit</a>
        @endif
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="table-container p-4">
            <div class="panel-head">
                <h5 class="panel-title mb-0"><i class="bi bi-person"></i> Klien</h5>
                <span class="badge badge-status bg-{{ $quotation->statusColor() }}">{{ $quotation->statusLabel() }}</span>
            </div>
            <p class="mb-1 fw-semibold">{{ $quotation->client_name }}</p>
            @if($quotation->client_email)<p class="mb-1 text-muted"><i class="bi bi-envelope"></i> {{ $quotation->client_email }}</p>@endif
            @if($quotation->client_phone)<p class="mb-0 text-muted"><i class="bi bi-whatsapp"></i> {{ $quotation->client_phone }}</p>@endif

            <hr class="my-4">

            <h6 class="fw-bold mb-3">Item Penawaran</h6>
            <div class="table-responsive">
                <table class="table mb-0 invoice-items-table">
                    <thead><tr><th>Deskripsi</th><th class="text-center">Qty</th><th class="text-end">Harga</th><th class="text-end">Jumlah</th></tr></thead>
                    <tbody>
                        @foreach($quotation->items as $item)
                        <tr>
                            <td>{{ $item['description'] ?? '—' }}</td>
                            <td class="text-center">{{ $item['quantity'] ?? 0 }}</td>
                            <td class="text-end">Rp {{ number_format($item['unit_price'] ?? 0, 0, ',', '.') }}</td>
                            <td class="text-end fw-semibold">Rp {{ number_format(($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0), 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr><td colspan="3" class="text-end text-muted">Subtotal</td><td class="text-end">Rp {{ number_format($quotation->subtotal, 0, ',', '.') }}</td></tr>
                        @if($quotation->discount_amount > 0)
                        <tr><td colspan="3" class="text-end text-muted">Diskon ({{ $quotation->discount }}%)</td><td class="text-end text-danger">-Rp {{ number_format($quotation->discount_amount, 0, ',', '.') }}</td></tr>
                        @endif
                        @if($quotation->tax_amount > 0)
                        <tr><td colspan="3" class="text-end text-muted">Pajak ({{ $quotation->tax }}%)</td><td class="text-end">Rp {{ number_format($quotation->tax_amount, 0, ',', '.') }}</td></tr>
                        @endif
                        <tr><td colspan="3" class="text-end fw-bold">TOTAL</td><td class="text-end fw-bold text-primary fs-5">Rp {{ number_format($quotation->total, 0, ',', '.') }}</td></tr>
                    </tfoot>
                </table>
            </div>

            @if($quotation->notes)
            <div class="alert alert-warning mt-3 mb-0"><strong>Catatan:</strong> {{ $quotation->notes }}</div>
            @endif
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card card-stat p-4">
            <h6 class="fw-bold mb-3"><i class="bi bi-info-circle"></i> Detail</h6>
            <div class="d-flex justify-content-between mb-2"><span class="text-muted">Dibuat</span><strong>{{ $quotation->created_at->format('d/m/Y H:i') }}</strong></div>
            <div class="d-flex justify-content-between mb-2"><span class="text-muted">Berlaku s/d</span><strong>{{ optional($quotation->valid_until)->format('d/m/Y') ?? '—' }}</strong></div>
            <div class="d-flex justify-content-between mb-2"><span class="text-muted">Status</span><span class="badge bg-{{ $quotation->statusColor() }}">{{ $quotation->statusLabel() }}</span></div>

            <hr>
            @if($quotation->status === 'converted')
                <div class="alert alert-info mb-0 small">
                    <i class="bi bi-check-circle"></i> Penawaran ini sudah dikonversi menjadi order/invoice.
                </div>
            @elseif($quotation->isExpired())
                <div class="alert alert-danger mb-0 small"><i class="bi bi-exclamation-triangle"></i> Penawaran sudah melewati masa berlaku.</div>
            @else
                <div class="alert alert-light border mb-0 small text-muted">
                    <i class="bi bi-lightbulb"></i> Tandai <strong>Disetujui Klien</strong> untuk membuka opsi konversi ke <strong>Order</strong> atau <strong>Invoice</strong>.
                </div>
            @endif
        </div>
    </div>
</div>

@endsection
