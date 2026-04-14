@extends('layouts.sidebarmenu')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<style>
    .nama-sop-link {
        color: #0d47a1;
        text-decoration: none;
        transition: 0.2s;
    }
    .nama-sop-link:hover {
        color: #1e88e5;
        text-decoration: underline;
    }

    /* Card & Table Styling */
    .main-card {
        border: none;
        border-radius: 24px;
        background: #ffffff;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
        overflow: hidden;
    }
    .table-container { padding: 0 1.5rem 1.5rem 1.5rem; }
    .custom-table { border-collapse: separate; border-spacing: 0 8px; }
    .custom-table thead th {
        background-color: transparent; border: none; color: #94a3b8;
        font-weight: 700; text-transform: uppercase; font-size: 0.75rem;
        letter-spacing: 1px; padding: 1.5rem 1rem;
    }
    .custom-table tbody tr {
        background-color: #ffffff; transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    }
    .custom-table tbody tr:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 15px rgba(0,0,0,0.06);
        background-color: #f8fafc !important;
    }
    .custom-table tbody td { padding: 1.25rem 1rem; border: none; vertical-align: middle; }
    .custom-table tbody tr td:first-child { border-radius: 12px 0 0 12px; }
    .custom-table tbody tr td:last-child { border-radius: 0 12px 12px 0; }

    /* Button Actions */
    .btn-action {
        width: 38px; height: 38px; display: inline-flex; align-items: center;
        justify-content: center; border-radius: 10px; transition: 0.2s;
        border: 1px solid #e2e8f0; background: #fff; text-decoration: none;
        cursor: pointer;
    }
    .btn-action:hover { background: #0d47a1; color: #fff !important; border-color: #0d47a1; }
    .btn-action.btn-revisi:hover { background: #f59e0b; border-color: #f59e0b; }

    /* Search & Badges */
    .search-box {
        background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 12px;
        padding: 10px 20px; transition: 0.3s;
    }
    .badge-subjek {
        background: #e0f2fe; color: #0369a1; font-weight: 700; font-size: 0.7rem;
        padding: 5px 12px; border-radius: 8px; text-transform: uppercase;
        display: inline-block;
    }

    /* MODER PAGINATION STYLING */
    .pagination-wrapper {
        margin-top: 2rem;
        padding: 1rem;
        border-top: 1px solid #f1f5f9;
    }
    .pagination {
        gap: 8px;
        margin-bottom: 0;
    }
    .page-item .page-link {
        border: none;
        border-radius: 10px !important;
        padding: 10px 16px;
        color: #64748b;
        font-weight: 600;
        transition: 0.3s;
        background: #f8fafc;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    }
    .page-item.active .page-link {
        background: #0d47a1 !important;
        color: white !important;
        box-shadow: 0 4px 12px rgba(13, 71, 161, 0.25);
    }

    /* New Styling for Locked SOP Row */
    .locked-sop-row {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 8px 15px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 8px;
    }
</style>

<div class="container-fluid py-4">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h3 class="fw-bold text-dark mb-1">Repository SOP</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                    <li class="breadcrumb-item active text-primary fw-bold">Data SOP</li>
                </ol>
            </nav>
        </div>
        <div class="col-auto d-flex gap-2">
            <a href="{{ route('admin.sop.create') }}" class="btn btn-primary px-4 py-2 fw-bold shadow-sm" style="border-radius: 12px; background: #0d47a1; border: none;">
                <i class="bi bi-plus-lg me-2"></i> Tambah SOP Baru
            </a>

            <button type="button" class="btn btn-warning px-4 py-2 fw-bold shadow-sm text-white" style="border-radius: 12px; background: #f59e0b; border: none;" data-bs-toggle="modal" data-bs-target="#modalRevisiBaru">
                <i class="bi bi-arrow-repeat me-2"></i> Revisi SOP
            </button>
        </div>
    </div>

    <div class="card main-card">
        <div class="p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <form action="{{ route('admin.sop.index') }}" id="searchForm" method="GET" class="search-box d-flex align-items-center">
                <i class="bi bi-search text-muted me-2"></i>
                <input type="text" name="search" id="searchInput" value="{{ request('search') }}" class="border-0 bg-transparent shadow-none" placeholder="Cari dokumen..." style="outline: none; width: 250px;">
                @if(request('search') || request('show_history'))
                    <a href="{{ route('admin.sop.index') }}" class="text-muted ms-2"><i class="bi bi-x-circle-fill"></i></a>
                @endif
            </form>

            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary border-0 fw-bold small d-flex align-items-center" style="border-radius: 10px;" data-bs-toggle="modal" data-bs-target="#modalFilter">
                    <i class="bi bi-filter me-2"></i> Filter
                </button>
                <a href="{{ route('admin.sop.index') }}" class="btn btn-outline-danger border-0 fw-bold small" style="border-radius: 10px;">
                    <i class="bi bi-arrow-clockwise me-2"></i> Reset
                </a>
            </div>
        </div>

        <div class="table-container">
            <div class="table-responsive">
                <table class="table custom-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Subjek</th>
                            <th>Nama SOP</th>
                            <th>Nomor SOP</th>
                            <th class="text-center">Revisi</th>
                            <th class="text-center">Status</th>
                            <th>Tim Kerja</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($allSop as $index => $item)
                        <tr>
                            <td class="fw-bold text-muted">{{ $allSop->firstItem() + $index }}</td>
                            <td>
                                <span class="badge-subjek">{{ $item->subjek->nama_subjek ?? 'Tanpa Subjek' }}</span>
                                <div class="text-muted mt-1" style="font-size: 0.75rem;">Tahun: {{ date('Y', strtotime($item->tahun)) }}</div>
                            </td>
                            <td>
                                <div class="fw-bold">
                                    <a href="{{ route('admin.sop.index', ['show_history' => $item->nama_sop]) }}" class="nama-sop-link" title="Lihat Riwayat Versi">
                                        {{ $item->nama_sop }}
                                    </a>
                                </div>
                            </td>
                            <td><span class="badge bg-primary-subtle text-primary px-3 py-2" style="border-radius: 8px;">{{ $item->nomor_sop }}</span></td>
                            <td class="text-center">
                                <div class="badge bg-light text-dark border fw-bold px-3 py-2" style="border-radius: 8px;">
                                    {{ ($item->revisi_ke && $item->revisi_ke !== '-') ? 'Revisi ke - '.$item->revisi_ke : '-' }}
                                </div>
                            </td>
                            <td class="text-center">
                                @if($item->status_active == 1)
                                    <span class="badge bg-success px-3 py-2" style="border-radius: 8px;">Aktif</span>
                                @else
                                    <span class="badge bg-secondary px-3 py-2" style="border-radius: 8px;">Non-Aktif</span>
                                @endif
                            </td>
                            <td><span class="small fw-bold text-secondary">{{ $item->unit->nama_unit ?? 'Internal' }}</span></td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    @if($item->status_active == 1)
                                    <button type="button" class="btn-action text-warning btn-revisi"
                                            data-id="{{ $item->id_sop }}"
                                            data-nama="{{ $item->nama_sop }}"
                                            data-revisi="{{ $item->revisi_ke }}"
                                            title="Revisi SOP Ini">
                                        <i class="bi bi-arrow-repeat"></i>
                                    </button>
                                    @endif
                                    <a href="{{ asset('storage/' . $item->link_sop) }}" target="_blank" class="btn-action text-primary" title="Lihat PDF"><i class="bi bi-eye"></i></a>
                                    <a href="{{ route('admin.sop.edit', $item->id_sop) }}" class="btn-action text-warning" title="Edit Data"><i class="bi bi-pencil-square"></i></a>
                                    <form action="{{ route('admin.sop.destroy', $item->id_sop) }}" method="POST" class="form-delete d-inline">
                                        @csrf @method('DELETE')
                                        <button type="button" class="btn-action text-danger btn-delete" title="Hapus"><i class="bi bi-trash3"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="text-center py-5"><h6 class="text-muted">Data tidak ditemukan!</h6></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="pagination-wrapper d-flex justify-content-between align-items-center">
                <div class="text-muted small fw-bold">Menampilkan {{ $allSop->count() }} data pada halaman ini</div>
                <div>{{ $allSop->appends(request()->input())->links('pagination::bootstrap-5') }}</div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL REVISI CEPAT (DARI TOMBOL TABEL) --}}
<div class="modal fade" id="modalRevisi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <div class="modal-header border-0 pt-4 px-4">
                <h5 class="fw-bold mb-0">Input Revisi SOP Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.sop.revisi') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body px-4 py-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">SOP yang Akan Direvisi</label>
                        <input type="hidden" name="id_sop_induk" id="revisi_id_sop">
                        <input type="text" id="revisi_nama_sop" class="form-control bg-light fw-bold" readonly style="border-radius: 10px;">
                        <div id="revisi_info_ke" class="text-muted mt-1 small"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Upload File PDF (Versi Baru)</label>
                        <input type="file" name="link_sop" class="form-control" accept=".pdf" required style="border-radius: 10px;">
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-bold">Keterangan Revisi</label>
                        <textarea name="keterangan_revisi" class="form-control" rows="3" placeholder="Jelaskan ringkasan perubahan..." required style="border-radius: 10px;"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pb-4 px-4 gap-2">
                    <button type="button" class="btn btn-light fw-bold py-2 flex-grow-1" style="border-radius: 12px;" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning fw-bold py-2 flex-grow-1 text-white" style="background: #f59e0b; border-radius: 12px;">Simpan Revisi</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL REVISI SOP BARU (HEADER) --}}
<div class="modal fade" id="modalRevisiBaru" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pt-4 px-4">
                <h5 class="fw-bold mb-0">Form Revisi SOP</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.sop.revisi') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body px-4 py-3">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Nama SOP Baru</label>
                            <input type="text" name="nama_sop" class="form-control" placeholder="Masukkan nama SOP baru" required style="border-radius: 10px;">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold">Nomor SOP</label>
                            <input type="text" name="nomor_sop" class="form-control" placeholder="Nomor/BPS/2026" required style="border-radius: 10px;">
                        </div>

                        <div class="col-12 mb-3">
                            <label class="form-label small fw-bold">SOP Terkait (Bisa pilih lebih dari 1)</label>

                            <div id="selected-sop-list" class="mb-2"></div>

                            <div class="input-group">
                                <select id="sop-selector" class="form-select" style="border-radius: 10px 0 0 10px;">
                                    <option value="">-- Pilih SOP Terkait --</option>
                                    @foreach($allSop as $sop)
                                        <option value="{{ $sop->id_sop }}">{{ $sop->nama_sop }}</option>
                                    @endforeach
                                </select>
                                <button class="btn btn-outline-primary" id="add-sop-btn" type="button" style="border-radius: 0 10px 10px 0;">
                                    <i class="bi bi-plus-circle"></i>
                                </button>
                            </div>
                            <small class="text-muted mt-1 d-block">*Pilih SOP dari dropdown lalu klik tombol (+) untuk menambahkan.</small>
                        </div>

                        <div class="col-12 mb-3">
                            <label class="form-label small fw-bold">Upload File PDF</label>
                            <input type="file" name="link_sop" class="form-control" accept=".pdf" required style="border-radius: 10px;">
                        </div>
                        <div class="col-12 mb-0">
                            <label class="form-label small fw-bold">Keterangan</label>
                            <textarea name="keterangan" class="form-control" rows="3" placeholder="Tambahkan catatan revisi..." style="border-radius: 10px;"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pb-4 px-4">
                    <button type="button" class="btn btn-light fw-bold px-4" data-bs-dismiss="modal" style="border-radius: 12px;">Batal</button>
                    <button type="submit" class="btn btn-warning fw-bold px-4 text-white" style="border-radius: 12px; background: #f59e0b;">Proses Revisi</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL FILTER --}}
