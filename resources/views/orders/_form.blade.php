{{-- Form shared: order create & edit --}}
<div class="card card-stat p-4">
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label fw-semibold">No. Order *</label>
            <input type="text" name="order_number" class="form-control" value="{{ old('order_number', $order->order_number ?? $nextNumber) }}" required>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Klien *</label>
            <select name="client_id" id="client_select" class="form-select">
                <option value="">— Manual —</option>
                @foreach($clients as $c)
                    <option value="{{ $c->id }}" data-name="{{ $c->name }}" data-email="{{ $c->email }}" data-phone="{{ $c->phone }}"
                        {{ old('client_id', $order->client_id ?? '') == $c->id ? 'selected' : '' }}>{{ $c->name }} {{ $c->company ? '(' . $c->company . ')' : '' }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Tanggal Order</label>
            <input type="date" name="order_date" class="form-control" value="{{ old('order_date', optional($order->order_date ?? null)->format('Y-m-d') ?? today()->format('Y-m-d')) }}">
        </div>

        <div class="col-md-4">
            <label class="form-label fw-semibold">Nama Klien *</label>
            <input type="text" name="client_name" id="client_name" class="form-control" value="{{ old('client_name', $order->client_name ?? '') }}" required>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Email Klien</label>
            <input type="email" name="client_email" id="client_email" class="form-control" value="{{ old('client_email', $order->client_email ?? '') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">No. HP Klien</label>
            <input type="text" name="client_phone" id="client_phone" class="form-control" value="{{ old('client_phone', $order->client_phone ?? '') }}">
        </div>

        @if(isset($quotations) && $quotations->count() > 0)
        <div class="col-12">
            <label class="form-label fw-semibold">Ambil dari Penawaran (opsional)</label>
            <select name="quotation_id" id="quotation_select" class="form-select">
                <option value="">— Tanpa penawaran —</option>
                @foreach($quotations as $q)
                    <option value="{{ $q->id }}" data-items='{{ json_encode($q->items) }}'
                        {{ old('quotation_id', $order->quotation_id ?? '') == $q->id ? 'selected' : '' }}>
                        {{ $q->quote_number }} — {{ $q->client_name }} (Rp {{ number_format($q->total, 0, ',', '.') }})
                    </option>
                @endforeach
            </select>
        </div>
        @endif
    </div>

    <hr>

    <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="fw-bold mb-0">Item Order</h6>
        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addItem()"><i class="bi bi-plus-lg"></i> Tambah Item</button>
    </div>

    <div id="items-wrap" class="d-flex flex-column gap-2">
        @php $existingItems = old('items', $order->items ?? []); @endphp
        @foreach($existingItems as $i => $item)
        <div class="row g-2 item-row align-items-end">
            <div class="col-md-6">
                <label class="form-label small text-muted">Deskripsi</label>
                <input type="text" name="items[{{ $i }}][description]" class="form-control item-desc" value="{{ $item['description'] ?? '' }}">
            </div>
            <div class="col-md-2 col-6">
                <label class="form-label small text-muted">Qty</label>
                <input type="number" name="items[{{ $i }}][quantity]" class="form-control item-qty" value="{{ $item['quantity'] ?? 1 }}" min="1" required>
            </div>
            <div class="col-md-3 col-6">
                <label class="form-label small text-muted">Harga Satuan</label>
                <input type="number" name="items[{{ $i }}][unit_price]" class="form-control item-price" value="{{ $item['unit_price'] ?? 0 }}" min="0" step="0.01" required>
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-outline-danger w-100" onclick="removeItem(this)"><i class="bi bi-trash"></i></button>
            </div>
        </div>
        @endforeach
    </div>

    <div class="d-flex justify-content-between align-items-center mt-3 fs-5" style="font-family:'Sora';">
        <strong>TOTAL</strong><strong id="totalText" class="text-primary">Rp 0</strong>
    </div>

    <div class="col-12 mt-3">
        <label class="form-label fw-semibold">Catatan</label>
        <textarea name="notes" rows="2" class="form-control">{{ old('notes', $order->notes ?? '') }}</textarea>
    </div>

    <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-brand text-white px-4"><i class="bi bi-save"></i> Simpan Order</button>
        <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary">Batal</a>
    </div>
</div>

@push('scripts')
<script>
let itemIndex = {{ count($existingItems) }};

document.getElementById('client_select')?.addEventListener('change', function () {
    const opt = this.options[this.selectedIndex];
    if (!opt.value) return;
    document.getElementById('client_name').value = opt.dataset.name || '';
    document.getElementById('client_email').value = opt.dataset.email || '';
    document.getElementById('client_phone').value = opt.dataset.phone || '';
});

// Isi otomatis dari quotation
document.getElementById('quotation_select')?.addEventListener('change', function () {
    const opt = this.options[this.selectedIndex];
    if (!opt.value || !opt.dataset.items) return;
    const items = JSON.parse(opt.dataset.items);
    const wrap = document.getElementById('items-wrap');
    wrap.innerHTML = '';
    itemIndex = 0;
    items.forEach(it => {
        addItem();
        const row = wrap.lastElementChild;
        row.querySelector('.item-desc').value = it.description || '';
        row.querySelector('.item-qty').value = it.quantity || 1;
        row.querySelector('.item-price').value = it.unit_price || 0;
    });
    calculate();
});

function addItem() {
    const wrap = document.getElementById('items-wrap');
    const row = document.createElement('div');
    row.className = 'row g-2 item-row align-items-end';
    row.innerHTML = `
        <div class="col-md-6">
            <label class="form-label small text-muted">Deskripsi</label>
            <input type="text" name="items[\${itemIndex}][description]" class="form-control item-desc" placeholder="Deskripsi item...">
        </div>
        <div class="col-md-2 col-6">
            <label class="form-label small text-muted">Qty</label>
            <input type="number" name="items[\${itemIndex}][quantity]" class="form-control item-qty" value="1" min="1" required>
        </div>
        <div class="col-md-3 col-6">
            <label class="form-label small text-muted">Harga Satuan</label>
            <input type="number" name="items[\${itemIndex}][unit_price]" class="form-control item-price" value="0" min="0" step="0.01" required>
        </div>
        <div class="col-md-1">
            <button type="button" class="btn btn-outline-danger w-100" onclick="removeItem(this)"><i class="bi bi-trash"></i></button>
        </div>`;
    wrap.appendChild(row);
    bindRow(row);
    itemIndex++;
}

function removeItem(btn) {
    const wrap = document.getElementById('items-wrap');
    if (wrap.querySelectorAll('.item-row').length <= 1) return alert('Minimal 1 item.');
    btn.closest('.item-row').remove();
    calculate();
}

function bindRow(row) {
    row.querySelectorAll('.item-qty, .item-price').forEach(inp => inp.addEventListener('input', calculate));
}

function calculate() {
    let total = 0;
    document.querySelectorAll('#items-wrap .item-row').forEach(row => {
        const qty = parseFloat(row.querySelector('.item-qty')?.value) || 0;
        const price = parseFloat(row.querySelector('.item-price')?.value) || 0;
        total += qty * price;
    });
    document.getElementById('totalText').textContent = 'Rp ' + Math.round(total).toLocaleString('id-ID');
}

document.querySelectorAll('#items-wrap .item-row').forEach(bindRow);
calculate();
</script>
@endpush
