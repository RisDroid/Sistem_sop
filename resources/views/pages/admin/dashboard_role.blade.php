@extends('layouts.sidebarmenu')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@php($dashboardRole = $role ?? strtolower(Auth::user()->role ?? 'admin'))
@php($dashboardTheme = [
    'admin' => [
        'accent' => '#0d47a1',
        'accentSoft' => '#dbeafe',
        'accentDark' => '#0f172a',
        'badge' => 'Kontrol Penuh',
        'headline' => 'Dasbor pusat kendali seluruh repositori SOP.',
        'quickLinks' => [
            ['label' => 'Manajemen User', 'route' => 'admin.user.index', 'icon' => 'bi-person-gear'],
            ['label' => 'Data SOP', 'route' => 'admin.sop.index', 'icon' => 'bi-file-earmark-text'],
            ['label' => 'Manajemen Subjek', 'route' => 'admin.subjek.index', 'icon' => 'bi-tags'],
        ],
    ],
    'operator' => [
        'accent' => '#0f766e',
        'accentSoft' => '#ccfbf1',
        'accentDark' => '#12372f',
        'badge' => 'Operasional Tim',
        'headline' => 'Dasbor kerja harian untuk operator.',
        'quickLinks' => [
            ['label' => 'Tambah SOP', 'route' => 'operator.sop.create', 'icon' => 'bi-plus-circle-fill'],
            ['label' => 'Input Monitoring', 'route' => 'operator.monitoring.create', 'icon' => 'bi-clipboard2-plus-fill'],
            ['label' => 'Input Evaluasi', 'route' => 'operator.evaluasi.create', 'icon' => 'bi-ui-checks-grid'],
        ],
    ],
    'viewer' => [
        'accent' => '#7c3aed',
        'accentSoft' => '#ede9fe',
        'accentDark' => '#312e81',
        'badge' => 'Mode Baca',
        'headline' => 'Dasbor referensi untuk kebutuhan pemantauan.',
        'quickLinks' => [
            ['label' => 'Lihat SOP', 'route' => 'viewer.sop.aksescepat', 'icon' => 'bi-folder2-open'],
            ['label' => 'Monitoring', 'route' => 'viewer.monitoring.index', 'icon' => 'bi-graph-up'],
            ['label' => 'Evaluasi', 'route' => 'viewer.evaluasi.index', 'icon' => 'bi-ui-checks-grid'],
            ['label' => 'Laporan Tahunan', 'route' => 'viewer.laporan.tahunan', 'icon' => 'bi-journal-text'],
        ],
    ],
][$dashboardRole])
@php($initial = strtoupper(substr(Auth::user()->nama ?? 'U', 0, 1)))

