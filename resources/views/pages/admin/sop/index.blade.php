@extends('layouts.sidebarmenu')

@section('content')
@php($prefix = strtolower(Auth::user()->role ?? 'admin'))
@php($canManage = in_array($prefix, ['admin', 'operator'], true))
@php($canBulkDelete = $prefix === 'admin')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<style>
    .nama-sop-link {
        color: #0f172a;
        text-decoration: none;
        transition: 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        font-weight: 700;
        padding: 10px 14px;
        border-radius: 14px;
        background: linear-gradient(135deg, #eff6ff 0%, #ffffff 100%);
        border: 1px solid #dbeafe;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.8);
    }
    .nama-sop-link:hover {
        color: #0d47a1;
        transform: translateY(-1px);
        border-color: #93c5fd;
        box-shadow: 0 8px 18px rgba(37, 99, 235, 0.12);
    }
    .nama-sop-link .click-indicator {
        width: 32px;
        height: 32px;
        border-radius: 10px;
        background: #dbeafe;
        color: #1d4ed8;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .nama-sop-link[type="button"] {
        width: 100%;
        text-align: left;
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
    .sop-meta-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 10px;
    }
    .sop-meta-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 999px;
        font-size: 0.95rem;
        border: 1px solid transparent;
    }
    .sop-meta-icon.monitoring {
        background: #eefbf3;
        color: #15803d;
        border-color: #bbf7d0;
    }
    .sop-meta-icon.evaluasi {
        background: #fff7ed;
        color: #c2410c;
        border-color: #fed7aa;
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

    /* FIX BACKDROP FREEZE */
    body.modal-open {
        overflow: hidden !important;
        padding-right: 0 !important;
    }

    /* Checkbox Styling */
    .form-check-input-custom {
        width: 1.2rem;
        height: 1.2rem;
        cursor: pointer;
    }
    .history-panel {
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        padding: 18px;
        background: linear-gradient(135deg, #f8fbff 0%, #ffffff 100%);
    }
    .history-stat {
        border: 1px solid #dbeafe;
        border-radius: 16px;
        padding: 16px;
        background: #ffffff;
    }
    .filter-dropdown {
        position: relative;
    }
    .filter-dropdown-toggle {
        width: 100%;
        border-radius: 12px;
        border: 1px solid #cbd5e1;
        padding: 10px 14px;
        background: #fff;
        min-height: 46px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        color: #334155;
    }
    .filter-dropdown-menu {
        display: none;
        position: absolute;
        top: calc(100% + 8px);
        left: 0;
        right: 0;
        background: #fff;
        border: 1px solid #cbd5e1;
        border-radius: 14px;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.14);
        padding: 10px;
        z-index: 1065;
    }
    .filter-dropdown.open .filter-dropdown-menu {
        display: block;
    }
    .filter-dropdown-search {
        width: 100%;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        padding: 9px 12px;
        margin-bottom: 10px;
    }
    .filter-dropdown-list {
        max-height: 220px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .filter-dropdown-item {
        border: 0;
        background: transparent;
        text-align: left;
        border-radius: 10px;
        padding: 10px 12px;
        color: #334155;
    }
    .filter-dropdown-item:hover,
    .filter-dropdown-item.active {
        background: #5b8def;
        color: #fff;
    }
    .filter-dropdown-empty {
        padding: 10px 12px;
        color: #64748b;
        font-size: 0.9rem;
    }
</style>

<div class="container-fluid py-4">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h3 class="fw-bold text-dark mb-1">Repository SOP</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route($prefix . '.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                    <li class="breadcrumb-item active text-primary fw-bold">Data SOP</li>
                </ol>
            </nav>
        </div>
        <div class="col-auto d-flex gap-2">
            @if($canManage)
                <a href="{{ route($prefix . '.sop.create') }}" class="btn btn-primary px-4 py-2 fw-bold shadow-sm" style="border-radius: 12px; background: #0d47a1; border: none;">
                    <i class="bi bi-plus-lg me-2"></i> Tambah SOP Baru
                </a>
            @endif

            @if($canBulkDelete)
                <a href="{{ route('admin.sop.bulkCreate') }}" class="btn btn-success px-4 py-2 fw-bold shadow-sm" style="border-radius: 12px; background: #0f766e; border: none;">
                    <i class="bi bi-collection me-2"></i> Tambah SOP Banyak
                </a>
            @endif

            @if($canManage)
                <a href="{{ route($prefix . '.sop.aksescepat') }}" class="btn btn-warning px-4 py-2 fw-bold shadow-sm text-white" style="border-radius: 12px; background: #f59e0b; border: none;">
                    <i class="bi bi-diagram-3 me-2"></i> Revisi Gabungan
                </a>
            @endif
        </div>
    </div>

    <div class="card main-card">
        <div class="p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="search-box d-flex align-items-center">
                    <i class="bi bi-search text-muted me-2"></i>
                    <input type="text" id="searchTable" class="border-0 bg-transparent shadow-none" placeholder="Cari subjek, nama, nomor, atau tim..." style="outline: none; width: 300px;">
                </div>

                {{-- TOMBOL HAPUS MASAL (Muncul Otomatis via JS) --}}
                @if($canBulkDelete)
                    <button type="button" id="btnBulkDelete" class="btn btn-danger px-3 py-2 fw-bold shadow-sm animate__animated animate__fadeIn" style="display: none; border-radius: 12px;">
                        <i class="bi bi-trash3 me-2"></i> Hapus Terpilih (<span id="checkCount">0</span>)
                    </button>
                @endif
            </div>

            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary border-0 fw-bold small d-flex align-items-center" style="border-radius: 10px;" data-bs-toggle="modal" data-bs-target="#modalFilter">
                    <i class="bi bi-filter me-2"></i> Filter
                </button>
                <a href="{{ route($prefix . '.sop.index') }}" class="btn btn-outline-danger border-0 fw-bold small" style="border-radius: 10px;">
                    <i class="bi bi-arrow-clockwise me-2"></i> Reset
                </a>
            </div>
        </div>

        <div class="table-container">
            <div class="table-responsive">
                {{-- Form untuk Bulk Delete --}}
                <form id="formBulkDelete" action="{{ $canBulkDelete ? route('admin.sop.bulkDelete') : '#' }}" method="POST">
                    @csrf
                    @if($canBulkDelete)
                        @method('DELETE')
                    @endif
                    <table class="table custom-table" id="sopTable">
                        <thead>
                            <tr>
                                @if($canBulkDelete)
                                    <th style="width: 40px;">
                                        <input type="checkbox" class="form-check-input form-check-input-custom" id="selectAll">
                                    </th>
                                @endif
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
                                @if($canBulkDelete)
                                    <td>
                                        <input type="checkbox" name="ids[]" value="{{ $item->id_sop }}" class="form-check-input form-check-input-custom sop-checkbox">
                                    </td>
                                @endif
                                <td class="fw-bold text-muted">{{ $allSop->firstItem() + $index }}</td>
                                <td class="target-subjek">
                                    @php($subjekItem = $item->subjek instanceof \Illuminate\Support\Collection ? $item->subjek->first() : $item->subjek)
                                    <span class="badge-subjek">{{ $subjekItem->nama_subjek ?? 'Tanpa Subjek' }}</span>
                                    <div class="text-muted mt-1" style="font-size: 0.75rem;">Tahun: {{ date('Y', strtotime($item->tahun)) }}</div>
                                </td>
                                <td class="target-nama">
                                    <div class="fw-bold">
                                        <button type="button"
                                           class="nama-sop-link btn-sop-history border-0"
                                           data-history-url="{{ route($prefix . '.sop.history', $item->id_sop) }}"
                                           title="Lihat rincian SOP">
                                            <span>{{ $item->nama_sop }}</span>
                                            <span class="click-indicator">
                                                <i class="bi bi-arrow-up-right"></i>
                                            </span>
                                        </button>
                                    </div>
                                    @if($item->monitorings_count > 0 || $item->evaluasis_count > 0)
                                        <div class="sop-meta-tags">
                                            @if($item->monitorings_count > 0)
                                                <span class="sop-meta-icon monitoring"
                                                      data-bs-toggle="tooltip"
                                                      data-bs-placement="top"
                                                      title="SOP ini sudah di monitoring">
                                                    <i class="bi bi-graph-up-arrow"></i>
                                                </span>
                                            @endif
                                            @if($item->evaluasis_count > 0)
                                                <span class="sop-meta-icon evaluasi"
                                                      data-bs-toggle="tooltip"
                                                      data-bs-placement="top"
                                                      title="SOP ini sudah di evaluasi">
                                                    <i class="bi bi-ui-checks-grid"></i>
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td class="target-nomor"><span class="badge bg-primary-subtle text-primary px-3 py-2" style="border-radius: 8px;">{{ $item->nomor_sop }}</span></td>

                                <td class="text-center">
                                    <div class="badge bg-light text-dark border fw-bold px-3 py-2" style="border-radius: 8px;">
                                        {{ (int) $item->revisi_ke === 0 ? 'Versi Awal' : 'Revisi ke-' . $item->revisi_ke }}
                                    </div>
                                </td>

                                <td class="text-center">
                                    @if($item->status === 'aktif')
                                        <span class="badge bg-success px-3 py-2" style="border-radius: 8px;">Aktif</span>
                                    @elseif($item->status === 'kadaluarsa')
                                        <span class="badge bg-danger px-3 py-2" style="border-radius: 8px;">Kadaluarsa</span>
                                    @elseif(blank($item->status))
                                        <span class="badge bg-light text-dark border px-3 py-2" style="border-radius: 8px;">-</span>
                                    @else
                                        <span class="badge bg-secondary px-3 py-2" style="border-radius: 8px;">Nonaktif</span>
                                    @endif
                                </td>

                                <td class="target-tim"><span class="small fw-bold text-secondary">{{ $subjekItem->timkerja->nama_timkerja ?? 'Internal' }}</span></td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-1">
                                        @if($canManage && $item->status === 'aktif')
                                            <button type="button" class="btn-action text-warning btn-revisi {{ $item->can_revise ? '' : 'opacity-50' }}"
                                                    data-id="{{ $item->id_sop }}"
                                                    data-nama="{{ $item->nama_sop }}"
                                                    data-revisi="{{ $item->revisi_ke }}"
                                                    data-can-revise="{{ $item->can_revise ? '1' : '0' }}"
                                                    data-revision-message="{{ $item->revision_message }}"
                                                    title="{{ $item->revision_message }}">
                                                <i class="bi bi-arrow-repeat"></i>
                                            </button>
                                        @endif
                                        <a href="{{ route('view.pdf', basename($item->link_sop)) }}"
                                           target="_blank"
                                           class="btn-action text-danger border-danger-subtle"
                                           title="Buka PDF"
                                           style="background-color: #fff5f5;">
                                            <i class="bi bi-file-earmark-pdf-fill"></i>
                                        </a>
                                        @if($canManage)
                                            <a href="{{ route($prefix . '.sop.edit', $item->id_sop) }}" class="btn-action text-warning" title="Edit Data"><i class="bi bi-pencil-square"></i></a>
                                        @endif
                                        @if($canBulkDelete)
                                            <button type="button" class="btn-action text-danger btn-delete-single" data-id="{{ $item->id_sop }}" title="Hapus"><i class="bi bi-trash3"></i></button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr id="noData"><td colspan="{{ $canBulkDelete ? 9 : 8 }}" class="text-center py-5"><h6 class="text-muted">Data tidak ditemukan!</h6></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </form>
            </div>
            <div class="pagination-wrapper d-flex justify-content-between align-items-center">
                <div class="text-muted small fw-bold">Menampilkan {{ $allSop->count() }} data pada halaman ini</div>
                <div>{{ $allSop->appends(request()->input())->links('pagination::bootstrap-5') }}</div>
            </div>
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
            <form action="{{ route($prefix . '.sop.index') }}" method="GET">
                <div class="modal-body px-4 py-4">
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-dark">Berdasarkan Subjek</label>
                        <div class="filter-dropdown" data-filter-dropdown>
                            <input type="hidden" name="id_subjek" value="{{ request('id_subjek') }}">
                            <button type="button" class="filter-dropdown-toggle" data-filter-toggle>
                                <span data-filter-label>{{ optional($subjek->firstWhere('id_subjek', (int) request('id_subjek')))->nama_subjek ?? 'Semua Subjek' }}</span>
                                <i class="bi bi-caret-down-fill"></i>
                            </button>
                            <div class="filter-dropdown-menu">
                                <input type="text" class="filter-dropdown-search" placeholder="Cari subjek..." data-filter-search>
                                <div class="filter-dropdown-list" data-filter-list>
                                    <button type="button" class="filter-dropdown-item {{ request('id_subjek') == '' ? 'active' : '' }}" data-value="" data-label="Semua Subjek">Semua Subjek</button>
                                    @foreach($subjek as $s)
                                        <button type="button" class="filter-dropdown-item {{ request('id_subjek') == $s->id_subjek ? 'active' : '' }}" data-value="{{ $s->id_subjek }}" data-label="{{ $s->nama_subjek }}">{{ $s->nama_subjek }}</button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Berdasarkan Tim Kerja</label>
                        <div class="filter-dropdown" data-filter-dropdown>
                            <input type="hidden" name="id_unit" value="{{ request('id_unit') }}">
                            <button type="button" class="filter-dropdown-toggle" data-filter-toggle>
                                <span data-filter-label>{{ optional($units->firstWhere('id_timkerja', (int) request('id_unit')))->nama_timkerja ?? 'Semua Tim Kerja' }}</span>
                                <i class="bi bi-caret-down-fill"></i>
                            </button>
                            <div class="filter-dropdown-menu">
                                <input type="text" class="filter-dropdown-search" placeholder="Cari tim kerja..." data-filter-search>
                                <div class="filter-dropdown-list" data-filter-list>
                                    <button type="button" class="filter-dropdown-item {{ request('id_unit') == '' ? 'active' : '' }}" data-value="" data-label="Semua Tim Kerja">Semua Tim Kerja</button>
                                    @foreach($units as $u)
                                        <button type="button" class="filter-dropdown-item {{ request('id_unit') == $u->id_timkerja ? 'active' : '' }}" data-value="{{ $u->id_timkerja }}" data-label="{{ $u->nama_timkerja }}">{{ $u->nama_timkerja }}</button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
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

{{-- MODAL RINCIAN SOP --}}
<div class="modal fade" id="modalSopHistory" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 24px;">
            <div class="modal-header border-0 pt-4 px-4">
                <div>
                    <h5 class="fw-bold mb-1">Rincian SOP</h5>
                    <div class="text-muted small">SOP terbaru dan riwayat revisi terdahulu</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 pb-4">
                <div class="history-panel mb-4">
                    <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                        <div>
                            <div class="text-muted small mb-1">SOP Aktif Terbaru</div>
                            <h4 class="fw-bold mb-2" id="historyLatestName">-</h4>
                            <div class="d-flex flex-wrap gap-2">
                                <span class="badge bg-primary-subtle text-primary px-3 py-2" id="historyLatestNumber">-</span>
                                <span class="badge bg-success px-3 py-2" id="historyLatestStatus">Aktif</span>
                                <span class="badge bg-light text-dark border px-3 py-2" id="historyLatestRevision">-</span>
                            </div>
                        </div>

                        <a href="#" target="_blank" id="historyLatestView" class="btn btn-danger fw-bold px-4 py-2">
                            <i class="bi bi-file-earmark-pdf-fill me-2"></i>Lihat SOP
                        </a>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="history-stat">
                            <div class="text-muted small">Subjek</div>
                            <div class="fw-bold mt-1" id="historyLatestSubjek">-</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="history-stat">
                            <div class="text-muted small">Tim Kerja</div>
                            <div class="fw-bold mt-1" id="historyLatestTimkerja">-</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="history-stat">
                            <div class="text-muted small">Tahun</div>
                            <div class="fw-bold mt-1" id="historyLatestYear">-</div>
                        </div>
                    </div>
                </div>

                <div class="history-panel mb-4">
                    <div class="fw-bold mb-2">Deskripsi Revisi Terbaru</div>
                    <div class="text-muted small" id="historyLatestDescription">Belum ada deskripsi revisi.</div>
                </div>

                <div class="history-panel">
                    <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap mb-3">
                        <div>
                            <div class="fw-bold">Revisi Terdahulu</div>
                            <div class="text-muted small">Pilih revisi lama yang ingin dilihat</div>
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <select id="historyRevisionSelect" class="form-select" style="min-width: 240px;">
                                <option value="">Tidak ada revisi terdahulu</option>
                            </select>
                            <a href="#" target="_blank" id="historyRevisionView" class="btn btn-outline-primary fw-bold disabled" aria-disabled="true">
                                <i class="bi bi-eye-fill me-2"></i>Lihat
                            </a>
                        </div>
                    </div>
                    <div class="history-stat">
                        <div class="text-muted small">Deskripsi Revisi Dipilih</div>
                        <div class="fw-bold mt-1" id="historyRevisionTitle">-</div>
                        <div class="text-muted small mt-2" id="historyRevisionDescription">Pilih salah satu revisi terdahulu dari dropdown untuk melihat file SOP sebelumnya.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($canManage)
    {{-- MODAL REVISI CEPAT --}}
    <div class="modal fade" id="modalRevisi" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg">
                <div class="modal-header border-0 pt-4 px-4">
                    <h5 class="fw-bold mb-0">Input Revisi SOP Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formRevisiSop" action="{{ route($prefix . '.sop.revisi') }}" method="POST" enctype="multipart/form-data">
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
                        <button type="submit" class="btn btn-warning fw-bold py-2 flex-grow-1 text-white" id="btnSubmitRevisiSop" style="background: #f59e0b; border-radius: 12px;">Simpan Revisi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        $('[data-filter-dropdown]').each(function() {
            const dropdown = $(this);
            const toggle = dropdown.find('[data-filter-toggle]');
            const search = dropdown.find('[data-filter-search]');
            const hiddenInput = dropdown.find('input[type="hidden"]');
            const label = dropdown.find('[data-filter-label]');
            const items = dropdown.find('.filter-dropdown-item');
            const list = dropdown.find('[data-filter-list]');

            toggle.on('click', function() {
                $('[data-filter-dropdown]').not(dropdown).removeClass('open');
                dropdown.toggleClass('open');
                if (dropdown.hasClass('open')) {
                    search.trigger('focus');
                }
            });

            search.on('input', function() {
                const keyword = ($(this).val() || '').trim().toLowerCase();
                let visibleCount = 0;

                items.each(function() {
                    const item = $(this);
                    const text = String(item.data('label') || '').toLowerCase();
                    const matched = text.includes(keyword);
                    item.toggle(matched);
                    if (matched) {
                        visibleCount++;
                    }
                });

                list.find('.filter-dropdown-empty').remove();
                if (visibleCount === 0) {
                    list.append('<div class="filter-dropdown-empty">Data tidak ditemukan</div>');
                }
            });

            items.on('click', function() {
                const item = $(this);
                hiddenInput.val(item.data('value'));
                label.text(item.data('label'));
                items.removeClass('active');
                item.addClass('active');
                dropdown.removeClass('open');
                search.val('');
                items.show();
                list.find('.filter-dropdown-empty').remove();
            });
        });

        $(document).on('click', function(event) {
            if (!$(event.target).closest('[data-filter-dropdown]').length) {
                $('[data-filter-dropdown]').removeClass('open');
            }
        });

        // --- FITUR LIVE SEARCH (MODIFIKASI UTAMA) ---
        $("#searchTable").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            var visibleRows = 0;

            $("#sopTable tbody tr").filter(function() {
                // Mencari di kolom Subjek, Nama SOP, Nomor SOP, dan Tim Kerja
                var match = $(this).text().toLowerCase().indexOf(value) > -1;
                $(this).toggle(match);
                if(match) visibleRows++;
            });

            // Handle jika data tidak ditemukan saat filter
            if (visibleRows === 0 && value !== "") {
                if ($("#noDataSearch").length === 0) {
                    $("#sopTable tbody").append('<tr id="noDataSearch"><td colspan="9" class="text-center py-5"><h6 class="text-muted">Pencarian tidak ditemukan!</h6></td></tr>');
                }
            } else {
                $("#noDataSearch").remove();
            }
        });

        const sopHistoryModal = new bootstrap.Modal(document.getElementById('modalSopHistory'));
        const historyRevisionSelect = $('#historyRevisionSelect');
        const historyRevisionView = $('#historyRevisionView');

        function setHistoryViewButton(url) {
            if (url) {
                historyRevisionView.removeClass('disabled').attr('href', url).attr('aria-disabled', 'false');
            } else {
                historyRevisionView.addClass('disabled').attr('href', '#').attr('aria-disabled', 'true');
            }
        }

        $(document).on('click', '.btn-sop-history', function () {
            const historyUrl = $(this).data('history-url');

            $('#historyLatestName').text('Memuat...');
            $('#historyLatestNumber').text('-');
            $('#historyLatestStatus').text('-');
            $('#historyLatestRevision').text('-');
            $('#historyLatestSubjek').text('-');
            $('#historyLatestTimkerja').text('-');
            $('#historyLatestYear').text('-');
            $('#historyLatestDescription').text('Belum ada deskripsi revisi.');
            $('#historyLatestView').attr('href', '#');
            historyRevisionSelect.html('<option value="">Memuat revisi...</option>');
            setHistoryViewButton(null);
            $('#historyRevisionTitle').text('-');
            $('#historyRevisionDescription').text('Memuat rincian revisi SOP...');

            sopHistoryModal.show();

            $.get(historyUrl, function (response) {
                const latest = response.latest;
                const history = response.history || [];
                const oldRevisions = history.filter((item, index) => index > 0);

                $('#historyLatestName').text(latest?.nama_sop ?? '-');
                $('#historyLatestNumber').text(latest?.nomor_sop ?? '-');
                $('#historyLatestStatus').text(latest?.status_label ?? '-');
                $('#historyLatestRevision').text(latest?.revisi_label ?? '-');
                $('#historyLatestSubjek').text(latest?.subjek ?? '-');
                $('#historyLatestTimkerja').text(latest?.timkerja ?? '-');
                $('#historyLatestYear').text(latest?.tahun ?? '-');
                $('#historyLatestDescription').text(latest?.keterangan ?? 'Belum ada deskripsi revisi.');
                $('#historyLatestView').attr('href', latest?.view_url ?? '#');

                if (oldRevisions.length === 0) {
                    historyRevisionSelect.html('<option value="">Belum ada revisi terdahulu</option>');
                    setHistoryViewButton(null);
                    $('#historyRevisionTitle').text('-');
                    $('#historyRevisionDescription').text('SOP ini belum memiliki revisi terdahulu yang bisa ditampilkan.');
                    return;
                }

                const options = oldRevisions.map((item) => {
                    const label = `${item.revisi_label} • ${item.status_label}`;
                    const url = item.view_url ?? '';
                    const desc = item.keterangan ? item.keterangan : `Nomor SOP: ${item.nomor_sop}`;
                    return `<option value="${url}" data-title="${$('<div>').text(item.revisi_label ?? '-').html()}" data-description="${$('<div>').text(desc).html()}">${label}</option>`;
                });

                historyRevisionSelect.html(`<option value="">Pilih revisi terdahulu</option>${options.join('')}`);
                setHistoryViewButton(null);
                $('#historyRevisionTitle').text('-');
                $('#historyRevisionDescription').text('Pilih salah satu revisi terdahulu dari dropdown untuk melihat file SOP sebelumnya.');
            }).fail(function () {
                historyRevisionSelect.html('<option value="">Gagal memuat revisi</option>');
                setHistoryViewButton(null);
                $('#historyRevisionTitle').text('-');
                $('#historyRevisionDescription').text('Rincian SOP gagal dimuat. Coba lagi.');
            });
        });

        historyRevisionSelect.on('change', function () {
            const selected = $(this).find('option:selected');
            const url = $(this).val();
            const title = selected.data('title');
            const description = selected.data('description');

            setHistoryViewButton(url || null);
            $('#historyRevisionTitle').text(title || '-');
            $('#historyRevisionDescription').text(description || 'Pilih salah satu revisi terdahulu dari dropdown untuk melihat file SOP sebelumnya.');
        });

        @if($canManage)
            // Trigger Modal Revisi Cepat
            $('.btn-revisi').on('click', function() {
                const canRevisi = $(this).data('can-revise') == 1;
                const revisionMessage = $(this).data('revision-message');

                if (!canRevisi) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Revisi belum bisa dilakukan',
                        text: revisionMessage || 'SOP ini belum memenuhi syarat revisi.',
                        confirmButtonColor: '#f59e0b'
                    });
                    return;
                }

                let id = $(this).data('id');
                let nama = $(this).data('nama');
                let rev = $(this).data('revisi');
                $('#revisi_id_sop').val(id);
                $('#revisi_nama_sop').val(nama);
                $('#revisi_info_ke').text('Posisi saat ini: ' + (rev === '-' ? 'SOP Baru' : 'Revisi ke-' + rev));
                $('#modalRevisi').modal('show');
            });

            $('#formRevisiSop').on('submit', function(e) {
                const form = $(this);
                const submitButton = $('#btnSubmitRevisiSop');

                if (form.data('isSubmitting')) {
                    e.preventDefault();
                    return;
                }

                form.data('isSubmitting', true);
                submitButton.prop('disabled', true).text('Menyimpan...');
            });

            $('#modalRevisi').on('hidden.bs.modal', function() {
                const form = $('#formRevisiSop');
                form[0].reset();
                form.removeData('isSubmitting');
                $('#btnSubmitRevisiSop').prop('disabled', false).text('Simpan Revisi');
                $('#revisi_id_sop').val('');
                $('#revisi_nama_sop').val('');
                $('#revisi_info_ke').text('');
            });
        @endif

        @if($canBulkDelete)
            // --- LOGIKA BULK DELETE (CEKLIS) ---
            const $selectAll = $('#selectAll');
            const $btnBulkDelete = $('#btnBulkDelete');
            const $checkCount = $('#checkCount');

            function updateBulkButton() {
                const count = $('.sop-checkbox:checked').length;
                $checkCount.text(count);
                if (count > 0) {
                    $btnBulkDelete.fadeIn();
                } else {
                    $btnBulkDelete.fadeOut();
                }
            }

            $selectAll.on('change', function() {
                $('.sop-checkbox:visible').prop('checked', this.checked);
                updateBulkButton();
            });

            $(document).on('change', '.sop-checkbox', function() {
                updateBulkButton();
            });

            $btnBulkDelete.on('click', function() {
                Swal.fire({
                    title: 'Hapus Masal?',
                    text: `Anda akan menghapus ${$('.sop-checkbox:checked').length} data sekaligus.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Ya, Hapus Semua',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#formBulkDelete').submit();
                    }
                });
            });

            // Delete Confirmation (Single Delete)
            $('.btn-delete-single').on('click', function() {
                const id = $(this).data('id');
                Swal.fire({
                    title: 'Hapus Dokumen?',
                    text: "Data SOP akan dihapus permanen.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Ya, Hapus',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = $(`<form action="{{ url($prefix . '/sop') }}/${id}" method="POST">
                            @csrf @method('DELETE')
                        </form>`).appendTo('body');
                        form.submit();
                    }
                });
            });
        @endif

        @if(session('success'))
            Swal.fire({ icon: 'success', title: 'Berhasil', text: '{{ session("success") }}', showConfirmButton: false, timer: 1500 });
        @endif

        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.forEach(function (tooltipTriggerEl) {
            new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endsection
