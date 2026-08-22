@extends('layouts.app')
@section('title', 'Profil Bisnis')
@section('content')

<h3 class="fw-bold mb-4">🏢 Profil Bisnis</h3>

<div class="card card-stat p-4">
    <form method="POST" action="{{ route('profile.business.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label fw-semibold">Nama Bisnis</label>
                <input type="text" name="business_name" class="form-control" value="{{ old('business_name', $profile?->business_name) }}" required placeholder="PT. Nama Bisnis Anda">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Logo</label>
                <input type="file" name="logo" class="form-control" accept="image/*">
                @if($profile && $profile->logo)
                    <small class="text-success"><i class="bi bi-check-circle"></i> Logo tersimpan</small>
                @endif
            </div>
            <div class="col-12">
                <label class="form-label">Alamat</label>
                <textarea name="address" class="form-control" rows="3" placeholder="Jl. Contoh No. 123, Jakarta">{{ old('address', $profile?->address) }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label">No. Telepon</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone', $profile?->phone) }}" placeholder="0812xxxxx">
                <small class="text-muted">Untuk notifikasi WhatsApp dan kontak klien</small>
            </div>
            <div class="col-md-6">
                <label class="form-label">Email Bisnis</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $profile?->email) }}" placeholder="bisnis@email.com">
            </div>

            <hr class="my-2">
            <h5 class="fw-bold"><i class="bi bi-bank text-muted"></i> Informasi Rekening</h5>
            <small class="text-muted">Informasi ini akan ditampilkan di invoice untuk pembayaran klien</small>

            <div class="col-md-4">
                <label class="form-label">Nama Bank</label>
                <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name', $profile?->bank_name) }}" placeholder="BCA, Mandiri, dll.">
            </div>
            <div class="col-md-4">
                <label class="form-label">No. Rekening</label>
                <input type="text" name="bank_account_number" class="form-control" value="{{ old('bank_account_number', $profile?->bank_account_number) }}" placeholder="1234567890">
            </div>
            <div class="col-md-4">
                <label class="form-label">Atas Nama</label>
                <input type="text" name="bank_account_name" class="form-control" value="{{ old('bank_account_name', $profile?->bank_account_name) }}" placeholder="Nama Pemilik Rekening">
            </div>

            <div class="col-12 pt-2">
                <button type="submit" class="btn text-white px-4" style="background:#4f46e5;">
                    <i class="bi bi-save"></i> Simpan Profil
                </button>
            </div>
        </div>
    </form>
</div>

@if($profile)
<div class="card card-stat p-4 mt-3">
    <h5 class="fw-bold mb-3">👀 Preview Profil di Invoice</h5>
    <div class="bg-light rounded-3 p-3">
        <p class="mb-1"><strong>{{ $profile->business_name ?? 'Belum diisi' }}</strong></p>
        @if($profile->address)<p class="mb-1 text-muted small">{!! nl2br(e($profile->address)) !!}</p>@endif
        @if($profile->phone)<p class="mb-1 small">📱 {{ $profile->phone }}</p>@endif
        @if($profile->email)<p class="mb-1 small">✉️ {{ $profile->email }}</p>@endif
        @if($profile->bank_name)
            <hr>
            <p class="mb-0 small">
                🏦 <strong>{{ $profile->bank_name }}</strong> |
                {{ $profile->bank_account_number }} |
                A.n {{ $profile->bank_account_name }}
            </p>
        @endif
    </div>
</div>
@endif

@endsection
