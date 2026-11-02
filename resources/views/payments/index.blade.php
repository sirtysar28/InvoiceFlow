@extends('layouts.app')
@section('title', 'Konfirmasi Pembayaran')

@push('styles')
<style>
    .proof-thumb { width: 54px; height: 54px; object-fit: cover; border-radius: 10px; border: 1px solid #e2e8f0; cursor: pointer; }
    .method-badge { font-size: .74rem; font-weight: 700; padding: 5px 12px; border-radius: 20px; }
    .method-midtrans { background: #e0f2fe; color: #0369a1; }
    .method-manual { background: #fef3c7; color: #b45309; }
</style>
@endpush

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-0">💳 Pembayaran</h3>
        <small class="text-muted">Verifikasi konfirmasi manual & pantau pembayaran Midtrans</small>
    </div>
</div>

<!-- Filter -->
<form method="GET" class="table-container p-3 mb-4">
    <div class="row g-2 align-items-end">
        <div class="col-md-3 col-6">
            <label class="form-label small fw-semibold">Status</label>
            <select name="status" class="form-select">
                <option value="">Semua</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Berhasil</option>
                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Gagal</option>
            </select>
        </div>
        <div class="col-md-3 col-6">
            <label class="form-label small fw-semibold">Metode</label>
            <select name="method" class="form-select">
                <option value="">Semua Metode</option>
                <option value="manual" {{ request('method') == 'manual' ? 'selected' : '' }}>Transfer Manual</option>
                <option value="midtrans" {{ request('method') == 'midtrans' ? 'selected' : '' }}>Midtrans</option>
            </select>
        </div>
        <div class="col-md-6 col-12">
            <label class="form-label small fw-semibold">Cari</label>
            <div class="d-flex gap-2">
                <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="no. invoice / nama klien...">
                <button class="btn btn-brand text-white"><i class="bi bi-search"></i></button>
            </div>
        </div>
    </div>
</form>

<div class="table-container p-3">
    @if($payments->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle responsive-card-table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Invoice</th>
                    <th>Metode</th>
                    <th>Detail</th>
                    <th class="text-end">Jumlah</th>
                    <th>Status</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payments as $p)
                <tr>
                    <td data-label="Tanggal">
                        <div class="fw-semibold">{{ $p->created_at->format('d/m/Y') }}</div>
                        <small class="text-muted">{{ $p->created_at->format('H:i') }}</small>
                    </td>
                    <td data-label="Invoice">
                        <a href="{{ route('invoices.show', $p->invoice_id) }}" class="fw-semibold text-decoration-none">{{ $p->invoice->invoice_number }}</a>
                        <div><small class="text-muted">{{ Str::limit($p->invoice->client_name, 22) }}</small></div>
                    </td>
                    <td data-label="Metode">
                        <span class="method-badge method-{{ $p->method }}">
                            <i class="bi {{ $p->method == 'midtrans' ? 'bi-credit-card' : 'bi-bank' }}"></i>
                            {{ $p->method == 'midtrans' ? 'Midtrans' : 'Manual' }}
                        </span>
                    </td>
                    <td data-label="Detail">
                        @if($p->method == 'manual')
                            <small>
                                {{ $p->bank_name }} · {{ $p->sender_name }}<br>
                                <span class="text-muted">Transfer: {{ optional($p->transfer_date)->format('d/m/Y') }}</span>
                            </small>
                        @else
                            <small>
                                {{ $p->payment_type ?: '—' }}<br>
                                <span class="text-muted">{{ $p->order_id }}</span>
                            </small>
                        @endif
                    </td>
                    <td class="text-end fw-semibold" data-label="Jumlah">Rp {{ number_format($p->amount, 0, ',', '.') }}</td>
                    <td data-label="Status"><span class="badge badge-status bg-{{ $p->status_color }}">{{ $p->status_label }}</span></td>
                    <td class="text-end actions-cell" data-label="Aksi">
                        @if($p->status == 'pending' && $p->method == 'manual')
                            <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#approveModal{{ $p->id }}">
                                <i class="bi bi-check-lg"></i> Setujui
                            </button>
                            <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $p->id }}">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        @elseif($p->status == 'pending' && $p->method == 'midtrans')
                            <form method="POST" action="{{ route('payments.sync', $p) }}" class="d-inline"
                                  onsubmit="return confirm('Cek status transaksi ini langsung ke Midtrans?');">
                                @csrf
                                <button class="btn btn-sm btn-primary text-white" title="Cek status langsung ke Midtrans">
                                    <i class="bi bi-arrow-repeat"></i> Sinkron
                                </button>
                            </form>
                            @if($p->snap_url)
                                <a href="{{ $p->snap_url }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary" title="Buka halaman pembayaran Midtrans">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                            @endif
                        @elseif($p->proof_path)
                            <a href="{{ $p->proofUrl() }}" target="_blank" class="btn btn-sm btn-outline-secondary" title="Lihat bukti transfer">
                                <i class="bi bi-receipt"></i> Bukti
                            </a>
                        @else
                            <span class="text-muted small">—</span>
                        @endif
                    </td>
                </tr>

                <!-- Modal Approve -->
                <div class="modal fade" id="approveModal{{ $p->id }}" tabindex="-1">
                    <div class="modal-dialog"><form method="POST" action="{{ route('payments.approve', $p) }}">@csrf
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title fw-bold">✅ Setujui Pembayaran</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p>Konfirmasi pembayaran <strong>{{ $p->invoice->invoice_number }}</strong> sebesar <strong>Rp {{ number_format($p->amount, 0, ',', '.') }}</strong> akan disetujui dan invoice ditandai <span class="text-success fw-bold">LUNAS</span>.</p>
                                @if($p->proof_path)
                                <div class="text-center mb-3">
                                    <img src="{{ $p->proofUrl() }}" class="img-fluid rounded" style="max-height:260px;" alt="Bukti transfer">
                                </div>
                                @endif
                                <label class="form-label">Catatan (opsional)</label>
                                <input type="text" name="admin_note" class="form-control" placeholder="Contoh: Nominal sesuai">
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-success text-white"><i class="bi bi-check-lg"></i> Setujui & Tandai Lunas</button>
                            </div>
                        </div>
                    </form></div>
                </div>

                <!-- Modal Reject -->
                <div class="modal fade" id="rejectModal{{ $p->id }}" tabindex="-1">
                    <div class="modal-dialog"><form method="POST" action="{{ route('payments.reject', $p) }}">@csrf
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title fw-bold text-danger">❌ Tolak Konfirmasi</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <label class="form-label">Alasan penolakan <span class="text-danger">*</span></label>
                                <textarea name="admin_note" rows="3" class="form-control" required placeholder="Contoh: Nominal tidak sesuai / bukti tidak jelas"></textarea>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-danger text-white"><i class="bi bi-x-lg"></i> Tolak</button>
                            </div>
                        </div>
                    </form></div>
                </div>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="p-3 d-flex justify-content-center">{{ $payments->links() }}</div>
    @else
    <div class="empty-state">
        <i class="bi bi-credit-card-2-front"></i>
        <h5>Belum ada pembayaran</h5>
        <p>Konfirmasi pembayaran dari klien akan muncul di sini.</p>
    </div>
    @endif
</div>

@endsection
