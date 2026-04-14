@extends('layouts.sidebarmenu')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

<style>
    .main-content-area {
        background-color: #f8fafc;
        min-height: 100vh;
        padding: 2rem;
    }

    .card-premium {
        border: none;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        overflow: hidden;
    }

    .card-header-bps {
        background: #0d47a1;
        color: white;
        padding: 1.5rem;
        border: none;
    }

    .form-label {
        font-weight: 600;
        color: #475569;
        font-size: 0.9rem;
    }

    .btn-save {
        background: #0d47a1;
        border: none;
        border-radius: 8px;
        padding: 12px 30px;
        font-weight: 600;
        transition: 0.3s;
    }

    .btn-save:hover {
        background: #0a3d8d;
        transform: translateY(-1px);
    }

    .form-control {
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        padding: 12px 15px;
        transition: all 0.2s;
    }

    .form-control:focus {
        border-color: #0d47a1;
        box-shadow: 0 0 0 0.25rem rgba(13, 71, 161, 0.1);
    }

    .search-container {
        position: relative;
    }

    .search-results-wrapper {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        margin-top: 8px;
        z-index: 1050;
        max-height: 280px;
        overflow-y: auto;
        box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        display: none;
        animation: fadeInDown 0.2s ease-out;
    }

    @keyframes fadeInDown {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .result-item {
        padding: 12px 18px;
        cursor: pointer;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        flex-direction: column;
    }

    .result-item:hover {
        background-color: #f0f7ff;
    }

    .result-item .item-title {
        font-weight: 600;
        color: #1e293b;
        font-size: 0.95rem;
    }

    .result-item .item-sub {
        font-size: 0.75rem;
        color: #94a3b8;
        margin-top: 2px;
    }

    .selected-badge {
        display: none;
        background: #ecfdf5;
        color: #059669;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 0.85rem;
        margin-top: 8px;
        border: 1px solid #a7f3d0;
    }

    .search-icon-inside {
        position: absolute;
        right: 15px;
        top: 42px;
        color: #94a3b8;
    }
</style>

<div class="main-content-area">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card card-premium">
                    <div class="card-header-bps d-flex align-items-center">
                        <div class="bg-white rounded-circle p-2 me-3 d-inline-flex">
                            <i class="bi bi-plus-lg text-primary"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold">Tambah SOP Baru</h5>
                            <small class="opacity-75">Sistem Pencarian Otomatis Subjek & Unit Kerja BPS Banten</small>
                        </div>
                    </div>

                    <div class="card-body p-4 p-lg-5">
                        <form action="{{ route('admin.sop.store') }}" method="POST" enctype="multipart/form-data" id="formSop">
                            @csrf

                            <div class="row g-4">
                                <div class="col-12">
                                    <label class="form-label">Nama Lengkap SOP</label>
                                    <input type="text" name="nama_sop" value="{{ old('nama_sop') }}"
                                           class="form-control form-control-lg"
                                           placeholder="Contoh: SOP Pelayanan Statistik" required>
                                </div>

                                <div class="col-md-6 search-container">
                                    <label class="form-label text-primary">Subjek</label>
                                    <i class="bi bi-search search-icon-inside"></i>
                                    <input type="text" id="subjekInput" class="form-control" placeholder="Klik atau ketik nama subjek..." autocomplete="off">
                                    <input type="hidden" name="id_subjek" id="selectedSubjekId" required>

                                    <div id="subjekBadge" class="selected-badge">
                                        <i class="bi bi-check-circle-fill me-1"></i> Terpilih: <span id="subjekLabel" class="fw-bold"></span>
                                    </div>
                                    <div id="subjekResults" class="search-results-wrapper"></div>
                                </div>

                                <div class="col-md-6 search-container">
                                    <label class="form-label text-primary">Unit Kerja (Opsional)</label>
                                    <i class="bi bi-search search-icon-inside"></i>
                                    <input type="text" id="unitInput" class="form-control" placeholder="Klik atau ketik nama unit..." autocomplete="off">
                                    <input type="hidden" name="id_unit" id="selectedUnitId">

                                    <div id="unitBadge" class="selected-badge">
                                        <i class="bi bi-check-circle-fill me-1"></i> Terpilih: <span id="unitLabel" class="fw-bold"></span>
                                    </div>
                                    <div id="unitResults" class="search-results-wrapper"></div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Nomor SOP</label>
                                    <input type="text" name="nomor_sop" value="{{ old('nomor_sop') }}"
                                           class="form-control" placeholder="B/123/BPS/2026" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Tahun Terbit</label>
                                    <input type="number" name="tahun" class="form-control"
                                           value="{{ old('tahun', date('Y')) }}" required>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Dokumen SOP (PDF)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bi bi-file-earmark-pdf text-danger"></i></span>
                                        <input type="file" name="link_sop" class="form-control" accept=".pdf" id="fileSop" required>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4 text-secondary opacity-25">

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.sop.index') }}" class="btn btn-outline-secondary px-4 border-0">Batal</a>
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
        const subjekList = @json($subjek);
        const unitList = @json($units);

        function setupLiveSearch(inputId, resultsId, dataList, valueKey, textKey, hiddenId, badgeId, labelId) {
            const $input = $(inputId);
            const $results = $(resultsId);

            // Fungsi untuk merender daftar
            const renderResults = (query = '') => {
                $results.empty();

                let filtered = dataList.filter((item, index, self) =>
                    item[textKey].toLowerCase().includes(query.toLowerCase()) &&
                    index === self.findIndex((t) => t[textKey] === item[textKey])
                );

                if (filtered.length > 0) {
                    filtered.forEach(item => {
                        $results.append(`
                            <div class="result-item" data-id="${item[valueKey]}" data-name="${item[textKey]}">
                                <span class="item-title">${item[textKey]}</span>
                                <span class="item-sub">Database ID: ${item[valueKey]}</span>
                            </div>
                        `);
                    });
                    $results.show();
                } else {
                    $results.append('<div class="p-3 text-muted small text-center">Data tidak ditemukan</div>').show();
                }
            };

            // Munculkan semua data saat kolom diklik (fokus)
            $input.on('focus', function() {
                renderResults($(this).val());
            });

            // Filter data saat mengetik
            $input.on('input', function() {
                renderResults($(this).val());
            });

            // Aksi saat item dipilih
            $results.on('click', '.result-item', function() {
                const id = $(this).data('id');
                const name = $(this).data('name');

                if(id) {
                    $(hiddenId).val(id);
                    $input.val(name);
                    $(labelId).text(name);
                    $(badgeId).fadeIn();
                    $results.hide();
                }
            });

            // Tutup jika klik di luar area input
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.search-container').length) {
                    $results.hide();
                }
            });
        }

        // Jalankan Setup
        setupLiveSearch('#subjekInput', '#subjekResults', subjekList, 'id_subjek', 'nama_subjek', '#selectedSubjekId', '#subjekBadge', '#subjekLabel');
        setupLiveSearch('#unitInput', '#unitResults', unitList, 'id_unit', 'nama_unit', '#selectedUnitId', '#unitBadge', '#unitLabel');

        // Validasi Form
        $('#formSop').on('submit', function(e) {
            if (!$('#selectedSubjekId').val()) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Pilihan Diperlukan',
                    text: 'Mohon klik dan pilih salah satu Subjek dari daftar yang tersedia.',
                    confirmButtonColor: '#0d47a1'
                });
            }
        });
    });
</script>
@endsection
