{{-- Form shared: quotation create & edit --}}
<div class="card card-stat p-4">
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label fw-semibold">No. Penawaran *</label>
            <input type="text" name="quote_number" class="form-control" value="{{ old('quote_number', $quotation->quote_number ?? $nextNumber) }}" required>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Klien *</label>
            <select name="client_id" id="client_select" class="form-select">
                <option value="">— Manual —</option>
                @foreach($clients as $c)
                    <option value="{{ $c->id }}" data-name="{{ $c->name }}" data-email="{{ $c->email }}" data-phone="{{ $c->phone }}"
                        {{ old('client_id', $quotation->client_id ?? '') == $c->id ? 'selected' : '' }}>{{ $c->name }} {{ $c->company ? '(' . $c->company . ')' : '' }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Berlaku Sampai</label>
            <input type="date" name="valid_until" class="form-control" value="{{ old('valid_until', optional($quotation->valid_until ?? null)->format('Y-m-d') ?? now()->addDays(30)->format('Y-m-d')) }}">
        </div>

        <div class="col-md-4">
            <label class="form-label fw-semibold">Nama Klien *</label>
            <input type="text" name="client_name" id="client_name" class="form-control" value="{{ old('client_name', $quotation->client_name ?? '') }}" required>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Email Klien</label>
            <input type="email" name="client_email" id="client_email" class="form-control" value="{{ old('client_email', $quotation->client_email ?? '') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">No. HP Klien</label>
            <input type="text" name="client_phone" id="client_phone" class="form-control" value="{{ old('client_phone', $quotation->client_phone ?? '') }}">
        </div>
    </div>

    <hr>

    <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="fw-bold mb-0">Item Penawaran</h6>
        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addItem()"><i class="bi bi-plus-lg"></i> Tambah Item</button>
    </div>

    <div id="items-wrap" class="d-flex flex-column gap-2">
        @php $existingItems = old('items', $quotation->items ?? []); @endphp
        @foreach($existingItems as $i => $item)
        <div class="row g-2 item-row align-items-end">
            <div class="col-md-6">
                <label class="form-label small text-muted">Deskripsi</label>
                <input type="text" name="items[{{ $i }}][description]" class="form-control item-desc" value="{{ $item['description'] ?? '' }}" placeholder="Jasa desain logo...">
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

    <div class="row g-3 mt-3">
        <div class="col-md-3 col-6">
            <label class="form-label fw-semibold">Diskon (%)</label>
            <input type="number" name="discount" id="discount" class="form-control" value="{{ old('discount', $quotation->discount ?? 0) }}" min="0" max="100" step="0.01">
        </div>
        <div class="col-md-3 col-6">
            <label class="form-label fw-semibold">Pajak (%)</label>
            <input type="number" name="tax" id="tax" class="form-control" value="{{ old('tax', $quotation->tax ?? 0) }}" min="0" max="100" step="0.01">
        </div>
        <div class="col-md-6 d-flex flex-column justify-content-end">
            <div class="d-flex justify-content-between"><span class="text-muted">Subtotal</span><strong id="subtotalText">Rp 0</strong></div>
            <div class="d-flex justify-content-between"><span class="text-muted">Diskon</span><strong id="discountText" class="text-danger">- Rp 0</strong></div>
            <div class="d-flex justify-content-between"><span class="text-muted">Pajak</span><strong id="taxText">Rp 0</strong></div>
            <div class="d-flex justify-content-between fs-5 mt-1" style="font-family:'Sora';"><strong>TOTAL</strong><strong id="totalText" class="text-primary">Rp 0</strong></div>
        </div>
        <div class="col-12">
            <label class="form-label fw-semibold">Catatan</label>
            <textarea name="notes" rows="2" class="form-control" placeholder="Catatan untuk klien...">{{ old('notes', $quotation->notes ?? '') }}</textarea>
        </div>
    </div>

    <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-brand text-white px-4"><i class="bi bi-save"></i> Simpan Penawaran</button>
        <a href="{{ route('quotations.index') }}" class="btn btn-outline-secondary">Batal</a>
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

function fmt(n) { return 'Rp ' + Math.round(n).toLocaleString('id-ID'); }

function calculate() {
    let subtotal = 0;
    document.querySelectorAll('#items-wrap .item-row').forEach(row => {
        const qty = parseFloat(row.querySelector('.item-qty')?.value) || 0;
        const price = parseFloat(row.querySelector('.item-price')?.value) || 0;
        subtotal += qty * price;
    });
    const discountPct = parseFloat(document.getElementById('discount')?.value) || 0;
    const taxPct = parseFloat(document.getElementById('tax')?.value) || 0;
    const discount = subtotal * discountPct / 100;
    const tax = subtotal * taxPct / 100;
    document.getElementById('subtotalText').textContent = fmt(subtotal);
    document.getElementById('discountText').textContent = '- ' + fmt(discount);
    document.getElementById('taxText').textContent = fmt(tax);
    document.getElementById('totalText').textContent = fmt(subtotal - discount + tax);
}

document.getElementById('discount')?.addEventListener('input', calculate);
document.getElementById('tax')?.addEventListener('input', calculate);
document.querySelectorAll('#items-wrap .item-row').forEach(bindRow);
calculate();
</script>
@endpush
