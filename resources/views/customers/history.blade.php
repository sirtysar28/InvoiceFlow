@extends('layouts.app')
@section('title', 'Riwayat Transaksi')

@section('content')

<div class="mb-4">
    <h3 class="fw-bold mb-1">🧾 Riwayat Transaksi</h3>
    <p class="text-muted mb-0">Semua pembayaran yang pernah Anda lakukan.</p>
</div>

<div class="table-container p-3">
    @if($payments->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle responsive-card-table">
            <thead>
                <tr><th>Tanggal</th><th>Invoice</th><th>Metode</th><th class="text-end">Jumlah</th><th>Status</th><th class="text-end">Bukti</th></tr>
            </thead>
            <tbody>
                @foreach($payments as $p)
                <tr>
                    <td data-label="Tanggal">
                        <div class="fw-semibold">{{ $p->created_at->format('d/m/Y') }}</div>
                        <small class="text-muted">{{ $p->created_at->format('H:i') }}</small>
                    </td>
                    <td data-label="Invoice">
                        <a href="{{ route('invoices.public', $p->invoice_id) }}" class="fw-semibold text-decoration-none">{{ $p->invoice->invoice_number }}</a>
                        <div><small class="text-muted">{{ Str::limit($p->invoice->client_name, 22) }}</small></div>
                    </td>
                    <td data-label="Metode">
                        <span class="method-badge method-{{ $p->method }}" style="font-size:.74rem;font-weight:700;padding:5px 12px;border-radius:20px;">
                            {{ $p->method == 'midtrans' ? 'Midtrans' : 'Transfer Manual' }}
                        </span>
                    </td>
                    <td class="text-end fw-semibold" data-label="Jumlah">Rp {{ number_format($p->amount, 0, ',', '.') }}</td>
                    <td data-label="Status"><span class="badge badge-status bg-{{ $p->status_color }}">{{ $p->status_label }}</span></td>
                    <td class="text-end" data-label="Bukti">
                        @if($p->proof_path)
                            <a href="{{ $p->proofUrl() }}" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-receipt"></i></a>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="p-3 d-flex justify-content-center">{{ $payments->links() }}</div>
    @else
    <div class="empty-state"><i class="bi bi-clock-history"></i><h5>Belum ada transaksi</h5><p>Riwayat pembayaran Anda akan tampil di sini.</p></div>
    @endif
</div>

@endsection
