@extends('layouts.app')
@section('title', 'Edit Order')

@section('content')
<div class="mb-4">
    <a href="{{ route('orders.show', $order) }}" class="text-muted text-decoration-none small"><i class="bi bi-arrow-left"></i> Kembali</a>
    <h3 class="fw-bold mb-0 mt-1">✏️ Edit Order {{ $order->order_number }}</h3>
</div>

<form method="POST" action="{{ route('orders.update', $order) }}">
    @csrf @method('PUT')
    @include('orders._form')
</form>
@endsection
