@extends('layouts.app')
@section('title', 'Detail Invoice')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold mb-0">📋 Invoice #{{ $invoice->invoice_number }}</h3>
    <div class="d-flex gap-2">
        <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
        @if(!auth()->user()?->isKlien())
        <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-outline-warning">
            <i class="bi bi-pencil"></i> Edit
        </a>
        @endif
    </div>
</div>

<div class="row g-4">
    <!-- Main Invoice -->
    <div class="col-lg-8">
        <div class="card card-stat p-4">
            <!-- Status and Actions -->
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
                <span class="badge badge-status bg-{{ $invoice->status_color }} fs-6 px-3 py-2">
                    {{ $invoice->status_label }}
                    @if($invoice->is_paid && $invoice->paid_at)
                        <small>· {{ $invoice->paid_at->format('d/m/Y H:i') }}</small>
                    @endif
                </span>

                <div class="d-flex gap-2 flex-wrap">
                    @if(!auth()->user()?->isKlien())
                    @if(!$invoice->is_paid)
                    <form action="{{ route('invoices.mark-paid', $invoice) }}" method="POST" class="d-inline">
                        @csrf
                        <button class="btn btn-success" onclick="return confirm('Tandai invoice ini sebagai lunas?')">
                            <i class="bi bi-check-lg"></i> Tandai Lunas
                        </button>
                    </form>
                    @else
                    <form action="{{ route('invoices.mark-unpaid', $invoice) }}" method="POST" class="d-inline">
                        @csrf
                        <button class="btn btn-outline-warning">
                            <i class="bi bi-arrow-counterclockwise"></i> Batal Lunas
                        </button>
                    </form>
                    @endif
                    @endif

                    <a href="{{ route('invoices.pdf', $invoice) }}" class="btn btn-outline-secondary">
                        <i class="bi bi-download"></i> PDF
                    </a>
                </div>
            </div>

            <!-- Invoice Details -->
            <div class="row g-3 mb-4">
                <div class="col-sm-6">
                    <small class="text-muted">No. Invoice</small>
                    <h5 class="fw-semibold">{{ $invoice->invoice_number }}</h5>
                </div>
                <div class="col-sm-6">
                    <small class="text-muted">Tanggal Dibuat</small>
                    <h5 class="fw-semibold">{{ $invoice->created_at->format('d M Y') }}</h5>
                </div>
                <div class="col-sm-6">
                    <small class="text-muted">Jatuh Tempo</small>
                    <h5 class="fw-semibold {{ $invoice->isOverdue() ? 'text-danger' : '' }}">
                        {{ $invoice->due_date->format('d M Y') }}
                        @if($invoice->isOverdue())
                            <span class="badge bg-danger ms-2">Lewat {{ $invoice->due_date->diffInDays(now()) }} hari</span>
                        @elseif($invoice->due_date->isToday() && !$invoice->is_paid)
                            <span class="badge bg-warning ms-2">Hari ini!</span>
                        @endif
                    </h5>
                </div>
                <div class="col-sm-6">
                    <small class="text-muted">Status Pengiriman</small>
                    <h5>
                        @if($invoice->is_sent)
                            <span class="text-success"><i class="bi bi-send-check"></i> Terkirim</span>
                            <small class="text-muted">{{ $invoice->sent_at?->format('d/m/Y H:i') }}</small>
                        @else
                            <span class="text-muted">Belum dikirim</span>
                        @endif
                    </h5>
                </div>
            </div>

            <!-- Client Info -->
            <div class="bg-light rounded-3 p-3 mb-4">
                <h6 class="fw-bold mb-3"><i class="bi bi-person-circle me-2"></i>Informasi Klien</h6>
                <div class="row">
                    <div class="col-sm-6">
                        <small class="text-muted">Nama</small>
                        <p class="fw-semibold mb-2">{{ $invoice->client_name }}</p>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted">Email</small>
                        <p class="fw-semibold mb-2">{{ $invoice->client_email }}</p>
                    </div>
                    @if($invoice->client_phone)
                    <div class="col-sm-6">
                        <small class="text-muted">No. HP</small>
                        <p class="fw-semibold mb-0">{{ $invoice->client_phone }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Items -->
            <div class="table-responsive mb-3">
                <table class="table table-bordered responsive-card-table">
                    <thead class="table-light">
                        <tr>
                            <th>Deskripsi</th>
                            <th class="text-center">Jumlah</th>
                            <th class="text-end">Harga Satuan</th>
                            <th class="text-end">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->items as $item)
                        <tr>
                            <td data-label="Deskripsi">{{ $item->description }}</td>
                            <td class="text-center" data-label="Jumlah">{{ $item->quantity }}</td>
                            <td class="text-end" data-label="Harga Satuan">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                            <td class="text-end" data-label="Jumlah">Rp {{ number_format($item->amount, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="3" class="text-end fw-semibold" data-label="">Subtotal</td>
                            <td class="text-end" data-label="Subtotal">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        @if($invoice->tax > 0)
                        <tr>
                            <td colspan="3" class="text-end" data-label="">Pajak ({{ $invoice->tax }}%)</td>
                            <td class="text-end" data-label="Pajak">Rp {{ number_format($invoice->tax_amount, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                        @if($invoice->discount > 0)
                        <tr>
                            <td colspan="3" class="text-end" data-label="">Diskon ({{ $invoice->discount }}%)</td>
                            <td class="text-end text-danger" data-label="Diskon">-Rp {{ number_format($invoice->discount_amount, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td colspan="3" class="text-end fw-bold fs-5" data-label="">TOTAL</td>
                            <td class="text-end fw-bold fs-5" data-label="Total">Rp {{ number_format($invoice->total, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if($invoice->notes)
            <div class="bg-warning bg-opacity-10 rounded-3 p-3">
                <small class="text-muted">Catatan:</small>
                <p class="mb-0">{{ $invoice->notes }}</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Sidebar Actions -->
    <div class="col-lg-4">
        <!-- Send Invoice -->
        @if(!auth()->user()?->isKlien())
        <div class="card card-stat p-4 mb-3">
            <h6 class="fw-bold mb-3"><i class="bi bi-send me-2"></i>Kirim Invoice ke Klien</h6>

            <!-- WhatsApp -->
            @if($invoice->client_phone)
            <a href="{{ route('invoices.send-wa', $invoice) }}" class="btn w-100 mb-2 text-white" style="background:#25D366;" target="_blank">
                <i class="bi bi-whatsapp"></i> Kirim via WhatsApp
            </a>
            @else
            <button class="btn w-100 mb-2 btn-outline-secondary" disabled>
                <i class="bi bi-whatsapp"></i> No. HP tidak tersedia
            </button>
            @endif

            <!-- Email -->
            <button class="btn w-100 btn-outline-primary" data-bs-toggle="modal" data-bs-target="#emailModal">
                <i class="bi bi-envelope"></i> Kirim via Email
            </button>
        </div>
        @endif

        <!-- Business Profile for Payment -->
        @if($businessProfile && $businessProfile->bank_account_number)
        <div class="card card-stat p-4 mb-3">
            <h6 class="fw-bold mb-3"><i class="bi bi-bank me-2"></i>Info Pembayaran</h6>
            <p class="mb-1"><strong>Bank:</strong> {{ $businessProfile->bank_name }}</p>
            <p class="mb-1"><strong>No. Rek:</strong> {{ $businessProfile->bank_account_number }}</p>
            <p class="mb-0"><strong>A.n:</strong> {{ $businessProfile->bank_account_name }}</p>
        </div>
        @endif

        <!-- Notification Logs -->
        <div class="card card-stat p-4">
            <h6 class="fw-bold mb-3"><i class="bi bi-bell me-2"></i>Riwayat Notifikasi</h6>
            @php $logs = $invoice->notificationLogs()->latest()->take(5)->get(); @endphp
            @if($logs->count() > 0)
                @foreach($logs as $log)
                <div class="d-flex align-items-center gap-2 mb-2 pb-2 border-bottom">
                    <i class="bi bi-{{ $log->channel == 'whatsapp' ? 'whatsapp' : 'envelope' }} text-{{ $log->status == 'sent' ? 'success' : 'danger' }}"></i>
                    <div>
                        <small class="fw-semibold">{{ $log->channel == 'whatsapp' ? 'WhatsApp' : 'Email' }}</small>
                        <br><small class="text-muted">{{ $log->created_at->format('d/m/Y H:i') }}</small>
                        <span class="badge bg-{{ $log->status == 'sent' ? 'success' : 'danger' }}">{{ $log->status }}</span>
                    </div>
                </div>
                @endforeach
            @else
                <p class="text-muted small mb-0">Belum ada notifikasi terkirim.</p>
            @endif
        </div>

        @if(auth()->user()?->canDelete())
        <!-- Danger Zone -->
        <div class="card card-stat p-4 mt-3 border-danger">
            <h6 class="fw-bold text-danger mb-2"><i class="bi bi-exclamation-triangle me-2"></i>Zona Berbahaya</h6>
            <form action="{{ route('invoices.destroy', $invoice) }}" method="POST" onsubmit="return confirm('Yakin hapus invoice ini? Tindakan ini tidak dapat dibatalkan.')">
                @csrf @method('DELETE')
                <button class="btn btn-outline-danger btn-sm w-100">
                    <i class="bi bi-trash"></i> Hapus Invoice
                </button>
            </form>
        </div>
        @endif
    </div>
</div>

<!-- Email Modal -->
<div class="modal fade" id="emailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('invoices.send-email', $invoice) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-envelope"></i> Kirim Invoice via Email</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Ke</label>
                        <input type="text" class="form-control" value="{{ $invoice->client_email }}" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subject</label>
                        <input type="text" name="subject" class="form-control"
                            value="{{ $emailTemplate ? str_replace(['{no_invoice}','{nama_klien}'], [$invoice->invoice_number, $invoice->client_name], $emailTemplate->subject ?? 'Invoice #'.$invoice->invoice_number) : 'Invoice #'.$invoice->invoice_number.' - '.$invoice->client_name }}"
                            required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pesan</label>
                        <textarea name="message" class="form-control" rows="8" required>{{ $emailTemplate ? $emailTemplate->getParsedBody($invoice) : "Halo {$invoice->client_name},\n\nBerikut terlampir invoice dengan detail:\n\nNo. Invoice: {$invoice->invoice_number}\nTotal: Rp " . number_format($invoice->total, 0, ',', '.') . "\nJatuh Tempo: " . $invoice->due_date->format('d/m/Y') . "\n\nSilahkan melakukan pembayaran sebelum tanggal jatuh tempo.\n\nTerima kasih." }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn text-white" style="background:#4f46e5;">
                        <i class="bi bi-send"></i> Kirim Email
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
