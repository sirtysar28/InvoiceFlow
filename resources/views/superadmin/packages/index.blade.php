@extends('layouts.app')
@section('title', 'Paket Subscription')

@push('scripts')
<script>
function editPackage(btn) {
    const d = btn.dataset;
    const f = document.getElementById('packageForm');
    f.action = '{{ url('packages') }}/' + d.id;
    f.querySelector('input[name=_method]').value = 'PUT';
    f.querySelector('[name=name]').value = d.name;
    f.querySelector('[name=price_monthly]').value = d.monthly;
    f.querySelector('[name=price_yearly]').value = d.yearly;
    f.querySelector('[name=max_users]').value = d.maxUsers;
    f.querySelector('[name=max_invoices]').value = d.maxInvoices;
    f.querySelector('[name=max_clients]').value = d.maxClients;
    f.querySelector('[name=features]').value = d.features;
    f.querySelector('[name=is_active]').checked = d.active === '1';
    document.getElementById('packageModalTitle').textContent = 'Edit Paket';
    new bootstrap.Modal(document.getElementById('packageModal')).show();
}
function newPackage() {
    const f = document.getElementById('packageForm');
    f.action = '{{ route('packages.store') }}';
    f.querySelector('input[name=_method]').value = 'POST';
    f.reset();
    f.querySelector('[name=is_active]').checked = true;
    document.getElementById('packageModalTitle').textContent = 'Tambah Paket';
    new bootstrap.Modal(document.getElementById('packageModal')).show();
}
</script>
@endpush

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1">📦 Paket Subscription</h3>
        <p class="text-muted mb-0">Kelola paket langganan yang tersedia untuk perusahaan.</p>
    </div>
    <button class="btn btn-brand text-white" onclick="newPackage()">
        <i class="bi bi-plus-lg"></i> Tambah Paket
    </button>
</div>

<div class="row g-3">
    @forelse($packages as $pkg)
    <div class="col-xl-3 col-md-6">
        <div class="card card-stat p-4 h-100 d-flex flex-column">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <h5 class="fw-bold mb-0">{{ $pkg->name }}</h5>
                <span class="badge bg-{{ $pkg->is_active ? 'success' : 'secondary' }}">{{ $pkg->is_active ? 'Aktif' : 'Nonaktif' }}</span>
            </div>
            <div class="mb-2">
                <span class="fs-4 fw-bold text-primary" style="font-family:'Sora';">{{ $pkg->formattedMonthly() }}</span>
                @if($pkg->price_yearly > 0)
                    <div><small class="text-muted">atau Rp {{ number_format($pkg->price_yearly, 0, ',', '.') }}/tahun</small></div>
                @endif
            </div>
            <ul class="small text-muted ps-3 mb-3">
                <li>Maks <strong>{{ $pkg->max_users }}</strong> user</li>
                <li>Maks <strong>{{ number_format($pkg->max_invoices) }}</strong> invoice</li>
                <li>Maks <strong>{{ $pkg->max_clients }}</strong> klien</li>
                @foreach(($pkg->features ?? []) as $feat)<li>{{ $feat }}</li>@endforeach
            </ul>
            <div class="mt-auto d-flex justify-content-between align-items-center">
                <small class="text-muted"><i class="bi bi-building"></i> {{ $pkg->companies_count }} perusahaan</small>
                <div class="d-flex gap-1">
                    <button class="btn btn-sm btn-outline-primary"
                            data-id="{{ $pkg->id }}" data-name="{{ $pkg->name }}"
                            data-monthly="{{ $pkg->price_monthly }}" data-yearly="{{ $pkg->price_yearly }}"
                            data-max-users="{{ $pkg->max_users }}" data-max-invoices="{{ $pkg->max_invoices }}"
                            data-max-clients="{{ $pkg->max_clients }}" data-features="{{ implode("\n", $pkg->features ?? []) }}"
                            data-active="{{ $pkg->is_active ? '1' : '0' }}"
                            onclick="editPackage(this)">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <form action="{{ route('packages.destroy', $pkg) }}" method="POST" class="d-inline">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus paket ini?')"><i class="bi bi-trash"></i></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12"><div class="empty-state"><i class="bi bi-box-seam"></i><h5>Belum ada paket</h5></div></div>
    @endforelse
</div>

<!-- Modal Form -->
<div class="modal fade" id="packageModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="POST" id="packageForm" action="{{ route('packages.store') }}">
            @csrf
            <input type="hidden" name="_method" value="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="packageModalTitle">Tambah Paket</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Paket *</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Harga / bulan *</label>
                            <input type="number" name="price_monthly" class="form-control" required min="0" step="1000">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Harga / tahun</label>
                            <input type="number" name="price_yearly" class="form-control" min="0" step="1000">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Maks User *</label>
                            <input type="number" name="max_users" class="form-control" required min="1" value="5">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Maks Invoice *</label>
                            <input type="number" name="max_invoices" class="form-control" required min="1" value="100">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Maks Klien *</label>
                            <input type="number" name="max_clients" class="form-control" required min="1" value="50">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Fitur <small class="text-muted">(satu per baris)</small></label>
                            <textarea name="features" rows="4" class="form-control" placeholder="Invoice & quotation&#10;Notifikasi WA&#10;Laporan lengkap"></textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="pkgActive" value="1" checked>
                                <label class="form-check-label" for="pkgActive">Paket aktif</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-brand text-white"><i class="bi bi-save"></i> Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection
