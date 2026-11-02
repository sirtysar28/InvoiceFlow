@extends('layouts.app')
@section('title', 'Manajemen Perusahaan')

@push('scripts')
<script>
function editCompany(btn) {
    const d = btn.dataset;
    const f = document.getElementById('companyForm');
    f.action = '{{ url('companies') }}/' + d.id;
    f.querySelector('input[name=_method]').value = 'PUT';
    f.querySelector('[name=name]').value = d.name;
    f.querySelector('[name=email]').value = d.email || '';
    f.querySelector('[name=phone]').value = d.phone || '';
    f.querySelector('[name=address]').value = d.address || '';
    f.querySelector('[name=package_id]').value = d.packageId || '';
    f.querySelector('[name=subscription_expires_at]').value = d.expires || '';
    f.querySelector('[name=is_active]').checked = d.active === '1';
    document.getElementById('companyModalTitle').textContent = 'Edit Perusahaan';
    new bootstrap.Modal(document.getElementById('companyModal')).show();
}
function newCompany() {
    const f = document.getElementById('companyForm');
    f.action = '{{ route('companies.store') }}';
    f.querySelector('input[name=_method]').value = 'POST';
    f.reset();
    f.querySelector('[name=is_active]').checked = true;
    document.getElementById('companyModalTitle').textContent = 'Tambah Perusahaan';
    new bootstrap.Modal(document.getElementById('companyModal')).show();
}
</script>
@endpush

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1">🏢 Manajemen Perusahaan</h3>
        <p class="text-muted mb-0">Kelola tenant & langganan paket tiap perusahaan.</p>
    </div>
    <button class="btn btn-brand text-white" onclick="newCompany()">
        <i class="bi bi-plus-lg"></i> Tambah Perusahaan
    </button>
</div>

<div class="table-container p-3">
    @if($companies->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle responsive-card-table">
            <thead>
                <tr><th>Perusahaan</th><th>Paket</th><th class="text-center">User</th><th>Berakhir</th><th>Status</th><th class="text-end">Aksi</th></tr>
            </thead>
            <tbody>
                @foreach($companies as $c)
                <tr>
                    <td class="fw-semibold" data-label="Perusahaan">
                        {{ $c->name }}
                        <div><small class="text-muted">{{ $c->email ?: '—' }}</small></div>
                        @if($c->activation_token)
                            <span class="badge bg-warning text-dark mt-1"><i class="bi bi-key"></i> Token: {{ $c->activation_token }}</span>
                        @endif
                    </td>
                    <td data-label="Paket"><span class="badge bg-primary-subtle text-primary">{{ $c->package?->name ?? '—' }}</span></td>
                    <td class="text-center" data-label="User">{{ $c->users_count }}</td>
                    <td data-label="Berakhir"><small>{{ optional($c->subscription_expires_at)->format('d/m/Y') ?? '∞' }}</small></td>
                    <td data-label="Status"><span class="badge badge-status bg-{{ $c->subscriptionColor() }}">{{ $c->subscriptionStatus() }}</span></td>
                    <td class="text-end actions-cell" data-label="Aksi">
                        <form action="{{ route('companies.token', $c) }}" method="POST" class="d-inline">
                            @csrf
                            <button class="btn btn-sm btn-warning" title="Buat kode token aktivasi (untuk buka kunci trial habis)">
                                <i class="bi bi-key"></i>
                            </button>
                        </form>
                        <form action="{{ route('companies.toggle', $c) }}" method="POST" class="d-inline">
                            @csrf
                            <button class="btn btn-sm {{ $c->is_active ? 'btn-outline-secondary' : 'btn-success' }}"
                                    title="{{ $c->is_active ? 'Kunci akses perusahaan' : 'Buka akses perusahaan' }}">
                                <i class="bi bi-{{ $c->is_active ? 'lock' : 'unlock' }}"></i>
                            </button>
                        </form>
                        <button class="btn btn-sm btn-outline-primary"
                                data-id="{{ $c->id }}" data-name="{{ $c->name }}" data-email="{{ $c->email }}"
                                data-phone="{{ $c->phone }}" data-address="{{ $c->address }}"
                                data-package-id="{{ $c->package_id }}" data-expires="{{ optional($c->subscription_expires_at)->format('Y-m-d') }}"
                                data-active="{{ $c->is_active ? '1' : '0' }}"
                                onclick="editCompany(this)">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <form action="{{ route('companies.destroy', $c) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus perusahaan ini? User terkait tidak terhapus.')">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="p-3 d-flex justify-content-center">{{ $companies->links() }}</div>
    @else
    <div class="empty-state"><i class="bi bi-building"></i><h5>Belum ada perusahaan</h5><p>Tambahkan perusahaan tenant pertama Anda.</p></div>
    @endif
</div>

<!-- Modal Form -->
<div class="modal fade" id="companyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="POST" id="companyForm" action="{{ route('companies.store') }}">
            @csrf
            <input type="hidden" name="_method" value="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="companyModalTitle">Tambah Perusahaan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Nama Perusahaan *</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Telepon</label>
                            <input type="text" name="phone" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Paket Subscription</label>
                            <select name="package_id" class="form-select">
                                <option value="">— Tanpa Paket —</option>
                                @foreach($packages as $pkg)
                                    <option value="{{ $pkg->id }}">{{ $pkg->name }} ({{ $pkg->formattedMonthly() }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Langganan Berakhir</label>
                            <input type="date" name="subscription_expires_at" class="form-control">
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="coActive" value="1" checked>
                                <label class="form-check-label" for="coActive">Perusahaan aktif</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Alamat</label>
                            <textarea name="address" rows="2" class="form-control"></textarea>
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
