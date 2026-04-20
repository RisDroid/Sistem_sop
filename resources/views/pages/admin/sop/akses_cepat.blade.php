@extends('layouts.sidebarmenu')

@section('content')
@php($prefix = strtolower(Auth::user()->role ?? 'admin'))
@php($pageRole = strtolower($role ?? Auth::user()->role ?? 'admin'))
@php($theme = [
    'admin' => ['accent' => '#0d47a1', 'soft' => '#dbeafe', 'surface' => 'linear-gradient(135deg, #eff6ff 0%, #ffffff 70%)', 'label' => 'Seluruh repositori SOP'],
    'operator' => ['accent' => '#0f766e', 'soft' => '#ccfbf1', 'surface' => 'linear-gradient(135deg, #ecfeff 0%, #ffffff 70%)', 'label' => 'Fokus pada SOP tim kerja Anda'],
    'viewer' => ['accent' => '#7c3aed', 'soft' => '#ede9fe', 'surface' => 'linear-gradient(135deg, #f5f3ff 0%, #ffffff 70%)', 'label' => 'Mode baca dokumen aktif'],
][$pageRole])

<style>
    .quick-hero {
        background: {{ $theme['surface'] }};
        border: 1px solid rgba(15, 23, 42, 0.06);
        border-radius: 28px;
        padding: 28px;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
    }
    .role-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 14px;
        border-radius: 999px;
        background: {{ $theme['soft'] }};
        color: {{ $theme['accent'] }};
        font-size: 0.82rem;
        font-weight: 700;
    }
    .summary-card {
        background: #fff;
        border: 1px solid rgba(15, 23, 42, 0.06);
        border-radius: 22px;
        padding: 22px;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
        height: 100%;
    }
    .summary-icon {
        width: 52px;
        height: 52px;
        border-radius: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: {{ $theme['soft'] }};
        color: {{ $theme['accent'] }};
        font-size: 1.35rem;
        margin-bottom: 16px;
    }
    .subject-card {
        border-radius: 24px;
        background: #ffffff;
        border: 1px solid rgba(15, 23, 42, 0.06);
        position: relative;
        overflow: hidden;
        transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
        box-shadow: 0 10px 26px rgba(15, 23, 42, 0.05);
        height: 100%;
    }
    .subject-card:hover {
        transform: translateY(-8px);
        border-color: {{ $theme['accent'] }};
        box-shadow: 0 18px 36px rgba(15, 23, 42, 0.10);
    }
    .subject-card::before {
        content: "";
        position: absolute;
        inset: 0 0 auto 0;
        height: 5px;
        background: linear-gradient(90deg, {{ $theme['accent'] }} 0%, #111827 100%);
    }
    .subject-icon {
        width: 64px;
        height: 64px;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: {{ $theme['soft'] }};
        color: {{ $theme['accent'] }};
        font-size: 1.6rem;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.65);
    }
    .subject-count {
        min-width: 64px;
        padding: 8px 12px;
        border-radius: 14px;
        background: #f8fafc;
        border: 1px solid rgba(15, 23, 42, 0.06);
        color: #0f172a;
        font-weight: 800;
        text-align: center;
    }
    .subject-meta {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 12px;
        border-radius: 999px;
        background: #f8fafc;
        color: #475569;
        font-size: 0.78rem;
        font-weight: 600;
    }
    .subject-action {
        color: {{ $theme['accent'] }};
        font-weight: 700;
        letter-spacing: 0.4px;
        text-transform: uppercase;
        font-size: 0.8rem;
    }
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
    <div class="quick-hero mb-4 fade-up">
        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
            <div>
                <div class="role-badge mb-3">{{ strtoupper($pageRole) }} • {{ $theme['label'] }}</div>
                <h3 class="fw-bold text-dark mb-2">Akses Cepat SOP</h3>
                <p class="text-muted mb-0">Pilih subjek untuk langsung membuka daftar SOP yang relevan dengan hak akses Anda.</p>
            </div>
            <div class="subject-icon d-none d-md-flex">
                <i class="bi bi-lightning-charge-fill"></i>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4 fade-up" style="animation-delay: 0.05s;">
            <div class="summary-card">
                <div class="summary-icon"><i class="bi bi-tags-fill"></i></div>
                <div class="text-muted small fw-semibold">TOTAL SUBJEK</div>
                <div class="display-6 fw-bold text-dark mb-2">{{ $summary['total_subjek'] ?? 0 }}</div>
                <div class="text-muted small">Kategori yang bisa Anda buka sekarang.</div>
            </div>
        </div>
        <div class="col-md-4 fade-up" style="animation-delay: 0.1s;">
            <div class="summary-card">
                <div class="summary-icon"><i class="bi bi-file-earmark-text-fill"></i></div>
                <div class="text-muted small fw-semibold">TOTAL SOP AKTIF</div>
                <div class="display-6 fw-bold text-dark mb-2">{{ $summary['total_sop'] ?? 0 }}</div>
                <div class="text-muted small">Dihitung dari SOP aktif yang berada di masing-masing subjek.</div>
            </div>
        </div>
        <div class="col-md-4 fade-up" style="animation-delay: 0.15s;">
            <div class="summary-card">
                <div class="summary-icon"><i class="bi bi-funnel-fill"></i></div>
                <div class="text-muted small fw-semibold">MODE TAMPILAN</div>
                <div class="h4 fw-bold text-dark mb-2">Per Subjek</div>
                <div class="text-muted small">Setiap card sudah mewakili hasil filter SOP aktif berdasarkan subjek.</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        @forelse($subjek as $index => $s)
            <div class="col-xl-4 col-lg-6 fade-up" style="animation-delay: {{ 0.2 + ($index * 0.05) }}s;">
                <a href="{{ route($prefix . '.sop.index', ['nama_subjek' => $s->nama_subjek]) }}" class="text-decoration-none">
                    <div class="subject-card">
                        <div class="card-body p-4 p-lg-4">
                            <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
                                <div class="subject-icon">
                                    <i class="bi bi-file-earmark-richtext-fill"></i>
                                </div>
                                <div class="subject-count">
                                    {{ $s->visible_sop_count ?? 0 }}
                                    <div class="small fw-semibold text-muted">Aktif</div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                                <h5 class="fw-bold text-dark mb-0">{{ $s->nama_subjek }}</h5>
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <span class="subject-action">Buka Dokumen SOP</span>
                                <i class="bi bi-arrow-right-circle-fill fs-4" style="color: {{ $theme['accent'] }};"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <div class="summary-card p-5">
                    <div class="summary-icon mx-auto"><i class="bi bi-folder-x"></i></div>
                    <h5 class="fw-bold text-dark mt-3">Belum ada subjek tersedia</h5>
                    <p class="text-muted mb-0">Data akses cepat akan muncul di sini setelah subjek dan SOP aktif tersedia.</p>
                </div>
            </div>
        @endforelse
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.fade-up').forEach((item) => {
            item.style.opacity = '1';
        });
    });
</script>
@endsection
