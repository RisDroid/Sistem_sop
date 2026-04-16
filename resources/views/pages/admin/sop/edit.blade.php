@extends('layouts.sidebarmenu')

@section('content')
<div class="container-fluid py-4">
    <div class="row align-items-center mb-4">
        <div class="col">
            <h4 class="fw-bold text-dark mb-1">Perbarui Dokumen SOP</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.sop.index') }}" class="text-decoration-none text-muted">Daftar SOP</a></li>
                    <li class="breadcrumb-item active text-primary fw-bold">Edit SOP</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius: 20px; background: #ffffff;">
        <div class="card-body p-4">
            <form action="{{ route('admin.sop.update', $sop->id_sop) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold small text-muted">Nama SOP</label>
                        <input type="text" name="nama_sop" class="form-control"
                               value="{{ old('nama_sop', $sop->nama_sop) }}"
                               style="border-radius: 12px; padding: 12px; border: 1px solid #e2e8f0;" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold small text-muted">Nomor SOP</label>
                        <input type="text" name="nomor_sop" class="form-control"
                               value="{{ old('nomor_sop', $sop->nomor_sop) }}"
                               style="border-radius: 12px; padding: 12px; border: 1px solid #e2e8f0;" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold small text-muted">Subjek</label>
                        <select name="id_subjek" class="form-select" style="border-radius: 12px; padding: 12px; border: 1px solid #e2e8f0;" required>
                            @foreach($subjek as $s)
                                <option value="{{ $s->id_subjek }}" {{ $sop->id_subjek == $s->id_subjek ? 'selected' : '' }}>
                                    {{ $s->nama_subjek }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold small text-muted">Tim Kerja</label>
                        <input type="text" class="form-control bg-light"
                               value="{{ $sop->subjek->timkerja->nama_timkerja ?? 'Mengikuti subjek' }}"
                               style="border-radius: 12px; padding: 12px; border: 1px solid #e2e8f0;"
                               readonly>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold small text-muted">Tahun Terbit</label>
                        <input type="number" name="tahun" class="form-control"
                               value="{{ old('tahun', date('Y', strtotime($sop->tahun))) }}"
                               style="border-radius: 12px; padding: 12px; border: 1px solid #e2e8f0;" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold small text-muted">Status Dokumen</label>
                        <select name="status" class="form-select" style="border-radius: 12px; padding: 12px; border: 1px solid #e2e8f0;">
                            <option value="aktif" {{ $sop->status == 'aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="revisi" {{ $sop->status == 'revisi' ? 'selected' : '' }}>Revisi</option>
                            <option value="kadaluarsa" {{ $sop->status == 'kadaluarsa' ? 'selected' : '' }}>Kadaluarsa</option>
                            <option value="nonaktif" {{ $sop->status == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>

                    <div class="col-md-12 mb-4">
                        <label class="form-label fw-bold small text-muted">Perbarui Berkas SOP (Format PDF)</label>
                        <input type="file" name="link_sop" class="form-control"
                               style="border-radius: 12px; padding: 12px; border: 1px solid #e2e8f0;" accept=".pdf">
                        <div class="form-text text-muted mt-2">
                            <i class="bi bi-info-circle me-1"></i> Kosongkan jika Anda tidak ingin mengubah berkas yang sudah ada.
                            <br>
                            <span class="badge bg-light text-secondary border mt-1">Berkas saat ini: {{ basename($sop->link_sop) }}</span>
                        </div>
                    </div>
                </div>

                <hr class="my-4" style="opacity: 0.1;">

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.sop.index') }}" class="btn btn-light px-4 fw-bold" style="border-radius: 12px;">Batal</a>
                    <button type="submit" class="btn btn-primary px-5 fw-bold" style="border-radius: 12px; background: #0d47a1; border: none;">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
