<?php $__env->startSection('title', 'Aktivitas Log'); ?>

<?php $__env->startSection('content'); ?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h3 class="fw-bold mb-0">🕘 Aktivitas Log</h3>
    <span class="badge bg-secondary-subtle text-secondary fs-6"><?php echo e($logs->total()); ?> aktivitas</span>
</div>

<!-- Filter -->
<form method="GET" class="table-container p-3 mb-4">
    <div class="row g-2 align-items-end">
        <div class="col-md-4 col-6">
            <label class="form-label small fw-semibold">User</label>
            <select name="user_id" class="form-select">
                <option value="">Semua User</option>
                <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($u->id); ?>" <?php echo e(request('user_id') == $u->id ? 'selected' : ''); ?>><?php echo e($u->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div class="col-md-4 col-6">
            <label class="form-label small fw-semibold">Jenis Aksi</label>
            <select name="action" class="form-select">
                <option value="">Semua Aksi</option>
                <?php $__currentLoopData = $actions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($a); ?>" <?php echo e(request('action') == $a ? 'selected' : ''); ?>><?php echo e($a); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div class="col-md-4 col-12">
            <label class="form-label small fw-semibold">Cari</label>
            <div class="d-flex gap-2">
                <input type="text" name="q" class="form-control" value="<?php echo e(request('q')); ?>" placeholder="deskripsi / modul...">
                <button class="btn btn-brand text-white"><i class="bi bi-search"></i></button>
            </div>
        </div>
    </div>
</form>

<!-- Log Table -->
<div class="table-container p-3">
    <?php if($logs->count() > 0): ?>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle responsive-card-table">
            <thead>
                <tr><th>Waktu</th><th>User</th><th>Aksi</th><th>Deskripsi</th><th>IP Address</th></tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td data-label="Waktu">
                        <div class="fw-semibold"><?php echo e($log->created_at->format('d/m/Y')); ?></div>
                        <small class="text-muted"><?php echo e($log->created_at->format('H:i:s')); ?></small>
                    </td>
                    <td data-label="User">
                        <?php if($log->user): ?>
                            <div class="d-flex align-items-center gap-2">
                                <span class="avatar-initial"><?php echo e(strtoupper(substr($log->user->name, 0, 1))); ?></span>
                                <div>
                                    <div class="fw-semibold" style="font-size:.9rem;"><?php echo e($log->user->name); ?></div>
                                    <small class="text-muted"><?php echo e($log->user->role); ?></small>
                                </div>
                            </div>
                        <?php else: ?>
                            <span class="text-muted">System</span>
                        <?php endif; ?>
                    </td>
                    <td data-label="Aksi">
                        <span class="badge badge-status bg-<?php echo e($log->color); ?>">
                            <i class="bi <?php echo e($log->icon); ?>"></i> <?php echo e($log->action); ?>

                        </span>
                    </td>
                    <td data-label="Deskripsi">
                        <span class="fw-semibold" style="font-size:.85rem;"><?php echo e($log->module); ?></span> —
                        <?php echo e($log->description); ?>

                    </td>
                    <td data-label="IP"><small class="text-muted"><?php echo e($log->ip_address); ?></small></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
    <div class="p-3 d-flex justify-content-center">
        <?php echo e($logs->links()); ?>

    </div>
    <?php else: ?>
    <div class="empty-state">
        <i class="bi bi-clock-history"></i>
        <h5>Belum ada aktivitas</h5>
        <p>Aktivitas user akan terekam otomatis di sini.</p>
    </div>
    <?php endif; ?>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/permatabank/Desktop/tysar/a Project/APLIKASI LARAVEL INVOICE FLOW/Invoiceflow-App/resources/views/activity-logs/index.blade.php ENDPATH**/ ?>