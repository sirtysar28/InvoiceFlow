
<?php $hu = auth()->user(); ?>
<div class="d-flex align-items-center gap-2 ha-cluster">

    
    <div class="dropdown">
        <button class="ha-btn ha-bell" type="button" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false" title="Notifikasi">
            <i class="bi bi-bell"></i>
            <?php if($headerNotifCount > 0): ?>
                <span class="ha-badge"><?php echo e($headerNotifCount > 9 ? '9+' : $headerNotifCount); ?></span>
            <?php endif; ?>
        </button>
        <div class="dropdown-menu dropdown-menu-end ha-menu ha-notif-menu">
            <div class="ha-menu-head">
                <span>Notifikasi</span>
                <?php if($headerNotifCount > 0): ?>
                    <span class="badge rounded-pill" style="background:#ef4444;"><?php echo e($headerNotifCount); ?></span>
                <?php endif; ?>
            </div>
            <?php if(count($headerNotifs) > 0): ?>
                <?php $__currentLoopData = $headerNotifs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $n): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e($n['url']); ?>" class="dropdown-item ha-notif-item">
                        <span class="ha-notif-icon" style="background: <?php echo e($n['color']); ?>;"><i class="bi bi-<?php echo e($n['icon']); ?>"></i></span>
                        <span class="ha-notif-body">
                            <span class="ha-notif-title"><?php echo e($n['title']); ?></span>
                            <span class="ha-notif-desc"><?php echo e($n['desc']); ?></span>
                        </span>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php else: ?>
                <div class="ha-notif-empty"><i class="bi bi-check2-circle d-block mb-1" style="font-size:1.5rem;"></i>Semua beres, tidak ada notifikasi 🎉</div>
            <?php endif; ?>
        </div>
    </div>

    
    <div class="dropdown">
        <button class="ha-btn ha-avatar" type="button" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false" title="<?php echo e($hu->name); ?>">
            <?php echo e(strtoupper(substr($hu->name, 0, 1))); ?>

        </button>
        <div class="dropdown-menu dropdown-menu-end ha-menu">
            <div class="ha-user-head">
                <div class="ha-user-avatar"><?php echo e(strtoupper(substr($hu->name, 0, 1))); ?></div>
                <div class="text-truncate" style="min-width:0;">
                    <div class="fw-bold text-truncate" style="font-family:'Sora',sans-serif; font-size:.9rem;"><?php echo e($hu->name); ?></div>
                    <span class="badge bg-<?php echo e($hu->roleColor()); ?>" style="font-size:.66rem;"><?php echo e($hu->roleLabel()); ?></span>
                </div>
            </div>
            <a class="dropdown-item ha-item" href="<?php echo e(route('dashboard')); ?>"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
            <?php if($hu->canManageSettings()): ?>
                <a class="dropdown-item ha-item" href="<?php echo e(route('profile.business')); ?>"><i class="bi bi-building me-2"></i>Profil Bisnis</a>
            <?php endif; ?>
            <?php if($hu->isKlien()): ?>
                <a class="dropdown-item ha-item" href="<?php echo e(route('customers.history')); ?>"><i class="bi bi-clock-history me-2"></i>Riwayat Transaksi</a>
            <?php endif; ?>
            <?php if($hu->isAdmin()): ?>
                <a class="dropdown-item ha-item" href="<?php echo e(route('users.index')); ?>"><i class="bi bi-person-badge me-2"></i>Master User</a>
            <?php endif; ?>
            <div class="dropdown-divider"></div>
            <form method="POST" action="<?php echo e(route('logout')); ?>">
                <?php echo csrf_field(); ?>
                <button type="submit" class="dropdown-item ha-item text-danger fw-semibold">
                    <i class="bi bi-box-arrow-right me-2"></i>Keluar
                </button>
            </form>
        </div>
    </div>
</div>
<?php /**PATH /Users/permatabank/Desktop/tysar/a Project/APLIKASI LARAVEL INVOICE FLOW/Invoiceflow-App/resources/views/layouts/partials/header-actions.blade.php ENDPATH**/ ?>