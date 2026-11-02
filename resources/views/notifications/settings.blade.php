@extends('layouts.app')
@section('title', 'Pengaturan Notifikasi')
@section('content')

<h3 class="fw-bold mb-1">⚙️ Pengaturan</h3>
<p class="text-muted mb-2" style="font-size:.9rem;">Notifikasi jatuh tempo, pengingat WhatsApp &amp; pengaturan email (SMTP)</p>

{{-- SaaS: penanda scope pengaturan (perusahaan vs platform) --}}
@if(auth()->user()->isSuperAdmin())
<div class="alert alert-dark d-flex align-items-center gap-2 py-2 mb-4" style="font-size:.85rem;">
    <i class="bi bi-shield-lock fs-5"></i>
    <div><b>Mode Platform (Super Admin)</b> — pengaturan ini dipakai sebagai <b>default fallback</b> seluruh tenant. Tiap perusahaan punya pengaturan sendiri lewat menu Pengaturan mereka.</div>
</div>
@elseif(auth()->user()->company)
<div class="alert alert-info d-flex align-items-center gap-2 py-2 mb-4" style="font-size:.85rem;">
    <i class="bi bi-building fs-5"></i>
    <div><b>Scope: {{ auth()->user()->company->name }}</b> — SMTP, WhatsApp &amp; Midtrans di bawah ini <b>khusus perusahaan Anda</b>, terpisah dari perusahaan lain.</div>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <strong><i class="bi bi-exclamation-triangle"></i> Gagal menyimpan:</strong>
    <ul class="mb-0 mt-1">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card card-stat p-4">
            <form method="POST" action="{{ route('notifications.store') }}">
                @csrf

                <h5 class="fw-bold mb-3"><i class="bi bi-bell me-2"></i>Channel Notifikasi</h5>

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="wa_enabled" id="wa_enabled" value="1"
                        {{ $settings && $settings->wa_enabled ? 'checked' : '' }}>
                    <label class="form-check-label" for="wa_enabled">
                        <i class="bi bi-whatsapp text-success"></i> <strong>WhatsApp</strong>
                    </label>
                    <br><small class="text-muted">Kirim notifikasi via WhatsApp ke nomor Anda</small>
                </div>

                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" type="checkbox" name="email_enabled" id="email_enabled" value="1"
                        {{ !$settings || $settings->email_enabled ? 'checked' : '' }}>
                    <label class="form-check-label" for="email_enabled">
                        <i class="bi bi-envelope text-primary"></i> <strong>Email</strong>
                    </label>
                    <br><small class="text-muted">Kirim notifikasi via Email</small>
                </div>

                <h5 class="fw-bold mb-3"><i class="bi bi-geo-alt me-2"></i>Nomor & Email Penerima</h5>
                <small class="text-muted">Notifikasi akan dikirim ke nomor/email ini (bukan ke klien)</small>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Nomor WhatsApp</label>
                        <input type="text" name="wa_number" class="form-control"
                            value="{{ old('wa_number', $settings?->wa_number) }}" placeholder="0812xxxxx">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control"
                            value="{{ old('email', $settings?->email ?? auth()->user()->email) }}">
                        <small class="text-muted">Default: email akun Anda</small>
                    </div>
                </div>

                <h5 class="fw-bold mb-3"><i class="bi bi-calendar3 me-2"></i>Jadwal Notifikasi</h5>

                <p class="fw-semibold mb-2">Kirim notifikasi H-... sebelum jatuh tempo:</p>
                <div class="d-flex flex-wrap gap-2 mb-3">
                    @foreach([1, 2, 3, 5, 7] as $day)
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="days_before_due[]" value="{{ $day }}" id="day{{ $day }}"
                            {{ $settings && in_array($day, $settings->days_before_due ?? [2]) ? 'checked' : '' }}
                            {{ !$settings && $day == 2 ? 'checked' : '' }}>
                        <label class="form-check-label" for="day{{ $day }}">H-{{ $day }}</label>
                    </div>
                    @endforeach
                </div>

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="send_on_due_day" id="send_on_due_day" value="1"
                        {{ !$settings || $settings->send_on_due_day ? 'checked' : '' }}>
                    <label class="form-check-label" for="send_on_due_day">
                        <strong>Kirim juga di hari H (tanggal jatuh tempo)</strong>
                    </label>
                </div>

                <div class="mb-4">
                    <label class="form-label">Jam Kirim Notifikasi</label>
                    <select name="send_at_hour" class="form-select" style="max-width:200px;">
                        @for($h = 6; $h <= 22; $h++)
                            <option value="{{ $h }}" {{ $settings && $settings->send_at_hour == $h ? 'selected' : ($h == 9 ? 'selected' : '') }}>
                                {{ str_pad($h, 2, '0', STR_PAD_LEFT) }}:00
                            </option>
                        @endfor
                    </select>
                    <small class="text-muted">Notifikasi akan dikirim pada jam ini setiap harinya</small>
                </div>

                <button type="submit" class="btn text-white px-4" style="background:linear-gradient(135deg,#4f46e5,#7c3aed); box-shadow:0 6px 16px rgba(79,70,229,.3);">
                    <i class="bi bi-save"></i> Simpan Pengaturan
                </button>
            </form>
        </div>
    </div>

    <div class="col-lg-5">
        <!-- ====== SMTP / Email Settings ====== -->
        @can('admin')
        <div class="card card-stat p-4 mb-3" id="smtp" style="scroll-margin-top:24px;">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h5 class="fw-bold mb-1"><i class="bi bi-envelope-gear me-2" style="color:#4f46e5;"></i>Pengaturan SMTP</h5>
                    <p class="text-muted mb-0" style="font-size:.82rem;">
                        Konfigurasi server email untuk semua notifikasi &amp; pengiriman invoice ke klien.
                    </p>
                </div>
                @if($smtp && $smtp->is_active)
                    <span class="badge rounded-pill" style="background:#dcfce7; color:#166534; font-size:.72rem;">
                        <i class="bi bi-check-circle-fill me-1"></i>Aktif
                    </span>
                @else
                    <span class="badge rounded-pill" style="background:#fef3c7; color:#92400e; font-size:.72rem;">
                        <i class="bi bi-exclamation-circle-fill me-1"></i>Default .env
                    </span>
                @endif
            </div>

            <form method="POST" action="{{ route('smtp.store') }}" id="smtpForm">
                @csrf

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="is_active" id="smtp_active" value="1"
                        {{ $smtp && $smtp->is_active ? 'checked' : '' }}>
                    <label class="form-check-label" for="smtp_active">
                        <strong>Gunakan SMTP kustom</strong>
                        <br><small class="text-muted">Jika nonaktif, sistem memakai konfigurasi bawaan file <code>.env</code></small>
                    </label>
                </div>

                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label" style="font-size:.82rem; font-weight:600;">SMTP Host</label>
                        <input type="text" name="mail_host" class="form-control" id="mail_host"
                               value="{{ old('mail_host', $smtp?->mail_host) }}" placeholder="smtp.gmail.com">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" style="font-size:.82rem; font-weight:600;">Port</label>
                        <input type="number" name="mail_port" class="form-control" id="mail_port"
                               value="{{ old('mail_port', $smtp?->mail_port ?? 587) }}" placeholder="587">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" style="font-size:.82rem; font-weight:600;">Enkripsi</label>
                        <select name="mail_encryption" class="form-select" id="mail_encryption">
                            @foreach(['tls' => 'TLS (587)', 'ssl' => 'SSL (465)', 'none' => 'Tanpa Enkripsi (25)'] as $val => $label)
                                <option value="{{ $val }}" {{ old('mail_encryption', $smtp?->mail_encryption ?? 'tls') === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" style="font-size:.82rem; font-weight:600;">Username SMTP</label>
                        <input type="text" name="mail_username" class="form-control" id="mail_username"
                               value="{{ old('mail_username', $smtp?->mail_username) }}" placeholder="user@domain.com">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" style="font-size:.82rem; font-weight:600;">Password SMTP</label>
                        <div class="input-group">
                            <input type="password" name="mail_password" class="form-control" id="mail_password"
                                   placeholder="{{ $smtp?->mail_password ? '•••••••• (tersimpan)' : 'app password / password' }}" autocomplete="new-password">
                            <button class="btn btn-outline-secondary" type="button" id="toggleSmtpPass" aria-label="Tampilkan password SMTP" title="Intip password">
                                <i class="bi bi-eye" id="toggleSmtpPassIcon"></i>
                            </button>
                        </div>
                        <small class="text-muted">Kosongkan jika tidak diubah</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" style="font-size:.82rem; font-weight:600;">Email Pengirim (From)</label>
                        <input type="email" name="mail_from_address" class="form-control" id="mail_from_address"
                               value="{{ old('mail_from_address', $smtp?->mail_from_address ?? config('mail.from.address')) }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label" style="font-size:.82rem; font-weight:600;">Nama Pengirim</label>
                        <input type="text" name="mail_from_name" class="form-control" id="mail_from_name"
                               value="{{ old('mail_from_name', $smtp?->mail_from_name ?? config('mail.from.name')) }}" placeholder="InvoiceFlow">
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2 align-items-center mt-4">
                    <button type="submit" class="btn text-white px-4" style="background:linear-gradient(135deg,#4f46e5,#7c3aed); box-shadow:0 6px 16px rgba(79,70,229,.3);">
                        <i class="bi bi-save"></i> Simpan SMTP
                    </button>
                </div>
            </form>

            <!-- Uji koneksi + email tes -->
            <hr style="border-color:#e2e8f0; margin:20px 0 16px;">
            <form method="POST" action="{{ route('smtp.test') }}" id="smtpTestForm">
                @csrf
                <div class="d-flex flex-wrap gap-2 align-items-end">
                    <div style="flex:1 1 220px;">
                        <label class="form-label" style="font-size:.82rem; font-weight:600;">Kirim Email Tes Ke</label>
                        <input type="email" name="test_to" class="form-control" value="{{ auth()->user()->email }}" required>
                    </div>
                    <button type="submit" class="btn btn-outline-primary px-4" id="smtpTestBtn" style="border-color:#4f46e5; color:#4f46e5;">
                        <i class="bi bi-send"></i> Uji Kirim
                    </button>
                </div>
            </form>

            @if($smtp && $smtp->last_tested_at)
                <div class="mt-3 p-2 rounded-3 {{ $smtp->last_test_status === 'success' ? 'bg-success-subtle' : 'bg-danger-subtle' }}"
                     style="font-size:.8rem;">
                    @if($smtp->last_test_status === 'success')
                        <i class="bi bi-check-circle-fill text-success me-1"></i>
                        <strong>Terakhir diuji {{ $smtp->last_tested_at->format('d M Y H:i') }}</strong> — berhasil.
                    @else
                        <i class="bi bi-x-circle-fill text-danger me-1"></i>
                        <strong>Terakhir diuji {{ $smtp->last_tested_at->format('d M Y H:i') }}</strong> — gagal:
                        <span class="text-muted">{{ $smtp->last_test_message }}</span>
                    @endif
                </div>
            @endif
        </div>
        @endcan

        <!-- ====== WhatsApp API Settings ====== -->
        @can('admin')
        <div class="card card-stat p-4 mb-3" id="whatsapp" style="scroll-margin-top:24px;">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h5 class="fw-bold mb-1"><i class="bi bi-whatsapp me-2 text-success"></i>Koneksi WhatsApp API</h5>
                    <p class="text-muted mb-0" style="font-size:.82rem;">
                        Gateway untuk semua notifikasi &amp; pesan WhatsApp.
                    </p>
                </div>
                @if($whatsapp && $whatsapp->is_active)
                    <span class="badge rounded-pill" style="background:#dcfce7; color:#166534; font-size:.72rem;">
                        <i class="bi bi-check-circle-fill me-1"></i>Aktif
                    </span>
                @elseif(config('whatsapp.api_key'))
                    <span class="badge rounded-pill" style="background:#e0e7ff; color:#3730a3; font-size:.72rem;">
                        <i class="bi bi-info-circle-fill me-1"></i>Default .env
                    </span>
                @else
                    <span class="badge rounded-pill" style="background:#fef3c7; color:#92400e; font-size:.72rem;">
                        <i class="bi bi-exclamation-circle-fill me-1"></i>Belum dikonfigurasi
                    </span>
                @endif
            </div>

            <form method="POST" action="{{ route('whatsapp.store') }}" id="waForm">
                @csrf

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="is_active" id="wa_active" value="1"
                        {{ $whatsapp && $whatsapp->is_active ? 'checked' : '' }}>
                    <label class="form-check-label" for="wa_active">
                        <strong>Gunakan API kustom</strong>
                        <br><small class="text-muted">Jika nonaktif, sistem memakai konfigurasi bawaan file <code>.env</code></small>
                    </label>
                </div>

                <div class="row g-3">
                    <div class="col-md-7">
                        <label class="form-label" style="font-size:.82rem; font-weight:600;">API URL</label>
                        <input type="url" name="api_url" class="form-control" id="wa_api_url"
                               value="{{ old('api_url', $whatsapp?->api_url ?? config('whatsapp.api_url')) }}" placeholder="https://api.fonnte.com/send">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label" style="font-size:.82rem; font-weight:600;">API Key</label>
                        <div class="input-group">
                            <input type="password" name="api_key" class="form-control" id="wa_api_key"
                                   placeholder="{{ $whatsapp?->api_key ? '•••••••• (tersimpan)' : 'token dari penyedia gateway' }}" autocomplete="new-password">
                            <button class="btn btn-outline-secondary" type="button" id="toggleWaKey" aria-label="Tampilkan API Key" title="Intip API Key">
                                <i class="bi bi-eye" id="toggleWaKeyIcon"></i>
                            </button>
                        </div>
                        <small class="text-muted">Kosongkan jika tidak diubah</small>
                    </div>
                    <div class="col-12">
                        <small class="text-muted">
                            Didukung: <strong>Fonnte</strong>, <strong>WAPlus</strong>, <strong>WhatsApp Cloud API</strong>, atau gateway lain dengan endpoint <code>/send</code>.
                        </small>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2 align-items-center mt-4">
                    <button type="submit" class="btn text-white px-4" style="background:linear-gradient(135deg,#059669,#10b981); box-shadow:0 6px 16px rgba(5,150,105,.3);">
                        <i class="bi bi-save"></i> Simpan WhatsApp API
                    </button>
                </div>
            </form>

            <!-- Uji koneksi + pesan tes -->
            <hr style="border-color:#e2e8f0; margin:20px 0 16px;">
            <form method="POST" action="{{ route('whatsapp.test') }}" id="waTestForm">
                @csrf
                <div class="d-flex flex-wrap gap-2 align-items-end">
                    <div style="flex:1 1 220px;">
                        <label class="form-label" style="font-size:.82rem; font-weight:600;">Kirim Pesan Tes Ke</label>
                        <input type="text" name="test_to" class="form-control" value="{{ old('wa_number', $settings?->wa_number) }}" placeholder="0812xxxxx" required>
                    </div>
                    <button type="submit" class="btn btn-outline-success px-4" id="waTestBtn" style="border-color:#059669; color:#059669;">
                        <i class="bi bi-send"></i> Uji Kirim
                    </button>
                </div>
            </form>

            @if($whatsapp && $whatsapp->last_tested_at)
                <div class="mt-3 p-2 rounded-3 {{ $whatsapp->last_test_status === 'success' ? 'bg-success-subtle' : 'bg-danger-subtle' }}"
                     style="font-size:.8rem;">
                    @if($whatsapp->last_test_status === 'success')
                        <i class="bi bi-check-circle-fill text-success me-1"></i>
                        <strong>Terakhir diuji {{ $whatsapp->last_tested_at->format('d M Y H:i') }}</strong> — berhasil.
                    @else
                        <i class="bi bi-x-circle-fill text-danger me-1"></i>
                        <strong>Terakhir diuji {{ $whatsapp->last_tested_at->format('d M Y H:i') }}</strong> — gagal:
                        <span class="text-muted">{{ $whatsapp->last_test_message }}</span>
                    @endif
                </div>
            @endif
        </div>
        @else
        <div class="card card-stat p-4 mb-3">
            <h5 class="fw-bold mb-3"><i class="bi bi-whatsapp text-success"></i> Koneksi WhatsApp API</h5>
            @if(config('whatsapp.api_key'))
                <div class="alert alert-success mt-2 mb-0"><i class="bi bi-check-circle"></i> API terkonfigurasi!</div>
            @else
                <div class="alert alert-warning mt-2 mb-0"><i class="bi bi-exclamation-triangle"></i> API belum dikonfigurasi. Hubungi admin.</div>
            @endif
        </div>
        @endcan

        <!-- ====== Payment Gateway Midtrans (Admin) ====== -->
        @can('admin')
        <div class="card card-stat p-4 mb-3" id="midtrans" style="scroll-margin-top:24px;">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h5 class="fw-bold mb-1"><i class="bi bi-credit-card-2-front me-2" style="color:#0369a1;"></i>Payment Gateway — Midtrans</h5>
                    <p class="text-muted mb-0" style="font-size:.82rem;">
                        Aktifkan pembayaran online (VA, QRIS, E-Wallet, Kartu) untuk invoice klien.
                    </p>
                </div>
                @if($midtrans && $midtrans->is_active)
                    <span class="badge rounded-pill" style="background:#e0f2fe; color:#075985; font-size:.72rem;">
                        <i class="bi bi-check-circle-fill me-1"></i>{{ $midtrans->is_production ? 'Production' : 'Sandbox' }} Aktif
                    </span>
                @else
                    <span class="badge rounded-pill" style="background:#fef3c7; color:#92400e; font-size:.72rem;">
                        <i class="bi bi-exclamation-circle-fill me-1"></i>Nonaktif
                    </span>
                @endif
            </div>

            <form method="POST" action="{{ route('midtrans.store') }}">
                @csrf
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="is_active" id="mt_active" value="1"
                        {{ $midtrans?->is_active ? 'checked' : '' }}>
                    <label class="form-check-label" for="mt_active"><strong>Aktifkan pembayaran Midtrans</strong></label>
                </div>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="is_production" id="mt_prod" value="1"
                        {{ $midtrans?->is_production ? 'checked' : '' }}>
                    <label class="form-check-label" for="mt_prod">
                        <strong>Mode Production</strong>
                        <br><small class="text-muted">Nonaktif = Sandbox (untuk pengujian)</small>
                    </label>
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label" style="font-size:.82rem; font-weight:600;">Merchant ID (MID)</label>
                        <input type="text" name="merchant_id" class="form-control"
                               value="{{ old('merchant_id', $midtrans?->merchant_id) }}" placeholder="G123456789">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" style="font-size:.82rem; font-weight:600;">Server Key</label>
                        <input type="password" name="server_key" class="form-control" autocomplete="new-password"
                               placeholder="{{ $midtrans?->server_key ? '•••••••• (tersimpan)' : 'SB-Mid-server-...' }}">
                        <small class="text-muted">Kosongkan jika tidak diubah</small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" style="font-size:.82rem; font-weight:600;">Client Key</label>
                        <input type="password" name="client_key" class="form-control" autocomplete="new-password"
                               placeholder="{{ $midtrans?->client_key ? '•••••••• (tersimpan)' : 'SB-Mid-client-...' }}">
                    </div>
                    <div class="col-12">
                        <div class="p-3 rounded-3" style="background:#f0f9ff; border:1px solid #bae6fd;">
                            <small style="color:#075985;">
                                <i class="bi bi-info-circle"></i>
                                <strong>Webhook URL</strong> — daftarkan di Midtrans Dashboard → Settings → Configuration:
                                <div class="mt-1"><code>{{ route('payment.midtrans.webhook') }}</code></div>
                            </small>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2 mt-4">
                    <button type="submit" class="btn text-white px-4" style="background:linear-gradient(135deg,#0369a1,#0ea5e9); box-shadow:0 6px 16px rgba(3,105,161,.3);">
                        <i class="bi bi-save"></i> Simpan Midtrans
                    </button>
                </div>
            </form>

            <hr style="border-color:#e2e8f0; margin:20px 0 16px;">
            <form method="POST" action="{{ route('midtrans.test') }}">
                @csrf
                <div class="d-flex flex-wrap gap-2 align-items-end">
                    <div style="flex:1 1 220px;">
                        <label class="form-label" style="font-size:.82rem; font-weight:600;">Tes Koneksi</label>
                        <div class="d-flex gap-2">
                            <input type="text" name="merchant_id" class="form-control" value="{{ $midtrans?->merchant_id }}" placeholder="Merchant ID">
                            <button type="submit" class="btn btn-outline-primary px-3" style="border-color:#0369a1; color:#0369a1;" title="Tes kredensial">
                                <i class="bi bi-lightning"></i> Tes
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            @if($midtrans && $midtrans->last_tested_at)
                <div class="mt-3 p-2 rounded-3 {{ $midtrans->last_test_status === 'success' ? 'bg-success-subtle' : 'bg-danger-subtle' }}"
                     style="font-size:.8rem;">
                    @if($midtrans->last_test_status === 'success')
                        <i class="bi bi-check-circle-fill text-success"></i>
                        <strong>Terakhir diuji {{ $midtrans->last_tested_at->format('d M Y H:i') }}</strong> — berhasil.
                    @else
                        <i class="bi bi-x-circle-fill text-danger"></i>
                        <strong>Terakhir diuji {{ $midtrans->last_tested_at->format('d M Y H:i') }}</strong> — gagal:
                        <span class="text-muted">{{ $midtrans->last_test_message }}</span>
                    @endif
                </div>
            @endif
        </div>
        @endcan

        <!-- How It Works -->
        <div class="card card-stat p-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-info-circle"></i> Cara Kerja</h5>
            <ul class="small text-muted mb-0">
                <li class="mb-2">Sistem akan otomatis mengecek invoice yang mendekati jatuh tempo setiap hari pukul <strong>{{ $settings ? str_pad($settings->send_at_hour ?? 9, 2, '0', STR_PAD_LEFT) : '09' }}:00</strong>.</li>
                <li class="mb-2">Notifikasi dikirim <strong>sekali saja</strong> per jadwal — tidak akan duplikat.</li>
                <li class="mb-2">Invoice yang sudah <strong>Lunas</strong> tidak akan menerima notifikasi.</li>
                <li class="mb-2">Anda perlu menjalankan <strong>Cron Job</strong> atau <strong>Scheduler</strong> Laravel:
                    <div class="bg-dark text-light p-2 rounded-3 mt-1">
                        <code>* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1</code>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Intip password SMTP
    document.getElementById('toggleSmtpPass')?.addEventListener('click', function () {
        const input = document.getElementById('mail_password');
        const icon = document.getElementById('toggleSmtpPassIcon');
        const show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        icon.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
    });

    // Intip API Key WhatsApp
    document.getElementById('toggleWaKey')?.addEventListener('click', function () {
        const input = document.getElementById('wa_api_key');
        const icon = document.getElementById('toggleWaKeyIcon');
        const show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        icon.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
    });

    // Pesan tes memakai nilai terakhir di form utama WhatsApp
    document.getElementById('waTestForm')?.addEventListener('submit', function () {
        ['wa_api_url', 'wa_api_key'].forEach(function (id) {
            const src = document.getElementById(id);
            if (!src) return;
            let hidden = this.querySelector('input[name="' + src.name + '"]');
            if (!hidden) {
                hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = src.name;
                this.appendChild(hidden);
            }
            hidden.value = src.value;
        }, this);

        const btn = document.getElementById('waTestBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Mengirim...';
    });

    // Email tes memakai nilai terakhir di form utama
    document.getElementById('smtpTestForm')?.addEventListener('submit', function () {
        ['mail_host', 'mail_port', 'mail_encryption', 'mail_username', 'mail_password', 'mail_from_address', 'mail_from_name'].forEach(function (id) {
            const src = document.getElementById(id);
            if (!src) return;
            let hidden = this.querySelector('input[name="' + src.name + '"]');
            if (!hidden) {
                hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = src.name;
                this.appendChild(hidden);
            }
            hidden.value = src.value;
        }, this);

        const btn = document.getElementById('smtpTestBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Mengirim...';
    });
</script>
@endpush

@endsection
