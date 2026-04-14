@extends('layouts.sidebarmenu')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0 text-dark">Manajemen Subjek</h4>
            <p class="text-muted small mb-0">Kelola kategori utama untuk pengelompokan Unit Kerja dan SOP</p>
        </div>
        <button type="button" class="btn btn-primary rounded-3 shadow-sm px-4 py-2 fw-bold d-flex align-items-center"
                data-bs-toggle="modal" data-bs-target="#modalTambahSubjek">
            <i class="bi bi-plus-circle me-2"></i> Tambah Subjek
        </button>
    </div>

    @if(session('success'))
        <div id="alert-berhasil" class="alert alert-success border-0 shadow-sm rounded-4 alert-dismissible fade show mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill me-3 fs-4"></i>
                <div><strong>Berhasil!</strong> {{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="py-3 px-4 text-secondary small fw-bold text-uppercase" width="70px">No</th>
                            <th class="py-3 text-secondary small fw-bold text-uppercase">Nama Subjek</th>
                            <th class="py-3 text-secondary small fw-bold text-uppercase">Deskripsi</th>
                            <th class="py-3 text-secondary small fw-bold text-uppercase">Tanggal Dibuat</th>
                            <th class="py-3 text-secondary small fw-bold text-uppercase">Status</th>
                            <th class="py-3 text-center text-secondary small fw-bold text-uppercase" width="150px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subjek as $s)
                        <tr>
                            <td class="px-4 py-3 fw-bold text-muted">{{ $loop->iteration }}</td>
                            <td class="py-3">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm me-3 bg-primary-subtle text-primary rounded-3 d-flex align-items-center justify-content-center fw-bold"
                                         style="width: 40px; height: 40px; background-color: #eef2ff; color: #4f46e5;">
                                        {{ strtoupper(substr($s->nama_subjek ?? 'S', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $s->nama_subjek }}</div>
                                        <div class="text-muted" style="font-size: 0.75rem;">ID: #{{ $s->id_subjek }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 text-muted small">{{ $s->deskripsi ?? '-' }}</td>
                            <td class="py-3 small text-muted">{{ $s->created_date ? date('d/m/Y', strtotime($s->created_date)) : '-' }}</td>
                            <td class="py-3">
                                <span class="badge {{ $s->status == 'aktif' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} rounded-pill px-3 text-capitalize">
                                    {{ $s->status }}
                                </span>
                            </td>
                            <td class="py-3 text-center">
                                <div class="btn-group shadow-sm">
                                    <button type="button" class="btn btn-sm btn-light border" data-bs-toggle="modal" data-bs-target="#modalEdit{{ $s->id_subjek }}">
                                        <i class="bi bi-pencil text-primary"></i>
                                    </button>
                                    <form action="{{ route('admin.subjek.destroy', $s->id_subjek) }}" method="POST" class="d-inline form-hapus">
                                        @csrf @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-light border btn-delete">
                                            <i class="bi bi-trash text-danger"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center py-5 text-muted">Data subjek tidak ditemukan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTambahSubjek" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="{{ route('admin.subjek.store') }}" method="POST">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <h5 class="fw-bold">Tambah Subjek Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Nama Subjek</label>
                        <input type="text" name="nama_subjek" class="form-control rounded-3" placeholder="Masukkan nama subjek..." required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-bold small">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control rounded-3" rows="3" placeholder="Keterangan kategori..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light fw-bold rounded-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold rounded-3">Simpan Subjek</button>
                </div>
            </form>
        </div>
    </div>
</div>

@foreach($subjek as $s)
<div class="modal fade" id="modalEdit{{ $s->id_subjek }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form action="{{ route('admin.subjek.update', $s->id_subjek) }}" method="POST">
                @csrf @method('PUT')
                <div class="modal-header border-0 pb-0">
                    <h5 class="fw-bold">Edit Subjek</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Nama Subjek</label>
                        <input type="text" name="nama_subjek" class="form-control rounded-3" value="{{ $s->nama_subjek }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control rounded-3" rows="3">{{ $s->deskripsi }}</textarea>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-bold small">Status</label>
                        <select name="status" class="form-select rounded-3">
                            <option value="aktif" {{ $s->status == 'aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="nonaktif" {{ $s->status == 'nonaktif' ? 'selected' : '' }}>Non-Aktif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-3 fw-bold" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 rounded-3 fw-bold">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<style>
    .avatar-sm { font-size: 1.1rem; }
    .bg-success-subtle { background-color: #d1fae5 !important; }
    .text-success { color: #059669 !important; }
    .bg-danger-subtle { background-color: #fee2e2 !important; }
    .text-danger { color: #dc2626 !important; }
    .btn-light { background: #fff; color: #6c757d; }
    .btn-light:hover { background: #f8fafc; border-color: #cbd5e1 !important; }
    /* Agar tidak menabrak sidebar */
    .modal { z-index: 1070 !important; }
    .modal-backdrop { z-index: 1060 !important; }
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // 1. AUTO HILANGKAN ALERT BERHASIL (Sangat Cepat & Bersih)
    document.addEventListener('DOMContentLoaded', function() {
        const alertElement = document.getElementById('alert-berhasil');
        if (alertElement) {
            setTimeout(function() {
                // Fade out menggunakan Bootstrap class
                const bsAlert = new bootstrap.Alert(alertElement);
                bsAlert.close();

                // Hapus paksa elemen setelah animasi tutup selesai agar tidak menghalangi klik
                setTimeout(() => {
                    if(alertElement) alertElement.remove();
                }, 500);
            }, 2000); // 2000ms = 2 detik saja
        }
    });

    // 2. SWEETALERT2 UNTUK KONFIRMASI HAPUS
    document.querySelectorAll('.btn-delete').forEach(button => {
        button.addEventListener('click', function(e) {
            const form = this.closest('.form-hapus');
            Swal.fire({
                title: 'Hapus Subjek?',
                text: "Data ini akan dihapus secara permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#4f46e5',
                cancelButtonColor: '#f3f4f6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                customClass: {
                    confirmButton: 'btn btn-primary px-4 fw-bold rounded-3 ms-2 shadow-sm',
                    cancelButton: 'btn btn-light px-4 fw-bold rounded-3 text-dark border'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
@endsection
