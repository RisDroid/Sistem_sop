@extends('layouts.sidebarmenu')

@section('content')

<style>
.table-card{
    background:#fff;
    border-radius:16px;
    border:1px solid #e2e8f0;
}
.table-modern{
    width:100%;
    border-collapse:collapse;
}
.table-modern thead{
    background:#f8fafc;
    border-bottom:2px solid #edf2f7;
}
.table-modern th{
    padding:14px 15px;
    text-align:left;
    font-size:11px;
    font-weight:700;
    color:#475569;
    text-transform:uppercase;
}
.table-modern td{
    padding:14px 15px;
    border-bottom:1px solid #f1f5f9;
    font-size:13px;
    vertical-align:middle;
}
.table-modern tbody tr:nth-child(even){
    background:#fcfdfe;
}
.badge-soft{
    padding:6px 12px;
    border-radius:8px;
    font-weight:700;
    font-size:11px;
}
.audit-text{
    font-size:10px;
    line-height:1.45;
    color:#94a3b8;
}
.btn-icon{
    width:34px;
    height:34px;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    border-radius:8px;
    border:1px solid #e2e8f0;
    background:#fff;
    transition:.2s;
}
.btn-icon:hover{
    background:#f8fafc;
    color:#2563eb;
}
</style>

<div class="container-fluid py-4">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">

        <div>
            <h4 class="fw-bold mb-1">Manajemen User</h4>
            <p class="text-muted small mb-0">
                Kelola akun login dan hak akses sistem.
            </p>
        </div>

        <button class="btn btn-primary px-4 fw-bold"
                data-bs-toggle="modal"
                data-bs-target="#modalTambah">

            <i class="bi bi-plus-lg me-2"></i>
            Tambah User
        </button>

    </div>

    {{-- ALERT --}}
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-4">
            {{ session('success') }}
        </div>
    @endif

    {{-- TABLE --}}
    <div class="table-card shadow-sm overflow-hidden">

        <div class="table-responsive">

            <table class="table-modern">

                <thead>
                    <tr>
                        <th width="50" class="text-center">No</th>
                        <th>Nama & NIP</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Tim Kerja</th>
                        <th>Subjek</th>
                        <th>Log Audit</th>
                        <th width="100" class="text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody>

                @forelse($users as $index => $u)
                    <tr>

                        <td class="text-center fw-bold text-muted">
                            {{ $index + 1 }}
                        </td>

                        <td>
                            <div class="fw-bold text-dark">
                                {{ $u->nama }}
                            </div>

                            <div class="text-muted small">
                                NIP. {{ $u->nip }}
                            </div>
                        </td>

                        <td>
                            <span class="badge bg-light text-primary border">
                                {{ $u->username }}
                            </span>
                        </td>

                        <td>

                            @if($u->role == 'admin')
                                <span class="badge-soft bg-danger text-white">
                                    ADMIN
                                </span>

                            @elseif($u->role == 'operator')
                                <span class="badge-soft bg-primary text-white">
                                    OPERATOR
                                </span>

                            @else
                                <span class="badge-soft bg-secondary text-white">
                                    VIEWER
                                </span>
                            @endif

                        </td>

                        <td>
                            {{ $u->timkerja->nama_timkerja ?? '-' }}
                        </td>

                        <td>
                            {{ optional($u->timkerja)->subjek?->pluck('nama_subjek')->filter()->join(', ') ?: '-' }}
                        </td>

                        <td>
                            <div class="audit-text">

                                <b>Create:</b>
                                {{ $u->creator->nama ?? '-' }}

                                <br>

                                {{ $u->created_date ?? '-' }}

                                <hr class="my-1">

                                <b>Modified:</b>
                                {{ $u->editor->nama ?? '-' }}

                                <br>

                                {{ $u->modified_date ?? '-' }}

                            </div>
                        </td>

                        <td class="text-center">

                            <div class="d-flex justify-content-center gap-2">

                                {{-- EDIT --}}
                                <button class="btn-icon"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalEdit{{ $u->id_user }}">
                                    <i class="bi bi-pencil"></i>
                                </button>

                                {{-- DELETE --}}
                                <form action="{{ route('admin.user.destroy',$u->id_user) }}"
                                      method="POST"
                                      class="form-delete d-inline">

                                    @csrf
                                    @method('DELETE')

                                    <button type="button"
                                            class="btn-icon text-danger btn-delete">
                                        <i class="bi bi-trash"></i>
                                    </button>

                                </form>

                            </div>

                        </td>

                    </tr>

                    {{-- MODAL EDIT --}}
                    <div class="modal fade"
                         id="modalEdit{{ $u->id_user }}"
                         tabindex="-1">

                        <div class="modal-dialog modal-dialog-centered">

                            <div class="modal-content border-0 shadow-lg rounded-4">

                                <div class="modal-header border-0 pt-4 px-4">

                                    <h5 class="fw-bold">
                                        Edit Data User
                                    </h5>

                                    <button type="button"
                                            class="btn-close"
                                            data-bs-dismiss="modal">
                                    </button>

                                </div>

                                <form action="{{ route('admin.user.update',$u->id_user) }}"
                                      method="POST"
                                      data-user-form="edit">

                                    @csrf
                                    @method('PUT')

                                    <div class="modal-body p-4">

                                        <div class="mb-3">
                                            <label class="small fw-bold">
                                                Nama Lengkap
                                            </label>

                                            <input type="text"
                                                   name="nama"
                                                   value="{{ $u->nama }}"
                                                   class="form-control bg-light border-0 py-2"
                                                   required>
                                        </div>

                                        <div class="row">

                                            <div class="col-6 mb-3">
                                                <label class="small fw-bold">NIP</label>

                                                <input type="text"
                                                       name="nip"
                                                       value="{{ $u->nip }}"
                                                       class="form-control bg-light border-0 py-2"
                                                       required>
                                            </div>

                                            <div class="col-6 mb-3">
                                                <label class="small fw-bold">Username</label>

                                                <input type="text"
                                                       name="username"
                                                       value="{{ $u->username }}"
                                                       class="form-control bg-light border-0 py-2"
                                                       required>
                                            </div>

                                        </div>

                                        <div class="mb-3">
                                            <label class="small fw-bold">
                                                Password (isi jika ganti)
                                            </label>

                                            <input type="password"
                                                   name="password"
                                                   minlength="6"
                                                   class="form-control bg-light border-0 py-2">
                                        </div>

                                        <div class="row">

                                            <div class="col-6 mb-3">

                                                <label class="small fw-bold">
                                                    Role
                                                </label>

                                                <select name="role"
                                                        data-role-select
                                                        class="form-select bg-light border-0 py-2">

                                                    <option value="admin" {{ $u->role == 'admin' ? 'selected' : '' }}>Admin</option>
                                                    <option value="operator" {{ $u->role == 'operator' ? 'selected' : '' }}>Operator</option>
                                                    <option value="viewer" {{ $u->role == 'viewer' ? 'selected' : '' }}>Viewer</option>

                                                </select>

                                            </div>

                                            <div class="col-6 mb-3">

                                                <label class="small fw-bold">
                                                    Tim Kerja
                                                </label>

                                                <select name="id_timkerja"
                                                        data-timkerja-select
                                                        class="form-select bg-light border-0 py-2">

                                                    <option value="">-- Pilih --</option>

                                                    @foreach($timkerja as $t)
                                                        <option value="{{ $t->id_timkerja }}"
                                                            {{ $u->id_timkerja == $t->id_timkerja ? 'selected' : '' }}>
                                                            {{ $t->nama_timkerja }}
                                                        </option>
                                                    @endforeach

                                                </select>

                                                <small class="text-muted d-block mt-2" data-timkerja-help>
                                                    Tim kerja wajib dipilih untuk operator dan viewer.
                                                </small>

                                            </div>

                                        </div>

                                    </div>

                                    <div class="modal-footer border-0 p-4 pt-0">

                                        <button type="submit"
                                                class="btn btn-primary w-100 py-3 fw-bold rounded-3">
                                            UPDATE DATA
                                        </button>

                                    </div>

                                </form>

                            </div>

                        </div>

                    </div>

                @empty

                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            Data user belum tersedia.
                        </td>
                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>
    </div>

