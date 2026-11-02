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
    <title>Daftar · {{ $brandName }}</title>
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
            /* SAFE CENTERING: div pembungkus pake margin:auto — form tetap di tengah
               saat muat, tapi saat konten LEBIH TINGGI dari layar tetap bisa discroll
               penuh (tidak terpotong atas seperti justify-content:center) */
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

        /* ===== Toggle jenis akun (Perusahaan vs Customer) ===== */
        .account-type-toggle { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .at-opt { position: relative; margin: 0; }
        .at-opt input { position: absolute; opacity: 0; }
        .at-card {
            display: flex; flex-direction: column; align-items: center; gap: 3px;
            border: 1.5px solid var(--border); border-radius: 13px;
            padding: 14px 8px; cursor: pointer; transition: all .2s;
            background: #fff; height: 100%;
        }
        .at-card i { font-size: 1.45rem; color: var(--muted); transition: color .2s; }
        .at-title { font-weight: 700; font-size: .92rem; color: var(--ink); }
        .at-sub { font-size: .72rem; color: var(--muted); text-align: center; }
        .at-opt input:checked + .at-card {
            border-color: var(--primary); background: #eef2ff;
            box-shadow: 0 8px 20px -10px rgba(79, 70, 229, .45);
        }
        .at-opt input:checked + .at-card i { color: var(--primary); }

        /* ===== Pilihan paket langganan ===== */
        .package-list { display: flex; flex-direction: column; gap: 8px; }
        .pkg-opt { position: relative; margin: 0; }
        .pkg-opt input { position: absolute; opacity: 0; }
        .pkg-card {
            display: flex; flex-direction: column; gap: 3px; width: 100%;
            border: 1.5px solid var(--border); border-radius: 13px;
            padding: 11px 14px; cursor: pointer; transition: all .2s; background: #fff;
        }
        .pkg-head { display: flex; justify-content: space-between; align-items: center; gap: 8px; flex-wrap: wrap; }
        .pkg-name { font-weight: 700; font-size: .92rem; color: var(--ink); }
        .pkg-trial {
            font-size: .7rem; font-weight: 800; color: #047857;
            background: #d1fae5; padding: 3px 10px; border-radius: 999px;
        }
        .pkg-price { font-weight: 800; font-size: .82rem; color: var(--primary); }
        .pkg-price small { font-weight: 600; color: var(--muted); font-size: .68rem; }
        .pkg-meta { font-size: .76rem; color: var(--muted); }
        .pkg-opt input:checked + .pkg-card {
            border-color: var(--primary); background: #eef2ff;
            box-shadow: 0 8px 20px -10px rgba(79, 70, 229, .45);
        }
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

        .btn-signup {
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
        .btn-signup:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(79, 70, 229, .42);
            background-position: 100% 50%;
            color: #fff;
        }
        .btn-signup:active { transform: translateY(0); }

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

        .showcase { position: relative; z-index: 2; max-width: 480px; color: #fff; }
        .showcase-logo { display: inline-flex; align-items: center; gap: 12px; margin-bottom: 28px; }
        .showcase-logo img { width: 46px; height: 46px; }
        .showcase-logo span { font-family: 'Sora', sans-serif; font-size: 1.5rem; font-weight: 700; letter-spacing: -0.02em; }
        .showcase h2 { font-size: clamp(1.6rem, 2.6vw, 2.3rem); font-weight: 700; line-height: 1.2; letter-spacing: -0.02em; margin-bottom: 14px; }
        .showcase p.lead-sm { color: rgba(224, 231, 255, .85); font-size: .98rem; line-height: 1.7; }

        .feature-list { list-style: none; padding: 0; margin: 28px 0 0; }
        .feature-list li { display: flex; align-items: flex-start; gap: 12px; padding: 10px 0; color: rgba(224, 231, 255, .95); font-size: .92rem; }
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

        .dev-credit {
            text-align: center;
            margin-top: 24px;
            padding-top: 18px;
            border-top: 1px solid var(--border);
            font-size: .78rem;
            color: #94a3b8;
        }
        .dev-credit a { color: var(--primary); font-weight: 600; text-decoration: none; transition: color .2s; }
        .dev-credit a:hover { color: var(--primary-dark); text-decoration: underline; }

        .fade-up { animation: fadeUp .6s cubic-bezier(.22,1,.36,1) both; }
        .fade-up-1 { animation-delay: .05s; }
        .fade-up-2 { animation-delay: .15s; }
        .fade-up-3 { animation-delay: .25s; }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(14px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 991.98px) {
            .auth-split { flex-direction: column; }
            .auth-left { flex: 1 1 auto; max-width: 100%; min-height: 0; padding: 40px 28px; }
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

        <!-- ====== KOLOM KIRI: FORM ====== -->
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
                    <h1 class="auth-title mb-1">Buat Akun Baru</h1>
                    <p class="auth-subtitle mb-0">Mulai kelola invoice bisnis Anda secara gratis</p>
                </div>

                @if($errors->any())
                    <div class="alert alert-modern mt-3 fade-up fade-up-2" role="alert">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}" class="mt-4">
                    @csrf

                    {{-- ===== JENIS AKUN: Perusahaan (tenant) vs Customer ===== --}}
                    <div class="mb-3 fade-up fade-up-2">
                        <label class="form-label-modern">Saya mendaftar sebagai</label>
                        <div class="account-type-toggle">
                            <label class="at-opt">
                                <input type="radio" name="account_type" value="company" {{ old('account_type', 'company') === 'company' ? 'checked' : '' }} onchange="toggleAccountType()">
                                <span class="at-card">
                                    <i class="bi bi-building-gear"></i>
                                    <span class="at-title">Perusahaan</span>
                                    <span class="at-sub">Pembuat invoice (tenant)</span>
                                </span>
                            </label>
                            <label class="at-opt">
                                <input type="radio" name="account_type" value="customer" {{ old('account_type') === 'customer' ? 'checked' : '' }} onchange="toggleAccountType()">
                                <span class="at-card">
                                    <i class="bi bi-person"></i>
                                    <span class="at-title">Customer</span>
                                    <span class="at-sub">Penerima & pembayar invoice</span>
                                </span>
                            </label>
                        </div>
                    </div>

                    {{-- ===== Khusus Perusahaan: nama bisnis + pilih paket ===== --}}
                    <div id="companyFields">
                        <div class="mb-3 fade-up fade-up-2">
                            <label for="business_name" class="form-label-modern">Nama Perusahaan / Bisnis</label>
                            <input type="text" id="business_name" name="business_name" class="input-modern"
                                   value="{{ old('business_name') }}" placeholder="cth: PT Maju Jaya">
                        </div>

                        <div class="mb-4 fade-up fade-up-2">
                            <label class="form-label-modern">Pilih Paket Langganan</label>
                            <div class="package-list">
                                @foreach($packages as $pkg)
                                <label class="pkg-opt">
                                    <input type="radio" name="package_id" value="{{ $pkg->id }}"
                                           {{ old('package_id') == $pkg->id ? 'checked' : (old('package_id') === null && $pkg->trial_days > 0 ? 'checked' : '') }}>
                                    <span class="pkg-card">
                                        <span class="pkg-head">
                                            <span class="pkg-name">{{ $pkg->name }}</span>
                                            @if($pkg->trial_days > 0)
                                                <span class="pkg-trial">🎉 Trial {{ $pkg->trial_days }} hari</span>
                                            @else
                                                <span class="pkg-price">Rp {{ number_format($pkg->price_monthly, 0, ',', '.') }}<small>/bln</small></span>
                                            @endif
                                        </span>
                                        <span class="pkg-meta">
                                            {{ $pkg->max_users }} user · {{ $pkg->max_invoices }} invoice · {{ $pkg->max_clients }} klien
                                            @if($pkg->trial_days > 0)<span class="text-danger">· setelah trial: aktifkan paket berbayar via token</span>@endif
                                        </span>
                                    </span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="mb-3 fade-up fade-up-2">
                        <label for="name" class="form-label-modern">Nama Lengkap</label>
                        <input type="text" id="name" name="name" class="input-modern"
                               value="{{ old('name') }}" placeholder="Nama Anda" required autofocus>
                    </div>

                    <div class="mb-3 fade-up fade-up-2">
                        <label for="email" class="form-label-modern">Email</label>
                        <input type="email" id="email" name="email" class="input-modern"
                               value="{{ old('email') }}" placeholder="nama@perusahaan.com" required>
                    </div>

                    <div class="mb-3 fade-up fade-up-3">
                        <label for="password" class="form-label-modern">Password</label>
                        <div class="input-password-wrapper">
                            <input type="password" id="password" name="password" class="input-modern"
                                   placeholder="Minimal 8 karakter" required autocomplete="new-password">
                            <button type="button" class="toggle-password" data-target="password"
                                    aria-label="Tampilkan password" title="Intip password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-4 fade-up fade-up-3">
                        <label for="password_confirmation" class="form-label-modern">Konfirmasi Password</label>
                        <div class="input-password-wrapper">
                            <input type="password" id="password_confirmation" name="password_confirmation" class="input-modern"
                                   placeholder="Ulangi password" required autocomplete="new-password">
                            <button type="button" class="toggle-password" data-target="password_confirmation"
                                    aria-label="Tampilkan password" title="Intip password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="fade-up fade-up-3">
                        <button type="submit" class="btn-signup">
                            <i class="bi bi-person-plus me-1"></i> Daftar
                        </button>
                    </div>
                </form>

                <p class="text-center mt-4 mb-0 fade-up fade-up-3" style="font-size:.88rem; color:#475569;">
                    Sudah punya akun? <a href="{{ route('login') }}" class="link-accent">Masuk</a>
                </p>
            </div>

            <div class="dev-credit fade-up fade-up-3">
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

                <h2 class="fade-up fade-up-2">Gabung &amp; Rasakan Alur Invoice Tanpa Ribet.</h2>
                <p class="lead-sm fade-up fade-up-2">
                    Daftar dalam satu menit. Akun Anda akan aktif setelah disetujui oleh admin
                    untuk menjaga keamanan data bisnis.
                </p>

                <ul class="feature-list fade-up fade-up-3">
                    <li>
                        <span class="fi"><i class="bi bi-shield-check"></i></span>
                        <span>Data bisnis Anda aman &amp; terprivasi</span>
                    </li>
                    <li>
                        <span class="fi"><i class="bi bi-people"></i></span>
                        <span>Kolaborasi tim dengan berbagai peran pengguna</span>
                    </li>
                    <li>
                        <span class="fi"><i class="bi bi-whatsapp"></i></span>
                        <span>Kirim invoice via Email &amp; WhatsApp</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        // ===== Show/hide field perusahaan sesuai jenis akun =====
        function toggleAccountType() {
            const isCompany = document.querySelector('input[name="account_type"]:checked')?.value === 'company';
            document.getElementById('companyFields').style.display = isCompany ? '' : 'none';
            // Hapus required field perusahaan saat mode customer
            document.getElementById('business_name')?.toggleAttribute('required', isCompany);
            document.querySelectorAll('input[name="package_id"]').forEach(r => r.toggleAttribute('required', isCompany));
        }
        document.addEventListener('DOMContentLoaded', toggleAccountType);

        // ===== Intip password (semua field) =====
        document.querySelectorAll('.toggle-password').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const input = document.getElementById(this.dataset.target);
                const icon = this.querySelector('i');
                const show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                icon.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
                this.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
            });
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
