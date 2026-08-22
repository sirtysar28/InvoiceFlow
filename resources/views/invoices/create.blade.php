@extends('layouts.app')
@section('title', 'Buat Invoice')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold mb-0">📝 Buat Invoice Baru</h3>
    <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<div class="card card-stat p-4">
    <form method="POST" action="{{ route('invoices.store') }}">
        @csrf
        <div class="row g-3">
            <!-- Invoice Number -->
            <div class="col-md-6">
                <label class="form-label fw-semibold">No. Invoice</label>
                <input type="text" name="invoice_number" class="form-control" value="{{ old('invoice_number', $nextNumber) }}" required>
                @error('invoice_number') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <!-- Due Date -->
            <div class="col-md-6">
                <label class="form-label fw-semibold">Tanggal Jatuh Tempo</label>
                <input type="date" name="due_date" class="form-control" value="{{ old('due_date', now()->addDays(14)->format('Y-m-d')) }}" required>
                @error('due_date') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <hr class="my-2">
            <h5 class="fw-bold"><i class="bi bi-person text-muted"></i> Data Klien</h5>

            <!-- Pilih Klien (Dropdown dari Master Data) -->
            <div class="col-12">
                <label class="form-label">
                    <i class="bi bi-people-fill"></i> Pilih dari Master Klien
                    <small class="text-muted">(opsional, isi otomatis)</small>
                </label>
                <select name="client_id" id="client_select" class="form-select">
                    <option value="">-- Kosong / Isi Manual --</option>
                    @foreach($clients as $c)
                    <option value="{{ $c->id }}"
                        data-name="{{ $c->name }}"
                        data-email="{{ $c->email }}"
                        data-phone="{{ $c->phone }}"
                        {{ (old('client_id') ?? $selectedClientId ?? '') == $c->id ? 'selected' : '' }}>
                        {{ $c->name }} · {{ $c->email }}
                        @if($c->company) ({{ $c->company }}) @endif
                    </option>
                    @endforeach
                </select>
                <small class="text-muted">Pilih klien untuk mengisi otomatis, atau <a href="{{ route('clients.create') }}" target="_blank">tambah klien baru</a></small>
            </div>

            <!-- Client Name -->
            <div class="col-md-6">
                <label class="form-label">Nama Klien <span class="text-danger">*</span></label>
                <input type="text" name="client_name" id="client_name" class="form-control" value="{{ old('client_name', $prefillClient->name ?? '') }}" required placeholder="PT. Maju Bersama">
            </div>

            <!-- Client Email -->
            <div class="col-md-6">
                <label class="form-label">Email Klien <span class="text-danger">*</span></label>
                <input type="email" name="client_email" id="client_email" class="form-control" value="{{ old('client_email', $prefillClient->email ?? '') }}" required placeholder="klien@email.com">
            </div>

            <!-- Client Phone -->
            <div class="col-md-6">
                <label class="form-label">No. HP Klien <small class="text-muted">(opsional)</small></label>
                <input type="text" name="client_phone" id="client_phone" class="form-control" value="{{ old('client_phone', $prefillClient->phone ?? '') }}" placeholder="0812xxxxx">
            </div>

            @push('scripts')
            <script>
            // Auto-fill client data when selecting from dropdown
            document.getElementById('client_select').addEventListener('change', function() {
                const selected = this.options[this.selectedIndex];
                if (this.value) {
                    document.getElementById('client_name').value = selected.dataset.name || '';
                    document.getElementById('client_email').value = selected.dataset.email || '';
                    document.getElementById('client_phone').value = selected.dataset.phone || '';
                }
            });
            </script>
            @endpush

            <hr class="my-2">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0"><i class="bi bi-cart text-muted"></i> Item Invoice</h5>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addItem()">
                    <i class="bi bi-plus"></i> Tambah Item
                </button>
            </div>

            <!-- Items Table -->
            <div class="col-12">
                <div class="table-responsive">
                    <table class="table table-bordered responsive-card-table invoice-items-table" id="items-table">
                        <thead class="table-light">
                            <tr>
                                <th width="40%">Deskripsi</th>
                                <th width="12%">Qty</th>
                                <th width="20%">Harga Satuan (Rp)</th>
                                <th width="20%">Subtotal</th>
                                <th width="8%"></th>
                            </tr>
                        </thead>
                        <tbody id="items-body">
                            @php $oldItems = old('items', [['description' => '', 'quantity' => 1, 'unit_price' => 0]]); @endphp
                            @foreach($oldItems as $i => $item)
                            <tr class="item-row">
                                <td data-label="Deskripsi">
                                    <input type="text" name="items[{{ $i }}][description]" class="form-control item-desc" value="{{ $item['description'] ?? '' }}" placeholder="Jasa Pembuatan Website">
                                </td>
                                <td data-label="Qty">
                                    <input type="number" name="items[{{ $i }}][quantity]" class="form-control item-qty" value="{{ $item['quantity'] ?? 1 }}" min="1" required>
                                </td>
                                <td data-label="Harga Satuan">
                                    <input type="number" name="items[{{ $i }}][unit_price]" class="form-control item-price" value="{{ $item['unit_price'] ?? 0 }}" min="0" step="0.01" required>
                                </td>
                                <td data-label="Subtotal">
                                    <input type="text" class="form-control bg-light item-subtotal-display" readonly value="Rp 0">
                                </td>
                                <td class="text-center align-middle actions-cell" data-label="Aksi">
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItem(this)" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <hr class="my-2">

            <!-- Tax -->
            <div class="col-md-4">
                <label class="form-label">Pajak (%) <small class="text-muted">(opsional)</small></label>
                <input type="number" name="tax" class="form-control" value="{{ old('tax') }}" min="0" max="100" step="0.01" id="tax">
            </div>

            <!-- Discount -->
            <div class="col-md-4">
                <label class="form-label">Diskon (%) <small class="text-muted">(opsional)</small></label>
                <input type="number" name="discount" class="form-control" value="{{ old('discount') }}" min="0" max="100" step="0.01" id="discount">
            </div>

            <!-- Total -->
            <div class="col-md-4">
                <label class="form-label fw-bold">Total</label>
                <input type="text" class="form-control bg-light fw-bold fs-5" id="total_display" readonly value="Rp 0">
            </div>

            <!-- Notes -->
            <div class="col-12">
                <label class="form-label">Catatan <small class="text-muted">(opsional)</small></label>
                <textarea name="notes" class="form-control" rows="2" placeholder="Syarat pembayaran atau catatan tambahan...">{{ old('notes') }}</textarea>
            </div>

            <div class="col-12 d-flex gap-2 pt-2">
                <button type="submit" class="btn text-white px-4" style="background:#4f46e5;">
                    <i class="bi bi-save"></i> Simpan Invoice
                </button>
                <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    let itemIndex = {{ count($oldItems) }};

    function addItem() {
        const tbody = document.getElementById('items-body');
        const row = document.createElement('tr');
        row.className = 'item-row';
        row.innerHTML = `
            <td data-label="Deskripsi">
                <input type="text" name="items[${itemIndex}][description]" class="form-control item-desc" placeholder="Deskripsi item...">
            </td>
            <td data-label="Qty">
                <input type="number" name="items[${itemIndex}][quantity]" class="form-control item-qty" value="1" min="1" required>
            </td>
            <td data-label="Harga Satuan">
                <input type="number" name="items[${itemIndex}][unit_price]" class="form-control item-price" value="0" min="0" step="0.01" required>
            </td>
            <td data-label="Subtotal">
                <input type="text" class="form-control bg-light item-subtotal-display" readonly value="Rp 0">
            </td>
            <td class="text-center align-middle actions-cell" data-label="Aksi">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItem(this)" title="Hapus">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
        itemIndex++;
        bindRowEvents(row);
        calculateAll();
    }

    function removeItem(btn) {
        const rows = document.querySelectorAll('#items-body .item-row');
        if (rows.length <= 1) {
            alert('Minimal 1 item harus ada!');
            return;
        }
        btn.closest('tr').remove();
        reindexItems();
        calculateAll();
    }

    function reindexItems() {
        const rows = document.querySelectorAll('#items-body .item-row');
        rows.forEach((row, i) => {
            row.querySelector('.item-desc').name = `items[${i}][description]`;
            row.querySelector('.item-qty').name = `items[${i}][quantity]`;
            row.querySelector('.item-price').name = `items[${i}][unit_price]`;
        });
        itemIndex = rows.length;
    }

    function bindRowEvents(row) {
        row.querySelector('.item-qty').addEventListener('input', calculateAll);
        row.querySelector('.item-price').addEventListener('input', calculateAll);
    }

    function calculateAll() {
        let grandTotal = 0;
        document.querySelectorAll('#items-body .item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
            const price = parseFloat(row.querySelector('.item-price').value) || 0;
            const subtotal = qty * price;
            row.querySelector('.item-subtotal-display').value = 'Rp ' + subtotal.toLocaleString('id-ID');
            grandTotal += subtotal;
        });

        const taxPct = parseFloat(document.getElementById('tax').value) || 0;
        const discPct = parseFloat(document.getElementById('discount').value) || 0;
        const taxAmount = grandTotal * (taxPct / 100);
        const discAmount = grandTotal * (discPct / 100);
        const total = grandTotal + taxAmount - discAmount;

        document.getElementById('total_display').value = 'Rp ' + total.toLocaleString('id-ID');
    }

    // Bind existing rows + tax/discount inputs
    document.querySelectorAll('#items-body .item-row').forEach(bindRowEvents);
    document.getElementById('tax').addEventListener('input', calculateAll);
    document.getElementById('discount').addEventListener('input', calculateAll);

    calculateAll();
</script>
@endpush

@endsection
