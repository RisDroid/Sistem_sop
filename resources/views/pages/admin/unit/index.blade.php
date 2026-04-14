@extends('layouts.sidebarmenu')
@section('content')
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0">Manajemen Unit Kerja</h5>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addUnit">Tambah Unit</button>
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Nama Unit</th>
                    <th>Subjek</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($units as $u)
                <tr>
                    <td>{{ $u->nama_unit }}</td>
                    <td><span class="badge bg-info text-dark">{{ $u->subjek->nama_subjek ?? '-' }}</span></td>
                    <td>
                        <form action="{{ route('admin.unit.destroy', $u->id_unit) }}" method="POST">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="addUnit" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.unit.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header"><h5>Tambah Unit</h5></div>
            <div class="modal-body">
                <select name="id_subjek" class="form-select mb-2" required>
                    @foreach($subjeks as $s)
                    <option value="{{ $s->id_subjek }}">{{ $s->nama_subjek }}</option>
                    @endforeach
                </select>
                <input type="text" name="nama_unit" class="form-control" placeholder="Nama Unit" required>
            </div>
            <div class="modal-footer"><button class="btn btn-primary">Simpan</button></div>
        </form>
    </div>
</div>
@endsection
