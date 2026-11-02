<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InvoiceFlow - Manajemen Invoice Modern</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('img/favicon.svg') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
        h1, h2, h3, h4, h5, h6 { font-family: 'Sora', 'Inter', sans-serif !important; letter-spacing: -0.015em; }
        body { background: linear-gradient(135deg, #1e1b4b 0%, #312e81 35%, #4338ca 70%, #6d28d9 100%); min-height: 100vh; }
        .hero { color: #fff; padding: 80px 0; }
        .hero h1 { font-size: 48px; font-weight: 800; }
        .hero .lead { font-size: 20px; opacity: 0.9; }
        .feature-card {
            background: #fff;
            border-radius: 16px;
            padding: 30px;
            text-align: center;
            transition: transform 0.2s;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        .feature-card:hover { transform: translateY(-5px); }
        .feature-card .icon { font-size: 40px; color: #667eea; margin-bottom: 16px; }
        .btn-hero { padding: 14px 36px; font-size: 18px; font-weight: 600; border-radius: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="hero text-center">
            <h1><i class="bi bi-receipt-cutoff"></i> InvoiceFlow</h1>
            <p class="lead mt-3">Aplikasi manajemen invoice modern untuk bisnis Anda. <br>Buat, kirim, dan pantau invoice dengan mudah.</p>
            <div class="mt-4">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-light btn-hero me-3">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-light btn-hero me-3">
                        <i class="bi bi-box-arrow-in-right"></i> Masuk
                    </a>
                    <a href="{{ route('register') }}" class="btn btn-outline-light btn-hero">
                        <i class="bi bi-person-plus"></i> Daftar
                    </a>
                @endauth
            </div>
        </div>

        <div class="row g-4 mt-5 pb-5">
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="icon">📄</div>
                    <h5>Manajemen Invoice</h5>
                    <p class="text-muted">Buat invoice profesional, edit, dan tandai lunas dengan mudah.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="icon">🔔</div>
                    <h5>Notifikasi Otomatis</h5>
                    <p class="text-muted">Pengingat jatuh tempo via WhatsApp & Email, anti lupa tagih!</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="icon">📤</div>
                    <h5>Kirim Invoice</h5>
                    <p class="text-muted">Kirim invoice langsung via WhatsApp & Email ke klien.</p>
                </div>
            </div>
        </div>

        <footer class="text-center pb-4">
            <p class="mb-0" style="color: rgba(224,231,255,.65); font-size:.82rem;">
                © {{ date('Y') }} InvoiceFlow · Developed by
                <a href="https://digimagine.web.id" target="_blank" rel="noopener noreferrer" style="color:#a5b4fc; font-weight:600; text-decoration:none;">Digital Imagine Indonesia</a>
            </p>
        </footer>
    </div>
</body>
</html>
