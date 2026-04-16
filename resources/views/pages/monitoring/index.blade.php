@extends('layouts.sidebarmenu')

@section('content')
@php($prefix = strtolower(Auth::user()->role) === 'admin' ? 'admin' : 'operator')

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">Monitoring SOP</h4>
            <p class="text-muted mb-0">Catat hasil monitoring untuk dokumen SOP aktif.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-4">{{ session('success') }}</div>
    @endif

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">Tambah Monitoring</h5>
                    <form method="POST" action="{{ route($prefix . '.monitoring.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">SOP</label>
                            <select name="id_sop" class="form-select" required>
                                <option value="">Pilih SOP</option>
                                @foreach($sops as $sop)
                                    <option value="{{ $sop->id_sop }}">{{ $sop->nama_sop }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Kriteria Penilaian</label>
                            <input type="text" name="kriteria_penilaian" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Hasil Monitoring</label>
                            <textarea name="hasil_monitoring" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Catatan</label>
                            <textarea name="catatan" class="form-control" rows="2"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Simpan Monitoring</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-4">Tanggal</th>
                                <th>SOP</th>
                                <th>Petugas</th>
                                <th>Kriteria</th>
                                <th>Hasil</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($monitorings as $monitoring)
                                <tr>
                                    <td class="px-4">{{ \Illuminate\Support\Carbon::parse($monitoring->tanggal)->format('d M Y H:i') }}</td>
                                    <td>{{ $monitoring->sop->nama_sop ?? '-' }}</td>
                                    <td>{{ $monitoring->user->nama ?? '-' }}</td>
                                    <td>{{ $monitoring->kriteria_penilaian }}</td>
                                    <td>{{ $monitoring->hasil_monitoring }}</td>
                                    <td class="text-center">
                                        <form method="POST" action="{{ route($prefix . '.monitoring.destroy', $monitoring->id_monitoring) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">Belum ada data monitoring.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
