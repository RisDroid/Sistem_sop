@extends('layouts.sidebarmenu')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('admin.subjek.index') }}" class="btn btn-white shadow-sm rounded-3 me-3">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h4 class="fw-bold mb-0 text-dark">Tambah Subjek Baru</h4>
            <p class="text-muted small mb-0">Silakan isi formulir di bawah ini untuk menambahkan kategori subjek baru ke sistem.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <form action="{{ route('admin.subjek.store') }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <label for="nama_subjek" class="form-label fw-bold text-secondary small">NAMA SUBJEK</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-tag text-primary"></i></span>
                                <input type="text" name="nama_subjek" id="nama_subjek"
                                    class="form-control form-control-lg @error('nama_subjek') is-invalid @enderror"
                                    placeholder="Contoh: Neraca Wilayah, Distribusi, dll"
                                    value="{{ old('nama_subjek') }}" required autocomplete="off">
                            </div>
                            @error('nama_subjek')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="deskripsi" class="form-label fw-bold text-secondary small">DESKRIPSI (OPSIONAL)</label>
                            <textarea name="deskripsi" id="deskripsi" rows="5"
                                class="form-control rounded-3 @error('deskripsi') is-invalid @enderror"
                                placeholder="Jelaskan cakupan informasi atau tujuan dari subjek ini...">{{ old('deskripsi') }}</textarea>
                            @error('deskripsi')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-secondary small d-block">STATUS LAYANAN</label>
                            <div class="form-check form-check-inline mt-2">
                                <input class="form-check-input" type="radio" name="status" id="statusAktif" value="aktif" checked>
                                <label class="form-check-label" for="statusAktif">Aktif</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="status" id="statusNon" value="nonaktif">
                                <label class="form-check-label" for="statusNon">Non-Aktif</label>
                            </div>
                        </div>

                        <hr class="my-4 border-light">

                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ route('admin.subjek.index') }}" class="btn btn-light px-4 py-2 rounded-3 fw-bold text-secondary">
                                Batal
                            </a>
                            <button type="submit" class="btn btn-primary px-5 py-2 rounded-3 fw-bold shadow-sm">
                                <i class="bi bi-cloud-arrow-up me-2"></i> Simpan Subjek
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card border-0 bg-primary text-white rounded-4 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3"><i class="bi bi-info-circle me-2"></i> Informasi Penting</h6>
                    <p class="small mb-0 opacity-75">
                        Pastikan nama subjek yang dimasukkan belum pernah ada sebelumnya. Subjek digunakan untuk mengkategorikan unit kerja dan file SOP yang akan diunggah nanti.
                    </p>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-dark mb-3">Contoh Subjek BPS</h6>
                    <ul class="list-unstyled mb-0 small text-muted">
                        <li class="mb-2"><i class="bi bi-check2 text-success me-2"></i> Neraca & Analisis Statistik</li>
                        <li class="mb-2"><i class="bi bi-check2 text-success me-2"></i> Statistik Sosial</li>
                        <li class="mb-2"><i class="bi bi-check2 text-success me-2"></i> Statistik Distribusi</li>
                        <li><i class="bi bi-check2 text-success me-2"></i> Integrasi Pengolahan Data</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .btn-white {
        background: #fff;
        border: 1px solid #dee2e6;
        color: #334155;
    }
    .btn-white:hover {
        background: #f8fafc;
        color: #0d6efd;
    }
    .form-control:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1);
    }
    .input-group-text {
        border-right: none;
    }
    .form-control-lg {
        font-size: 1rem;
    }
</style>
@endsection
