@extends('layouts.app')
@section('title', 'Pengaturan Notifikasi')
@section('content')

<h3 class="fw-bold mb-4">🔔 Pengaturan Notifikasi Jatuh Tempo</h3>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card card-stat p-4">
            <form method="POST" action="{{ route('notifications.store') }}">
                @csrf

                <h5 class="fw-bold mb-3"><i class="bi bi-bell me-2"></i>Channel Notifikasi</h5>

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="wa_enabled" id="wa_enabled"
                        {{ $settings && $settings->wa_enabled ? 'checked' : '' }}>
                    <label class="form-check-label" for="wa_enabled">
                        <i class="bi bi-whatsapp text-success"></i> <strong>WhatsApp</strong>
                    </label>
                    <br><small class="text-muted">Kirim notifikasi via WhatsApp ke nomor Anda</small>
                </div>

                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" type="checkbox" name="email_enabled" id="email_enabled"
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
                    <input class="form-check-input" type="checkbox" name="send_on_due_day" id="send_on_due_day"
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

                <button type="submit" class="btn text-white px-4" style="background:#4f46e5;">
                    <i class="bi bi-save"></i> Simpan Pengaturan
                </button>
            </form>
        </div>
    </div>

    <div class="col-lg-5">
        <!-- WhatsApp API Info -->
        <div class="card card-stat p-4 mb-3">
            <h5 class="fw-bold mb-3"><i class="bi bi-whatsapp text-success"></i> Koneksi WhatsApp API</h5>
            <p class="text-muted small">Untuk mengirim notifikasi WhatsApp, Anda perlu mengisi API Key dari penyedia gateway WhatsApp di file <code>.env</code>:</p>
            <div class="bg-dark text-light p-3 rounded-3 small mb-2">
                <code>WHATSAPP_API_KEY=your_api_key_here</code><br>
                <code>WHATSAPP_API_URL=https://api.fonnte.com/send</code>
            </div>
            <small class="text-muted">
                Didukung: <strong>Fonnte</strong>, <strong>WAPlus</strong>, <strong>WhatsApp Cloud API</strong>, atau gateway lain.
            </small>
            @if(config('whatsapp.api_key'))
                <div class="alert alert-success mt-2 mb-0"><i class="bi bi-check-circle"></i> API terkonfigurasi!</div>
            @else
                <div class="alert alert-warning mt-2 mb-0"><i class="bi bi-exclamation-triangle"></i> API belum dikonfigurasi</div>
            @endif
        </div>

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

@endsection
