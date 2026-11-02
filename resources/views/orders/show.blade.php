@extends('layouts.app')
@section('title', 'Order ' . $order->order_number)

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <a href="{{ route('orders.index') }}" class="text-muted text-decoration-none small"><i class="bi bi-arrow-left"></i> Semua Order</a>
        <h3 class="fw-bold mb-0 mt-1">📋 {{ $order->order_number }}</h3>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @if(!in_array($order->status, ['invoiced', 'cancelled']))
            <form method="POST" action="{{ route('orders.mark-status', $order) }}">
                @csrf <input type="hidden" name="status" value="processing">
                <button class="btn btn-outline-warning"><i class="bi bi-gear"></i> Proses</button>
            </form>
            <form method="POST" action="{{ route('orders.mark-status', $order) }}">
                @csrf <input type="hidden" name="status" value="completed">
                <button class="btn btn-success text-white"><i class="bi bi-check-lg"></i> Selesai</button>
            </form>
            <form method="POST" action="{{ route('orders.convert-invoice', $order) }}">
                @csrf
                <button class="btn btn-brand text-white"><i class="bi bi-receipt"></i> Buat Invoice</button>
            </form>
        @endif
        @if($order->status !== 'invoiced')
            <a href="{{ route('orders.edit', $order) }}" class="btn btn-outline-secondary"><i class="bi bi-pencil"></i> Edit</a>
        @endif
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="table-container p-4">
            <div class="panel-head">
                <h5 class="panel-title mb-0"><i class="bi bi-person"></i> Klien</h5>
                <span class="badge badge-status bg-{{ $order->statusColor() }}">{{ $order->statusLabel() }}</span>
            </div>
            <p class="mb-1 fw-semibold">{{ $order->client_name }}</p>
            @if($order->client_email)<p class="mb-1 text-muted"><i class="bi bi-envelope"></i> {{ $order->client_email }}</p>@endif
            @if($order->client_phone)<p class="mb-0 text-muted"><i class="bi bi-whatsapp"></i> {{ $order->client_phone }}</p>@endif

            <hr class="my-4">

            <h6 class="fw-bold mb-3">Item Order</h6>
            <div class="table-responsive">
                <table class="table mb-0 invoice-items-table">
                    <thead><tr><th>Deskripsi</th><th class="text-center">Qty</th><th class="text-end">Harga</th><th class="text-end">Jumlah</th></tr></thead>
                    <tbody>
                        @foreach($order->items as $item)
                        <tr>
                            <td>{{ $item['description'] ?? '—' }}</td>
                            <td class="text-center">{{ $item['quantity'] ?? 0 }}</td>
                            <td class="text-end">Rp {{ number_format($item['unit_price'] ?? 0, 0, ',', '.') }}</td>
                            <td class="text-end fw-semibold">Rp {{ number_format(($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0), 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr><td colspan="3" class="text-end fw-bold">TOTAL</td><td class="text-end fw-bold text-primary fs-5">Rp {{ number_format($order->total, 0, ',', '.') }}</td></tr>
                    </tfoot>
                </table>
            </div>

            @if($order->notes)
            <div class="alert alert-warning mt-3 mb-0"><strong>Catatan:</strong> {{ $order->notes }}</div>
            @endif
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card card-stat p-4">
            <h6 class="fw-bold mb-3"><i class="bi bi-info-circle"></i> Detail</h6>
            <div class="d-flex justify-content-between mb-2"><span class="text-muted">Tanggal Order</span><strong>{{ optional($order->order_date)->format('d/m/Y') ?? '—' }}</strong></div>
            <div class="d-flex justify-content-between mb-2"><span class="text-muted">Dibuat</span><strong>{{ $order->created_at->format('d/m/Y H:i') }}</strong></div>
            <div class="d-flex justify-content-between mb-2"><span class="text-muted">Status</span><span class="badge bg-{{ $order->statusColor() }}">{{ $order->statusLabel() }}</span></div>

            <hr>
            @if($order->status === 'invoiced' && $order->invoice_id)
                <a href="{{ route('invoices.show', $order->invoice_id) }}" class="btn btn-outline-primary w-100">
                    <i class="bi bi-receipt"></i> Lihat Invoice Terkait
                </a>
            @elseif($order->status === 'cancelled')
                <div class="alert alert-secondary mb-0 small">Order dibatalkan.</div>
            @else
                <div class="alert alert-light border mb-0 small text-muted">
                    <i class="bi bi-lightbulb"></i> Setelah order <strong>Selesai</strong>, klik <strong>Buat Invoice</strong> untuk menagih klien.
                </div>
            @endif
        </div>
    </div>
</div>

@endsection
