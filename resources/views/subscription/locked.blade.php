<!DOCTYPE html>
<html lang="id">
@php
    $brandProfile = \App\Models\BusinessProfile::whereNotNull('logo')->orderBy('id')->first()
        ?: \App\Models\BusinessProfile::orderBy('id')->first();
    $brandLogo = $brandProfile?->logo;
    $brandName = $brandProfile?->business_name ?: config('app.name');
@endphp
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aktivasi Akun · {{ $brandName }}</title>
    @if($brandLogo)
        <link rel="icon" type="image/png" href="{{ asset('storage/' . $brandLogo) }}">
    @else
        <link rel="icon" type="image/svg+xml" href="{{ asset('img/favicon.svg') }}">
    @endif
    <link rel="manifest" href="{{ route('pwa.manifest') }}">
    <meta name="theme-color" content="#6366f1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary:#4f46e5; --ink:#0f172a; --muted:#64748b; --border:#e2e8f0; }
        * { font-family:'Inter',sans-serif; }
        body { min-height:100vh; display:flex; align-items:center; justify-content:center;
               background:linear-gradient(160deg,#0f0c29 0%,#1e1b4b 40%,#312e81 75%,#4c1d95 100%);
               padding:20px; color:#fff; }
        h1,h2,h3,.font-header { font-family:'Sora',sans-serif; }
        .lock-card {
            width:100%; max-width:560px; background:rgba(255,255,255,.06);
            border:1px solid rgba(255,255,255,.14); border-radius:24px;
            padding:36px 34px; backdrop-filter:blur(16px);
            box-shadow:0 30px 80px -20px rgba(0,0,0,.5);
        }
        .lock-icon { width:72px; height:72px; border-radius:20px; margin:0 auto 18px;
                     background:linear-gradient(135deg,#f59e0b,#ef4444);
                     display:flex; align-items:center; justify-content:center;
                     font-size:2rem; box-shadow:0 16px 36px -12px rgba(239,68,68,.55); }
        .input-modern {
            width:100%; border:1.5px solid rgba(255,255,255,.2); background:rgba(255,255,255,.08);
            color:#fff; border-radius:13px; padding:13px 16px; font-size:.95rem;
            letter-spacing:.3em; text-transform:uppercase; font-weight:700; text-align:center;
        }
        .input-modern:focus { outline:none; border-color:#a5b4fc; background:rgba(255,255,255,.12); }
        .pkg-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); gap:10px; }
        .pkg-opt { position:relative; margin:0; }
        .pkg-opt input { position:absolute; opacity:0; }
        .pkg-card { display:flex; flex-direction:column; gap:4px; text-align:center;
                    border:1.5px solid rgba(255,255,255,.18); border-radius:14px;
                    padding:14px 10px; cursor:pointer; transition:all .2s; }
        .pkg-card:hover { border-color:rgba(255,255,255,.4); }
        .pkg-opt input:checked + .pkg-card {
            border-color:#a5b4fc; background:rgba(99,102,241,.25);
            box-shadow:0 10px 26px -10px rgba(99,102,241,.6); }
        .pkg-name { font-weight:700; font-size:.92rem; }
        .pkg-price { font-size:.78rem; color:#c7d2fe; }
        .pkg-meta { font-size:.7rem; color:rgba(255,255,255,.6); }
        .btn-activate { width:100%; border:0; border-radius:14px; padding:15px;
                        font-weight:800; font-size:1rem; color:#fff; margin-top:18px;
                        background:linear-gradient(135deg,#6366f1,#8b5cf6,#d946ef);
                        box-shadow:0 16px 36px -12px rgba(99,102,241,.6); }
        .btn-activate:hover { transform:translateY(-2px); }
        .hint { font-size:.82rem; color:rgba(255,255,255,.65); line-height:1.6; }
    </style>
</head>
<body>
    <div class="lock-card">
        <div class="lock-icon">🔒</div>
        <h3 class="text-center fw-bold mb-1" style="font-size:1.35rem;">Masa {{ $package?->trial_days > 0 && $company->subscription_expires_at?->isPast() ? 'Trial' : 'Langganan' }} Berakhir</h3>
        <p class="text-center hint mb-4">
            Perusahaan <b>{{ $company->name }}</b> — paket <b>{{ $package?->name ?? '-' }}</b>
            @if($company->subscription_expires_at)
                kedaluwarsa <b>{{ $company->subscription_expires_at->format('d/m/Y') }}</b>
            @endif
        </p>

        @if(session('error'))
        <div class="alert alert-danger py-2 small">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('subscription.activate') }}">
            @csrf

            <label class="form-label fw-semibold small">🔑 Kode Token Aktivasi <span class="text-warning">(dari Super Admin)</span></label>
            <input type="text" name="token" class="input-modern mb-1" maxlength="8"
                   placeholder="XXXXXXXX" value="{{ old('token') }}" required autofocus>
            <p class="hint mb-4"><i class="bi bi-info-circle"></i> Hubungi Super Admin / tim {{ $brandName }} via WhatsApp untuk mendapatkan kode token.</p>

            <label class="form-label fw-semibold small">📦 Pilih Paket Berbayar</label>
            <div class="pkg-grid">
                @foreach($packages as $pkg)
                <label class="pkg-opt">
                    <input type="radio" name="package_id" value="{{ $pkg->id }}" {{ old('package_id') == $pkg->id ? 'checked' : '' }} required>
                    <span class="pkg-card">
                        <span class="pkg-name">{{ $pkg->name }}</span>
                        <span class="pkg-price">Rp {{ number_format($pkg->price_monthly, 0, ',', '.') }}/bln</span>
                        <span class="pkg-meta">{{ $pkg->max_users }} user · {{ $pkg->max_invoices }} inv · aktif {{ $pkg->duration_days }} hari</span>
                    </span>
                </label>
                @endforeach
            </div>

            <button type="submit" class="btn-activate">
                <i class="bi bi-unlock"></i> Aktifkan Akun Sekarang
            </button>
        </form>

        <div class="text-center mt-4">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-light rounded-pill px-3">
                    <i class="bi bi-box-arrow-right"></i> Keluar
                </button>
            </form>
        </div>
    </div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
        navigator.serviceWorker.register('/sw.js').catch(function () {});
    });
}
</script>
</html>
