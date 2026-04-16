@extends('layouts.sidebarmenu')

@section('content')
<style>
    /* Global Background */
    body { background-color: #f8fafc; }

    /* Header Styling */
    .header-panel {
        background: white;
        border-radius: 24px;
        border-left: 6px solid #0d47a1;
        box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    }

    /* Card Base */
    .main-card {
        border-radius: 24px;
        background: #ffffff;
        border: 1px solid rgba(0,0,0,0.05);
        position: relative;
        overflow: hidden;
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
    }

    /* Hover Effect */
    .main-card:hover {
        transform: translateY(-12px);
        box-shadow: 0 20px 40px rgba(13, 71, 161, 0.15);
        border-color: #0d47a1;
    }

    /* Efek Kilauan (Shimmer) saat Hover */
    .main-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: -100%;
        width: 50%;
        height: 100%;
        background: linear-gradient(to right, transparent, rgba(255,255,255,0.3), transparent);
        transform: skewX(-25deg);
        transition: 0.7s;
    }

    .main-card:hover::before {
        left: 150%;
    }

    /* Icon Wrapper with Glow */
    .icon-wrapper {
        width: 65px;
        height: 65px;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 20px;
        box-shadow: 0 8px 15px rgba(0,0,0,0.1);
        background: #0d47a1; /* Default Color */
    }

    /* Badge Count */
    .badge-count {
        background: #f1f5f9;
        color: #0d47a1;
        padding: 6px 14px;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 800;
        border: 1px solid rgba(0,0,0,0.05);
    }

    /* Action Link */
    .action-link {
        font-size: 13px;
        font-weight: 700;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        color: #0d47a1;
        transition: 0.3s;
    }

    .main-card:hover .action-link {
        letter-spacing: 1px;
    }

    /* Animation Entry */
    .fade-up {
        animation: fadeInUp 0.6s ease-out forwards;
        opacity: 0;
    }

    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<div class="container-fluid py-4">
    <div class="row mb-5 fade-up">
        <div class="col-12">
            <div class="p-4 header-panel d-flex align-items-center justify-content-between">
                <div>
                    <h3 class="fw-bold text-dark mb-1">Pilih Subjek SOP</h3>
                    <p class="text-muted mb-0">Klik pada salah satu kategori untuk membuka daftar dokumen spesifik.</p>
                </div>
                <div class="bg-primary bg-opacity-10 p-3 rounded-circle d-none d-md-block">
                    <i class="bi bi-folder2-open text-primary fs-3"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Data diambil secara dinamis dari database --}}
        @forelse($subjek as $index => $s)
        <div class="col-xl-3 col-lg-4 col-md-6 fade-up" style="animation-delay: {{ $index * 0.05 }}s;">
            <a href="{{ route('admin.sop.index', ['id_subjek' => $s->id_subjek]) }}" class="text-decoration-none">
                <div class="card h-100 main-card">
                    <div class="card-body p-4">
                        {{-- Icon Box: Warna bisa statis atau buat logika warna random --}}
                        <div class="icon-wrapper">
                            <i class="bi bi-file-earmark-richtext text-white fs-3"></i>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="fw-bold text-dark mb-0">{{ $s->nama_subjek }}</h5>
                            {{-- sops_count berasal dari ->withCount('sops') di Controller --}}
                            <span class="badge-count">{{ $s->sops_count ?? 0 }}</span>
                        </div>

                        <p class="text-muted small mb-4" style="line-height: 1.6;">
                            {{ $s->deskripsi ?? 'Akses dokumen SOP untuk kategori ' . $s->nama_subjek }}
                        </p>
                    </div>

                    <div class="card-footer bg-transparent border-0 pb-4 px-4">
                        <div class="action-link d-flex align-items-center">
                            <span>Buka Dokumen SOP</span>
                            <i class="bi bi-arrow-right-circle-fill ms-auto fs-5"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <div class="p-5">
                <i class="bi bi-folder-x display-1 text-muted"></i>
                <p class="mt-3 text-muted">Belum ada subjek yang ditambahkan di Manajemen Subjek.</p>
            </div>
        </div>
        @endforelse
    </div>
</div>

<script>
    // Pastikan animasi tetap jalan saat halaman dimuat
    document.addEventListener('DOMContentLoaded', function() {
        const items = document.querySelectorAll('.fade-up');
        items.forEach((item, index) => {
            item.style.opacity = '1';
        });
    });
</script>
@endsection
