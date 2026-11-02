<!DOCTYPE html>
<html lang="id">
@php
    // Brand dinamis dari Profil Bisnis (logo & nama produk) — nyambung dengan landing
    $brandProfile = \App\Models\BusinessProfile::whereNotNull('logo')->orderBy('id')->first()
        ?: \App\Models\BusinessProfile::orderBy('id')->first();
    $brandLogo = $brandProfile?->logo;
    $brandName = $brandProfile?->business_name ?: config('app.name');
@endphp
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login · {{ $brandName }}</title>
    @if($brandLogo)
        <link rel="icon" type="image/png" href="{{ asset('storage/' . $brandLogo) }}">
        <link rel="shortcut icon" type="image/png" href="{{ asset('storage/' . $brandLogo) }}">
    @else
        <link rel="icon" type="image/svg+xml" href="{{ asset('img/favicon.svg') }}">
        <link rel="shortcut icon" type="image/svg+xml" href="{{ asset('img/favicon.svg') }}">
    @endif

    {{-- ===== PWA ===== --}}
    <link rel="manifest" href="{{ route('pwa.manifest') }}">
    <meta name="theme-color" content="#6366f1">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="{{ $brandName }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-light: #6366f1;
            --primary-dark: #3730a3;
            --ink: #0f172a;
            --muted: #64748b;
            --border: #e2e8f0;
        }

        * { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }

        html, body { height: 100%; }

        body {
            margin: 0;
            background: #f8fafc;
            min-height: 100vh;
            display: flex;
            overflow-x: hidden;
        }

        h1, h2, h3, h4, h5, h6, .font-header { font-family: 'Sora', 'Inter', sans-serif !important; }

        /* ====== Layout: form kiri, branding kanan ====== */
        /* Split di-lock setinggi viewport: kolom KIRI scroll sendiri,
           kolom KANAN (branding) tetap stay di tempat */
        .auth-split { display: flex; width: 100%; height: 100vh; height: 100dvh; }

        .auth-left {
            flex: 0 0 480px;
            max-width: 480px;
            width: 100%;
            background: #fff;
            display: flex;
            flex-direction: column;
            /* SAFE CENTERING: center saat muat, scrollable saat konten lebih tinggi */
            justify-content: flex-start;
            padding: 40px 48px;
            position: relative;
            z-index: 2;
            box-shadow: 12px 0 40px rgba(15, 23, 42, 0.06);
            overflow-y: auto;             /* ← scroll independen di kolom kiri */
            overscroll-behavior: contain;
            scrollbar-width: thin;
            scrollbar-color: #c7d2fe transparent;
        }
        .auth-left::-webkit-scrollbar { width: 6px; }
        .auth-left::-webkit-scrollbar-thumb { background: #c7d2fe; border-radius: 99px; }
        .auth-left > div { margin: auto 0; }

        .auth-right {
            flex: 1;
            position: relative;
            background: linear-gradient(135deg, #1e1b4b 0%, #312e81 35%, #4338ca 70%, #6d28d9 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 48px;
            overflow-x: hidden;
            overflow-y: auto;   /* fallback: window sangat pendek → ikut scroll, bukan kepotong */
        }

        /* Dekorasi mengambang halus di panel kanan */
        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(60px);
            opacity: .45;
            animation: float 9s ease-in-out infinite;
            pointer-events: none;
        }
        .orb-1 { width: 340px; height: 340px; background: #818cf8; top: -80px; right: -60px; }
        .orb-2 { width: 300px; height: 300px; background: #a78bfa; bottom: -100px; left: -40px; animation-delay: -3s; }
        .orb-3 { width: 180px; height: 180px; background: #38bdf8; bottom: 20%; right: 15%; opacity: .25; animation-delay: -6s; }

        @keyframes float {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-24px) scale(1.05); }
        }

        .grid-overlay {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.05) 1px, transparent 1px);
            background-size: 44px 44px;
            pointer-events: none;
        }

        /* ====== Kartu form ====== */
        .brand-badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 8px 14px;
            background: #eef2ff;
            border: 1px solid #c7d2fe;
            border-radius: 999px;
            font-size: .8rem;
            font-weight: 600;
            color: var(--primary-dark);
            width: fit-content;
        }

        .brand-badge img { width: 22px; height: 22px; }

        .auth-title { font-size: 1.65rem; font-weight: 700; color: var(--ink); letter-spacing: -0.02em; }
        .auth-subtitle { color: var(--muted); font-size: .9rem; }

        .form-label-modern {
            font-size: .8rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 6px;
            letter-spacing: .01em;
        }

        .input-modern {
            width: 100%;
            border: 1.5px solid var(--border);
            border-radius: 12px;
            padding: 11px 14px;
            font-size: .92rem;
            color: var(--ink);
            background: #f8fafc;
            transition: border-color .2s, box-shadow .2s, background .2s;
            outline: none;
        }
        .input-modern:focus {
            border-color: var(--primary-light);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, .14);
        }

        .input-password-wrapper { position: relative; }
        .input-password-wrapper .input-modern { padding-right: 46px; }

        .toggle-password {
            position: absolute;
            right: 6px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            color: #94a3b8;
            padding: 8px;
            border-radius: 8px;
            font-size: 1.05rem;
            line-height: 1;
            cursor: pointer;
            transition: color .2s, background .2s;
        }
        .toggle-password:hover { color: var(--primary); background: #eef2ff; }

        /* ====== Captcha ====== */
        .captcha-box {
            display: flex;
            align-items: stretch;
            gap: 10px;
        }
        .captcha-box .input-modern { flex: 1; letter-spacing: .35em; font-weight: 600; text-transform: uppercase; }
        .captcha-img-wrap {
            border: 1.5px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
            display: flex;
            align-items: center;
            background: #eef2ff;
            flex: 0 0 auto;
        }
        .captcha-img {
            display: block;
            height: 46px;
            width: auto;
            user-select: none;
            -webkit-user-drag: none;
        }
        .btn-captcha-refresh {
            flex: 0 0 auto;
            border: 1.5px solid var(--border);
            background: #fff;
            color: var(--muted);
            border-radius: 12px;
            width: 46px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.05rem;
            cursor: pointer;
            transition: all .2s;
        }
        .btn-captcha-refresh:hover { color: var(--primary); border-color: #c7d2fe; background: #eef2ff; }
        .btn-captcha-refresh.spinning i { animation: spin .6s ease; }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ====== Tombol ====== */
        .btn-signin {
            width: 100%;
            border: none;
            border-radius: 12px;
            padding: 12px 16px;
            font-weight: 600;
            font-size: .95rem;
            color: #fff;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            background-size: 150% 150%;
            box-shadow: 0 8px 20px rgba(79, 70, 229, .32);
            transition: transform .2s, box-shadow .2s, background-position .4s;
            cursor: pointer;
        }
        .btn-signin:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(79, 70, 229, .42);
            background-position: 100% 50%;
            color: #fff;
        }
        .btn-signin:active { transform: translateY(0); }

        .form-check-input:checked { background-color: var(--primary); border-color: var(--primary); }
        .form-check-input { border-radius: 6px !important; }

        .link-accent { color: var(--primary); font-weight: 600; text-decoration: none; transition: color .2s; }
        .link-accent:hover { color: var(--primary-dark); text-decoration: underline; }

        .alert-modern {
            border: none;
            border-radius: 12px;
            border-left: 4px solid #ef4444;
            background: #fef2f2;
            color: #991b1b;
            font-size: .86rem;
            padding: 12px 14px;
            animation: shakeIn .4s ease;
        }
        @keyframes shakeIn {
            0% { opacity: 0; transform: translateX(-8px); }
            50% { transform: translateX(4px); }
            100% { opacity: 1; transform: translateX(0); }
        }

        /* ====== Panel kanan ====== */
        .showcase { position: relative; z-index: 2; max-width: 480px; color: #fff; }
        .showcase-logo {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 28px;
        }
        .showcase-logo img { width: 46px; height: 46px; }
        .showcase-logo span {
            font-family: 'Sora', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: -0.02em;
        }
        .showcase h2 {
            font-size: clamp(1.6rem, 2.6vw, 2.3rem);
            font-weight: 700;
            line-height: 1.2;
            letter-spacing: -0.02em;
            margin-bottom: 14px;
        }
        .showcase p.lead-sm { color: rgba(224, 231, 255, .85); font-size: .98rem; line-height: 1.7; }

        .feature-list { list-style: none; padding: 0; margin: 28px 0 0; }
        .feature-list li {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 10px 0;
            color: rgba(224, 231, 255, .95);
            font-size: .92rem;
        }
        .feature-list .fi {
            flex: 0 0 34px;
            height: 34px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, .12);
            border: 1px solid rgba(255, 255, 255, .18);
            backdrop-filter: blur(6px);
            font-size: .95rem;
        }

        .stat-row { display: flex; gap: 14px; margin-top: 30px; flex-wrap: wrap; }
        .stat-card {
            flex: 1 1 120px;
            background: rgba(255, 255, 255, .08);
            border: 1px solid rgba(255, 255, 255, .16);
            backdrop-filter: blur(10px);
            border-radius: 14px;
            padding: 14px 16px;
        }
        .stat-card .num { font-family: 'Sora', sans-serif; font-size: 1.35rem; font-weight: 700; }
        .stat-card .lbl { font-size: .74rem; color: rgba(224, 231, 255, .75); text-transform: uppercase; letter-spacing: .06em; }

        /* ====== Footer credit ====== */
        .dev-credit {
            text-align: center;
            margin-top: 28px;
            padding-top: 18px;
            border-top: 1px solid var(--border);
            font-size: .78rem;
            color: #94a3b8;
        }
        .dev-credit a {
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
            transition: color .2s;
        }
        .dev-credit a:hover { color: var(--primary-dark); text-decoration: underline; }

        /* Animasi masuk halus */
        .fade-up { animation: fadeUp .6s cubic-bezier(.22,1,.36,1) both; }
        .fade-up-1 { animation-delay: .05s; }
        .fade-up-2 { animation-delay: .15s; }
        .fade-up-3 { animation-delay: .25s; }
        .fade-up-4 { animation-delay: .35s; }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(14px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* ====== Responsive ====== */
        @media (max-width: 991.98px) {
            .auth-split { flex-direction: column; }
            .auth-left {
                flex: 1 1 auto;
                max-width: 100%;
                min-height: 0;
                padding: 40px 28px;
            }
            .auth-right { display: none; }
        }
        @media (max-width: 575.98px) {
            .auth-left { padding: 32px 20px; }
            .auth-title { font-size: 1.4rem; }
        }
    </style>
</head>
<body>
    <div class="auth-split">

        <!-- ====== KOLOM KIRI: FORM LOGIN ====== -->
        <div class="auth-left">
            <div>
                <div class="brand-badge fade-up fade-up-1">
                    @if($brandLogo)
                        <img src="{{ asset('storage/' . $brandLogo) }}" alt="Logo {{ $brandName }}">
                    @else
                        <img src="{{ asset('img/favicon.svg') }}" alt="Logo {{ $brandName }}">
                    @endif
                    {{ $brandName }}
                </div>

                <div class="mt-4 fade-up fade-up-1">
                    <h1 class="auth-title mb-1">Selamat Datang 👋</h1>
                    <p class="auth-subtitle mb-0">Masuk untuk mengelola invoice bisnis Anda</p>
                </div>

                @if($errors->any())
                    <div class="alert alert-modern mt-3 fade-up fade-up-2" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="mt-4">
                    @csrf

                    <div class="mb-3 fade-up fade-up-2">
                        <label for="email" class="form-label-modern">Email</label>
                        <input type="email" id="email" name="email" class="input-modern"
                               value="{{ old('email') }}" placeholder="nama@perusahaan.com" required autofocus autocomplete="email">
                    </div>

                    <div class="mb-3 fade-up fade-up-2">
                        <label for="password" class="form-label-modern">Password</label>
                        <div class="input-password-wrapper">
                            <input type="password" id="password" name="password" class="input-modern"
                                   placeholder="••••••••" required autocomplete="current-password">
                            <button type="button" class="toggle-password" id="togglePassword"
                                    aria-label="Tampilkan password" title="Intip password">
                                <i class="bi bi-eye" id="togglePasswordIcon"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Captcha 5 karakter -->
                    <div class="mb-3 fade-up fade-up-3">
                        <label for="captcha" class="form-label-modern">Kode Keamanan (CAPTCHA)</label>
                        <div class="captcha-box">
                            <input type="text" id="captcha" name="captcha" class="input-modern"
                                   placeholder="•••••" maxlength="5" required autocomplete="off" spellcheck="false">
                            <div class="captcha-img-wrap" title="Kode keamanan">
                                <img src="{{ route('captcha.login') }}?t={{ uniqid() }}" alt="CAPTCHA" class="captcha-img" id="captchaImg">
                            </div>
                            <button type="button" class="btn-captcha-refresh" id="captchaRefresh"
                                    aria-label="Ganti kode CAPTCHA" title="Ganti kode">
                                <i class="bi bi-arrow-clockwise"></i>
                            </button>
                        </div>
                        <small class="text-muted" style="font-size:.75rem;">Ketik 5 karakter seperti gambar di samping</small>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4 fade-up fade-up-3">
                        <div class="form-check">
                            <input type="checkbox" name="remember" class="form-check-input" id="remember">
                            <label class="form-check-label" for="remember" style="font-size:.85rem; color:#475569;">Ingat saya</label>
                        </div>
                        <a href="/" class="link-accent" style="font-size:.85rem;">← Beranda</a>
                    </div>

                    <div class="fade-up fade-up-4">
                        <button type="submit" class="btn-signin">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
                        </button>
                    </div>
                </form>

                <p class="text-center mt-4 mb-0 fade-up fade-up-4" style="font-size:.88rem; color:#475569;">
                    Belum punya akun? <a href="{{ route('register') }}" class="link-accent">Daftar sekarang</a>
                </p>
            </div>

            <!-- Credit -->
            <div class="dev-credit fade-up fade-up-4">
                © {{ date('Y') }} {{ $brandName }} · Developed by
                <a href="https://digimagine.web.id" target="_blank" rel="noopener noreferrer">Digital Imagine Indonesia</a>
            </div>
        </div>

        <!-- ====== KOLOM KANAN: BRANDING ====== -->
        <div class="auth-right">
            <div class="orb orb-1"></div>
            <div class="orb orb-2"></div>
            <div class="orb orb-3"></div>
            <div class="grid-overlay"></div>

            <div class="showcase">
                <div class="showcase-logo fade-up fade-up-1">
                    @if($brandLogo)
                        <img src="{{ asset('storage/' . $brandLogo) }}" alt="{{ $brandName }}">
                    @else
                        <img src="{{ asset('img/favicon.svg') }}" alt="{{ $brandName }}">
                    @endif
                    <span>{{ $brandName }}</span>
                </div>

                <h2 class="fade-up fade-up-2">Kelola Invoice Lebih Cepat, Tertagih Lebih Tepat.</h2>
                <p class="lead-sm fade-up fade-up-2">
                    Satu platform untuk membuat, mengirim, dan memantau invoice —
                    lengkap dengan pengingat otomatis via Email &amp; WhatsApp.
                </p>

                <ul class="feature-list fade-up fade-up-3">
                    <li>
                        <span class="fi"><i class="bi bi-lightning-charge"></i></span>
                        <span>Buat invoice profesional dalam hitungan detik</span>
                    </li>
                    <li>
                        <span class="fi"><i class="bi bi-bell"></i></span>
                        <span>Notifikasi otomatis H-1 s.d. H-7 sebelum jatuh tempo</span>
                    </li>
                    <li>
                        <span class="fi"><i class="bi bi-graph-up-arrow"></i></span>
                        <span>Pantau piutang &amp; arus kas secara real-time</span>
                    </li>
                </ul>

                <div class="stat-row fade-up fade-up-4">
                    <div class="stat-card">
                        <div class="num">2x</div>
                        <div class="lbl">Lebih cepat tertagih</div>
                    </div>
                    <div class="stat-card">
                        <div class="num">100%</div>
                        <div class="lbl">Aman &amp; terenkripsi</div>
                    </div>
                    <div class="stat-card">
                        <div class="num">24/7</div>
                        <div class="lbl">Pengingat otomatis</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // ===== Intip password =====
        document.getElementById('togglePassword').addEventListener('click', function () {
            const input = document.getElementById('password');
            const icon = document.getElementById('togglePasswordIcon');
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            icon.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
            this.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
        });

        // ===== Refresh CAPTCHA =====
        const captchaImg = document.getElementById('captchaImg');
        const refreshBtn = document.getElementById('captchaRefresh');

        function refreshCaptcha() {
            captchaImg.style.opacity = '0.25';
            captchaImg.src = '{{ route('captcha.login') }}?t=' + Date.now();
        }

        captchaImg.addEventListener('load', () => { captchaImg.style.opacity = '1'; });

        refreshBtn.addEventListener('click', function () {
            this.classList.remove('spinning');
            void this.offsetWidth; // restart animasi
            this.classList.add('spinning');
            refreshCaptcha();
            document.getElementById('captcha').focus();
        });

        // Auto refresh captcha saat halaman dimuat dari cache browser (back button)
        window.addEventListener('pageshow', (e) => {
            if (e.persisted) refreshCaptcha();
        });
    </script>
</body>
<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
        navigator.serviceWorker.register('/sw.js').catch(function () {});
    });
}
</script>
</html>
