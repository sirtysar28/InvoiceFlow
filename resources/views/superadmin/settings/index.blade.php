@extends('layouts.app')
@section('title', 'Pengaturan Sistem')

@section('content')

<div class="mb-4">
    <h3 class="fw-bold mb-1">⚙️ Pengaturan Sistem</h3>
    <p class="text-muted mb-0">Konfigurasi global aplikasi (berlaku untuk semua perusahaan).</p>
</div>

<form method="POST" action="{{ route('system-settings.update') }}" class="table-container p-4" style="max-width:720px;">
    @csrf
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label fw-semibold">Nama Aplikasi</label>
            <input type="text" name="app_name" class="form-control" value="{{ $settings['app_name'] }}" required>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">Jatuh Tempo Default (hari)</label>
            <input type="number" name="default_payment_due_days" class="form-control" value="{{ $settings['default_payment_due_days'] }}" min="1" max="120">
            <small class="text-muted">Dipakai saat konversi quotation/order → invoice</small>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">WhatsApp Support</label>
            <input type="text" name="support_whatsapp" class="form-control" value="{{ $settings['support_whatsapp'] }}" placeholder="6281234567890">
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">Email Support</label>
            <input type="email" name="support_email" class="form-control" value="{{ $settings['support_email'] }}">
        </div>
        <div class="col-md-6">
            <div class="form-check form-switch mt-2">
                <input class="form-check-input" type="checkbox" name="registration_open" id="regOpen" value="1" {{ $settings['registration_open'] == '1' ? 'checked' : '' }}>
                <label class="form-check-label" for="regOpen"><strong>Pendaftaran terbuka</strong><br><small class="text-muted">Izinkan registrasi customer baru dari landing page</small></label>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-check form-switch mt-2">
                <input class="form-check-input" type="checkbox" name="maintenance_mode" id="maintMode" value="1" {{ $settings['maintenance_mode'] == '1' ? 'checked' : '' }}>
                <label class="form-check-label" for="maintMode"><strong>Mode maintenance</strong><br><small class="text-muted">Tampilkan pemberitahuan sedang perbaikan</small></label>
            </div>
        </div>
    </div>
    <button type="submit" class="btn btn-brand text-white mt-4"><i class="bi bi-save"></i> Simpan Pengaturan</button>
</form>

@endsection
