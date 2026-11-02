<?php $__env->startSection('title', 'Master User'); ?>
<?php $__env->startSection('content'); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold mb-0">👤 Master Data User</h3>
    <a href="<?php echo e(route('users.create')); ?>" class="btn text-white" style="background:#4f46e5;">
        <i class="bi bi-plus-lg"></i> Tambah User
    </a>
</div>

<!-- Filter -->
<div class="card card-stat p-3 mb-3">
    <form method="GET" action="<?php echo e(route('users.index')); ?>" class="row g-2 align-items-center">
        <div class="col-md-4">
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control" placeholder="Cari nama atau email..." value="<?php echo e(request('search')); ?>">
            </div>
        </div>
        <div class="col-md-2">
            <select name="role" class="form-select">
                <option value="">Semua Role</option>
                <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($key); ?>" <?php echo e(request('role') == $key ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select">
                <option value="">Semua Status</option>
                <option value="active" <?php echo e(request('status') == 'active' ? 'selected' : ''); ?>>Aktif</option>
                <option value="pending" <?php echo e(request('status') == 'pending' ? 'selected' : ''); ?>>Pending</option>
                <option value="rejected" <?php echo e(request('status') == 'rejected' ? 'selected' : ''); ?>>Ditolak</option>
            </select>
        </div>
        <div class="col-md-4 d-flex gap-2">
            <button type="submit" class="btn text-white" style="background:#4f46e5;"><i class="bi bi-funnel"></i> Filter</button>
            <?php if(request('search') || request('role') || request('status')): ?>
            <a href="<?php echo e(route('users.index')); ?>" class="btn btn-outline-secondary">Reset</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="card card-stat p-0">
    <div class="table-responsive">
        <table class="table table-hover mb-0 responsive-card-table">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Role</th>
                    <?php if(auth()->user()->isSuperAdmin()): ?><th>Perusahaan</th><?php endif; ?>
                    <th>Status</th>
                    <th class="text-center">Invoice</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td class="fw-semibold" data-label="Nama"><?php echo e($u->name); ?></td>
                    <td data-label="Email"><?php echo e($u->email); ?></td>
                    <td data-label="Role">
                        <span class="badge bg-<?php echo e($u->roleColor()); ?>" <?php echo e($u->isSuperAdmin() ? 'style="background:#0f172a !important;"' : ''); ?>>
                            <?php echo e($u->isSuperAdmin() ? '🛡️ ' : ''); ?><?php echo e($u->roleLabel()); ?>

                        </span>
                    </td>
                    <?php if(auth()->user()->isSuperAdmin()): ?>
                    <td data-label="Perusahaan"><small><?php echo e($u->company?->name ?? '—'); ?></small></td>
                    <?php endif; ?>
                    <td data-label="Status">
                        <?php if($u->isPending()): ?>
                            <span class="badge bg-secondary">Pending</span>
                            <div class="mt-1 d-flex gap-1">
                                <form action="<?php echo e(route('users.approve', $u)); ?>" method="POST" class="d-inline">
                                    <?php echo csrf_field(); ?>
                                    <button class="btn btn-sm btn-success" title="Setujui">
                                        <i class="bi bi-check"></i>
                                    </button>
                                </form>
                                <form action="<?php echo e(route('users.reject', $u)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Tolak akun <?php echo e($u->name); ?>?')">
                                    <?php echo csrf_field(); ?>
                                    <button class="btn btn-sm btn-outline-danger" title="Tolak">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </form>
                            </div>
                        <?php elseif($u->status === 'rejected'): ?>
                            <span class="badge bg-danger">Ditolak</span>
                        <?php else: ?>
                            <span class="badge bg-success">Aktif</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center" data-label="Invoice"><?php echo e($u->invoices_count); ?></td>
                    <td class="text-end actions-cell" data-label="Aksi">
                        <div class="responsive-actions">
                            
                            <?php if($u->isSuperAdmin() && !auth()->user()->isSuperAdmin()): ?>
                                <span class="badge bg-dark"><i class="bi bi-lock-fill"></i> Terkunci</span>
                            <?php else: ?>
                                <a href="<?php echo e(route('users.edit', $u)); ?>" class="btn btn-sm btn-outline-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <?php if($u->id !== auth()->id()): ?>
                                <form action="<?php echo e(route('users.destroy', $u)); ?>" method="POST" onsubmit="return confirm('Yakin hapus user <?php echo e($u->name); ?>?')" class="d-inline">
                                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                    <button class="btn btn-sm btn-outline-danger" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="<?php echo e(auth()->user()->isSuperAdmin() ? 7 : 6); ?>">
                        <div class="empty-state">
                            <i class="bi bi-person-x"></i>
                            <h5>Tidak ada data user</h5>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="p-3"><?php echo e($users->links()); ?></div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/permatabank/Desktop/tysar/a Project/APLIKASI LARAVEL INVOICE FLOW/Invoiceflow-App/resources/views/users/index.blade.php ENDPATH**/ ?>