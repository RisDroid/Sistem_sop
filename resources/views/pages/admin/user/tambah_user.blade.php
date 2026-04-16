@extends('layouts.sidebarmenu')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* 1. RESET WRAPPER TOTAL - Menghilangkan gap bawaan template */
    .content-wrapper, .main-panel, .content, .main-content {
        padding: 0 !important;
        margin: 0 !important;
        background-color: #f4f7fe;
        overflow-x: hidden;
    }

    /* 2. HEADER BLUE BPS - Dibuat lebar penuh 100% */
    .premium-header {
        background: linear-gradient(135deg, #002d72 0%, #0056b3 100%);
        padding: 40px 30px 100px 30px;
        color: white;
        border-bottom: 5px solid #ffd700;
        width: 100% !important;
        margin: 0 !important;
        position: relative;
    }

    /* 3. POSISI FORM - KUNCI AGAR PAS DENGAN WARNA BIRU */
    .card-form-container {
        margin-top: -65px;
        padding-left: 0px;
        padding-right: 0px; /* DIUBAH KE 0 AGAR PAS DENGAN HEADER */
        width: 100%;
        position: relative;
        z-index: 10;
    }

    .glass-card {
        background: #ffffff;
        border: none;
        /* Radius hanya di kanan agar tidak ada celah di kiri (sidebar) */
        border-radius: 0 10px 10px 0;
        box-shadow: 10px 10px 40px rgba(0,0,0,0.08);
        margin-left: 0px; /* Menempel sidebar */
        margin-right: 0px; /* Menghilangkan gap kanan agar pas dengan biru */
        overflow: hidden;
        width: 100%;
    }

    /* Form Input Style Pemerintah Modern */
    .input-custom {
        border: 1px solid #d1d9e6;
        border-radius: 4px;
        padding: 12px 15px;
        background-color: #ffffff;
        font-size: 0.9rem;
    }

    .input-custom:focus {
        border-color: #002d72;
        box-shadow: 0 0 0 3px rgba(0, 45, 114, 0.1);
        outline: none;
    }

    .form-label-smart {
        font-size: 11px;
        font-weight: 700;
        color: #4a5568;
        text-transform: uppercase;
        margin-bottom: 8px;
        display: block;
        letter-spacing: 0.5px;
    }

    /* Custom SweetAlert */
    .swal2-popup.gov-popup {
        border-radius: 12px !important;
        border-bottom: 5px solid #ffd700 !important;
    }
</style>

<div class="main-content">
    <div class="premium-header">
        <div class="d-flex align-items-center">
            <a href="{{ route('admin.user.index') }}" class="btn btn-link text-white p-0 me-3">
                <i class="bi bi-arrow-left-short fs-1"></i>
            </a>
            <div>
                <h2 class="fw-bold mb-0" style="letter-spacing: -0.5px;">Registrasi Akun Pengguna</h2>
                <span class="badge bg-white text-primary fw-bold px-3" style="font-size: 10px;">INTERNAL SYSTEM BPS</span>
            </div>
        </div>
    </div>

    <div class="card-form-container">
        <div class="glass-card">
            <form action="{{ route('admin.user.store') }}" method="POST">
                @csrf
                <div class="card-body p-4 p-md-5">

                    <div class="row g-4">
                        <div class="col-12 border-bottom pb-2 mb-3">
                            <h6 class="fw-bold text-primary mb-0" style="font-size: 13px;">
                                <i class="bi bi-shield-lock-fill me-2"></i>OTORISASI & IDENTITAS PETUGAS
                            </h6>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label-smart">Nama Lengkap Sesuai SK</label>
                            <input type="text" name="nama" class="form-control input-custom" placeholder="Masukkan nama lengkap..." required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label-smart">NIP (18 Digit)</label>
                            <input type="text" name="nip" class="form-control input-custom" placeholder="Contoh: 199501..." required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label-smart">Username Login</label>
                            <input type="text" name="username" class="form-control input-custom" placeholder="Contoh: budi_bps" required>
                        </div>

                        <div class="col-md-12 mt-4">
                            <label class="form-label-smart">Level Hak Akses (Role)</label>
                            <select name="role" class="form-select input-custom" required>
                                <option value="" selected disabled>Pilih Hak Akses...</option>
                                <option value="Admin">Administrator (Akses Penuh)</option>
                                <option value="Operator">Operator (Entri Data)</option>
                                <option value="Viewer">Viewer (Lihat Data)</option>
                            </select>
                        </div>

                        <div class="col-12 mt-4">
                            <label class="form-label-smart">Kata Sandi Default</label>
                            <input type="password" name="password" class="form-control input-custom" placeholder="Minimal 6 karakter" required>
                        </div>
                    </div>

                    <div class="mt-5 pt-4 border-top d-flex justify-content-end align-items-center gap-3">
                        <a href="{{ route('admin.user.index') }}" class="btn btn-link text-muted fw-bold text-decoration-none small">BATALKAN</a>
                        <button type="submit" class="btn btn-primary px-5 py-2 fw-bold" style="background-color: #002d72; border: none; border-radius: 4px;">
                            SIMPAN DATA USER
                        </button>
                    </div>

                </div>
            </form>
        </div>

        <div class="mt-4 text-center pb-5">
            <p class="text-muted" style="font-size: 11px;">v1.0.0 &copy; 2026 Badan Pusat Statistik Provinsi Banten</p>
        </div>
    </div>
</div>

@if(session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'BERHASIL DISIMPAN',
        text: 'Data akun pengguna telah berhasil masuk sistem.',
        showConfirmButton: false,
        timer: 2000,
        timerProgressBar: true,
        customClass: {
            popup: 'gov-popup'
        }
    });
</script>
@endif

@endsection
