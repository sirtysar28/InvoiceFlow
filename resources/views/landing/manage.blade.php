@extends('layouts.app')
@section('title', 'Kelola Landing Page')

@push('styles')
<style>
    .lp-tabs { display:flex; flex-wrap: wrap; gap:8px; }
    .lp-tab {
        border: 1px solid #e2e8f0; background:#fff; border-radius: 30px;
        padding: 9px 18px; font-weight: 600; font-size: .88rem; color:#64748b;
        text-decoration: none; transition: all .2s;
    }
    .lp-tab:hover { color: var(--primary); border-color:#c7d2fe; }
    .lp-tab.active { background: var(--grad-brand); color:#fff; border-color: transparent; box-shadow: 0 8px 18px -6px rgba(99,102,241,.55); }
    .item-card {
        border: 1px dashed #cbd5e1; border-radius: 14px; padding: 16px;
        background: #f8fafc; position: relative;
    }
    .item-card .remove-item {
        position: absolute; top: 10px; right: 10px; width: 30px; height: 30px;
        border-radius: 8px; border: 1px solid #fecaca; background: #fef2f2; color: #dc2626;
        display: flex; align-items: center; justify-content: center;
    }
    .icon-pick { max-width: 130px; text-align:center; }
    .section-form { display: none; }
    .section-form.active { display: block; }
</style>
@endpush

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h3 class="fw-bold mb-0">🖥️ Kelola Landing Page</h3>
    <a href="{{ route('landing') }}" target="_blank" class="btn btn-outline-secondary">
        <i class="bi bi-eye"></i> Lihat Halaman
    </a>
</div>

<div class="lp-tabs mb-4">
    @foreach(['hero' => '🚀 Hero', 'features' => '✨ Fitur', 'steps' => '📌 Cara Kerja', 'testimonials' => '💬 Testimoni', 'faq' => '❓ FAQ', 'cta' => '🎯 CTA', 'contact' => '📞 Kontak & WA'] as $key => $label)
        <a href="javascript:void(0)" class="lp-tab {{ $key === 'hero' ? 'active' : '' }}" data-tab="{{ $key }}">{{ $label }}</a>
    @endforeach
</div>

{{-- ================= HERO ================= --}}
<form method="POST" action="{{ route('landing.update', 'hero') }}" class="section-form active" id="tab-hero">
    @csrf
    <div class="table-container p-4">
        <h5 class="panel-title mb-3"><i class="bi bi-rocket-takeoff"></i> Bagian Hero (atas)</h5>
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label small fw-semibold">Badge Kecil</label>
                <input type="text" name="data[badge]" class="form-control" value="{{ $sections['hero']['badge'] }}"></div>
            <div class="col-md-6"><label class="form-label small fw-semibold">Judul Utama</label>
                <input type="text" name="data[title]" class="form-control" value="{{ $sections['hero']['title'] }}"></div>
            <div class="col-md-6"><label class="form-label small fw-semibold">Highlight (teks gradient)</label>
                <input type="text" name="data[highlight]" class="form-control" value="{{ $sections['hero']['highlight'] }}"></div>
            <div class="col-md-6"><label class="form-label small fw-semibold">Subjudul</label>
                <input type="text" name="data[subtitle]" class="form-control" value="{{ $sections['hero']['subtitle'] }}"></div>
            <div class="col-md-3 col-6"><label class="form-label small fw-semibold">Tombol Utama</label>
                <input type="text" name="data[btn_primary_text]" class="form-control" value="{{ $sections['hero']['btn_primary_text'] }}"></div>
            <div class="col-md-3 col-6"><label class="form-label small fw-semibold">Link Tombol Utama</label>
                <input type="text" name="data[btn_primary_link]" class="form-control" value="{{ $sections['hero']['btn_primary_link'] }}"></div>
            <div class="col-md-3 col-6"><label class="form-label small fw-semibold">Tombol Kedua</label>
                <input type="text" name="data[btn_secondary_text]" class="form-control" value="{{ $sections['hero']['btn_secondary_text'] }}"></div>
            <div class="col-md-3 col-6"><label class="form-label small fw-semibold">Link Tombol Kedua</label>
                <input type="text" name="data[btn_secondary_link]" class="form-control" value="{{ $sections['hero']['btn_secondary_link'] }}"></div>
        </div>

        <label class="form-label small fw-semibold mt-4 d-block">Statistik (maks 4)</label>
        <div class="row g-2" id="stats-wrap">
            @foreach($sections['hero']['stats'] as $i => $s)
            <div class="col-md-3 col-6">
                <div class="item-card">
                    <input type="text" name="data[stats][{{ $i }}][value]" class="form-control form-control-sm mb-2" placeholder="500+" value="{{ $s['value'] }}">
                    <input type="text" name="data[stats][{{ $i }}][label]" class="form-control form-control-sm" placeholder="Invoice Terkirim" value="{{ $s['label'] }}">
                </div>
            </div>
            @endforeach
        </div>
        <button type="submit" class="btn btn-brand text-white mt-4"><i class="bi bi-save"></i> Simpan Hero</button>
    </div>
</form>

{{-- ================= ITEMS SECTIONS (features/steps/testimonials/faq) ================= --}}
@php
    $itemSections = [
        'features' => ['label' => 'Fitur', 'icon' => 'bi-stars', 'titleKey' => 'title', 'fields' => [
            'icon' => ['label' => 'Ikon Bootstrap', 'placeholder' => 'bi-receipt-cutoff'],
            'title' => ['label' => 'Judul', 'placeholder' => 'Invoice Profesional'],
            'desc' => ['label' => 'Deskripsi', 'placeholder' => '...'],
        ]],
        'steps' => ['label' => 'Cara Kerja', 'icon' => 'bi-signpost-split', 'titleKey' => 'title', 'fields' => [
            'title' => ['label' => 'Judul Langkah', 'placeholder' => 'Daftar & Lengkapi Profil'],
            'desc' => ['label' => 'Deskripsi', 'placeholder' => '...'],
        ]],
        'testimonials' => ['label' => 'Testimoni', 'icon' => 'bi-chat-quote', 'titleKey' => 'name', 'fields' => [
            'name' => ['label' => 'Nama', 'placeholder' => 'Andi Pratama'],
            'role' => ['label' => 'Profesi', 'placeholder' => 'Owner Digital Agency'],
            'quote' => ['label' => 'Isi Testimoni', 'placeholder' => '...'],
        ]],
        'faq' => ['label' => 'FAQ', 'icon' => 'bi-question-circle', 'titleKey' => 'q', 'fields' => [
            'q' => ['label' => 'Pertanyaan', 'placeholder' => 'Apakah bisa dipakai gratis?'],
            'a' => ['label' => 'Jawaban', 'placeholder' => '...'],
        ]],
    ];
@endphp

@foreach($itemSections as $key => $cfg)
<form method="POST" action="{{ route('landing.update', $key) }}" class="section-form" id="tab-{{ $key }}">
    @csrf
    <div class="table-container p-4">
        <div class="row g-3 mb-4">
            <div class="col-md-6"><label class="form-label small fw-semibold">Judul Section</label>
                <input type="text" name="data[heading]" class="form-control" value="{{ $sections[$key]['heading'] }}"></div>
            <div class="col-md-6"><label class="form-label small fw-semibold">Subjudul</label>
                <input type="text" name="data[subheading]" class="form-control" value="{{ $sections[$key]['subheading'] }}"></div>
        </div>

        <label class="form-label small fw-semibold d-block mb-2">Item {{ $cfg['label'] }}</label>
        <div class="d-flex flex-column gap-3 items-wrap" data-section="{{ $key }}">
            @foreach($sections[$key]['items'] as $i => $item)
            <div class="item-card">
                <button type="button" class="remove-item" title="Hapus item"><i class="bi bi-trash"></i></button>
                <div class="row g-2">
                    @foreach($cfg['fields'] as $field => $fc)
                    <div class="{{ $field == 'desc' || $field == 'quote' || $field == 'a' ? 'col-md-8' : 'col-md-4' }}">
                        <label class="form-label" style="font-size:.78rem; font-weight:600; color:#64748b;">{{ $fc['label'] }}</label>
                        @if($field == 'desc' || $field == 'quote' || $field == 'a')
                            <textarea name="data[items][{{ $i }}][{{ $field }}]" rows="2" class="form-control form-control-sm">{{ $item[$field] ?? '' }}</textarea>
                        @else
                            <input type="text" name="data[items][{{ $i }}][{{ $field }}]" class="form-control form-control-sm {{ $field == 'icon' ? 'icon-pick' : '' }}" placeholder="{{ $fc['placeholder'] }}" value="{{ $item[$field] ?? '' }}">
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>

        <button type="button" class="btn btn-outline-primary btn-sm mt-3 add-item"
                data-section="{{ $key }}"
                data-fields='@json($cfg['fields'])'>
            <i class="bi bi-plus-lg"></i> Tambah Item
        </button>

        <button type="submit" class="btn btn-brand text-white mt-4 ms-2"><i class="bi bi-save"></i> Simpan {{ $cfg['label'] }}</button>
    </div>
</form>
@endforeach

{{-- ================= CTA ================= --}}
<form method="POST" action="{{ route('landing.update', 'cta') }}" class="section-form" id="tab-cta">
    @csrf
    <div class="table-container p-4">
        <h5 class="panel-title mb-3"><i class="bi bi-megaphone"></i> Banner Ajakan (CTA)</h5>
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label small fw-semibold">Judul</label>
                <input type="text" name="data[title]" class="form-control" value="{{ $sections['cta']['title'] }}"></div>
            <div class="col-md-6"><label class="form-label small fw-semibold">Subjudul</label>
                <input type="text" name="data[subtitle]" class="form-control" value="{{ $sections['cta']['subtitle'] }}"></div>
            <div class="col-md-6"><label class="form-label small fw-semibold">Teks Tombol</label>
                <input type="text" name="data[btn_text]" class="form-control" value="{{ $sections['cta']['btn_text'] }}"></div>
            <div class="col-md-6"><label class="form-label small fw-semibold">Link Tombol</label>
                <input type="text" name="data[btn_link]" class="form-control" value="{{ $sections['cta']['btn_link'] }}"></div>
        </div>
        <button type="submit" class="btn btn-brand text-white mt-4"><i class="bi bi-save"></i> Simpan CTA</button>
    </div>
</form>

{{-- ================= CONTACT ================= --}}
<form method="POST" action="{{ route('landing.update', 'contact') }}" class="section-form" id="tab-contact">
    @csrf
    <div class="table-container p-4">
        <h5 class="panel-title mb-3"><i class="bi bi-telephone"></i> Kontak & WhatsApp Floating</h5>
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label small fw-semibold">Telepon</label>
                <input type="text" name="data[phone]" class="form-control" value="{{ $sections['contact']['phone'] }}"></div>
            <div class="col-md-6"><label class="form-label small fw-semibold">Email</label>
                <input type="email" name="data[email]" class="form-control" value="{{ $sections['contact']['email'] }}"></div>
            <div class="col-md-6"><label class="form-label small fw-semibold">Alamat</label>
                <input type="text" name="data[address]" class="form-control" value="{{ $sections['contact']['address'] }}"></div>
            <div class="col-md-6">
                <label class="form-label small fw-semibold">No. WhatsApp (tombol floating)</label>
                <input type="text" name="data[whatsapp]" class="form-control" value="{{ $sections['contact']['whatsapp'] }}" placeholder="6281234567890">
                <small class="text-muted">Format internasional tanpa +, contoh: 6281234567890</small>
            </div>
        </div>
        <button type="submit" class="btn btn-brand text-white mt-4"><i class="bi bi-save"></i> Simpan Kontak</button>
    </div>
</form>

@push('scripts')
<script>
// ===== Tabs =====
document.querySelectorAll('.lp-tab').forEach(tab => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('.lp-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.section-form').forEach(f => f.classList.remove('active'));
        tab.classList.add('active');
        document.getElementById('tab-' + tab.dataset.tab).classList.add('active');
    });
});

