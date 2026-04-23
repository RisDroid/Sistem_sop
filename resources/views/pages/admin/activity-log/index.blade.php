@extends('layouts.sidebarmenu')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">Log Aktivitas Sistem</h4>
            <div class="text-muted">Riwayat aktivitas utama pengguna di dalam sistem.</div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Modul</label>
                    <select name="modul" class="form-select">
                        <option value="">Semua modul</option>
                        @foreach($moduls as $modul)
                            <option value="{{ $modul }}" {{ request('modul') === $modul ? 'selected' : '' }}>{{ $modul }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Aksi</label>
                    <select name="aksi" class="form-select">
                        <option value="">Semua aksi</option>
                        @foreach($aksis as $aksi)
                            <option value="{{ $aksi }}" {{ request('aksi') === $aksi ? 'selected' : '' }}>{{ ucfirst($aksi) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('admin.activity-log.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="px-4">Waktu</th>
                        <th>Pengguna</th>
                        <th>Modul</th>
                        <th>Aksi</th>
                        <th>Deskripsi</th>
                        <th>Subjek</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td class="px-4">{{ optional($log->created_date)->format('d M Y H:i:s') ?? '-' }}</td>
                            <td>
                                <div class="fw-semibold">{{ $log->nama_user ?? 'Guest' }}</div>
                                <small class="text-muted text-uppercase">{{ $log->role_user ?? '-' }}</small>
                            </td>
                            <td>{{ $log->modul }}</td>
                            <td><span class="badge bg-light text-dark border">{{ ucfirst($log->aksi) }}</span></td>
                            <td style="min-width: 320px;">{{ $log->deskripsi }}</td>
                            <td>
                                @if($log->subjek_tipe || $log->subjek_id)
                                    <span class="text-muted">{{ $log->subjek_tipe ?? '-' }}</span><br>
                                    <small class="text-muted">{{ $log->subjek_id ?? '-' }}</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">Belum ada aktivitas yang tercatat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
            <div class="card-body border-top">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
