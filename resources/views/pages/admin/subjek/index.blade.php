@extends('layouts.sidebarmenu')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<div class="container-fluid px-4 py-4">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-0 text-dark">Manajemen Subjek</h4>
            <p class="text-muted small mb-0">
                Kelola data subjek berdasarkan Tim Kerja
            </p>
        </div>

        <button type="button"
                class="btn btn-primary rounded-3 shadow-sm px-4 py-2 fw-bold d-flex align-items-center"
                data-bs-toggle="modal"
                data-bs-target="#modalTambahSubjek">
            <i class="bi bi-plus-circle me-2"></i>
            Tambah Subjek
        </button>
    </div>

    {{-- ALERT --}}
    @if(session('success'))
        <div id="alert-berhasil"
             class="alert alert-success border-0 shadow-sm rounded-4 alert-dismissible fade show mb-4">
            <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill me-3 fs-4"></i>
                <div>
                    <strong>Berhasil!</strong>
                    {{ session('success') }}
                </div>
            </div>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill me-3 fs-4"></i>
                <div>
                    <strong>Gagal!</strong>
                    {{ session('error') }}
                </div>
            </div>
        </div>
    @endif

    {{-- TABLE --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">

        <div class="card-header bg-white border-0 py-3 px-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">

                <div class="fw-bold text-dark">
                    Total Data :
                    <span class="text-primary">{{ count($subjek) }}</span>
                </div>

                <input type="text"
                       id="searchTable"
                       class="form-control rounded-3"
                       placeholder="Cari subjek..."
                       style="max-width:250px;">
            </div>
        </div>

        <div class="card-body p-0">

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tableSubjek">

                    <thead class="bg-light">
                        <tr>
                            <th width="70" class="px-4 py-3">No</th>
                            <th class="py-3">Tim Kerja</th>
                            <th class="py-3">Nama Subjek</th>
                            <th class="py-3">Deskripsi</th>
                            <th class="py-3">Status</th>
                            <th class="py-3 text-center" width="150">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                    @forelse($subjek as $s)
                        <tr>

                            <td class="px-4 fw-bold text-muted">
                                {{ $loop->iteration }}
                            </td>

                            <td>
                                <span class="badge bg-info-subtle text-info px-3 py-2 rounded-pill">
                                    {{ $s->timkerja->nama_timkerja ?? '-' }}
                                </span>
                            </td>

                            <td>
                                <div class="fw-bold text-dark">
                                    {{ $s->nama_subjek }}
                                </div>
                                <small class="text-muted">
                                    ID : #{{ $s->id_subjek }}
                                </small>
                            </td>

                            <td class="text-muted small">
                                {{ $s->deskripsi ?: '-' }}
                            </td>

                            <td>
                                @if($s->status == 'aktif')
                                    <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill">
                                        Aktif
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>

                            <td class="text-center">

                                <div class="btn-group shadow-sm">

                                    {{-- EDIT --}}
                                    <button type="button"
                                            class="btn btn-sm btn-light border"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalEdit{{ $s->id_subjek }}">
                                        <i class="bi bi-pencil text-primary"></i>
                                    </button>

                                    {{-- DELETE --}}
                                    <form action="{{ route('admin.subjek.destroy', $s->id_subjek) }}"
                                          method="POST"
                                          class="d-inline form-hapus">

                                        @csrf
                                        @method('DELETE')

                                        <button type="button"
                                                class="btn btn-sm btn-light border btn-delete">
                                            <i class="bi bi-trash text-danger"></i>
                                        </button>
                                    </form>

                                </div>

                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                Data subjek tidak ditemukan.
                            </td>
                        </tr>
                    @endforelse

                    </tbody>

                </table>
            </div>

        </div>
    </div>
</div>

{{-- MODAL TAMBAH --}}
<div class="modal fade" id="modalTambahSubjek" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">

            <form action="{{ route('admin.subjek.store') }}" method="POST">
                @csrf

                <div class="modal-header border-0 pb-0">
                    <h5 class="fw-bold">Tambah Subjek</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Nama Subjek</label>
                        <input type="text"
                               name="nama_subjek"
                               class="form-control rounded-3"
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Tim Kerja</label>
                        <select name="id_timkerja" class="form-select rounded-3" required>
                            <option value="">-- Pilih Tim Kerja --</option>
                            @foreach($timkerja as $t)
                                <option value="{{ $t->id_timkerja }}">
                                    {{ $t->nama_timkerja }}
                                </option>
                            @endforeach
                        </select>
                    </div>



                    <div>
                        <label class="form-label fw-bold small">Deskripsi</label>
                        <textarea name="deskripsi"
                                  class="form-control rounded-3"
                                  rows="3"></textarea>
                    </div>

                </div>

                <div class="modal-footer border-0">
                    <button type="button"
                            class="btn btn-light fw-bold rounded-3"
                            data-bs-dismiss="modal">
                        Batal
                    </button>

                    <button type="submit"
                            class="btn btn-primary px-4 fw-bold rounded-3">
                        Simpan
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>

{{-- MODAL EDIT --}}
@foreach($subjek as $s)
<div class="modal fade" id="modalEdit{{ $s->id_subjek }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">

            <form action="{{ route('admin.subjek.update', $s->id_subjek) }}"
                  method="POST">

                @csrf
                @method('PUT')

                <div class="modal-header border-0 pb-0">
                    <h5 class="fw-bold">Edit Subjek</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Tim Kerja</label>
                        <select name="id_timkerja" class="form-select rounded-3" required>
                            @foreach($timkerja as $t)
                                <option value="{{ $t->id_timkerja }}"
                                    {{ $s->id_timkerja == $t->id_timkerja ? 'selected' : '' }}>
                                    {{ $t->nama_timkerja }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Nama Subjek</label>
                        <input type="text"
                               name="nama_subjek"
                               class="form-control rounded-3"
                               value="{{ $s->nama_subjek }}"
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Deskripsi</label>
                        <textarea name="deskripsi"
                                  class="form-control rounded-3"
                                  rows="3">{{ $s->deskripsi }}</textarea>
                    </div>

                    <div>
                        <label class="form-label fw-bold small">Status</label>
                        <select name="status" class="form-select rounded-3">
                            <option value="aktif" {{ $s->status == 'aktif' ? 'selected' : '' }}>
                                Aktif
                            </option>
                            <option value="nonaktif" {{ $s->status == 'nonaktif' ? 'selected' : '' }}>
                                Nonaktif
                            </option>
                        </select>
                    </div>

                </div>

                <div class="modal-footer border-0">
                    <button type="button"
                            class="btn btn-light rounded-3 fw-bold"
                            data-bs-dismiss="modal">
                        Batal
                    </button>

                    <button type="submit"
                            class="btn btn-primary px-4 rounded-3 fw-bold">
                        Simpan Perubahan
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>
@endforeach

<script>
document.addEventListener('DOMContentLoaded', function(){

    const alertElement = document.getElementById('alert-berhasil');

    if(alertElement){
        setTimeout(function(){
            const bsAlert = new bootstrap.Alert(alertElement);
            bsAlert.close();
        }, 2000);
    }

    document.querySelectorAll('.btn-delete').forEach(button => {
        button.addEventListener('click', function(){

            const form = this.closest('.form-hapus');

            Swal.fire({
                title: 'Hapus Subjek?',
                text: 'Data akan dihapus permanen!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if(result.isConfirmed){
                    form.submit();
                }
            });

        });
    });

    document.getElementById('searchTable').addEventListener('keyup', function(){

        let keyword = this.value.toLowerCase();
        let rows = document.querySelectorAll('#tableSubjek tbody tr');

        rows.forEach(row => {
            row.style.display =
                row.innerText.toLowerCase().includes(keyword)
                ? ''
                : 'none';
        });

    });

});
</script>
@endsection
