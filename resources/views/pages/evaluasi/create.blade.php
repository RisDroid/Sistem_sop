@extends('layouts.sidebarmenu')

@section('content')
@php($prefix = strtolower(Auth::user()->role) === 'admin' ? 'admin' : 'operator')

<style>
.evaluasi-panel {
    border: 1px solid #dbe4f0;
    border-radius: 24px;
    overflow: hidden;
    background: #fff;
}
.evaluasi-header {
    background: linear-gradient(180deg, #dbe8b8 0%, #e8f0cf 100%);
    padding: 28px 24px;
    text-align: center;
    font-weight: 800;
    letter-spacing: .6px;
    color: #1f2937;
    border-bottom: 1px solid #cad5af;
}
.evaluasi-list {
    padding: 24px;
    display: grid;
    gap: 12px;
}
.evaluasi-option {
    border: 1px solid #d7dee8;
    border-radius: 16px;
    padding: 14px 16px;
    background: #fff;
    cursor: pointer;
    transition: .2s ease;
}
.evaluasi-option:hover {
    border-color: #8ab4ff;
    background: #f8fbff;
}
.evaluasi-option.is-active {
    border-color: #0d6efd;
    background: #eef5ff;
    box-shadow: 0 6px 18px rgba(13, 110, 253, 0.08);
}
.evaluasi-option input[type="checkbox"] {
    width: 18px;
    height: 18px;
    margin-top: 2px;
    flex-shrink: 0;
}
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">Tambah Evaluasi SOP</h4>
            <p class="text-muted mb-0">Evaluasi hanya dapat dibuat untuk SOP yang sudah dimonitoring dan fokus pada checklist kriteria penilaian.</p>
        </div>

        <a href="{{ route($prefix . '.evaluasi.index') }}" class="btn btn-outline-secondary px-4 fw-bold">
            <i class="bi bi-arrow-left me-2"></i>Kembali
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-4">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4 p-lg-5">
            <form method="POST" action="{{ route($prefix . '.evaluasi.store') }}" id="evaluasiForm">
                @csrf

                <div class="mb-4">
                    <label class="form-label fw-semibold">Tim Kerja</label>
                    <input type="text"
                           class="form-control"
                           value="{{ $currentTimkerja ?: 'Belum ada tim kerja pada akun ini' }}"
                           readonly>
                    <small class="text-muted">Tim kerja mengikuti akun operator yang sedang login.</small>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">SOP</label>
                    <select name="id_sop" class="form-select" required>
                        <option value="">Pilih SOP</option>
                        @foreach($sops as $sop)
                            <option value="{{ $sop->id_sop }}" {{ old('id_sop') == $sop->id_sop ? 'selected' : '' }}>
                                {{ $sop->nama_sop }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold d-block mb-3">Kriteria Evaluasi Penilaian</label>

                    <div class="evaluasi-panel">
                        <div class="evaluasi-header">
                            KRITERIA EVALUASI PENILAIAN
                        </div>

                        <div class="evaluasi-list">
                            @foreach($kriteriaOptions as $kriteria)
                                <label class="evaluasi-option {{ in_array($kriteria, old('kriteria_evaluasi', []), true) ? 'is-active' : '' }}">
                                    <div class="d-flex align-items-start gap-3">
                                        <input type="checkbox"
                                               name="kriteria_evaluasi[]"
                                               class="evaluasi-checkbox"
                                               value="{{ $kriteria }}"
                                               {{ in_array($kriteria, old('kriteria_evaluasi', []), true) ? 'checked' : '' }}>
                                        <div class="fw-semibold">{{ $kriteria }}</div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary px-4 fw-bold">
                    Simpan Evaluasi
                </button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('evaluasiForm');
    const checkboxes = document.querySelectorAll('.evaluasi-checkbox');

    function syncEvaluasiState() {
        checkboxes.forEach((checkbox) => {
            checkbox.closest('.evaluasi-option')?.classList.toggle('is-active', checkbox.checked);
        });
    }

    checkboxes.forEach((checkbox) => {
        checkbox.addEventListener('change', syncEvaluasiState);
    });

    form?.addEventListener('submit', function (event) {
        const hasChecked = Array.from(checkboxes).some((checkbox) => checkbox.checked);

        if (!hasChecked) {
            event.preventDefault();
            alert('Pilih minimal satu kriteria evaluasi terlebih dahulu.');
        }
    });

    syncEvaluasiState();
});
</script>
@endsection
