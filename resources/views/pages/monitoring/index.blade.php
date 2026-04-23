@extends('layouts.sidebarmenu')

@section('content')
@php($prefix = strtolower(Auth::user()->role ?? 'admin'))
@php($canManage = in_array($prefix, ['admin', 'operator'], true))

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">Monitoring SOP</h4>
            <p class="text-muted mb-0">Catat hasil monitoring untuk dokumen SOP aktif.</p>
        </div>

        @if($canManage)
            <a href="{{ route($prefix . '.monitoring.create') }}" class="btn btn-primary px-4 fw-bold">
                <i class="bi bi-plus-lg me-2"></i>Tambah Monitoring
            </a>
        @else
            <span class="badge bg-light text-dark border px-3 py-2">Mode baca untuk viewer</span>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-4">{{ session('success') }}</div>
    @endif

    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="px-4">Tanggal</th>
                        <th>SOP</th>
                        <th>Tim Kerja</th>
                        <th>Kriteria</th>
                        <th>Catatan Hasil</th>
                        <th>Tindakan</th>
                        <th class="text-center">{{ $canManage ? 'Aksi' : 'Status' }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($monitorings as $monitoring)
                        <tr>
                            <td class="px-4">{{ \Illuminate\Support\Carbon::parse($monitoring->tanggal)->format('d M Y H:i') }}</td>
                            <td>{{ $monitoring->sop->nama_sop ?? '-' }}</td>
                            <td>{{ $monitoring->sop->subjek->timkerja->nama_timkerja ?? 'Internal' }}</td>
                            <td>{{ $monitoring->kriteria_penilaian }}</td>
                            <td>{{ $monitoring->hasil_monitoring }}</td>
                            <td>
                                <span class="badge {{ ($monitoring->tindakan_yang_harus_diambil ?? '') === 'Perlu Revisi' ? 'bg-warning-subtle text-warning border border-warning-subtle' : 'bg-light text-dark border' }}">
                                    {{ $monitoring->tindakan_yang_harus_diambil ?? '-' }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($canManage)
                                    <form method="POST" action="{{ route($prefix . '.monitoring.destroy', $monitoring->id_monitoring) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                    </form>
                                @else
                                    <span class="badge bg-success-subtle text-success border">Read Only</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">Belum ada data monitoring.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
