@extends('layouts.app')
@section('title', 'Edit Klien')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold mb-0">✏️ Edit Klien: {{ $client->name }}</h3>
    <a href="{{ route('clients.show', $client) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<div class="card card-stat p-4">
    <form method="POST" action="{{ route('clients.update', $client) }}">
        @csrf @method('PUT')
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Nama Klien <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $client->name) }}" required>
                @error('name') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $client->email) }}" required>
                @error('email') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label">No. HP</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone', $client->phone) }}">
            </div>

            <div class="col-md-6">
                <label class="form-label">Perusahaan</label>
                <input type="text" name="company" class="form-control" value="{{ old('company', $client->company) }}">
            </div>

            <div class="col-12">
                <label class="form-label">Alamat</label>
                <textarea name="address" class="form-control" rows="2">{{ old('address', $client->address) }}</textarea>
            </div>

            <div class="col-12">
                <label class="form-label">Catatan</label>
                <textarea name="notes" class="form-control" rows="2">{{ old('notes', $client->notes) }}</textarea>
            </div>

            <div class="col-12 d-flex gap-2 pt-2">
                <button type="submit" class="btn text-white px-4" style="background:#4f46e5;">
                    <i class="bi bi-save"></i> Perbarui Klien
                </button>
                <a href="{{ route('clients.show', $client) }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </div>
    </form>
</div>

@endsection
