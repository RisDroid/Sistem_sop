@extends('layouts.sidebarmenu')

@section('content')
@php($prefix = strtolower(Auth::user()->role ?? 'viewer'))
@php($evaluasiChecklist = [
    'Mampu mendorong peningkatan kinerja',
    'Mudah dipahami',
    'Mudah dilaksanakan',
    'Semua orang dapat menjalankan perannya masing-masing',
    'Mampu mengatasi permasalahan yang berkaitan dengan proses',
    'Mampu menjawab kebutuhan peningkatan kinerja organisasi',
])

<style>
    .report-shell {
        display: grid;
        gap: 22px;
    }
    .report-toolbar {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 22px;
        padding: 20px 22px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
    }
    .report-paper {
        background: #fff;
        border: 1px solid #d1d5db;
        border-radius: 18px;
        padding: 28px;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.06);
    }
    .report-heading {
        text-align: center;
        color: #111827;
        margin-bottom: 24px;
        line-height: 1.35;
    }
    .report-heading-title {
        font-size: 1.15rem;
        font-weight: 800;
        text-transform: uppercase;
    }
    .report-heading-subtitle {
        font-size: 1.15rem;
        font-weight: 800;
        text-transform: uppercase;
    }
    .report-heading-meta {
        font-size: 0.95rem;
        font-weight: 800;
        text-transform: uppercase;
    }
    .report-summary {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 18px;
    }
    .report-stat {
        border: 1px solid #dbeafe;
        border-radius: 16px;
        padding: 14px 16px;
        background: #f8fbff;
    }
    .report-stat-label {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #6b7280;
        font-weight: 700;
    }
    .report-stat-value {
        font-size: 1.7rem;
        font-weight: 800;
        color: #111827;
        margin-top: 8px;
        line-height: 1;
    }
    .report-table {
        width: 100%;
        border-collapse: collapse;
    }
    .report-table th,
    .report-table td {
        border: 1px solid #1f2937;
        padding: 8px 10px;
        vertical-align: top;
        color: #111827;
    }
    .report-table thead th {
        text-align: center;
        font-size: 0.88rem;
        font-weight: 800;
    }
    .th-no,
    .th-monitoring-sub {
        background: #8db4e2;
    }
    .th-sop {
        background: #8db4e2;
    }
    .th-evaluasi {
        background: #d8e4bc;
    }
    .th-monitoring {
        background: #fabf8f;
    }
    .th-monitoring-detail {
        background: #f3f4f6;
        font-size: 0.82rem;
    }
    .unit-row td {
        background: #dbeef4;
        font-weight: 800;
        text-transform: uppercase;
    }
    .cell-center {
        text-align: center;
    }
    .sop-name {
        font-weight: 700;
        margin-top: 4px;
    }
    .compact-list {
        margin: 0;
        padding-left: 18px;
    }
    .compact-list li {
        margin-bottom: 4px;
    }
    .check-list {
        list-style: none;
        margin: 0;
        padding: 0;
    }
    .check-list li {
        margin-bottom: 3px;
    }
    .check-mark {
        font-weight: 800;
        display: inline-block;
        width: 18px;
    }
    .report-empty {
        border: 1px dashed #cbd5e1;
        border-radius: 18px;
        padding: 24px;
        text-align: center;
        color: #6b7280;
        background: #f8fafc;
    }
    @media (max-width: 991px) {
        .report-summary {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 767px) {
        .report-summary {
            grid-template-columns: 1fr;
        }
        .report-paper,
        .report-toolbar {
            padding: 18px;
        }
    }
    @media print {
        .report-toolbar {
            display: none;
        }
        .report-paper {
            box-shadow: none;
            border: none;
            padding: 0;
        }
    }
</style>

<div class="container-fluid py-4">
    <div class="report-shell">
        <section class="report-toolbar">
            <form method="GET" action="{{ route($prefix . '.laporan.tahunan') }}" class="d-flex justify-content-between align-items-end gap-3 flex-wrap">
                <div>
                    <div class="fw-bold text-dark mb-1">Laporan Tahunan Monitoring dan Evaluasi</div>
                    <div class="text-muted small">Pilih tahun laporan untuk menampilkan rekap monitoring dan evaluasi SOP AP.</div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <label for="tahun" class="form-label mb-0 fw-semibold">Tahun</label>
                    <select name="tahun" id="tahun" class="form-select" onchange="this.form.submit()">
                        @forelse($availableYears as $year)
                            <option value="{{ $year }}" {{ (int) $selectedYear === (int) $year ? 'selected' : '' }}>{{ $year }}</option>
                        @empty
                            <option value="{{ $selectedYear }}">{{ $selectedYear }}</option>
                        @endforelse
                    </select>
                    <button type="button" class="btn btn-light border fw-semibold" onclick="window.print()">Cetak</button>
                </div>
            </form>
        </section>

        <section class="report-paper">
            <div class="report-heading">
                <div class="report-heading-title">Laporan Hasil</div>
                <div class="report-heading-subtitle">Monitoring dan Evaluasi</div>
                <div class="report-heading-meta">Sistem Operasional Prosedur Administrasi Pemerintahan (SOP AP)</div>
                <div class="report-heading-meta">Badan Pusat Statistik Provinsi Banten</div>
                <div class="report-heading-meta">Periode {{ $selectedYear }}</div>
            </div>

            <div class="report-summary">
                <div class="report-stat">
                    <div class="report-stat-label">Total SOP Aktif</div>
                    <div class="report-stat-value">{{ $summary['total_sop'] ?? 0 }}</div>
                </div>
                <div class="report-stat">
                    <div class="report-stat-label">Total Subjek</div>
                    <div class="report-stat-value">{{ $summary['total_subjek'] ?? 0 }}</div>
                </div>
                <div class="report-stat">
                    <div class="report-stat-label">Sudah Monitoring</div>
                    <div class="report-stat-value">{{ $summary['sudah_monitoring'] ?? 0 }}</div>
                </div>
                <div class="report-stat">
                    <div class="report-stat-label">Sudah Evaluasi</div>
                    <div class="report-stat-value">{{ $summary['sudah_evaluasi'] ?? 0 }}</div>
                </div>
            </div>

            @if(($groupedRows ?? collect())->isNotEmpty())
                <div class="table-responsive">
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th rowspan="2" class="th-no">No</th>
                                <th rowspan="2" class="th-sop">Nomor dan Nama SOP AP</th>
                                <th rowspan="2" class="th-evaluasi">Kriteria Evaluasi Penilaian</th>
                                <th colspan="3" class="th-monitoring">Kriteria Penilaian Monitoring</th>
                            </tr>
                            <tr>
                                <th class="th-monitoring-detail">Penilaian Terhadap Penerapan</th>
                                <th class="th-monitoring-detail">Catatan Hasil Penilaian</th>
                                <th class="th-monitoring-detail">Tindakan Yang Harus Diambil</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($groupedRows as $unitName => $rows)
                                <tr class="unit-row">
                                    <td colspan="6">Unit {{ strtoupper($unitName) }}</td>
                                </tr>
                                @foreach($rows as $index => $row)
                                    @php($monitoring = $row->monitoring)
                                    @php($evaluasi = $row->evaluasi)
                                    <tr>
                                        <td class="cell-center">{{ $index + 1 }}</td>
                                        <td>
                                            <div>{{ $row->sop->nomor_sop ?? '-' }}</div>
                                            <div class="sop-name">{{ strtoupper($row->sop->nama_sop ?? '-') }}</div>
                                        </td>
                                        <td>
                                            <ul class="check-list">
                                                @foreach($evaluasiChecklist as $item)
                                                    <li>
                                                        <span class="check-mark">{{ in_array($item, $evaluasi->kriteria_evaluasi ?? [], true) ? '☑' : '☐' }}</span>{{ $item }}
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </td>
                                        <td>
                                            <div><span class="check-mark">{{ ($monitoring->kriteria_penilaian ?? null) === 'Berjalan dengan baik' ? '☑' : '☐' }}</span>Berjalan Dengan Baik</div>
                                            <div class="mt-2"><span class="check-mark">{{ ($monitoring->kriteria_penilaian ?? null) === 'Tidak berjalan dengan baik' ? '☑' : '☐' }}</span>Tidak Berjalan Dengan Baik</div>
                                        </td>
                                        <td>
                                            @php($catatanList = collect(preg_split('/\r\n|\r|\n/', trim((string) ($monitoring->hasil_monitoring ?? $evaluasi->hasil_evaluasi ?? ''))))->filter())
                                            @if($catatanList->isNotEmpty())
                                                <ul class="compact-list">
                                                    @foreach($catatanList as $catatan)
                                                        <li>{{ $catatan }}</li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <span>-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php($tindakanText = $monitoring->tindakan_yang_harus_diambil ?? $evaluasi->tindakan_yang_harus_diambil ?? '')
                                            @php($tindakanList = collect(preg_split('/\r\n|\r|\n/', trim((string) $tindakanText)))->filter())
                                            @if($tindakanList->isNotEmpty())
                                                <ul class="compact-list">
                                                    @foreach($tindakanList as $tindakan)
                                                        <li>{{ $tindakan }}</li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <span>-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="report-empty">Belum ada data monitoring dan evaluasi SOP untuk periode {{ $selectedYear }}.</div>
            @endif
        </section>
    </div>
</div>
@endsection
