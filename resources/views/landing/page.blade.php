<!DOCTYPE html>
<html lang="id">
@php
    // Logo brand landing: milik user yang login, atau profil pertama yang punya logo
    $brandProfile = auth()->check()
        ? \App\Models\BusinessProfile::where('user_id', auth()->id())->first()
        : \App\Models\BusinessProfile::whereNotNull('logo')->orderBy('id')->first();
    $brandLogo = $brandProfile?->logo;
    $brandName = $brandProfile?->business_name ?: config('app.name');
@endphp
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $brandName }} — Manajemen Invoice Modern</title>
    @if($brandLogo)
        <link rel="icon" type="image/png" href="{{ asset('storage/' . $brandLogo) }}">
        <link rel="shortcut icon" type="image/png" href="{{ asset('storage/' . $brandLogo) }}">
    @else
        <link rel="icon" type="image/svg+xml" href="{{ asset('img/favicon.svg') }}">
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
            --grad-brand: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #d946ef 100%);
            --grad-soft: linear-gradient(135deg, #eef2ff 0%, #faf5ff 100%);
        }
        * { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; scroll-behavior: smooth; }
        h1,h2,h3,h4,h5,h6,.font-header { font-family: 'Sora', 'Inter', sans-serif !important; letter-spacing: -0.02em; }
        body { overflow-x: hidden; background: #fff; }
        ::selection { background: #818cf8; color: #fff; }

        /* ===== Navbar ===== */
        .lp-nav {
            position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
            transition: all .35s ease; padding: 18px 0;
        }
        .lp-nav.scrolled {
            background: rgba(255,255,255,.82);
            backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);
            box-shadow: 0 8px 32px -12px rgba(15,23,42,.15);
            padding: 10px 0;
        }
        .lp-nav .brand { font-family:'Sora'; font-weight:800; font-size:1.3rem; color:#fff; text-decoration:none; display:flex; align-items:center; gap:10px; transition:color .35s ease; }
        .lp-nav .brand i { color:#a5b4fc; font-size:1.5rem; }
        .lp-nav .brand img.lp-brand-logo {
            height:36px; width:36px; object-fit:cover; border-radius:11px;
            background:#fff; padding:3px; border:1px solid rgba(255,255,255,.35);
        }
        .lp-nav.scrolled .brand { color:#0f172a; }
        .lp-nav.scrolled .brand i { color:#6366f1; }
        .lp-nav.scrolled .brand img.lp-brand-logo { border-color:#e2e8f0; }
        .lp-nav .nav-link-lp { color:rgba(255,255,255,.88); font-weight:500; text-decoration:none; margin:0 14px; position:relative; transition:color .35s ease; }
        .lp-nav.scrolled .nav-link-lp { color:#475569; }
        .lp-nav .nav-link-lp::after { content:''; position:absolute; left:0; bottom:-4px; width:0; height:2px; background:var(--grad-brand); border-radius:2px; transition:width .25s ease; }
        .lp-nav .nav-link-lp:hover { color:#4f46e5; } .lp-nav .nav-link-lp:hover::after { width:100%; }

        /* ===== Hero ===== */
        .hero {
            position: relative; min-height: 100vh; display:flex; align-items:center;
            background: linear-gradient(160deg,#0f0c29 0%, #1e1b4b 35%, #312e81 65%, #4c1d95 100%);
            overflow: hidden; padding: 130px 0 90px; color:#fff;
        }
        .blob { position:absolute; border-radius:50%; filter: blur(70px); opacity:.5; will-change: transform; }
        .blob-1 { width:480px; height:480px; background:#7c3aed; top:-120px; right:-100px; }
        .blob-2 { width:380px; height:380px; background:#4f46e5; bottom:-140px; left:-80px; animation-delay:1.2s; }
        .blob-3 { width:220px; height:220px; background:#d946ef; top:40%; left:55%; animation-delay:.6s; }
        .hero-grid {
            position:absolute; inset:0; opacity:.14;
            background-image: linear-gradient(rgba(255,255,255,.18) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.18) 1px, transparent 1px);
            background-size: 56px 56px;
            mask-image: radial-gradient(ellipse 90% 70% at 50% 40%, #000 30%, transparent 75%);
            -webkit-mask-image: radial-gradient(ellipse 90% 70% at 50% 40%, #000 30%, transparent 75%);
        }
        .hero-badge {
            display:inline-flex; align-items:center; gap:8px;
            background: rgba(129,140,248,.16); border:1px solid rgba(165,180,252,.35);
            color:#c7d2fe; padding:8px 18px; border-radius:30px; font-size:.85rem; font-weight:600;
            backdrop-filter: blur(8px);
        }
        .hero-title { font-size: clamp(2.3rem, 5vw, 3.9rem); font-weight:800; line-height:1.12; }
        .grad-text { background: linear-gradient(90deg,#a5b4fc 0%,#e879f9 60%,#f0abfc 100%); -webkit-background-clip:text; background-clip:text; color:transparent; }
        .hero-sub { color: rgba(226,232,240,.82); font-size: clamp(1rem, 1.4vw, 1.15rem); max-width: 540px; }
        .btn-glow { background:var(--grad-brand); color:#fff; font-weight:700; padding:14px 32px; border-radius:14px; border:0;
            box-shadow: 0 14px 36px -10px rgba(139,92,246,.75); transition: all .25s ease; text-decoration:none; display:inline-flex; align-items:center; gap:8px; }
        .btn-glow:hover { color:#fff; transform: translateY(-3px); box-shadow: 0 20px 44px -10px rgba(139,92,246,.85); }
        .btn-ghost { color:#e0e7ff; font-weight:600; padding:14px 28px; border-radius:14px; border:1px solid rgba(165,180,252,.4);
            text-decoration:none; display:inline-flex; align-items:center; gap:8px; transition:all .25s ease; background:rgba(255,255,255,.04); }
        .btn-ghost:hover { color:#fff; border-color:#a5b4fc; background:rgba(129,140,248,.15); transform: translateY(-3px); }

        /* Floating invoice mockup */
        .mockup-wrap { position:relative; perspective: 1200px; }
        .mockup {
            background: rgba(255,255,255,.97); border-radius:22px; padding:26px; color:#0f172a;
            box-shadow: 0 40px 90px -20px rgba(0,0,0,.55);
            transform: rotateY(-8deg) rotateX(4deg); animation: floatCard 6s ease-in-out infinite;
            will-change: transform;
        }
        @keyframes floatCard { 0%,100%{ transform: rotateY(-8deg) rotateX(4deg) translateY(0);} 50%{ transform: rotateY(-6deg) rotateX(3deg) translateY(-16px);} }
        .mockup .inv-head { display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid #eef2f7; padding-bottom:14px; margin-bottom:14px; }
        .mockup .inv-no { font-family:'Sora'; font-weight:700; }
        .mockup .badge-lunas { background:#dcfce7; color:#15803d; font-weight:700; font-size:.72rem; padding:6px 12px; border-radius:20px; }
        .mockup .row-line { display:flex; justify-content:space-between; padding:9px 0; border-bottom:1px dashed #e2e8f0; font-size:.9rem; }
        .mockup .row-line .amt { font-weight:600; }
        .mockup .total { display:flex; justify-content:space-between; margin-top:14px; background:#eef2ff; border-radius:12px; padding:12px 16px; font-family:'Sora'; font-weight:800; font-size:1.15rem; color:#4338ca; }
        .chip-float {
            position:absolute; background:#fff; border-radius:14px; padding:12px 16px; display:flex; align-items:center; gap:10px;
            box-shadow: 0 20px 44px -14px rgba(0,0,0,.4); font-size:.85rem; font-weight:700; color:#0f172a;
            animation: floatChip 5s ease-in-out infinite;
        }
        .chip-float .ic { width:34px;height:34px;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:1rem; }
        @keyframes floatChip { 0%,100%{ transform:translateY(0);} 50%{ transform:translateY(-12px);} }

        /* Stats */
        .hero-stats { display:flex; gap:0; margin-top:52px; border-top:1px solid rgba(165,180,252,.25); padding-top:28px; flex-wrap:wrap; }
        .stat-item { flex:1 1 130px; text-align:center; padding:0 10px; }
        .stat-item + .stat-item { border-left:1px solid rgba(165,180,252,.25); }
        .stat-item .num { font-family:'Sora'; font-size: clamp(1.5rem,2.6vw,2.1rem); font-weight:800; }
        .stat-item .lbl { color:#a5b4fc; font-size:.8rem; font-weight:500; }

        /* ===== Section umum ===== */
        section { position:relative; }
        .sec { padding: 96px 0; }
        .sec-label { display:inline-flex; align-items:center; gap:8px; background:#eef2ff; color:#4f46e5; font-weight:700; font-size:.78rem; letter-spacing:.08em; text-transform:uppercase; padding:8px 16px; border-radius:30px; }
        .sec-title { font-size: clamp(1.7rem, 3.4vw, 2.6rem); font-weight:800; color:#0f172a; margin-top:18px; }
        .sec-sub { color:#64748b; font-size:1.05rem; max-width:620px; margin:14px auto 0; }

        /* Features */
        .feat-card {
            background:#fff; border:1px solid #eef2f7; border-radius:20px; padding:30px 26px; height:100%;
            transition: all .3s cubic-bezier(.22,1,.36,1); position:relative; overflow:hidden;
        }
        .feat-card::before { content:''; position:absolute; inset:0 0 auto 0; height:4px; background:var(--grad-brand); transform:scaleX(0); transform-origin:left; transition:transform .35s ease; }
        .feat-card:hover { transform:translateY(-8px); box-shadow: 0 24px 52px -18px rgba(79,70,229,.28); border-color:transparent; }
        .feat-card:hover::before { transform:scaleX(1); }
        .feat-icon { width:58px; height:58px; border-radius:16px; display:flex; align-items:center; justify-content:center; font-size:1.5rem; color:#fff; background:var(--grad-brand); box-shadow:0 12px 26px -10px rgba(99,102,241,.6); margin-bottom:20px; }

        /* Steps */
        .steps-sec { background: var(--grad-soft); }
        .step-card { position:relative; background:#fff; border-radius:20px; padding:34px 26px 30px; text-align:center; box-shadow: 0 12px 34px -16px rgba(15,23,42,.12); transition:transform .3s ease; }
        .step-card:hover { transform: translateY(-6px); }
        .step-num { position:absolute; top:-24px; left:50%; transform:translateX(-50%); width:48px; height:48px; border-radius:50%;
            background:var(--grad-brand); color:#fff; font-family:'Sora'; font-weight:800; font-size:1.15rem; display:flex; align-items:center; justify-content:center;
            box-shadow: 0 12px 26px -8px rgba(99,102,241,.6); border: 5px solid #eef2ff; }

        /* Testimonials */
        .testi-card { background:#fff; border:1px solid #eef2f7; border-radius:20px; padding:28px; height:100%; transition: all .3s ease; }
        .testi-card:hover { transform:translateY(-6px); box-shadow: 0 20px 48px -18px rgba(15,23,42,.2); }
        .testi-quote { font-size:2.6rem; line-height:1; background:var(--grad-brand); -webkit-background-clip:text; background-clip:text; color:transparent; font-family:'Sora'; font-weight:800; }
        .testi-text { color:#334155; font-size:.97rem; line-height:1.7; }
        .avatar-circle { width:46px; height:46px; border-radius:50%; background:var(--grad-brand); color:#fff; font-family:'Sora'; font-weight:700; display:flex; align-items:center; justify-content:center; }

        /* FAQ */
        .faq-item { background:#fff; border:1px solid #eef2f7; border-radius:16px; margin-bottom:14px; overflow:hidden; transition:box-shadow .3s ease; }
        .faq-item.open { box-shadow: 0 14px 34px -16px rgba(79,70,229,.3); border-color:#c7d2fe; }
        .faq-q { width:100%; background:none; border:0; text-align:left; padding:20px 24px; font-family:'Sora'; font-weight:600; color:#0f172a; display:flex; justify-content:space-between; align-items:center; gap:12px; cursor:pointer; font-size:1rem; }
        .faq-q .ar { transition: transform .3s ease; color:#6366f1; flex:0 0 auto; }
        .faq-item.open .faq-q .ar { transform: rotate(45deg); }
        .faq-a { max-height:0; overflow:hidden; transition: max-height .4s ease; }
        .faq-a-inner { padding:0 24px 20px; color:#64748b; line-height:1.75; }

        /* CTA */
        .cta-sec { padding: 40px 0 100px; }
        .cta-banner {
            position:relative; overflow:hidden; border-radius:28px; padding:70px 40px; text-align:center; color:#fff;
            background: linear-gradient(120deg,#4338ca 0%,#6d28d9 50%,#a21caf 100%);
            box-shadow: 0 30px 70px -24px rgba(79,70,229,.6);
        }
        .cta-banner::before, .cta-banner::after { content:''; position:absolute; border-radius:50%; background:rgba(255,255,255,.09); }
        .cta-banner::before { width:340px; height:340px; top:-160px; right:-90px; }
        .cta-banner::after { width:240px; height:240px; bottom:-130px; left:60px; }

        /* Footer */
        .lp-footer { background:#0f0c29; color:#94a3b8; padding:56px 0 28px; }

        /* ===== Section Pembayaran Otomatis (Midtrans) ===== */
        .pay-sec { background:#f8fafc; }
        .pay-method {
            background:#fff; border:1px solid #e2e8f0; border-radius:16px;
            padding:18px 10px; transition:all .25s ease;
        }
        .pay-method:hover { transform:translateY(-4px); box-shadow:0 16px 36px -16px rgba(79,70,229,.25); border-color:#c7d2fe; }
        .pay-method i { font-size:1.7rem; color:#4f46e5; }
        .pay-step {
            background:#fff; border:1px solid #e2e8f0; border-radius:18px;
            padding:26px 22px; position:relative; transition:all .25s ease;
        }
        .pay-step:hover { transform:translateY(-4px); box-shadow:0 16px 36px -16px rgba(79,70,229,.22); }
        .pay-step-num {
            width:42px; height:42px; border-radius:12px;
            background:var(--grad-brand); color:#fff;
            display:flex; align-items:center; justify-content:center;
            font-family:'Sora'; font-weight:800; font-size:1.05rem;
            box-shadow:0 10px 22px -8px rgba(99,102,241,.55);
        }

        /* ===== Section Install App (PWA) ===== */
        .pwa-sec { background:linear-gradient(180deg,#f8fafc 0%,#eef2ff 100%); }
        .pwa-card {
            background:#fff; border:1px solid #e2e8f0; border-radius:18px;
            padding:30px 24px; transition:all .25s ease;
        }
        .pwa-card:hover { transform:translateY(-4px); box-shadow:0 16px 36px -16px rgba(79,70,229,.22); }
        .pwa-icon {
            width:58px; height:58px; border-radius:16px;
            background:var(--grad-brand); color:#fff; font-size:1.5rem;
            display:flex; align-items:center; justify-content:center;
            box-shadow:0 12px 26px -10px rgba(99,102,241,.6);
        }
        .lp-footer h6 { color:#fff; font-family:'Sora'; font-weight:700; margin-bottom:18px; }
        .lp-footer a { color:#94a3b8; text-decoration:none; transition:color .2s; }
        .lp-footer a:hover { color:#a5b4fc; }
        .footer-bottom { border-top:1px solid rgba(148,163,184,.15); margin-top:36px; padding-top:22px; font-size:.85rem; }

        /* WhatsApp float */
        .wa-float { position:fixed; right:22px; bottom:22px; z-index:1050; width:56px; height:56px; border-radius:50%;
            background:linear-gradient(135deg,#25d366,#128c7e); color:#fff; display:flex; align-items:center; justify-content:center;
            font-size:1.7rem; text-decoration:none; box-shadow:0 14px 30px -8px rgba(18,140,126,.65); transition:transform .2s ease; }
        .wa-float:hover { color:#fff; transform:scale(1.1); }
        .wa-float::before { content:''; position:absolute; inset:0; border-radius:50%; background:rgba(37,211,102,.5); z-index:-1; animation: waPulse 2.4s ease-out infinite; }
        @keyframes waPulse { 0%{transform:scale(1);opacity:.6;} 70%{transform:scale(1.7);opacity:0;} 100%{opacity:0;} }
        @media (max-width:768px){ .wa-float{ right:14px; bottom:14px; width:50px; height:50px; font-size:1.45rem; } }

        /* ===== Reveal & Parallax ===== */
        .reveal { opacity:0; transform: translateY(42px); transition: opacity .8s cubic-bezier(.22,1,.36,1), transform .8s cubic-bezier(.22,1,.36,1); transition-delay: var(--d, 0s); }
        .reveal.left { transform: translateX(-48px); } .reveal.right { transform: translateX(48px); } .reveal.zoom { transform: scale(.86); }
        .reveal.in { opacity:1; transform:none; }
        .blob { animation: blobDrift 14s ease-in-out infinite alternate; }
        @keyframes blobDrift { from { transform: translate(0,0) scale(1); } to { transform: translate(40px,-30px) scale(1.12); } }
        @media (prefers-reduced-motion: reduce) { .reveal { transition:none; opacity:1; transform:none; } .blob,.mockup,.chip-float { animation:none; } }
    </style>
</head>
<body>

@php
    $hero = $sections['hero'];
    $features = $sections['features'];
    $steps = $sections['steps'];
    $payments = $sections['payments'];
    $pwa = $sections['pwa'];
    $testi = $sections['testimonials'];
    $faq = $sections['faq'];
    $cta = $sections['cta'];
    $contact = $sections['contact'];
@endphp

<!-- ===== NAVBAR ===== -->
<nav class="lp-nav" id="lpNav">
    <div class="container d-flex align-items-center justify-content-between">
        <a href="#top" class="brand">
            @if($brandLogo)
                <img src="{{ asset('storage/' . $brandLogo) }}" alt="{{ $brandName }}" class="lp-brand-logo">
            @else
                <i class="bi bi-receipt-cutoff"></i>
            @endif
            {{ $brandName }}
        </a>
        <div class="d-none d-md-flex align-items-center">
            <a href="#fitur" class="nav-link-lp">Fitur</a>
            <a href="#pembayaran" class="nav-link-lp">Pembayaran</a>
            <a href="#cara" class="nav-link-lp">Cara Kerja</a>
            <a href="#install" class="nav-link-lp">Install App</a>
            <a href="#testimoni" class="nav-link-lp">Testimoni</a>
            <a href="#faq" class="nav-link-lp">FAQ</a>
        </div>
        <div class="d-flex align-items-center gap-2">
            @auth
                <a href="{{ route('dashboard') }}" class="btn btn-glow py-2 px-3" style="font-size:.9rem;"><i class="bi bi-speedometer2"></i> Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="nav-link-lp d-none d-sm-inline-block"><i class="bi bi-box-arrow-in-right"></i> Masuk</a>
                <a href="{{ route('register') }}" class="btn btn-glow py-2 px-3" style="font-size:.9rem;">Daftar Gratis</a>
            @endauth
        </div>
    </div>
</nav>

<!-- ===== HERO ===== -->
<header class="hero" id="top">
    <div class="hero-grid"></div>
    <div class="blob blob-1" data-speed="0.25"></div>
    <div class="blob blob-2" data-speed="-0.2"></div>
    <div class="blob blob-3" data-speed="0.15"></div>

    <div class="container position-relative">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <span class="hero-badge reveal">{{ $hero['badge'] }}</span>
                <h1 class="hero-title mt-4 reveal" style="--d:.1s">
                    {{ $hero['title'] }}<br>
                    <span class="grad-text">{{ $hero['highlight'] }}</span>
                </h1>
                <p class="hero-sub mt-3 reveal" style="--d:.2s">{{ $hero['subtitle'] }}</p>
                <div class="d-flex flex-wrap gap-3 mt-4 reveal" style="--d:.3s">
                    <a href="{{ $hero['btn_primary_link'] }}" class="btn btn-glow">{{ $hero['btn_primary_text'] }} <i class="bi bi-arrow-right"></i></a>
                    <a href="{{ $hero['btn_secondary_link'] }}" class="btn btn-ghost"><i class="bi bi-play-circle"></i> {{ $hero['btn_secondary_text'] }}</a>
                </div>

                <div class="hero-stats reveal" style="--d:.4s">
                    @foreach($hero['stats'] as $i => $stat)
                        <div class="stat-item">
                            <div class="num grad-text" data-counter>{{ $stat['value'] }}</div>
                            <div class="lbl">{{ $stat['label'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="col-lg-6 d-none d-lg-block">
                <div class="mockup-wrap reveal right" style="--d:.2s">
                    <div class="mockup">
                        <div class="inv-head">
                            <div>
                                <div class="text-muted" style="font-size:.75rem;">INVOICE</div>
                                <div class="inv-no">INV/2026/001</div>
                            </div>
                            <span class="badge-lunas">✓ LUNAS</span>
                        </div>
                        <div class="row-line"><span class="text-muted">Klien</span><span class="amt">PT Maju Jaya</span></div>
                        <div class="row-line"><span class="text-muted">Jatuh Tempo</span><span class="amt">30 Nov 2026</span></div>
                        <div class="row-line"><span class="text-muted">Subtotal</span><span class="amt">Rp 15.000.000</span></div>
                        <div class="row-line"><span class="text-muted">Pajak (11%)</span><span class="amt">Rp 1.650.000</span></div>
                        <div class="total"><span>TOTAL</span><span>Rp 16.650.000</span></div>
                    </div>
                    <div class="chip-float" style="top:-26px; right:-6px; animation-delay:.8s;">
                        <span class="ic" style="background:linear-gradient(135deg,#25d366,#128c7e);"><i class="bi bi-whatsapp"></i></span>
                        Terkirim via WA
                    </div>
                    <div class="chip-float" style="bottom:-22px; left:-16px; animation-delay:1.6s;">
                        <span class="ic" style="background:linear-gradient(135deg,#f59e0b,#fbbf24);"><i class="bi bi-graph-up-arrow"></i></span>
                        +34% bulan ini
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- ===== FEATURES ===== -->
<section class="sec" id="fitur">
    <div class="container">
        <div class="text-center reveal">
            <span class="sec-label">✨ Fitur Unggulan</span>
            <h2 class="sec-title">{{ $features['heading'] }}</h2>
            <p class="sec-sub">{{ $features['subheading'] }}</p>
        </div>
        <div class="row g-4 mt-4">
            @foreach($features['items'] as $i => $f)
            <div class="col-md-6 col-lg-4 reveal" style="--d: {{ $i * 0.08 }}s">
                <div class="feat-card">
                    <div class="feat-icon"><i class="bi {{ $f['icon'] }}"></i></div>
                    <h5 class="fw-bold" style="font-size:1.05rem;">{{ $f['title'] }}</h5>
                    <p class="text-muted mb-0" style="font-size:.92rem;">{{ $f['desc'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- ===== STEPS ===== -->
<section class="sec steps-sec" id="cara">
    <div class="container">
        <div class="text-center reveal">
            <span class="sec-label">🚀 Cara Kerja</span>
            <h2 class="sec-title">{{ $steps['heading'] }}</h2>
            <p class="sec-sub">{{ $steps['subheading'] }}</p>
        </div>
        <div class="row g-5 mt-5">
            @foreach($steps['items'] as $i => $s)
            <div class="col-md-4 reveal" style="--d: {{ $i * 0.15 }}s">
                <div class="step-card">
                    <div class="step-num">{{ $i + 1 }}</div>
                    <h5 class="fw-bold mt-3 mb-2" style="font-size:1.02rem;">{{ $s['title'] }}</h5>
                    <p class="text-muted mb-0" style="font-size:.92rem;">{{ $s['desc'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- ===== PEMBAYARAN OTOMATIS (MIDTRANS) ===== -->
<section class="sec pay-sec" id="pembayaran">
    <div class="container">
        <div class="text-center reveal">
            <span class="sec-label">{{ $payments['badge'] }}</span>
            <h2 class="sec-title">{{ $payments['heading'] }}</h2>
            <p class="sec-sub">{{ $payments['subheading'] }}</p>
        </div>

        <div class="row justify-content-center g-3 mt-4 reveal">
            @foreach($payments['methods'] as $m)
            <div class="col-6 col-md-3">
                <div class="pay-method text-center">
                    <i class="bi {{ $m['icon'] }}"></i>
                    <div class="fw-semibold mt-2" style="font-size:.9rem;">{{ $m['label'] }}</div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="row g-4 mt-4">
            @foreach($payments['steps'] as $i => $ps)
            <div class="col-md-4 reveal" style="--d: {{ $i * 0.1 }}s">
                <div class="pay-step h-100">
                    <div class="pay-step-num">{{ $i + 1 }}</div>
                    <h6 class="fw-bold mb-2 mt-3">{{ $ps['title'] }}</h6>
                    <p class="text-muted mb-0" style="font-size:.9rem;">{{ $ps['desc'] }}</p>
                </div>
            </div>
            @endforeach
        </div>

        <div class="text-center mt-5 reveal">
            <div class="d-inline-flex align-items-center gap-2 px-4 py-3" style="background:#ecfdf5; border:1px solid #a7f3d0; border-radius:16px; color:#065f46; font-weight:600;">
                <i class="bi bi-patch-check-fill" style="font-size:1.2rem;"></i>
                Diverifikasi otomatis payment gateway — invoice langsung LUNAS, tanpa konfirmasi manual!
            </div>
        </div>
    </div>
</section>

<!-- ===== TESTIMONIALS ===== -->
<section class="sec" id="testimoni">
    <div class="container">
        <div class="text-center reveal">
            <span class="sec-label">💬 Testimoni</span>
            <h2 class="sec-title">{{ $testi['heading'] }}</h2>
            <p class="sec-sub">{{ $testi['subheading'] }}</p>
        </div>
        <div class="row g-4 mt-4">
            @foreach($testi['items'] as $i => $t)
            <div class="col-md-4 reveal" style="--d: {{ $i * 0.1 }}s">
                <div class="testi-card">
                    <div class="testi-quote">"</div>
                    <p class="testi-text">{{ $t['quote'] }}</p>
                    <hr>
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar-circle">{{ strtoupper(substr($t['name'], 0, 1)) }}</div>
                        <div>
                            <div class="fw-bold" style="font-size:.95rem;">{{ $t['name'] }}</div>
                            <small class="text-muted">{{ $t['role'] }}</small>
                        </div>
                        <div class="ms-auto text-warning"><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i></div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- ===== FAQ ===== -->
<section class="sec" id="faq" style="background:var(--grad-soft);">
    <div class="container" style="max-width:820px;">
        <div class="text-center reveal">
            <span class="sec-label">❓ FAQ</span>
            <h2 class="sec-title">{{ $faq['heading'] }}</h2>
            <p class="sec-sub">{{ $faq['subheading'] }}</p>
        </div>
        <div class="mt-4">
            @foreach($faq['items'] as $i => $item)
            <div class="faq-item reveal {{ $i === 0 ? 'open' : '' }}" style="--d: {{ $i * 0.06 }}s">
                <button class="faq-q" type="button">
                    {{ $item['q'] }}
                    <i class="bi bi-plus-lg ar"></i>
                </button>
                <div class="faq-a">
                    <div class="faq-a-inner">{{ $item['a'] }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- ===== INSTALL APP (PWA) ===== -->
<section class="sec pwa-sec" id="install">
    <div class="container">
        <div class="text-center reveal">
            <span class="sec-label">{{ $pwa['badge'] }}</span>
            <h2 class="sec-title">{{ $pwa['heading'] }}</h2>
            <p class="sec-sub">{{ $pwa['subheading'] }}</p>
        </div>

        <div class="row g-4 mt-4">
            @foreach($pwa['features'] as $i => $pf)
            <div class="col-md-4 reveal" style="--d: {{ $i * 0.1 }}s">
                <div class="pwa-card h-100 text-center">
                    <div class="pwa-icon mx-auto"><i class="bi {{ $pf['icon'] }}"></i></div>
                    <h6 class="fw-bold mb-2">{{ $pf['title'] }}</h6>
                    <p class="text-muted mb-0" style="font-size:.9rem;">{{ $pf['desc'] }}</p>
                </div>
            </div>
            @endforeach
        </div>

        <div class="text-center mt-5 reveal">
            <button type="button" id="btnInstallPwa" class="btn btn-glow px-4 py-3 d-none">
                <i class="bi bi-download"></i> {{ $pwa['btn_text'] }}
            </button>
            <div id="pwaIosHint" class="d-none text-muted" style="font-size:.9rem;">
                <i class="bi bi-phone"></i> Di iPhone/iPad: buka menu <b>Share</b> (kotak panah ke atas) → pilih <b>"Tambahkan ke Layar Utama"</b>.
            </div>
            <div id="pwaInstalled" class="d-none" style="color:#059669; font-weight:700;">
                <i class="bi bi-check-circle-fill"></i> Aplikasi sudah ter-install di perangkat Anda!
            </div>
        </div>
    </div>
</section>

<!-- ===== CTA ===== -->
<section class="cta-sec" id="cta">
    <div class="container">
        <div class="cta-banner reveal zoom">
            <h2 class="fw-bold" style="font-size:clamp(1.6rem,3.2vw,2.4rem); position:relative;">{{ $cta['title'] }}</h2>
            <p class="mt-3" style="color:rgba(255,255,255,.85); font-size:1.05rem; position:relative;">{{ $cta['subtitle'] }}</p>
            <a href="{{ $cta['btn_link'] }}" class="btn btn-light fw-bold px-4 py-3 mt-4" style="border-radius:14px; position:relative;">
                {{ $cta['btn_text'] }} <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<!-- ===== FOOTER ===== -->
<footer class="lp-footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <a href="#top" class="brand d-inline-flex align-items-center gap-2 mb-3" style="color:#fff; font-family:'Sora'; font-weight:800; font-size:1.25rem; text-decoration:none;">
                    @if($brandLogo)
                        <img src="{{ asset('storage/' . $brandLogo) }}" alt="{{ $brandName }}" style="height:38px; width:38px; object-fit:cover; border-radius:11px; background:#fff; padding:3px;">
                    @else
                        <i class="bi bi-receipt-cutoff" style="color:#818cf8;"></i>
                    @endif
                    {{ $brandName }}
                </a>
                <p style="font-size:.9rem; line-height:1.7;">Aplikasi manajemen invoice modern untuk bisnis Indonesia. Buat, kirim, dan pantau penagihan dengan mudah.</p>
            </div>
            <div class="col-6 col-lg-2">
                <h6>Menu</h6>
                <div class="d-flex flex-column gap-2">
                    <a href="#fitur">Fitur</a>
                    <a href="#cara">Cara Kerja</a>
                    <a href="#faq">FAQ</a>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <h6>Kontak</h6>
                <div class="d-flex flex-column gap-2" style="font-size:.9rem;">
                    @if(!empty($contact['phone']))<span><i class="bi bi-telephone me-2"></i>{{ $contact['phone'] }}</span>@endif
                    @if(!empty($contact['email']))<span><i class="bi bi-envelope me-2"></i>{{ $contact['email'] }}</span>@endif
                    @if(!empty($contact['address']))<span><i class="bi bi-geo-alt me-2"></i>{{ $contact['address'] }}</span>@endif
                </div>
            </div>
            <div class="col-lg-3">
                <h6>Mulai Sekarang</h6>
                <p style="font-size:.9rem;">Buat invoice pertama Anda hari ini, gratis!</p>
                <a href="{{ route('register') }}" class="btn btn-glow py-2 px-3" style="font-size:.9rem;">Daftar Gratis</a>
            </div>
        </div>
        <div class="footer-bottom d-flex flex-wrap justify-content-between gap-2">
            <span>© {{ date('Y') }} {{ $brandName }}. All rights reserved.</span>
            <span>Developed by <a href="https://digimagine.web.id" target="_blank" rel="noopener" style="color:#a5b4fc; font-weight:600;">Digital Imagine Indonesia</a></span>
        </div>
    </div>
</footer>

<!-- WhatsApp Floating -->
@if(!empty($contact['whatsapp']))
<a href="https://wa.me/{{ preg_replace('/[^0-9]/','',$contact['whatsapp']) }}?text={{ rawurlencode('Halo, saya tertarik dengan ' . $brandName . ' 🙌') }}"
   class="wa-float" target="_blank" rel="noopener noreferrer" aria-label="Chat WhatsApp" title="Chat via WhatsApp">
    <i class="bi bi-whatsapp"></i>
</a>
@endif

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ===== Navbar shrink on scroll =====
const nav = document.getElementById('lpNav');
window.addEventListener('scroll', () => {
    nav.classList.toggle('scrolled', window.scrollY > 40);
}, { passive: true });

// ===== Parallax (blob & elemen data-speed) =====
const parallaxEls = document.querySelectorAll('[data-speed]');
let ticking = false;
window.addEventListener('scroll', () => {
    if (ticking) return;
    ticking = true;
    requestAnimationFrame(() => {
        const y = window.scrollY;
        parallaxEls.forEach(el => {
            el.style.translate = `0 ${y * parseFloat(el.dataset.speed)}px`;
        });
        ticking = false;
    });
}, { passive: true });

// ===== Scroll reveal =====
const io = new IntersectionObserver((entries) => {
    entries.forEach(e => {
        if (e.isIntersecting) {
            e.target.classList.add('in');
            io.unobserve(e.target);
        }
    });
}, { threshold: 0.12 });
document.querySelectorAll('.reveal').forEach(el => io.observe(el));

// ===== FAQ accordion =====
document.querySelectorAll('.faq-q').forEach(btn => {
    btn.addEventListener('click', () => {
        const item = btn.closest('.faq-item');
        const answer = item.querySelector('.faq-a');
        const isOpen = item.classList.contains('open');

        document.querySelectorAll('.faq-item.open').forEach(o => {
            o.classList.remove('open');
            o.querySelector('.faq-a').style.maxHeight = null;
        });

        if (!isOpen) {
            item.classList.add('open');
            answer.style.maxHeight = answer.scrollHeight + 'px';
        }
    });
});
// Buka item pertama
const firstFaq = document.querySelector('.faq-item.open .faq-a');
if (firstFaq) firstFaq.style.maxHeight = firstFaq.scrollHeight + 'px';

// ===== Animated counter (angka murni saja, mis. "500+", "98%") =====
document.querySelectorAll('[data-counter]').forEach(el => {
    const raw = el.textContent.trim();
    const match = raw.match(/^(\d+(?:[.,]\d+)?)(.*)$/);
    if (!match) return;

    const target = parseFloat(match[1].replace(',', '.'));
    const suffix = match[2];
    const decimals = match[1].includes(',') || match[1].includes('.') ? 1 : 0;
    const duration = 1400;
    let start = null;

    const observer = new IntersectionObserver((entries) => {
        if (!entries[0].isIntersecting) return;
        observer.disconnect();

        const step = (ts) => {
            if (!start) start = ts;
            const p = Math.min((ts - start) / duration, 1);
            const eased = 1 - Math.pow(1 - p, 3); // easeOutCubic
            const val = target * eased;
            const formatted = decimals
                ? val.toFixed(1).replace('.', ',')
                : Math.round(val).toLocaleString('id-ID');
            el.textContent = formatted + suffix;
            if (p < 1) requestAnimationFrame(step);
            else el.textContent = raw; // pastikan nilai akhir persis
        };
        requestAnimationFrame(step);
    }, { threshold: 0.6 });

    observer.observe(el);
});

// ===== PWA: Install App =====
let deferredPrompt = null;
window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;
    const btn = document.getElementById('btnInstallPwa');
    if (btn && !window.matchMedia('(display-mode: standalone)').matches) btn.classList.remove('d-none');
});
document.getElementById('btnInstallPwa')?.addEventListener('click', async () => {
    if (!deferredPrompt) return;
    deferredPrompt.prompt();
    await deferredPrompt.userChoice;
    deferredPrompt = null;
});
window.addEventListener('appinstalled', () => {
    document.getElementById('btnInstallPwa')?.classList.add('d-none');
    document.getElementById('pwaInstalled')?.classList.remove('d-none');
});
// iOS Safari tidak punya beforeinstallprompt → tampilkan hint manual
if (/iphone|ipad|ipod/i.test(navigator.userAgent)
    && !window.matchMedia('(display-mode: standalone)').matches
    && !navigator.standalone) {
    document.getElementById('pwaIosHint')?.classList.remove('d-none');
}

// ===== Register Service Worker (PWA) =====
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
        navigator.serviceWorker.register('/sw.js').catch(function () {});
    });
}
</script>
</body>
</html>