</div>

{{-- MODAL TAMBAH --}}
<div class="modal fade" id="modalTambah" tabindex="-1">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content border-0 shadow-lg rounded-4">

            <div class="modal-header border-0 pt-4 px-4">

                <h5 class="fw-bold">
                    Tambah User Baru
                </h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                </button>

            </div>

            <form action="{{ route('admin.user.store') }}"
                  method="POST"
                  data-user-form="create">

                @csrf

                <div class="modal-body p-4">

                    <div class="mb-3">
                        <label class="small fw-bold">Nama Lengkap</label>

                        <input type="text"
                               name="nama"
                               value="{{ old('nama') }}"
                               class="form-control bg-light border-0 py-2"
                               required>
                    </div>

                    <div class="row">

                        <div class="col-6 mb-3">
                            <label class="small fw-bold">NIP</label>

                            <input type="text"
                                   name="nip"
                                   value="{{ old('nip') }}"
                                   class="form-control bg-light border-0 py-2"
                                   required>
                        </div>

                        <div class="col-6 mb-3">
                            <label class="small fw-bold">Username</label>

                            <input type="text"
                                   name="username"
                                   value="{{ old('username') }}"
                                   class="form-control bg-light border-0 py-2"
                                   required>
                        </div>

                    </div>

                    <div class="mb-3">
                        <label class="small fw-bold">Password</label>

                        <input type="password"
                               name="password"
                               minlength="6"
                               class="form-control bg-light border-0 py-2"
                               required>
                    </div>

                    <div class="row">

                        <div class="col-6 mb-3">

                            <label class="small fw-bold">Role</label>

                            <select name="role"
                                    data-role-select
                                    class="form-select bg-light border-0 py-2">

                                <option value="admin" {{ old('role', 'admin') == 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="operator" {{ old('role') == 'operator' ? 'selected' : '' }}>Operator</option>
                                <option value="viewer" {{ old('role') == 'viewer' ? 'selected' : '' }}>Viewer</option>

                            </select>

                        </div>

                        <div class="col-6 mb-3">

                            <label class="small fw-bold">
                                Tim Kerja
                            </label>

                            <select name="id_timkerja"
                                    data-timkerja-select
                                    class="form-select bg-light border-0 py-2">

                                <option value="">-- Pilih --</option>

                                @foreach($timkerja as $t)
                                    <option value="{{ $t->id_timkerja }}" {{ (string) old('id_timkerja') === (string) $t->id_timkerja ? 'selected' : '' }}>
                                        {{ $t->nama_timkerja }}
                                    </option>
                                @endforeach

                            </select>

                            <small class="text-muted d-block mt-2" data-timkerja-help>
                                Tim kerja wajib dipilih untuk operator dan viewer.
                            </small>

                        </div>

                    </div>

                </div>

                <div class="modal-footer border-0 p-4 pt-0">

                    <button type="submit"
                            class="btn btn-primary w-100 py-3 fw-bold rounded-3">
                        SIMPAN USER
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    @if($errors->any())
        Swal.fire({
            title: 'Data user belum valid',
            html: `{!! collect($errors->all())->map(fn ($error) => '<div>' . e($error) . '</div>')->implode('') !!}`,
            icon: 'warning',
            confirmButtonText: 'OK'
        });

        const modalTambah = document.getElementById('modalTambah');
        if (modalTambah && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(modalTambah).show();
        }
    @endif

    document.querySelectorAll('[data-user-form]').forEach(form => {
        const roleSelect = form.querySelector('[data-role-select]');
        const timkerjaSelect = form.querySelector('[data-timkerja-select]');
        const timkerjaHelp = form.querySelector('[data-timkerja-help]');

        const syncTimkerjaState = (showPopup = false) => {
            if (!roleSelect || !timkerjaSelect) {
                return;
            }

            const isAdmin = roleSelect.value === 'admin';

            timkerjaSelect.disabled = isAdmin;
            timkerjaSelect.required = !isAdmin;

            if (isAdmin) {
                timkerjaSelect.value = '';
                if (timkerjaHelp) {
                    timkerjaHelp.textContent = 'Role admin tidak perlu memilih tim kerja.';
                }

                if (showPopup) {
                    Swal.fire({
                        title: 'Role admin dipilih',
                        text: 'Tim kerja tidak perlu diisi untuk user admin.',
                        icon: 'info',
                        confirmButtonText: 'OK'
                    });
                }
            } else if (timkerjaHelp) {
                timkerjaHelp.textContent = 'Tim kerja wajib dipilih untuk operator dan viewer.';
            }
        };

        syncTimkerjaState();

        roleSelect?.addEventListener('change', function () {
            syncTimkerjaState(true);
        });

        form.addEventListener('submit', function (event) {
            const nama = form.querySelector('[name="nama"]')?.value.trim() || '';
            const username = form.querySelector('[name="username"]')?.value.trim() || '';
            const nip = form.querySelector('[name="nip"]')?.value.trim() || '';
            const passwordInput = form.querySelector('[name="password"]');
            const password = passwordInput?.value || '';
            const role = roleSelect?.value || '';
            const timkerja = timkerjaSelect?.value || '';
            const formType = form.dataset.userForm;

            if (!nama || !username || !nip) {
                event.preventDefault();

                Swal.fire({
                    title: 'Data belum lengkap',
                    text: 'Nama, username, dan NIP wajib diisi.',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });

                return;
            }

            if (formType === 'create' && password.length < 6) {
                event.preventDefault();

                Swal.fire({
                    title: 'Password terlalu pendek',
                    text: 'Password minimal 6 karakter.',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });

                passwordInput?.focus();
                return;
            }

            if (role !== 'admin' && !timkerja) {
                event.preventDefault();

                Swal.fire({
                    title: 'Tim kerja belum dipilih',
                    text: 'Untuk role operator atau viewer, tim kerja wajib diisi.',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });

                timkerjaSelect?.focus();
                return;
            }

            if (formType === 'edit' && password.length > 0 && password.length < 6) {
                event.preventDefault();

                Swal.fire({
                    title: 'Password terlalu pendek',
                    text: 'Jika ingin mengganti password, isi minimal 6 karakter.',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });

                passwordInput?.focus();
            }
        });
    });

    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', function(){
            let form = this.closest('form');

            Swal.fire({
                title:'Hapus user?',
                text:'Data akan dihapus permanen',
                icon:'warning',
                showCancelButton:true,
                confirmButtonText:'Ya',
                cancelButtonText:'Batal'
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
