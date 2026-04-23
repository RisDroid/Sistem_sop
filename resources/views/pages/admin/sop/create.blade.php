@extends('layouts.sidebarmenu')

@section('content')
@php($prefix = strtolower(Auth::user()->role) === 'admin' ? 'admin' : 'operator')
@php($isAdmin = $prefix === 'admin')
@php($subjekOptions = $subjek->groupBy(fn ($item) => trim((string) $item->nama_subjek))->map(function ($items, $namaSubjek) {
    return [
        'label' => $namaSubjek,
        'units' => $items->map(fn ($item) => [
            'id_subjek' => $item->id_subjek,
            'id_timkerja' => $item->id_timkerja,
            'nama_timkerja' => $item->timkerja->nama_timkerja ?? 'Internal',
        ])->sortBy('nama_timkerja')->values()->all(),
    ];
})->sortKeys(SORT_NATURAL | SORT_FLAG_CASE))
@php($selectedSubjek = $subjek->firstWhere('id_subjek', (int) old('id_subjek')))
@php($selectedSubjekLabel = $selectedSubjek?->nama_subjek)
@php($selectedTimkerjaId = $selectedSubjek?->id_timkerja)

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

<style>
    .main-content-area { background-color: #f8fafc; min-height: 100vh; padding: 2rem; }
    .card-premium { border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05); overflow: hidden; }
    .card-header-bps { background: #0d47a1; color: white; padding: 1.5rem; border: none; }
    .form-label { font-weight: 600; color: #475569; font-size: 0.9rem; }
    .btn-save { background: #0d47a1; border: none; border-radius: 8px; padding: 12px 30px; font-weight: 600; transition: 0.3s; }
    .btn-save:hover { background: #0a3d8d; transform: translateY(-1px); }
    .form-control, .form-select { border-radius: 8px; border: 1px solid #cbd5e1; padding: 12px 15px; transition: all 0.2s; }
    .form-control:focus, .form-select:focus { border-color: #0d47a1; box-shadow: 0 0 0 0.25rem rgba(13, 71, 161, 0.1); }
</style>

<div class="main-content-area">
    <div class="container-fluid">
        <div class="row g-4">
            <div class="col-12">
                <div class="card card-premium">
                    <div class="card-header-bps d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <div class="d-flex align-items-center">
                            <div class="bg-white rounded-circle p-2 me-3 d-inline-flex">
                                <i class="bi bi-plus-lg text-primary"></i>
                            </div>
                            <div>
                                <h5 class="mb-0 fw-bold">Tambah SOP Baru</h5>
                                <small class="opacity-75">Input satu SOP dalam satu form</small>
                            </div>
                        </div>
                        @if($isAdmin)
                            <a href="{{ route('admin.sop.bulkCreate') }}" class="btn btn-light fw-bold">
                                <i class="bi bi-collection me-2"></i>Buka Form SOP Massal
                            </a>
                        @endif
                    </div>

                    <div class="card-body p-4 p-lg-5">
                        @if($errors->any())
                            <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
                                {{ $errors->first() }}
                            </div>
                        @endif

                        <form action="{{ route($prefix . '.sop.store') }}" method="POST" enctype="multipart/form-data" id="formSop">
                            @csrf

                            <div class="row g-4">
                                <div class="col-12">
                                    <label class="form-label">Nama Lengkap SOP</label>
                                    <input type="text" name="nama_sop" value="{{ old('nama_sop') }}" class="form-control form-control-lg" placeholder="Contoh: SOP Pelayanan Statistik" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label text-primary">Subjek</label>
                                    <select id="selectedSubjekName" class="form-select" required>
                                        <option value="">Pilih subjek</option>
                                        @foreach($subjekOptions as $key => $item)
                                            <option value="{{ $key }}" {{ $selectedSubjekLabel === $key ? 'selected' : '' }}>
                                                {{ $item['label'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="id_subjek" id="selectedSubjekId" value="{{ old('id_subjek') }}">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label text-primary">Tim Kerja</label>
                                    <select id="selectedTimkerjaId" class="form-select" required>
                                        <option value="">Pilih tim kerja</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Nomor SOP</label>
                                    <input type="text" name="nomor_sop" value="{{ old('nomor_sop') }}" class="form-control" placeholder="B/123/BPS/2026" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Tahun Terbit</label>
                                    <input type="number" name="tahun" class="form-control" value="{{ old('tahun', date('Y')) }}" required>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Dokumen SOP (PDF)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bi bi-file-earmark-pdf text-danger"></i></span>
                                        <input type="file" name="link_sop" class="form-control" accept=".pdf" required>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4 text-secondary opacity-25">

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route($prefix . '.sop.index') }}" class="btn btn-outline-secondary px-4 border-0">Batal</a>
                                <button type="submit" class="btn btn-primary btn-save shadow-sm">
                                    <i class="bi bi-cloud-arrow-up me-2"></i>Simpan Data SOP
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        const subjekMap = @json($subjekOptions);
        const subjekNameSelect = $('#selectedSubjekName');
        const timkerjaSelect = $('#selectedTimkerjaId');
        const subjekIdInput = $('#selectedSubjekId');
        const oldTimkerjaId = @json($selectedTimkerjaId);

        function renderTimkerjaOptions(selectedTimkerjaId = '') {
            const selectedSubjekName = subjekNameSelect.val();
            const options = subjekMap[selectedSubjekName]?.units ?? [];

            timkerjaSelect.empty().append('<option value="">Pilih tim kerja</option>');

            options.forEach((item) => {
                const selected = String(selectedTimkerjaId) === String(item.id_timkerja) ? 'selected' : '';
                timkerjaSelect.append(`<option value="${item.id_timkerja}" data-id-subjek="${item.id_subjek}" ${selected}>${item.nama_timkerja}</option>`);
            });

            syncSubjekId();
        }

        function syncSubjekId() {
            const selectedOption = timkerjaSelect.find('option:selected');
            subjekIdInput.val(selectedOption.data('id-subjek') || '');
        }

        subjekNameSelect.on('change', function() {
            renderTimkerjaOptions('');
        });

        timkerjaSelect.on('change', syncSubjekId);

        renderTimkerjaOptions(oldTimkerjaId);

        $('#formSop').on('submit', function(e) {
            if (!subjekNameSelect.val() || !timkerjaSelect.val() || !subjekIdInput.val()) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Pilihan Diperlukan',
                    text: 'Mohon pilih subjek dan tim kerja yang sesuai terlebih dahulu.',
                    confirmButtonColor: '#0d47a1'
                });
            }
        });
    });
</script>
@endsection
