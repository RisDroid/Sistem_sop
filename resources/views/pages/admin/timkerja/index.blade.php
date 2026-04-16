
@extends('layouts.sidebarmenu')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<div class="container-fluid px-4 py-4">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">

        <div>
            <h4 class="fw-bold mb-0 text-dark">Manajemen Tim Kerja</h4>
            <p class="text-muted small mb-0">
                Kelola data tim kerja yang digunakan pada Subjek dan SOP
            </p>
        </div>

        <button type="button"
                class="btn btn-primary px-4 py-2 rounded-3 shadow-sm fw-bold"
                data-bs-toggle="modal"
                data-bs-target="#modalTambahTimkerja">

            <i class="bi bi-plus-circle me-2"></i>
            Tambah Tim Kerja
        </button>

    </div>

    {{-- ALERT --}}
    @if(session('success'))
        <div id="alert-berhasil"
             class="alert alert-success border-0 shadow-sm rounded-4 alert-dismissible fade show mb-4">

            <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill me-3 fs-5"></i>
                <div>
                    <strong>Berhasil!</strong>
                    {{ session('success') }}
                </div>
            </div>

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">

            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill me-3 fs-5"></i>
                <div>
                    <strong>Gagal!</strong>
                    {{ session('error') }}
                </div>
            </div>

        </div>
    @endif

    {{-- TABLE --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">

        <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">

            <div class="fw-bold">
                Total Data :
                <span class="text-primary">{{ count($timkerja) }}</span>
            </div>

            <input type="text"
                   id="searchTable"
                   class="form-control rounded-3"
                   placeholder="Cari tim kerja..."
                   style="max-width:250px;">

        </div>

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0" id="tableTimkerja">

                    <thead class="bg-light">
                        <tr>
                            <th class="px-4 py-3">No</th>
                            <th class="py-3">Nama Tim Kerja</th>
                            <th class="py-3">Deskripsi</th>
                            <th class="py-3">Status</th>
                            <th class="py-3 text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                    @forelse($timkerja as $t)
                        <tr>

                            <td class="px-4 fw-bold text-muted">
                                {{ $loop->iteration }}
                            </td>

                            <td>

                                <div class="d-flex align-items-center">

                                    <div class="avatar-sm bg-primary-subtle text-primary rounded-3 fw-bold d-flex align-items-center justify-content-center me-3">
                                        {{ strtoupper(substr($t->nama_timkerja,0,1)) }}
                                    </div>

                                    <div>
                                        <div class="fw-bold text-dark">
                                            {{ $t->nama_timkerja }}
                                        </div>

                                        <small class="text-muted">
                                            ID : #{{ $t->id_timkerja }}
                                        </small>
                                    </div>

                                </div>

                            </td>

                            <td class="text-muted small">
                                {{ $t->deskripsi ?: '-' }}
                            </td>

                            <td>

                                @if($t->status == 'aktif')
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
                                            class="btn btn-light border btn-sm"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalEdit{{ $t->id_timkerja }}">
                                        <i class="bi bi-pencil text-primary"></i>
                                    </button>

                                    {{-- DELETE --}}
                                    <form action="{{ route('admin.timkerja.destroy', $t->id_timkerja) }}"
                                          method="POST"
                                          class="form-delete d-inline">

                                        @csrf
                                        @method('DELETE')

                                        <button type="button"
                                                class="btn btn-light border btn-sm btn-delete">
                                            <i class="bi bi-trash text-danger"></i>
                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="5"
                                class="text-center py-5 text-muted">
                                Data tim kerja belum tersedia.
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
<div class="modal fade" id="modalTambahTimkerja" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">

        <form action="{{ route('admin.timkerja.store') }}"
              method="POST"
              class="modal-content border-0 shadow-lg rounded-4">

            @csrf

            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold mb-0">Tambah Tim Kerja</h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="mb-3">
                    <label class="form-label fw-bold small">
                        Nama Tim Kerja
                    </label>

                    <input type="text"
                           name="nama_timkerja"
                           class="form-control rounded-3"
                           required>
                </div>

                <div>
                    <label class="form-label fw-bold small">
                        Deskripsi
                    </label>

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

                <button class="btn btn-primary px-4 fw-bold rounded-3">
                    Simpan
                </button>

            </div>

        </form>

    </div>
</div>

{{-- MODAL EDIT --}}
@foreach($timkerja as $t)
<div class="modal fade" id="modalEdit{{ $t->id_timkerja }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">

        <form action="{{ route('admin.timkerja.update', $t->id_timkerja) }}"
              method="POST"
              class="modal-content border-0 shadow-lg rounded-4">

            @csrf
            @method('PUT')

            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold mb-0">Edit Tim Kerja</h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="mb-3">
                    <label class="form-label fw-bold small">
                        Nama Tim Kerja
                    </label>

                    <input type="text"
                           name="nama_timkerja"
                           class="form-control rounded-3"
                           value="{{ $t->nama_timkerja }}"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small">
                        Deskripsi
                    </label>

                    <textarea name="deskripsi"
                              class="form-control rounded-3"
                              rows="3">{{ $t->deskripsi }}</textarea>
                </div>

                <div>
                    <label class="form-label fw-bold small">
                        Status
                    </label>

                    <select name="status"
                            class="form-select rounded-3">

                        <option value="aktif"
                            {{ $t->status == 'aktif' ? 'selected' : '' }}>
                            Aktif
                        </option>

                        <option value="nonaktif"
                            {{ $t->status == 'nonaktif' ? 'selected' : '' }}>
                            Nonaktif
                        </option>

                    </select>
                </div>

            </div>

            <div class="modal-footer border-0">

                <button type="button"
                        class="btn btn-light fw-bold rounded-3"
                        data-bs-dismiss="modal">
                    Batal
                </button>

                <button class="btn btn-primary px-4 fw-bold rounded-3">
                    Simpan Perubahan
                </button>

            </div>

        </form>

    </div>
</div>
@endforeach

<style>
.avatar-sm{
    width:42px;
    height:42px;
    font-size:1rem;
}
.bg-success-subtle{
    background:#d1fae5 !important;
}
.text-success{
    color:#059669 !important;
}
.bg-danger-subtle{
    background:#fee2e2 !important;
}
.text-danger{
    color:#dc2626 !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function(){

    const alertElement = document.getElementById('alert-berhasil');

    if(alertElement){
        setTimeout(function(){
            const bsAlert = new bootstrap.Alert(alertElement);
            bsAlert.close();
        }, 2000);
    }

    // SEARCH
    document.getElementById('searchTable').addEventListener('keyup', function(){

        let keyword = this.value.toLowerCase();
        let rows = document.querySelectorAll('#tableTimkerja tbody tr');

        rows.forEach(row => {
            row.style.display =
                row.innerText.toLowerCase().includes(keyword)
                ? ''
                : 'none';
        });

    });

    // DELETE
    document.querySelectorAll('.btn-delete').forEach(button => {

        button.addEventListener('click', function(){

            let form = this.closest('.form-delete');

            Swal.fire({
                title: 'Hapus Tim Kerja?',
                text: 'Data akan dihapus permanen.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then((result)=>{

                if(result.isConfirmed){
                    form.submit();
                }

            });

        });

    });

});
</script>
@endsection
