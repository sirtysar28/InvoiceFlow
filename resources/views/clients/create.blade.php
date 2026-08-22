@extends('layouts.app')
@section('title', 'Tambah Klien')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold mb-0">➕ Tambah Klien Baru</h3>
    <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<div class="card card-stat p-4">
    <form method="POST" action="{{ route('clients.store') }}">
        @csrf
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Nama Klien <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required placeholder="Contoh: Budi Santoso">
                @error('name') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required placeholder="klien@email.com">
                @error('email') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">No. HP <small class="text-muted">(opsional)</small></label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="0812xxxxxxxx">
            </div>

            <div class="col-md-6">
                <label class="form-label">Perusahaan <small class="text-muted">(opsional)</small></label>
                <input type="text" name="company" class="form-control" value="{{ old('company') }}" placeholder="PT. Maju Jaya">
            </div>

            <div class="col-12">
                <label class="form-label">Alamat <small class="text-muted">(opsional)</small></label>
                <textarea name="address" class="form-control" rows="2" placeholder="Alamat lengkap...">{{ old('address') }}</textarea>
            </div>

            <div class="col-12">
                <label class="form-label">Catatan <small class="text-muted">(opsional)</small></label>
                <textarea name="notes" class="form-control" rows="2" placeholder="Catatan internal...">{{ old('notes') }}</textarea>
            </div>

            {{-- Admin: opsi buat akun login untuk klien --}}
            @if(auth()->user()?->isAdmin())
            <hr class="my-2">
            <div class="col-12">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="create_user" value="1" id="create_user" {{ old('create_user') ? 'checked' : '' }} onchange="togglePassword()">
                    <label class="form-check-label fw-semibold" for="create_user">
                        <i class="bi bi-person-plus"></i> Sekaligus buat akun login untuk klien ini
                    </label>
                </div>
                <small class="text-muted">Klien bisa login dan melihat invoice mereka sendiri.</small>
            </div>
            <div class="col-md-6" id="password_field" style="display: none;">
                <label class="form-label">Password Akun <span class="text-danger">*</span></label>
                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" minlength="8" placeholder="Minimal 8 karakter">
                @error('password') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
            @endif

            <div class="col-12 d-flex gap-2 pt-2">
                <button type="submit" class="btn text-white px-4" style="background:#4f46e5;">
                    <i class="bi bi-save"></i> Simpan Klien
                </button>
                <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
function togglePassword() {
    const pw = document.getElementById('password_field');
    const cb = document.getElementById('create_user');
    pw.style.display = cb.checked ? 'block' : 'none';
}
// init
togglePassword();
</script>
@endpush

@endsection
