@extends('layouts.sidebarmenu')

@section('content')
@php($subjekFlatOptions = $subjek->map(fn ($item) => [
    'id_subjek' => $item->id_subjek,
    'label' => trim((string) $item->nama_subjek) . ' - ' . ($item->timkerja->nama_timkerja ?? 'Internal'),
])->sortBy('label', SORT_NATURAL | SORT_FLAG_CASE)->values())
@php($bulkEntries = collect(old('entries', [['tahun' => date('Y')]]))->map(function ($item) {
    return [
        'nama_sop' => $item['nama_sop'] ?? '',
        'nomor_sop' => $item['nomor_sop'] ?? '',
        'id_subjek' => $item['id_subjek'] ?? '',
        'tahun' => $item['tahun'] ?? date('Y'),
    ];
})->values()->all())

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

<style>
    .main-content-area { background-color: #f8fafc; min-height: 100vh; padding: 2rem; }
    .card-premium { border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05); overflow: hidden; }
    .card-header-bps { background: linear-gradient(135deg, #0f766e 0%, #0ea5a4 100%); color: white; padding: 1.5rem; border: none; }
    .form-label { font-weight: 600; color: #475569; font-size: 0.9rem; }
    .btn-bulk-save { background: #0f766e; border: none; border-radius: 12px; padding: 12px 30px; font-weight: 700; }
    .btn-bulk-save:hover { background: #115e59; }
    .form-control, .form-select { border-radius: 8px; border: 1px solid #cbd5e1; padding: 12px 15px; transition: all 0.2s; }
    .form-control:focus, .form-select:focus { border-color: #0f766e; box-shadow: 0 0 0 0.25rem rgba(15, 118, 110, 0.1); }
    .bulk-entry-card { border: 1px solid #dbeafe; border-radius: 18px; padding: 1.25rem; background: linear-gradient(135deg, #f8fffe 0%, #ffffff 100%); }
    .bulk-entry-header { display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 1rem; }
    .bulk-entry-badge { width: 38px; height: 38px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; background: #ccfbf1; color: #0f766e; font-weight: 700; }
    .bulk-helper { background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 14px; padding: 14px 16px; color: #475569; }
</style>

<div class="main-content-area">
    <div class="container-fluid">
        <div class="row g-4">
            <div class="col-12">
                <div class="card card-premium">
                    <div class="card-header-bps d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <div class="d-flex align-items-center">
                            <div class="bg-white rounded-circle p-2 me-3 d-inline-flex">
                                <i class="bi bi-collection text-success"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-bold">Input SOP Massal Khusus Admin</h5>
                                <small class="opacity-75">Form terpisah untuk upload SOP massal</small>
                            </div>
                        </div>
                        <a href="{{ route('admin.sop.create') }}" class="btn btn-light fw-bold">
                            <i class="bi bi-plus-lg me-2"></i>Kembali ke Form Satuan
                        </a>
                    </div>

                    <div class="card-body p-4 p-lg-5">
                        @if($errors->has('entries') || $errors->has('entries.*'))
                            <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
                                {{ $errors->first() }}
                            </div>
                        @endif

                        <div class="bulk-helper mb-4">
                            Pilih banyak PDF sekaligus untuk membuat kartu otomatis. Maksimal 50 file utama per proses, dan setiap kartu SOP juga bisa diberi banyak file revisi terkait.
                        </div>

                        <form action="{{ route('admin.sop.bulkStore') }}" method="POST" enctype="multipart/form-data" id="formBulkSop">
                            @csrf

                            <div class="mb-4">
                                <label for="bulkFilePicker" class="form-label">Pilih Banyak File SOP (PDF)</label>
                                <input type="file" class="form-control" id="bulkFilePicker" accept=".pdf" multiple>
                                <div class="form-text">Sekali pilih bisa sampai 50 file PDF. Saat file dipilih, daftar SOP massal di bawah akan dibuat otomatis mengikuti jumlah file.</div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                                <div>
                                    <h6 class="fw-bold mb-1">Daftar SOP Massal</h6>
                                    <div class="text-muted small">Isi minimal 1 SOP dan maksimal 50 SOP untuk sekali simpan.</div>
                                </div>
                                <button type="button" class="btn btn-outline-success fw-bold" id="btnAddBulkRow">
                                    <i class="bi bi-plus-circle me-2"></i>Tambah Baris
                                </button>
                            </div>

                            <div id="bulkEntriesWrapper" class="d-grid gap-3"></div>

                            <hr class="my-4 text-secondary opacity-25">

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.sop.index') }}" class="btn btn-outline-secondary px-4 border-0">Kembali</a>
                                <button type="submit" class="btn text-white btn-bulk-save shadow-sm" id="btnSubmitBulkSop">
                                    <i class="bi bi-cloud-upload me-2"></i>Simpan Semua SOP
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<template id="bulkEntryTemplate">
    <div class="bulk-entry-card" data-bulk-entry>
        <div class="bulk-entry-header">
            <div class="d-flex align-items-center gap-3">
                <span class="bulk-entry-badge" data-entry-number></span>
                <div>
                    <div class="fw-bold text-dark">SOP Massal</div>
                    <div class="text-muted small">Lengkapi identitas dan file PDF untuk satu SOP</div>
                </div>
            </div>
            <button type="button" class="btn btn-outline-danger btn-sm fw-bold" data-remove-entry>
                <i class="bi bi-trash3 me-1"></i>Hapus
            </button>
        </div>

        <div class="row g-3">
            <div class="col-lg-6">
                <label class="form-label">Nama SOP</label>
                <input type="text" class="form-control" data-field="nama_sop" placeholder="Contoh: SOP Pengelolaan Arsip" required>
            </div>
            <div class="col-lg-3">
                <label class="form-label">Nomor SOP</label>
                <input type="text" class="form-control" data-field="nomor_sop" placeholder="B/123/BPS/2026" required>
            </div>
            <div class="col-lg-3">
                <label class="form-label">Tahun</label>
                <input type="number" class="form-control" data-field="tahun" min="2000" max="2999" required>
            </div>
            <div class="col-lg-6">
                <label class="form-label">Subjek dan Tim Kerja</label>
                <select class="form-select" data-field="id_subjek" required>
                    <option value="">Pilih subjek dan tim kerja</option>
                    @foreach($subjekFlatOptions as $option)
                        <option value="{{ $option['id_subjek'] }}">{{ $option['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-6">
                <label class="form-label">File SOP (PDF)</label>
                <input type="file" class="form-control" data-field="link_sop" accept=".pdf" required>
                <div class="form-text" data-file-label>Belum ada file dipilih.</div>
            </div>
            <div class="col-12">
                <label class="form-label">File Revisi Terkait (PDF, boleh banyak)</label>
                <input type="file" class="form-control" data-multiple-field="revision_files" data-revision-input accept=".pdf" multiple>
                <div class="form-text" data-revision-label>Belum ada file revisi dipilih.</div>
            </div>
        </div>
    </div>
</template>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        const bulkTemplate = document.getElementById('bulkEntryTemplate');
        const bulkWrapper = document.getElementById('bulkEntriesWrapper');
        const addBulkButton = document.getElementById('btnAddBulkRow');
        const bulkFilePicker = document.getElementById('bulkFilePicker');
        const bulkInitialEntries = @json($bulkEntries);
        const bulkUploadLimit = 50;

        function entryFieldName(index, field) {
            return `entries[${index}][${field}]`;
        }

        function updateBulkFileLabel(entryEl, fileName = '') {
            const label = entryEl.querySelector('[data-file-label]');
            if (label) {
                label.textContent = fileName || 'Belum ada file dipilih.';
            }
        }

        function updateRevisionFileLabel(entryEl, files = []) {
            const label = entryEl.querySelector('[data-revision-label]');
            if (!label) {
                return;
            }

            if (!files.length) {
                label.textContent = 'Belum ada file revisi dipilih.';
                return;
            }

            const names = files.map((file) => file.name).join(', ');
            label.textContent = `${files.length} file revisi dipilih: ${names}`;
        }

        function assignFileToInput(inputEl, file) {
            if (!inputEl || !file) {
                return;
            }

            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            inputEl.files = dataTransfer.files;
        }

        function assignFilesToInput(inputEl, files) {
            if (!inputEl || !files?.length) {
                return;
            }

            const dataTransfer = new DataTransfer();
            files.forEach((file) => dataTransfer.items.add(file));
            inputEl.files = dataTransfer.files;
        }

        function inferSopName(fileName = '') {
            return fileName.replace(/\.pdf$/i, '').replace(/[_-]+/g, ' ').trim();
        }

        function syncBulkEntryIndexes() {
            [...bulkWrapper.querySelectorAll('[data-bulk-entry]')].forEach((entryEl, index) => {
                entryEl.querySelector('[data-entry-number]').textContent = index + 1;

                entryEl.querySelectorAll('[data-field]').forEach((fieldEl) => {
                    const field = fieldEl.getAttribute('data-field');
                    fieldEl.setAttribute('name', entryFieldName(index, field));
                });

                entryEl.querySelectorAll('[data-multiple-field]').forEach((fieldEl) => {
                    const field = fieldEl.getAttribute('data-multiple-field');
                    fieldEl.setAttribute('name', `${entryFieldName(index, field)}[]`);
                });
            });

            bulkWrapper.querySelectorAll('[data-remove-entry]').forEach((button) => {
                button.disabled = bulkWrapper.querySelectorAll('[data-bulk-entry]').length <= 1;
            });
        }

        function appendBulkEntry(data = {}) {
            const fragment = bulkTemplate.content.cloneNode(true);
            const entryEl = fragment.querySelector('[data-bulk-entry]');
            const fileInput = entryEl.querySelector('[data-field="link_sop"]');
            const revisionInput = entryEl.querySelector('[data-revision-input]');

            entryEl.querySelector('[data-field="nama_sop"]').value = data.nama_sop || '';
            entryEl.querySelector('[data-field="nomor_sop"]').value = data.nomor_sop || '';
            entryEl.querySelector('[data-field="tahun"]').value = data.tahun || '{{ date('Y') }}';
            entryEl.querySelector('[data-field="id_subjek"]').value = data.id_subjek || '';
            updateBulkFileLabel(entryEl, data.file_name || '');
            updateRevisionFileLabel(entryEl, data.revision_files || []);

            fileInput.addEventListener('change', function(event) {
                updateBulkFileLabel(entryEl, event.target.files?.[0]?.name || '');
            });

            revisionInput.addEventListener('change', function(event) {
                updateRevisionFileLabel(entryEl, [...(event.target.files || [])]);
            });

            bulkWrapper.appendChild(fragment);

            if (data.file) {
                assignFileToInput(fileInput, data.file);
                updateBulkFileLabel(entryEl, data.file.name);
            }

            if (data.revision_files?.length) {
                assignFilesToInput(revisionInput, data.revision_files);
                updateRevisionFileLabel(entryEl, data.revision_files);
            }

            syncBulkEntryIndexes();
        }

        addBulkButton?.addEventListener('click', function() {
            const rowCount = bulkWrapper.querySelectorAll('[data-bulk-entry]').length;

            if (rowCount >= bulkUploadLimit) {
                Swal.fire({
                    icon: 'info',
                    title: 'Batas Tercapai',
                    text: 'Maksimal 50 SOP dapat ditambahkan dalam sekali upload.',
                    confirmButtonColor: '#0f766e'
                });
                return;
            }

            appendBulkEntry({ tahun: '{{ date('Y') }}' });
        });

        bulkFilePicker?.addEventListener('change', function(event) {
            const files = [...(event.target.files || [])];

            if (!files.length) {
                return;
            }

            if (files.length > bulkUploadLimit) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Terlalu Banyak File',
                    text: 'Sekali upload maksimal 50 file PDF.',
                    confirmButtonColor: '#0f766e'
                });
                event.target.value = '';
                return;
            }

            bulkWrapper.innerHTML = '';
            files.forEach((file) => {
                appendBulkEntry({
                    nama_sop: inferSopName(file.name),
                    tahun: '{{ date('Y') }}',
                    file,
                });
            });
        });

        bulkWrapper?.addEventListener('click', function(event) {
            const removeButton = event.target.closest('[data-remove-entry]');
            if (!removeButton) {
                return;
            }

            const allEntries = bulkWrapper.querySelectorAll('[data-bulk-entry]');
            if (allEntries.length <= 1) {
                Swal.fire({
                    icon: 'info',
                    title: 'Minimal Satu Baris',
                    text: 'Input massal membutuhkan minimal 1 baris data SOP.',
                    confirmButtonColor: '#0f766e'
                });
                return;
            }

            removeButton.closest('[data-bulk-entry]').remove();
            syncBulkEntryIndexes();
        });

        (bulkInitialEntries.length ? bulkInitialEntries.slice(0, bulkUploadLimit) : [{ tahun: '{{ date('Y') }}' }]).forEach((entry) => {
            appendBulkEntry(entry);
        });

        $('#formBulkSop').on('submit', function(e) {
            const rowCount = bulkWrapper.querySelectorAll('[data-bulk-entry]').length;

            if (rowCount < 1) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Baris Belum Cukup',
                    text: 'Minimal tambahkan 1 SOP untuk input massal.',
                    confirmButtonColor: '#0f766e'
                });
                return;
            }

            if (rowCount > bulkUploadLimit) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Melebihi Batas',
                    text: 'Maksimal 50 SOP dapat diproses dalam sekali upload.',
                    confirmButtonColor: '#0f766e'
                });
                return;
            }

            $('#btnSubmitBulkSop').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...');
        });
    });
</script>
@endsection
