@extends('layouts.sidebarmenu')

@section('content')
@php($prefix = strtolower(Auth::user()->role ?? 'admin'))
@php($canManage = in_array($prefix, ['admin', 'operator'], true))

<style>
    .merge-shell {
        background: linear-gradient(135deg, #fff7ed 0%, #ffffff 65%);
        border: 1px solid rgba(245, 158, 11, 0.18);
        border-radius: 28px;
        padding: 28px;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
    }
    .merge-panel {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 24px;
        padding: 24px;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
        height: 100%;
    }
    .merge-section-title {
        font-size: 0.85rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #92400e;
        margin-bottom: 14px;
    }
    .merge-summary {
        border-radius: 18px;
        background: #fff7ed;
        border: 1px dashed #f59e0b;
        padding: 16px 18px;
    }
    .selected-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        border-radius: 999px;
        background: #ffedd5;
        color: #9a3412;
        font-weight: 700;
        font-size: 0.82rem;
        margin: 0 8px 8px 0;
    }
    .sop-pick-group {
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        padding: 18px;
        margin-bottom: 16px;
        background: #fff;
    }
    .sop-pick-item {
        border: 1px solid #f1f5f9;
        border-radius: 16px;
        padding: 14px 16px;
        background: #f8fafc;
        margin-bottom: 12px;
    }
    .sop-pick-item:last-child {
        margin-bottom: 0;
    }
    .sop-pick-item.disabled-item {
        opacity: 0.55;
        background: #f8fafc;
    }
    .summary-stat {
        border-radius: 20px;
        border: 1px solid #fed7aa;
        background: #fff;
        padding: 18px 20px;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
    }
    .folder-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }
    .folder-card {
        display: block;
        text-decoration: none;
        color: inherit;
        border-radius: 22px;
        padding: 20px;
        background: linear-gradient(180deg, #ffffff 0%, #fff7ed 100%);
        border: 1px solid #fed7aa;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }
    .folder-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 18px 34px rgba(15, 23, 42, 0.08);
        border-color: #f59e0b;
    }
    .folder-icon {
        width: 50px;
        height: 50px;
        border-radius: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #ffedd5;
        color: #c2410c;
        font-size: 1.5rem;
        margin-bottom: 14px;
    }
    .folder-count {
        font-size: 0.8rem;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #9a3412;
        margin-bottom: 8px;
    }
    .folder-title {
        font-size: 1rem;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 6px;
    }
    .folder-subtitle {
        color: #64748b;
        font-size: 0.9rem;
        margin: 0;
    }
    .sop-readonly-group {
        border: 1px solid #e5e7eb;
        border-radius: 18px;
        padding: 18px;
        margin-bottom: 16px;
        background: #fff;
    }
    .sop-readonly-item {
        border: 1px solid #f1f5f9;
        border-radius: 16px;
        padding: 14px 16px;
        background: #f8fafc;
        margin-bottom: 12px;
    }
    .sop-readonly-item:last-child {
        margin-bottom: 0;
    }
</style>

<div class="container-fluid py-4">
    <div class="merge-shell mb-4">
        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
            <div>
                <div class="text-uppercase small fw-bold text-warning-emphasis mb-2">Revisi SOP</div>
                <h3 class="fw-bold text-dark mb-2">Revisi Gabungan SOP</h3>
                <p class="text-muted mb-0">Pilih minimal 2 SOP aktif sebagai sumber revisi, unggah file SOP baru hasil gabungan, lalu sistem akan menonaktifkan SOP sumber secara otomatis.</p>
            </div>
            <a href="{{ route($prefix . '.sop.index') }}" class="btn btn-light fw-bold px-4 py-2 border">
                <i class="bi bi-arrow-left me-2"></i>Kembali
            </a>
        </div>
    </div>

    @if(!$canManage)
        <div class="row g-4">
            <div class="col-md-6">
                <div class="summary-stat">
                    <div class="text-muted small text-uppercase fw-bold mb-2">Total Subjek</div>
                    <div class="display-6 fw-bold text-dark mb-0">{{ $summary['total_subjek'] ?? 0 }}</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="summary-stat">
                    <div class="text-muted small text-uppercase fw-bold mb-2">Total SOP Aktif</div>
                    <div class="display-6 fw-bold text-dark mb-0">{{ $summary['total_sop'] ?? 0 }}</div>
                </div>
            </div>
            <div class="col-12">
                <div class="merge-panel">
                    <div class="merge-section-title">Kategori Repositori SOP</div>
                    <p class="text-muted mb-4">Klik folder kategori untuk membuka daftar SOP dan langsung memfilter subjek yang dipilih.</p>

                    <div class="folder-grid">
                        @forelse(($sourceSops ?? collect()) as $subjekName => $items)
                            <a href="{{ route($prefix . '.sop.index', ['nama_subjek' => $subjekName]) }}" class="folder-card">
                                <div class="folder-icon">
                                    <i class="bi bi-folder2-open"></i>
                                </div>
                                <div class="folder-count">{{ $items->count() }} SOP aktif</div>
                                <div class="folder-title">{{ $subjekName }}</div>
                                <p class="folder-subtitle">Buka daftar SOP kategori {{ $subjekName }}.</p>
                            </a>
                        @empty
                            <div class="text-muted">Belum ada kategori SOP yang bisa ditampilkan.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @else
        <form method="POST" action="{{ route($prefix . '.sop.aksescepat.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="merge-panel">
                        <div class="merge-section-title">1. Pilih SOP Sumber</div>
                        <div class="merge-summary mb-4">
                            <div class="fw-bold text-dark mb-2">SOP terpilih</div>
                            <div id="selectedSopPreview">
                                <span class="text-muted small">Belum ada SOP yang dipilih.</span>
                            </div>
                            <div class="small text-muted mt-2">Minimal 2 SOP harus dipilih agar bisa digabung menjadi SOP baru.</div>
                        </div>

                        @forelse(($sourceSops ?? collect()) as $subjekName => $items)
                            <div class="sop-pick-group">
                                <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                                    <div>
                                        <div class="fw-bold text-dark">{{ $subjekName }}</div>
                                        <div class="text-muted small">{{ $items->count() }} SOP aktif tersedia</div>
                                    </div>
                                </div>

                                @foreach($items as $item)
                                    <label class="sop-pick-item d-block {{ $item->can_revise ? '' : 'disabled-item' }}">
                                        <div class="d-flex align-items-start gap-3">
                                            <input
                                                class="form-check-input mt-1 source-sop-checkbox"
                                                type="checkbox"
                                                name="source_sop_ids[]"
                                                value="{{ $item->id_sop }}"
                                                data-name="{{ $item->nama_sop }}"
                                                {{ $item->can_revise ? '' : 'disabled' }}
                                                {{ in_array($item->id_sop, old('source_sop_ids', [])) ? 'checked' : '' }}>
                                            <div class="flex-grow-1">
                                                <div class="fw-bold text-dark">{{ $item->nama_sop }}</div>
                                                <div class="text-muted small mb-2">{{ $item->nomor_sop }} &middot; {{ $item->subjek?->timkerja?->nama_timkerja ?? 'Tanpa Tim Kerja' }}</div>
                                                <div class="small {{ $item->can_revise ? 'text-success' : 'text-danger' }}">
                                                    {{ $item->revision_message }}
                                                </div>
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        @empty
                            <div class="text-muted">Belum ada SOP aktif yang bisa dipilih sebagai sumber revisi.</div>
                        @endforelse

                        @error('source_sop_ids')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="merge-panel">
                        <div class="merge-section-title">2. Data SOP Hasil Gabungan</div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama SOP Baru</label>
                            <input type="text" name="nama_sop" value="{{ old('nama_sop') }}" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nomor SOP Baru</label>
                            <input type="text" name="nomor_sop" value="{{ old('nomor_sop') }}" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Subjek Tujuan</label>
                            <select name="id_subjek" class="form-select" required>
                                <option value="">Pilih subjek tujuan</option>
                                @foreach($subjek as $item)
                                    <option value="{{ $item->id_subjek }}" {{ (string) old('id_subjek') === (string) $item->id_subjek ? 'selected' : '' }}>
                                        {{ $item->nama_subjek }} @if($item->timkerja) - {{ $item->timkerja->nama_timkerja }} @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Tahun</label>
                            <input type="number" name="tahun" value="{{ old('tahun', date('Y')) }}" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">File SOP Baru (PDF)</label>
                            <input type="file" name="link_sop" class="form-control" accept=".pdf" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Keterangan Revisi</label>
                            <textarea name="keterangan_revisi" rows="4" class="form-control" required>{{ old('keterangan_revisi') }}</textarea>
                        </div>

                        <div class="merge-summary mb-4">
                            <div class="fw-bold text-dark mb-2">Efek saat disimpan</div>
                            <ul class="small text-muted mb-0 ps-3">
                                <li>SOP baru akan dibuat sebagai dokumen aktif.</li>
                                <li>Semua SOP sumber yang dipilih akan berubah menjadi `nonaktif`.</li>
                                <li>Aktivitas revisi gabungan akan tercatat di log sistem.</li>
                            </ul>
                        </div>

                        <button type="submit" class="btn btn-warning text-white fw-bold w-100 py-2" style="background: #f59e0b; border: none;">
                            <i class="bi bi-check2-circle me-2"></i>Simpan Revisi Gabungan
                        </button>
                    </div>
                </div>
            </div>
        </form>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const checkboxes = Array.from(document.querySelectorAll('.source-sop-checkbox'));
        const preview = document.getElementById('selectedSopPreview');

        function renderSelectedSops() {
            if (!preview) {
                return;
            }

            const selected = checkboxes.filter((item) => item.checked);

            if (selected.length === 0) {
                preview.innerHTML = '<span class="text-muted small">Belum ada SOP yang dipilih.</span>';
                return;
            }

            preview.innerHTML = selected.map((item) => {
                const name = item.getAttribute('data-name') || 'SOP';
                return `<span class="selected-chip"><i class="bi bi-file-earmark-check"></i>${name}</span>`;
            }).join('');
        }

        checkboxes.forEach((item) => {
            item.addEventListener('change', renderSelectedSops);
        });

        renderSelectedSops();
    });
</script>
@endsection
