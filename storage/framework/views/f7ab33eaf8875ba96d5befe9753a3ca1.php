<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - InvoiceFlow</title>
    <link rel="icon" type="image/svg+xml" href="<?php echo e(asset('img/favicon.svg')); ?>">
    <link rel="shortcut icon" type="image/svg+xml" href="<?php echo e(asset('img/favicon.svg')); ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f1f5f9; min-height: 100vh; display: flex; align-items: center; padding: 16px 0; }
        .auth-card {
            background: #fff;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            max-width: 440px;
            width: 100%;
        }
        @media (max-width: 575.98px) {
            .auth-card {
                padding: 28px 20px;
                border-radius: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="container d-flex justify-content-center">
        <div class="auth-card">
            <div class="text-center mb-4">
                <img src="<?php echo e(asset('img/favicon.svg')); ?>" alt="Logo InvoiceFlow" width="80" height="80">
                <h3>InvoiceFlow</h3>
                <p class="text-muted">Masuk ke akun Anda</p>
            </div>

            <?php if($errors->any()): ?>
                <div class="alert alert-danger"><?php echo e($errors->first()); ?></div>
            <?php endif; ?>

            <form method="POST" action="<?php echo e(route('login')); ?>">
                <?php echo csrf_field(); ?>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?php echo e(old('email')); ?>" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" name="remember" class="form-check-input" id="remember">
                    <label class="form-check-label" for="remember">Ingat saya</label>
                </div>
                <button type="submit" class="btn w-100 text-white" style="background:#4f46e5;">
                    <i class="bi bi-box-arrow-in-right"></i> Masuk
                </button>
            </form>

            <p class="text-center mt-3 mb-0">
                Belum punya akun? <a href="<?php echo e(route('register')); ?>">Daftar</a>
            </p>
            <p class="text-center mt-2">
                <a href="/" class="text-muted">← Kembali ke beranda</a>
            </p>
        </div>
    </div>
</body>
</html>
<?php /**PATH /Users/permatabank/Desktop/tysar/a Project/Aplikasi INVOICE FLOW/Invoiceflow-App/resources/views/auth/login.blade.php ENDPATH**/ ?>