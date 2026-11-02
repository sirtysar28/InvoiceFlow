<?php $__env->startSection('title', 'Produk/Jasa'); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function editProduct(btn) {
    const d = btn.dataset;
    const f = document.getElementById('productForm');
    f.action = '<?php echo e(url('products')); ?>/' + d.id;
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
    f.action = '<?php echo e(route('products.store')); ?>';
    f.querySelector('input[name=_method]').value = 'POST';
    f.reset();
    f.querySelector('[name=is_active]').checked = true;
    document.getElementById('productModalTitle').textContent = 'Tambah Produk/Jasa';
    new bootstrap.Modal(document.getElementById('productModal')).show();
}
</script>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

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
        <input type="text" name="q" class="form-control" value="<?php echo e(request('q')); ?>" placeholder="Cari produk/jasa...">
        <button class="btn btn-brand text-white"><i class="bi bi-search"></i></button>
    </div>
</form>

<div class="table-container p-3">
    <?php if($products->count() > 0): ?>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle responsive-card-table">
            <thead>
                <tr><th>Nama</th><th>Deskripsi</th><th>Satuan</th><th class="text-end">Harga</th><th>Status</th><th class="text-end">Aksi</th></tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td class="fw-semibold" data-label="Nama"><?php echo e($p->name); ?></td>
                    <td data-label="Deskripsi"><small class="text-muted"><?php echo e(Str::limit($p->description, 50)); ?></small></td>
                    <td data-label="Satuan"><?php echo e($p->unit); ?></td>
                    <td class="text-end fw-semibold" data-label="Harga"><?php echo e($p->formattedPrice()); ?></td>
                    <td data-label="Status"><span class="badge bg-<?php echo e($p->is_active ? 'success' : 'secondary'); ?>"><?php echo e($p->is_active ? 'Aktif' : 'Nonaktif'); ?></span></td>
                    <td class="text-end actions-cell" data-label="Aksi">
                        <button class="btn btn-sm btn-outline-primary"
                                data-id="<?php echo e($p->id); ?>" data-name="<?php echo e($p->name); ?>" data-desc="<?php echo e($p->description); ?>"
                                data-unit="<?php echo e($p->unit); ?>" data-price="<?php echo e($p->price); ?>"
                                data-active="<?php echo e($p->is_active ? '1' : '0'); ?>"
                                onclick="editProduct(this)">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <form action="<?php echo e(route('products.destroy', $p)); ?>" method="POST" class="d-inline">
                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                            <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus produk ini?')"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
    <div class="p-3 d-flex justify-content-center"><?php echo e($products->links()); ?></div>
    <?php else: ?>
    <div class="empty-state"><i class="bi bi-box-seam"></i><h5>Belum ada produk</h5><p>Tambahkan produk/jasa agar cepat membuat invoice & quotation.</p></div>
    <?php endif; ?>
</div>

<!-- Modal Form -->
<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" id="productForm" action="<?php echo e(route('products.store')); ?>">
            <?php echo csrf_field(); ?>
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

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/permatabank/Desktop/tysar/a Project/APLIKASI LARAVEL INVOICE FLOW/Invoiceflow-App/resources/views/products/index.blade.php ENDPATH**/ ?>