@extends('layouts.sidebarmenu')

@section('content')
<style>
    .table-card { background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; }
    .table-modern { width: 100%; border-collapse: collapse; }
    .table-modern thead { background: #f8fafc; border-bottom: 2px solid #edf2f7; }
    .table-modern th { padding: 12px 15px; text-align: left; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; }
    .table-modern td { padding: 12px 15px; border-bottom: 1px solid #f1f5f9; font-size: 13px; vertical-align: middle; }
    .table-modern tbody tr:nth-child(even) { background-color: #fcfdfe; }
    .badge-soft { padding: 5px 10px; border-radius: 6px; font-weight: 600; font-size: 11px; }
    .audit-text { font-size: 10px; line-height: 1.4; color: #94a3b8; }
    .btn-icon { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; border: 1px solid #e2e8f0; background: #fff; transition: 0.2s; }
    .btn-icon:hover { background: #f8fafc; color: #2563eb; }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Manajemen User</h4>
            <p class="text-muted small mb-0">Kelola data login dan hak akses sistem.</p>
        </div>
        <button class="btn btn-primary px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#modalTambah">
            <i class="bi bi-plus-lg me-2"></i>Tambah User
        </button>
    </div>

    <div class="table-card shadow-sm overflow-hidden">
        <div class="table-responsive">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th width="50" class="text-center">No</th>
                        <th>Nama & NIP</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Subjek</th>
                        <th>Log Audit (C/M)</th>
                        <th width="100" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $index => $u)
                    <tr>
                        <td class="text-center fw-bold text-muted">{{ $index + 1 }}</td>
                        <td>
                            <div class="fw-bold text-dark">{{ $u->nama }}</div>
                            <div class="text-muted small">NIP. {{ $u->nip }}</div>
                        </td>
                        <td><span class="badge bg-light text-primary border">{{ $u->username }}</span></td>
                        <td>
                            <span class="badge-soft {{ $u->role == 'Admin' ? 'bg-danger text-white' : ($u->role == 'Operator' ? 'bg-primary text-white' : 'bg-secondary text-white') }}">
                                {{ $u->role }}
                            </span>
                        </td>
                        <td>{{ $u->subjek->nama_subjek ?? '-' }}</td>
                        <td>
                            <div class="audit-text">
                                <i class="bi bi-plus-circle me-1"></i><b>C:</b> {{ $u->created_date }} (ID:{{ $u->created_by }})<br>
                                <i class="bi bi-pencil-square me-1"></i><b>M:</b> {{ $u->modified_date }} (ID:{{ $u->modified_by }})
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-2">
                                <button class="btn-icon" data-bs-toggle="modal" data-bs-target="#modalEdit{{ $u->id_user }}"><i class="bi bi-pencil"></i></button>
                                <form action="{{ route('admin.user.destroy', $u->id_user) }}" method="POST" onsubmit="return confirm('Hapus user ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-icon text-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>

                    <div class="modal fade" id="modalEdit{{ $u->id_user }}" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow-lg rounded-4">
                                <div class="modal-header border-0 pt-4 px-4">
                                    <h5 class="fw-bold">Edit Data User</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form action="{{ route('admin.user.update', $u->id_user) }}" method="POST">
                                    @csrf @method('PUT')
                                    <div class="modal-body p-4">
                                        <div class="mb-3">
                                            <label class="form-label small fw-bold">NAMA LENGKAP</label>
                                            <input type="text" name="nama" value="{{ $u->nama }}" class="form-control bg-light border-0 py-2" required>
                                        </div>
                                        <div class="row">
                                            <div class="col-6 mb-3"><label class="small fw-bold">NIP</label><input type="text" name="nip" value="{{ $u->nip }}" class="form-control bg-light border-0 py-2" required></div>
                                            <div class="col-6 mb-3"><label class="small fw-bold">USERNAME</label><input type="text" name="username" value="{{ $u->username }}" class="form-control bg-light border-0 py-2" required></div>
                                        </div>
                                        <div class="mb-3"><label class="small fw-bold">PASSWORD (Isi jika ganti)</label><input type="text" name="password" class="form-control bg-light border-0 py-2"></div>
                                        <div class="row">
                                            <div class="col-6 mb-3">
                                                <label class="small fw-bold">ROLE</label>
                                                <select name="role" class="form-select bg-light border-0 py-2">
                                                    <option value="Admin" {{ $u->role == 'Admin' ? 'selected' : '' }}>Admin</option>
                                                    <option value="Operator" {{ $u->role == 'Operator' ? 'selected' : '' }}>Operator</option>
                                                    <option value="Viewer" {{ $u->role == 'Viewer' ? 'selected' : '' }}>Viewer</option>
                                                </select>
                                            </div>
                                            <div class="col-6 mb-3">
                                                <label class="small fw-bold">BIDANG</label>
                                                <select name="id_subjek" class="form-select bg-light border-0 py-2">
                                                    @foreach($subjek as $s)
                                                        <option value="{{ $s->id_subjek }}" {{ $u->id_subjek == $s->id_subjek ? 'selected' : '' }}>{{ $s->nama_subjek }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-0 p-4 pt-0">
                                        <button type="submit" class="btn btn-primary w-100 py-3 fw-bold rounded-3">UPDATE DATA</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pt-4 px-4">
                <h5 class="fw-bold">Tambah User Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.user.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3"><label class="small fw-bold">NAMA LENGKAP</label><input type="text" name="nama" class="form-control bg-light border-0 py-2" required></div>
                    <div class="row">
                        <div class="col-6 mb-3"><label class="small fw-bold">NIP</label><input type="text" name="nip" class="form-control bg-light border-0 py-2" required></div>
                        <div class="col-6 mb-3"><label class="small fw-bold">USERNAME</label><input type="text" name="username" class="form-control bg-light border-0 py-2" required></div>
                    </div>
                    <div class="mb-3"><label class="small fw-bold">PASSWORD</label><input type="text" name="password" class="form-control bg-light border-0 py-2" required></div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="small fw-bold">ROLE</label>
                            <select name="role" class="form-select bg-light border-0 py-2">
                                <option value="Admin">Admin</option>
                                <option value="Operator">Operator</option>
                                <option value="Viewer">Viewer</option>
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="small fw-bold">BIDANG</label>
                            <select name="id_subjek" class="form-select bg-light border-0 py-2">
                                <option value="">-- Pilih --</option>
                                @foreach($subjek as $s)
                                    <option value="{{ $s->id_subjek }}">{{ $s->nama_subjek }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="submit" class="btn btn-primary w-100 py-3 fw-bold rounded-3">SIMPAN USER</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