<style>
    .dashboard-shell { display: grid; gap: 24px; }
    .hero-panel {
        position: relative;
        overflow: hidden;
        border-radius: 28px;
        padding: 28px;
        background:
            radial-gradient(circle at top right, rgba(255,255,255,0.18), transparent 30%),
            linear-gradient(135deg, {{ $dashboardTheme['accent'] }} 0%, {{ $dashboardTheme['accentDark'] }} 100%);
        color: #fff;
        box-shadow: 0 20px 40px rgba(15, 23, 42, 0.16);
    }
    .hero-grid { display: grid; grid-template-columns: minmax(0, 1.8fr) minmax(280px, 1fr); gap: 24px; align-items: stretch; }
    .hero-user { display: flex; align-items: flex-start; gap: 18px; }
    .hero-avatar {
        width: 60px;
        height: 60px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255,255,255,0.16);
        border: 1px solid rgba(255,255,255,0.16);
        font-size: 24px;
        font-weight: 800;
    }
    .hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 14px;
        border-radius: 999px;
        background: rgba(255,255,255,0.12);
        border: 1px solid rgba(255,255,255,0.18);
        font-size: 12px;
        letter-spacing: .08em;
        text-transform: uppercase;
        font-weight: 700;
        margin-bottom: 12px;
    }
    .hero-title { font-size: clamp(1.6rem, 2.5vw, 2.2rem); font-weight: 800; line-height: 1.2; margin-bottom: 10px; }
    .hero-meta { color: rgba(255,255,255,0.82); margin: 14px 0 0; font-size: 0.95rem; }
    .hero-side-card {
        background: rgba(255,255,255,0.08);
        border: 1px solid rgba(255,255,255,0.12);
        border-radius: 22px;
        padding: 22px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .hero-clock { font-size: 2rem; font-weight: 800; line-height: 1; }
    .hero-scope { font-size: 0.95rem; color: rgba(255,255,255,0.82); }
    .stats-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 18px; }
    .stat-card {
        position: relative;
        overflow: hidden;
        border: 1px solid #e5eef8;
        border-radius: 24px;
        background: #fff;
        padding: 22px;
        min-height: 150px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
    }
    .stat-card::after {
        content: '';
        position: absolute;
        inset: auto -30px -30px auto;
        width: 110px;
        height: 110px;
        border-radius: 50%;
        background: {{ $dashboardTheme['accentSoft'] }};
        opacity: 0.8;
    }
    .stat-label { position: relative; z-index: 1; font-size: 0.78rem; text-transform: uppercase; letter-spacing: .08em; color: #64748b; font-weight: 700; }
    .stat-value { position: relative; z-index: 1; font-size: 2.25rem; line-height: 1; font-weight: 800; color: #0f172a; margin: 14px 0 8px; }
    .content-grid { display: grid; grid-template-columns: minmax(0, 2fr) minmax(320px, 1fr); gap: 24px; }
    .dashboard-card { background: #fff; border: 1px solid #e5eef8; border-radius: 26px; box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05); }
    .dashboard-card-body { padding: 24px; }
    .section-head { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 18px; flex-wrap: wrap; }
    .section-title { margin: 0; font-size: 1.05rem; font-weight: 800; color: #0f172a; }
    .pill-soft { display: inline-flex; align-items: center; padding: 7px 12px; border-radius: 999px; background: {{ $dashboardTheme['accentSoft'] }}; color: {{ $dashboardTheme['accentDark'] }}; font-size: 0.8rem; font-weight: 700; }
    .quick-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; }
    .quick-link {
        border-radius: 18px;
        border: 1px solid #e2e8f0;
        padding: 18px;
        text-decoration: none;
        color: inherit;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        display: grid;
        gap: 8px;
        min-height: 124px;
    }
    .quick-link:hover { transform: translateY(-3px); box-shadow: 0 12px 22px rgba(15, 23, 42, 0.08); border-color: {{ $dashboardTheme['accent'] }}; }
    .quick-link i { color: {{ $dashboardTheme['accent'] }}; font-size: 1.5rem; }
    .quick-link strong { color: #0f172a; }
    .list-stack { display: grid; gap: 12px; }
    .list-item { border: 1px solid #e2e8f0; border-radius: 18px; padding: 16px; background: #fff; }
    .list-item-title { font-weight: 700; color: #0f172a; }
    .list-item-meta { color: #64748b; font-size: 0.88rem; margin-top: 4px; }
    .list-item-row { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; }
    .chart-wrap { height: 320px; }
    .chart-wrap.small { height: 250px; }
    .empty-state { border: 1px dashed #cbd5e1; border-radius: 18px; padding: 24px; text-align: center; color: #64748b; background: #f8fafc; }
    @media (max-width: 1199px) {
        .hero-grid,
        .content-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 767px) {
        .stats-grid,
        .quick-grid { grid-template-columns: 1fr; }
        .hero-panel,
        .dashboard-card-body { padding: 20px; }
    }
</style>

<div class="container-fluid py-4">
    <div class="dashboard-shell">
        <section class="hero-panel">
            <div class="hero-grid">
                <div class="hero-user">
                    <div class="hero-avatar">{{ $initial }}</div>
                    <div>
                        <div class="hero-badge">{{ strtoupper($dashboardRole) }} | {{ $dashboardTheme['badge'] }}</div>
                        <div class="hero-title">{{ $dashboardTheme['headline'] }}</div>
                        <p class="hero-meta">
                            Pengguna aktif: <strong>{{ Auth::user()->nama }}</strong>
                            @if(!empty($teamName))
                                | Tim kerja: <strong>{{ $teamName }}</strong>
                            @endif
                        </p>
                    </div>
                </div>
                <div class="hero-side-card">
                    <div class="hero-clock" id="realtime-clock">00:00:00</div>
                    <div class="hero-scope mt-2">{{ $scopeLabel ?? '' }}</div>
                </div>
            </div>
        </section>

        <section class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Total Dokumen Aktif</div>
                <div class="stat-value">{{ $totalSop }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">SOP Sudah Monitoring</div>
                <div class="stat-value">{{ $totalMonitoring ?? 0 }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">SOP Sudah Evaluasi</div>
                <div class="stat-value">{{ $totalEvaluasi ?? 0 }}</div>
            </div>
        </section>

        <section class="content-grid">
            <div class="d-grid gap-4">
                <div class="dashboard-card">
                    <div class="dashboard-card-body">
                        <div class="section-head">
                            <div>
                                <h5 class="section-title">Akses Cepat</h5>
                            </div>
                            <span class="pill-soft">{{ count($dashboardTheme['quickLinks']) }} menu</span>
                        </div>
                        <div class="quick-grid">
                            @foreach($dashboardTheme['quickLinks'] as $item)
                                <a href="{{ route($item['route']) }}" class="quick-link">
                                    <i class="bi {{ $item['icon'] }}"></i>
                                    <strong>{{ $item['label'] }}</strong>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="dashboard-card">
                    <div class="dashboard-card-body">
                        <div class="section-head">
                            <div>
                                <h5 class="section-title">Statistik SOP Aktif per Subjek</h5>
                            </div>
                            <span class="pill-soft">Terkini</span>
                        </div>
                        <div class="chart-wrap">
                            <canvas id="bidangChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="dashboard-card">
                    <div class="dashboard-card-body">
                        <div class="section-head">
                            <div>
                                <h5 class="section-title">SOP Menunggu Evaluasi</h5>
                            </div>
                            <span class="pill-soft">{{ ($pendingEvaluasiSops ?? collect())->count() }} item</span>
                        </div>
                        @if(($pendingEvaluasiSops ?? collect())->isNotEmpty())
                            <div class="list-stack">
                                @foreach($pendingEvaluasiSops as $pendingSop)
                                    <div class="list-item">
                                        <div class="list-item-row">
                                            <div>
                                                <div class="list-item-title">{{ $pendingSop->nama_sop }}</div>
                                                <div class="list-item-meta">
                                                    {{ $pendingSop->subjek?->nama_subjek ?? 'Tanpa Subjek' }} | {{ $pendingSop->subjek?->timkerja?->nama_timkerja ?? 'Internal' }}
                                                </div>
                                            </div>
                                            <span class="pill-soft">Belum Evaluasi</span>
                                        </div>
                                        <div class="list-item-meta mt-3">
                                            Monitoring terakhir:
                                            {{ $pendingSop->latestMonitoring?->tanggal ? \Illuminate\Support\Carbon::parse($pendingSop->latestMonitoring->tanggal)->format('d M Y H:i') : '-' }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="empty-state">Tidak ada SOP yang tertunda untuk evaluasi saat ini.</div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="d-grid gap-4">
                <div class="dashboard-card">
                    <div class="dashboard-card-body">
                        <div class="section-head">
                            <div>
                                <h5 class="section-title">Progres SOP</h5>
                            </div>
                        </div>
                        <div class="chart-wrap small">
                            <canvas id="statusChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="dashboard-card">
                    <div class="dashboard-card-body">
                        <div class="section-head">
                            <div>
                                <h5 class="section-title">Aktivitas Terbaru</h5>
                            </div>
                        </div>
                        @if(($recentActivities ?? collect())->isNotEmpty())
                            <div class="list-stack">
                                @foreach($recentActivities as $activity)
                                    <div class="list-item">
                                        <div class="list-item-row">
                                            <div>
                                                <div class="list-item-title">{{ $activity['type'] }} | {{ $activity['title'] }}</div>
                                                <div class="list-item-meta">{{ $activity['subtitle'] }}</div>
                                            </div>
                                            <span class="pill-soft">{{ $activity['type'] }}</span>
                                        </div>
                                        <div class="list-item-meta mt-3">
                                            Oleh {{ $activity['actor'] }} pada
                                            {{ $activity['date'] ? \Illuminate\Support\Carbon::parse($activity['date'])->format('d M Y H:i') : '-' }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="empty-state">Belum ada aktivitas terbaru untuk ditampilkan.</div>
                        @endif
                    </div>
                </div>

                <div class="dashboard-card">
                    <div class="dashboard-card-body">
                        <div class="section-head">
                            <div>
                                <h5 class="section-title">SOP Aktif Terbaru</h5>
                            </div>
                        </div>
                        @if(($recentSops ?? collect())->isNotEmpty())
                            <div class="list-stack">
                                @foreach($recentSops as $sop)
                                    <div class="list-item">
                                        <div class="list-item-title">{{ $sop->nama_sop }}</div>
                                        <div class="list-item-meta">
                                            {{ $sop->subjek?->nama_subjek ?? 'Tanpa Subjek' }} | {{ $sop->subjek?->timkerja?->nama_timkerja ?? 'Internal' }}
                                        </div>
                                        <div class="list-item-meta mt-2">
                                            Nomor SOP: {{ $sop->nomor_sop ?? '-' }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="empty-state">Belum ada SOP aktif yang bisa ditampilkan.</div>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<script>
    let serverNow = new Date(@json(now('Asia/Bangkok')->format('Y-m-d\\TH:i:sP')));

    function updateClock() {
        const options = {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false,
            timeZone: 'Asia/Bangkok'
        };

        document.getElementById('realtime-clock').textContent = serverNow.toLocaleTimeString('id-ID', options);
        serverNow = new Date(serverNow.getTime() + 1000);
    }

    updateClock();
    setInterval(updateClock, 1000);

    const subjekLabels = {!! json_encode($labels) !!};
    const subjekCounts = {!! json_encode($dataCounts) !!};

    function generateColors(count) {
        const colors = ['#0d47a1', '#0f766e', '#f59e0b', '#ef4444', '#7c3aed', '#0891b2', '#475569', '#16a34a'];
        const results = [];

        for (let index = 0; index < count; index += 1) {
            results.push(colors[index % colors.length]);
        }

        return results;
    }

    new Chart(document.getElementById('bidangChart'), {
        type: 'bar',
        data: {
            labels: subjekLabels,
            datasets: [{
                label: 'Jumlah SOP Aktif',
                data: subjekCounts,
                backgroundColor: generateColors(subjekLabels.length),
                borderRadius: 10,
                borderSkipped: false,
                barThickness: 30
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });

    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: ['Belum Monitoring', 'Sudah Monitoring, Belum Evaluasi', 'Sudah Evaluasi'],
            datasets: [{
                data: [{{ $belumMonitoring ?? 0 }}, {{ $monitoringBelumEvaluasi ?? 0 }}, {{ $sudahEvaluasi ?? 0 }}],
                backgroundColor: ['#ef4444', '#f59e0b', '{{ $dashboardTheme['accent'] }}'],
                borderWidth: 0,
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '72%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 18
                    }
                }
            }
        }
    });
</script>
@endsection
