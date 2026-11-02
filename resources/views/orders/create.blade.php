@extends('layouts.app')
@section('title', 'Buat Order')

@section('content')
<div class="mb-4">
    <a href="{{ route('orders.index') }}" class="text-muted text-decoration-none small"><i class="bi bi-arrow-left"></i> Kembali</a>
    <h3 class="fw-bold mb-0 mt-1">📋 Buat Sales Order</h3>
</div>

<form method="POST" action="{{ route('orders.store') }}">
    @csrf
    @include('orders._form')
</form>
@endsection
