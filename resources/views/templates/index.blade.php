@extends('layouts.app')
@section('title', 'Template Pesan')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold mb-0">📝 Template Pesan</h3>
    <button class="btn text-white" style="background:#4f46e5;" data-bs-toggle="modal" data-bs-target="#createTemplateModal">
        <i class="bi bi-plus-lg"></i> Buat Template
    </button>
</div>

<!-- Available Variables -->
<div class="alert alert-info small mb-4">
    <i class="bi bi-info-circle"></i> <strong>Variabel yang tersedia:</strong>
    <code>{no_invoice}</code> <code>{nama_klien}</code> <code>{total}</code> <code>{tanggal_jatuh_tempo}</code> <code>{link_invoice}</code>
</div>

<div class="table-container p-3">
    @if($templates->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle responsive-card-table">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Tipe</th>
                    <th>Subject</th>
                    <th>Dibuat</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($templates as $tpl)
                <tr>
                    <td class="fw-semibold" data-label="Nama">{{ $tpl->name }}</td>
                    <td data-label="Tipe">
                        @if($tpl->type == 'whatsapp')
                            <span class="badge bg-success"><i class="bi bi-whatsapp"></i> WhatsApp</span>
                        @else
                            <span class="badge bg-primary"><i class="bi bi-envelope"></i> Email</span>
                        @endif
                    </td>
                    <td data-label="Subject">{{ Str::limit($tpl->subject, 40) }}</td>
                    <td data-label="Dibuat"><small class="text-muted">{{ $tpl->created_at->format('d/m/Y') }}</small></td>
                    <td class="text-end actions-cell" data-label="Aksi">
                        <button class="btn btn-sm btn-outline-primary" onclick="editTemplate({{ $tpl->id }}, '{{ addslashes($tpl->name) }}', '{{ addslashes($tpl->subject) }}', '{{ addslashes($tpl->body) }}')">
                            <i class="bi bi-pencil"></i> Edit
                        </button>
                        <form action="{{ route('templates.destroy', $tpl) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus template ini?')">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="empty-state">
        <i class="bi bi-chat-square-text"></i>
        <h5>Belum ada template</h5>
        <p>Buat template pesan default untuk WhatsApp dan Email. Template akan otomatis terisi saat Anda mengirim invoice.</p>
    </div>
    @endif
</div>

<!-- Create Modal -->
<div class="modal fade" id="createTemplateModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="{{ route('templates.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle"></i> Buat Template Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Template</label>
                        <input type="text" name="name" class="form-control" placeholder="Contoh: Template WA Default" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tipe</label>
                        <select name="type" class="form-select" required>
                            <option value="whatsapp">WhatsApp</option>
                            <option value="email">Email</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subject <small class="text-muted">(untuk email)</small></label>
                        <input type="text" name="subject" class="form-control" placeholder="Invoice {no_invoice} - {nama_klien}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Isi Pesan</label>
                        <textarea name="body" class="form-control" rows="8" required placeholder="Halo {nama_klien},%0ABerikut tagihan invoice Anda:%0ANo. Invoice: {no_invoice}%0ATotal: Rp {total}%0AJatuh Tempo: {tanggal_jatuh_tempo}%0A%0ALink: {link_invoice}%0A%0ATerima kasih."></textarea>
                        <small class="text-muted">Gunakan %0A untuk baris baru (WhatsApp).</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn text-white" style="background:#4f46e5;">
                        <i class="bi bi-save"></i> Simpan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editTemplateModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="POST" id="editForm">
            @csrf @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-pencil"></i> Edit Template</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Template</label>
                        <input type="text" name="name" id="editName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Subject</label>
                        <input type="text" name="subject" id="editSubject" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Isi Pesan</label>
                        <textarea name="body" id="editBody" class="form-control" rows="8" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn text-white" style="background:#4f46e5;">
                        <i class="bi bi-save"></i> Perbarui
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function editTemplate(id, name, subject, body) {
        document.getElementById('editForm').action = '/templates/' + id;
        document.getElementById('editName').value = name;
        document.getElementById('editSubject').value = subject || '';
        document.getElementById('editBody').value = body;
        new bootstrap.Modal(document.getElementById('editTemplateModal')).show();
    }
</script>
@endpush

@endsection