// ===== Re-index semua input name setelah add/remove =====
function reindex(wrap) {
    wrap.querySelectorAll('.item-card').forEach((card, i) => {
        card.querySelectorAll('[name]').forEach(input => {
            input.name = input.name.replace(/\[items\]\[\d+\]/, `[items][${i}]`);
        });
    });
    wrap.querySelectorAll('.remove-item').forEach(btn => {
        btn.style.display = wrap.querySelectorAll('.item-card').length > 1 ? 'flex' : 'none';
    });
}

// ===== Remove item =====
document.addEventListener('click', e => {
    const btn = e.target.closest('.remove-item');
    if (!btn) return;
    const wrap = btn.closest('.items-wrap');
    btn.closest('.item-card').remove();
    reindex(wrap);
});

// ===== Add item =====
document.querySelectorAll('.add-item').forEach(btn => {
    btn.addEventListener('click', () => {
        const section = btn.dataset.section;
        const fields = JSON.parse(btn.dataset.fields);
        const wrap = document.querySelector(`.items-wrap[data-section="${section}"]`);
        const idx = wrap.querySelectorAll('.item-card').length;

        const card = document.createElement('div');
        card.className = 'item-card';

        let row = '<button type="button" class="remove-item" title="Hapus item"><i class="bi bi-trash"></i></button><div class="row g-2">';
        for (const [field, cfg] of Object.entries(fields)) {
            const wide = ['desc', 'quote', 'a'].includes(field);
            row += `<div class="${wide ? 'col-md-8' : 'col-md-4'}">
                <label class="form-label" style="font-size:.78rem;font-weight:600;color:#64748b;">${cfg.label}</label>
                ${wide
                    ? `<textarea name="data[items][${idx}][${field}]" rows="2" class="form-control form-control-sm" placeholder="${cfg.placeholder}"></textarea>`
                    : `<input type="text" name="data[items][${idx}][${field}]" class="form-control form-control-sm" placeholder="${cfg.placeholder}">`}
            </div>`;
        }
        row += '</div>';
        card.innerHTML = row;
        wrap.appendChild(card);
        reindex(wrap);
        card.querySelector('input, textarea')?.focus();
    });
});

// Pastikan minimal 1 item tetap ada saat load
document.querySelectorAll('.items-wrap').forEach(reindex);
</script>
@endpush

@endsection