<div class="modal fade" id="modalFilter" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <div class="modal-header border-0 pt-4 px-4">
                <h5 class="fw-bold mb-0">Filter Data SOP</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.sop.index') }}" method="GET">
                <div class="modal-body px-4 py-4">
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-dark">Berdasarkan Subjek</label>
                        <select name="id_subjek" class="form-select">
                            <option value="">Semua Subjek</option>
                            @foreach($subjek as $s)
                                <option value="{{ $s->id_subjek }}" {{ request('id_subjek') == $s->id_subjek ? 'selected' : '' }}>{{ $s->nama_subjek }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Berdasarkan Tim Kerja</label>
                        <select name="id_unit" class="form-select">
                            <option value="">Semua Tim Kerja</option>
                            @foreach($units as $u)
                                <option value="{{ $u->id_unit }}" {{ request('id_unit') == $u->id_unit ? 'selected' : '' }}>{{ $u->nama_unit }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 pb-4 px-4 gap-2">
                    <button type="button" class="btn btn-light fw-bold py-2 flex-grow-1" style="border-radius: 12px;" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-bold py-2 flex-grow-1" style="background: #0d47a1; border-radius: 12px;">Terapkan Filter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        // Search Auto Submit
        $('#searchInput').on('input', function() {
            if ($(this).val() === '') { $('#searchForm').submit(); }
        });

        // Trigger Modal Revisi Cepat
        $('.btn-revisi').on('click', function() {
            let id = $(this).data('id');
            let nama = $(this).data('nama');
            let rev = $(this).data('revisi');
            $('#revisi_id_sop').val(id);
            $('#revisi_nama_sop').val(nama);
            $('#revisi_info_ke').text('Posisi saat ini: ' + (rev === '-' ? 'SOP Baru' : 'Revisi ke-' + rev));
            $('#modalRevisi').modal('show');
        });

        // LOGIKA DINAMIS SOP TERKAIT
        $('#add-sop-btn').on('click', function() {
            let selector = $('#sop-selector');
            let id = selector.val();
            let name = selector.find('option:selected').text();

            if (id === "") {
                Swal.fire({ icon: 'warning', title: 'Pilih SOP', text: 'Silakan pilih SOP dari daftar terlebih dahulu.' });
                return;
            }

            // Cek apakah sudah ada di list
            let existing = false;
            $("input[name='sop_terkait[]']").each(function() {
                if ($(this).val() === id) existing = true;
            });

            if (existing) {
                Swal.fire({ icon: 'error', title: 'Sudah Ada', text: 'SOP ini sudah ditambahkan ke dalam daftar terkait.' });
                return;
            }

            // Template Row Baru (Bukan dropdown, tapi teks locked)
            let newRow = `
                <div class="locked-sop-row animate__animated animate__fadeIn">
                    <input type="hidden" name="sop_terkait[]" value="${id}">
                    <div class="d-flex align-items-center text-dark fw-bold">
                        <i class="bi bi-file-earmark-check text-primary me-2"></i>
                        <span>${name}</span>
                    </div>
                    <button type="button" class="btn btn-link text-danger p-0 remove-sop-item" title="Hapus">
                        <i class="bi bi-x-circle-fill"></i>
                    </button>
                </div>
            `;

            $('#selected-sop-list').append(newRow);
            selector.val('').trigger('change'); // Reset selector
        });

        // Hapus Item SOP Terkait
        $(document).on('click', '.remove-sop-item', function() {
            $(this).closest('.locked-sop-row').addClass('animate__fadeOut').on('animationend', function() {
                $(this).remove();
            });
        });

        // Delete Confirmation
        $('.btn-delete').on('click', function(e) {
            let form = $(this).closest('.form-delete');
            Swal.fire({
                title: 'Hapus Dokumen?',
                text: "Data SOP akan dihapus permanen dari server BPS.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) { form.submit(); }
            });
        });

        @if(session('success'))
            Swal.fire({ icon: 'success', title: 'Berhasil', text: '{{ session("success") }}', showConfirmButton: false, timer: 1500 });
        @endif
    });
</script>
@endsection
