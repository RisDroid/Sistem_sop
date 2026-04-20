@extends('layouts.sidebarmenu')

@section('content')
@php($prefix = strtolower(Auth::user()->role) === 'admin' ? 'admin' : 'operator')

<style>
.monitoring-option {
    border: 1px solid #dbe4f0;
    border-radius: 14px;
    padding: 12px 14px;
    transition: .2s ease;
    cursor: pointer;
    background: #fff;
}
.monitoring-option.is-active {
    border-color: #0d6efd;
    background: #eff6ff;
}
.monitoring-option input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
}
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">Tambah Monitoring SOP</h4>
            <p class="text-muted mb-0">Isi form monitoring secara terpisah seperti alur tambah SOP.</p>
        </div>

        <a href="{{ route($prefix . '.monitoring.index') }}" class="btn btn-outline-secondary px-4 fw-bold">
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
            <form method="POST" action="{{ route($prefix . '.monitoring.store') }}" id="monitoringForm">
                @csrf

                <div class="mb-4">
                    <label class="form-label fw-semibold">SOP</label>
                    <select name="id_sop" class="form-select" required>
                        <option value="">Pilih SOP</option>
                        @foreach($sops as $sop)
                            <option value="{{ $sop->id_sop }}" {{ old('id_sop') == $sop->id_sop ? 'selected' : '' }}>{{ $sop->nama_sop }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Kriteria Penilaian</label>
                    <input type="hidden" name="kriteria_penilaian" id="kriteria_penilaian" value="{{ old('kriteria_penilaian') }}">

                    <div class="d-grid gap-2">
                        <label class="monitoring-option {{ old('kriteria_penilaian') === 'Berjalan dengan baik' ? 'is-active' : '' }}">
                            <div class="d-flex align-items-center gap-3">
                                <input type="checkbox"
                                       class="monitoring-checkbox"
                                       value="Berjalan dengan baik"
                                       {{ old('kriteria_penilaian') === 'Berjalan dengan baik' ? 'checked' : '' }}>
                                <div>
                                    <div class="fw-semibold">Berjalan dengan baik</div>
                                    <small class="text-muted">Proses SOP berjalan sesuai ketentuan.</small>
                                </div>
                            </div>
                        </label>

                        <label class="monitoring-option {{ old('kriteria_penilaian') === 'Tidak berjalan dengan baik' ? 'is-active' : '' }}">
                            <div class="d-flex align-items-center gap-3">
                                <input type="checkbox"
                                       class="monitoring-checkbox"
                                       value="Tidak berjalan dengan baik"
                                       {{ old('kriteria_penilaian') === 'Tidak berjalan dengan baik' ? 'checked' : '' }}>
                                <div>
                                    <div class="fw-semibold">Tidak berjalan dengan baik</div>
                                    <small class="text-muted">Proses SOP belum berjalan sesuai ketentuan.</small>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Hasil Monitoring</label>
                    <textarea name="hasil_monitoring" class="form-control" rows="4" required>{{ old('hasil_monitoring') }}</textarea>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Catatan</label>
                    <textarea name="catatan" class="form-control" rows="3">{{ old('catatan') }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary px-4 fw-bold">
                    Simpan Monitoring
                </button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const hiddenInput = document.getElementById('kriteria_penilaian');
    const checkboxes = document.querySelectorAll('.monitoring-checkbox');
    const form = document.getElementById('monitoringForm');

    function syncCheckboxState(activeCheckbox = null) {
        checkboxes.forEach((checkbox) => {
            const wrapper = checkbox.closest('.monitoring-option');

            if (activeCheckbox && checkbox !== activeCheckbox) {
                checkbox.checked = false;
            }

            wrapper?.classList.toggle('is-active', checkbox.checked);
        });

        const selected = Array.from(checkboxes).find((checkbox) => checkbox.checked);
        hiddenInput.value = selected ? selected.value : '';
    }

    checkboxes.forEach((checkbox) => {
        checkbox.addEventListener('change', function () {
            syncCheckboxState(this.checked ? this : null);
        });
    });

    form?.addEventListener('submit', function (event) {
        syncCheckboxState();

        if (!hiddenInput.value) {
            event.preventDefault();
            alert('Pilih salah satu kriteria penilaian terlebih dahulu.');
        }
    });

    syncCheckboxState();
});
</script>
@endsection
