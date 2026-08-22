@extends('layouts.app')
@section('title', 'Daftar Invoice')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold mb-0">📄 {{ auth()->user()?->isKlien() ? 'Invoice Saya' : 'Daftar Invoice' }}</h3>
    @if(!auth()->user()?->isKlien())
    <a href="{{ route('invoices.create') }}" class="btn text-white" style="background:#4f46e5;">
        <i class="bi bi-plus-lg"></i> Buat Invoice
    </a>
    @endif
</div>

<!-- Filter Pills -->
<div class="filter-pills mb-3 d-flex flex-wrap align-items-center gap-2">
    <a href="{{ route('invoices.index') }}" class="btn {{ !request('status') ? 'btn-primary' : 'btn-outline-secondary' }}">
        Semua <span class="badge bg-light text-dark ms-1">{{ $counts['all'] }}</span>
    </a>
    <a href="{{ route('invoices.index', ['status' => 'unpaid']) }}" class="btn {{ request('status') == 'unpaid' ? 'btn-primary' : 'btn-outline-secondary' }}">
        <i class="bi bi-clock"></i> Belum Dibayar <span class="badge bg-light text-dark ms-1">{{ $counts['unpaid'] }}</span>
    </a>
    <a href="{{ route('invoices.index', ['status' => 'paid']) }}" class="btn {{ request('status') == 'paid' ? 'btn-primary' : 'btn-outline-secondary' }}">
        <i class="bi bi-check-circle"></i> Lunas <span class="badge bg-light text-dark ms-1">{{ $counts['paid'] }}</span>
    </a>
    <a href="{{ route('invoices.index', ['status' => 'overdue']) }}" class="btn {{ request('status') == 'overdue' ? 'btn-primary' : 'btn-outline-secondary' }}">
        <i class="bi bi-exclamation-triangle"></i> Lewat Tempo <span class="badge bg-light text-dark ms-1">{{ $counts['overdue'] }}</span>
    </a>
    <a href="{{ route('invoices.index', ['status' => 'today']) }}" class="btn {{ request('status') == 'today' ? 'btn-primary' : 'btn-outline-secondary' }}">
        <i class="bi bi-calendar-event"></i> Jatuh Tempo <span class="badge bg-light text-dark ms-1">{{ $counts['today'] }}</span>
    </a>
</div>

<!-- Search -->
<div class="table-container p-3">
    <form method="GET" action="{{ route('invoices.index') }}" class="row g-2 mb-3">
        @if(request('status'))
            <input type="hidden" name="status" value="{{ request('status') }}">
        @endif
        <div class="col-md-8">
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control" placeholder="Cari no. invoice, nama klien..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-md-4">
            <button type="submit" class="btn btn-primary w-100">Cari</button>
        </div>
    </form>

    @if($invoices->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle responsive-card-table">
            <thead>
                <tr>
                    <th>No. Invoice</th>
                    <th>Klien</th>
                    <th>Total</th>
                    <th>Jatuh Tempo</th>
                    <th>Status</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoices as $invoice)
                <tr>
                    <td class="fw-semibold" data-label="No. Invoice">{{ $invoice->invoice_number }}</td>
                    <td data-label="Klien">
                        <strong>{{ $invoice->client_name }}</strong>
                        <br><small class="text-muted">{{ $invoice->client_email }}</small>
                    </td>
                    <td data-label="Total"><strong>Rp {{ number_format($invoice->total, 0, ',', '.') }}</strong></td>
                    <td data-label="Jatuh Tempo">
                        {{ $invoice->due_date->format('d/m/Y') }}
                        @if(!$invoice->is_paid && $invoice->due_date->isPast())
                            <br><small class="text-danger fw-bold">Lewat {{ $invoice->due_date->diffInDays(now()) }} hari</small>
                        @elseif(!$invoice->is_paid && $invoice->due_date->isToday())
                            <br><small class="text-warning fw-bold">Hari ini!</small>
                        @endif
                    </td>
                    <td data-label="Status">
                        <span class="badge badge-status bg-{{ $invoice->status_color }}">
                            {{ $invoice->status_label }}
                        </span>
                        @if($invoice->is_sent)
                            <br><small class="text-muted"><i class="bi bi-send-check"></i> Terkirim</small>
                        @endif
                    </td>
                    <td class="text-end actions-cell" data-label="Aksi">
                        <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-sm btn-outline-primary btn-action" title="Detail">
                            <i class="bi bi-eye"></i>
                        </a>
                        @if(!auth()->user()?->isKlien())
                        <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-sm btn-outline-warning btn-action" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </a>
                        @endif
                        @if(auth()->user()?->canDelete())
                        <form action="{{ route('invoices.destroy', $invoice) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus invoice ini?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger btn-action" title="Hapus">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $invoices->links() }}
    </div>
    @else
    <div class="empty-state">
        <i class="bi bi-file-earmark-text"></i>
        <h5>Belum ada invoice</h5>
        <p>Klik tombol "Buat Invoice" untuk membuat invoice pertama Anda.</p>
        <a href="{{ route('invoices.create') }}" class="btn text-white mt-2" style="background:#4f46e5;">
            <i class="bi bi-plus-lg"></i> Buat Invoice
        </a>
    </div>
    @endif
</div>

@endsection
