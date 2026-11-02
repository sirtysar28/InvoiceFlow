@extends('layouts.app')
@section('title', 'Buat Penawaran')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <a href="{{ route('quotations.index') }}" class="text-muted text-decoration-none small"><i class="bi bi-arrow-left"></i> Kembali</a>
        <h3 class="fw-bold mb-0 mt-1">📝 Buat Penawaran</h3>
    </div>
</div>

<form method="POST" action="{{ route('quotations.store') }}">
    @csrf
    @include('quotations._form')
</form>
@endsection
