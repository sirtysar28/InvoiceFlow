@extends('layouts.app')
@section('title', 'Produk/Jasa')

@push('scripts')
<script>
function editProduct(btn) {
    const d = btn.dataset;
    const f = document.getElementById('productForm');
    f.action = '{{ url('products') }}/' + d.id;
    f.querySelector('input[name=_method]').value = 'PUT';
    f.querySelector('[name=name]').value = d.name;
    f.querySelector('[name=description]').value = d.desc || '';
    f.querySelector('[name=unit]').value = d.unit || 'pcs';
    f.querySelector('[name=price]').value = d.price;
    f.querySelector('[name=is_active]').checked = d.active === '1';
    document.getElementById('productModalTitle').textContent = 'Edit Produk/Jasa';
    new bootstrap.Modal(document.getElementById('productModal')).show();
}
function newProduct() {
    const f = document.getElementById('productForm');
    f.action = '{{ route('products.store') }}';
    f.querySelector('input[name=_method]').value = 'POST';
    f.reset();
    f.querySelector('[name=is_active]').checked = true;
    document.getElementById('productModalTitle').textContent = 'Tambah Produk/Jasa';
    new bootstrap.Modal(document.getElementById('productModal')).show();
}
</script>
@endpush

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1">📦 Produk / Jasa</h3>
        <p class="text-muted mb-0">Katalog produk untuk mempercepat pembuatan invoice & penawaran.</p>
    </div>
    <button class="btn btn-brand text-white" onclick="newProduct()">
        <i class="bi bi-plus-lg"></i> Tambah Produk
    </button>
</div>

<form method="GET" class="table-container p-3 mb-4">
    <div class="d-flex gap-2">
        <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="Cari produk/jasa...">
        <button class="btn btn-brand text-white"><i class="bi bi-search"></i></button>
    </div>
</form>

<div class="table-container p-3">
    @if($products->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle responsive-card-table">
            <thead>
                <tr><th>Nama</th><th>Deskripsi</th><th>Satuan</th><th class="text-end">Harga</th><th>Status</th><th class="text-end">Aksi</th></tr>
            </thead>
            <tbody>
                @foreach($products as $p)
                <tr>
                    <td class="fw-semibold" data-label="Nama">{{ $p->name }}</td>
                    <td data-label="Deskripsi"><small class="text-muted">{{ Str::limit($p->description, 50) }}</small></td>
                    <td data-label="Satuan">{{ $p->unit }}</td>
                    <td class="text-end fw-semibold" data-label="Harga">{{ $p->formattedPrice() }}</td>
                    <td data-label="Status"><span class="badge bg-{{ $p->is_active ? 'success' : 'secondary' }}">{{ $p->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                    <td class="text-end actions-cell" data-label="Aksi">
                        <button class="btn btn-sm btn-outline-primary"
                                data-id="{{ $p->id }}" data-name="{{ $p->name }}" data-desc="{{ $p->description }}"
                                data-unit="{{ $p->unit }}" data-price="{{ $p->price }}"
                                data-active="{{ $p->is_active ? '1' : '0' }}"
                                onclick="editProduct(this)">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <form action="{{ route('products.destroy', $p) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus produk ini?')"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="p-3 d-flex justify-content-center">{{ $products->links() }}</div>
    @else
    <div class="empty-state"><i class="bi bi-box-seam"></i><h5>Belum ada produk</h5><p>Tambahkan produk/jasa agar cepat membuat invoice & quotation.</p></div>
    @endif
</div>

<!-- Modal Form -->
<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" id="productForm" action="{{ route('products.store') }}">
            @csrf
            <input type="hidden" name="_method" value="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="productModalTitle">Tambah Produk/Jasa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama *</label>
                        <input type="text" name="name" class="form-control" required placeholder="Jasa Pembuatan Website">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" rows="2" class="form-control" placeholder="Detail layanan..."></textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">Satuan</label>
                            <input type="text" name="unit" class="form-control" value="pcs" placeholder="pcs / jam / bulan">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Harga *</label>
                            <input type="number" name="price" class="form-control" required min="0" step="0.01">
                        </div>
                    </div>
                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" name="is_active" id="prodActive" value="1" checked>
                        <label class="form-check-label" for="prodActive">Produk aktif</label>
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
