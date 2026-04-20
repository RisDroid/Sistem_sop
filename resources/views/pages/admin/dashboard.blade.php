@extends('layouts.sidebarmenu')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@php($dashboardRole = $role ?? strtolower(Auth::user()->role ?? 'admin'))
@php($dashboardTheme = [
    'admin' => ['accent' => '#0d47a1', 'badge' => 'Kontrol penuh', 'summary' => 'Pusat kendali untuk seluruh unit kerja.', 'quickLinks' => [
        ['label' => 'Manajemen User', 'route' => 'admin.user.index', 'icon' => 'bi-person-gear', 'tone' => 'text-info'],
        ['label' => 'Monitoring', 'route' => 'admin.monitoring.index', 'icon' => 'bi-bar-chart-steps', 'tone' => 'text-success'],
        ['label' => 'Manajemen Subjek', 'route' => 'admin.subjek.index', 'icon' => 'bi-tags', 'tone' => 'text-secondary'],
    ]],
    'operator' => ['accent' => '#0f766e', 'badge' => 'Operasional', 'summary' => 'Fokus pada input SOP, monitoring, dan evaluasi tim kerja Anda.', 'quickLinks' => [
        ['label' => 'Tambah SOP', 'route' => 'operator.sop.create', 'icon' => 'bi-plus-circle-fill', 'tone' => 'text-primary'],
        ['label' => 'Monitoring', 'route' => 'operator.monitoring.create', 'icon' => 'bi-clipboard2-plus-fill', 'tone' => 'text-success'],
        ['label' => 'Evaluasi', 'route' => 'operator.evaluasi.create', 'icon' => 'bi-ui-checks-grid', 'tone' => 'text-warning'],
    ]],
    'viewer' => ['accent' => '#7c3aed', 'badge' => 'Read only', 'summary' => 'Viewer dapat membuka data yang diizinkan tanpa menu sidebar repositori.', 'quickLinks' => [
        ['label' => 'Lihat SOP', 'route' => 'viewer.sop.aksescepat', 'icon' => 'bi-folder2-open', 'tone' => 'text-primary'],
        ['label' => 'Monitoring', 'route' => 'viewer.monitoring.index', 'icon' => 'bi-graph-up', 'tone' => 'text-success'],
        ['label' => 'Evaluasi', 'route' => 'viewer.evaluasi.index', 'icon' => 'bi-ui-checks-grid', 'tone' => 'text-warning'],
    ]],
][$dashboardRole])

<style>
    /* Animasi masuk */
    .fade-in-up { animation: fadeInUp 0.6s ease-out forwards; }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Top Bar Luxury Style */
    .top-header {
        background: linear-gradient(135deg, {{ $dashboardTheme['accent'] }} 0%, #111827 100%);
        border-radius: 20px;
        padding: 20px 30px;
        box-shadow: 0 16px 32px rgba(15,23,42,0.14);
        margin-bottom: 25px;
        border: none;
    }

    /* Card Stats dengan Gradasi & Soft Shadow */
    .card-stat {
        border: none;
        border-radius: 24px;
        padding: 25px;
        color: white;
        transition: 0.3s ease;
        position: relative;
        overflow: hidden;
        min-height: 140px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .card-stat:hover { transform: translateY(-7px); box-shadow: 0 15px 30px rgba(0,0,0,0.12); }

    .bg-gradient-blue { background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); }
    .bg-gradient-green { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
    .bg-gradient-orange { background: linear-gradient(135deg, #f2994a 0%, #f2c94c 100%); }
    .bg-gradient-red { background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%); }

    /* Icon Glassmorphism effect */
    .stat-icon {
        position: absolute;
        right: -10px;
        bottom: -10px;
        font-size: 80px;
        opacity: 0.15;
        transform: rotate(-15deg);
    }

    /* Navigasi Cepat Style */
    .nav-box {
        background: #ffffff;
        border: 1px solid #eef2f7;
        border-radius: 20px;
        padding: 20px;
        text-align: center;
        text-decoration: none !important;
        transition: 0.3s;
        height: 100%;
        display: block;
    }
    .nav-box:hover {
        background: #f8fafc;
        border-color: #1e3c72;
        box-shadow: 0 10px 20px rgba(30, 60, 114, 0.05);
    }

    /* Digital Clock Header */
    .time-badge {
        background: rgba(255,255,255,0.12);
        color: #ffffff;
        padding: 8px 18px;
        border-radius: 12px;
        font-weight: 700;
        font-family: 'Courier New', Courier, monospace;
        font-size: 1.1rem;
        border: 1px solid rgba(255,255,255,0.18);
    }

    .user-greeting h4 { color: #ffffff; font-weight: 800; margin: 0; }
    .user-greeting p { color: rgba(255,255,255,0.82); font-size: 0.9rem; margin: 0; }
    .role-chip {
        display: inline-flex;
        align-items: center;
        padding: 6px 12px;
        border-radius: 999px;
        background: rgba(255,255,255,0.14);
        border: 1px solid rgba(255,255,255,0.18);
        color: #fff;
        font-size: 0.8rem;
        font-weight: 700;
        margin-top: 10px;
    }
</style>

<div class="container-fluid fade-in-up py-4">

    <div class="top-header d-flex justify-content-between align-items-center">
        <div class="user-greeting d-flex align-items-center gap-3">
            <div class="avatar-wrapper">
                <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->nama) }}&background=1e3c72&color=fff"
                     class="rounded-circle" width="50" alt="Profile">
            </div>
            <div>
                <h4>Selamat Datang, {{ Auth::user()->nama }}! 👋</h4>
                <p>{{ $dashboardTheme['summary'] }}</p>
                <div class="role-chip">{{ strtoupper(Auth::user()->role) }} • {{ $dashboardTheme['badge'] }}</div>
            </div>
        </div>
        <div class="text-end d-none d-md-block">
            <div class="time-badge shadow-sm" id="realtime-clock">00:00:00</div>
            <div class="small fw-bold text-white-50 mt-1">{{ $scopeLabel ?? date('l, d F Y') }}</div>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="card card-stat bg-gradient-blue shadow">
                <i class="bi bi-file-earmark-text stat-icon"></i>
                <div class="fw-bold opacity-75 small">TOTAL DOKUMEN</div>
                <h1 class="fw-extrabold mb-0">{{ $totalSop }}</h1>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-stat bg-gradient-green shadow">
                <i class="bi bi-shield-check stat-icon"></i>
                <div class="fw-bold opacity-75 small">SOP AKTIF</div>
                <h1 class="fw-extrabold mb-0">{{ $aman ?? 0 }}</h1>
            </div>
            
        </div>
        <div class="col-md-3">
            <div class="card card-stat bg-gradient-orange shadow">
                <i class="bi bi-clipboard2-data stat-icon"></i>
                <div class="fw-bold opacity-75 small">MONITORING</div>
                <h1 class="fw-extrabold mb-0">{{ $totalMonitoring ?? 0 }}</h1>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-stat bg-gradient-red shadow">
                <i class="bi bi-ui-checks-grid stat-icon"></i>
                <div class="fw-bold opacity-75 small">EVALUASI</div>
                <h1 class="fw-extrabold mb-0">{{ $totalEvaluasi ?? 0 }}</h1>
            </div>
        </div>
    </div>

    @if(!empty($dashboardTheme['quickLinks']))
        <div class="mb-4">
            <h5 class="fw-bold text-dark mb-3"><i class="bi bi-lightning-fill text-warning me-2"></i>Akses Sesuai Role</h5>
            <div class="row g-3">
                @foreach($dashboardTheme['quickLinks'] as $item)
                    <div class="col-6 col-md-3">
                        <a href="{{ route($item['route']) }}" class="nav-box">
                            <i class="bi {{ $item['icon'] }} {{ $item['tone'] }} fs-2 d-block mb-2"></i>
                            <span class="fw-bold text-dark small">{{ $item['label'] }}</span>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="row g-4 mt-2">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm p-4" style="border-radius: 24px;">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="fw-bold text-dark mb-0">Statistik Nama Subjek</h6>
                    <span class="badge bg-light text-dark rounded-pill">Data Terkini</span>
                </div>
                <div style="height: 320px;">
                    <canvas id="bidangChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm p-4" style="border-radius: 24px;">
                    <h6 class="fw-bold text-dark mb-4">Kesehatan Berkas</h6>
                    <div style="height: 220px;">
                        <canvas id="statusChart"></canvas>
                    </div>
                    <div class="mt-4 pt-3 border-top">
                    <div class="d-flex justify-content-between small text-muted mb-2">
                        <span>SOP revisi:</span>
                        <span class="fw-bold">{{ $review ?? 0 }}</span>
                    </div>
                    <div class="d-flex justify-content-between small text-muted">
                        <span>SOP nonaktif/kadaluarsa:</span>
                        <span class="fw-bold">{{ $kritis ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Real-time Clock Header
    function updateClock() {
        const now = new Date();
        const options = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false };
        document.getElementById('realtime-clock').textContent = now.toLocaleTimeString('id-ID', options);
    }
    setInterval(updateClock, 1000);
    updateClock();

    // Data Dinamis dari HomeController
    const subjekLabels = {!! json_encode($labels) !!};
    const subjekCounts = {!! json_encode($dataCounts) !!};

    // Fungsi untuk generate warna dinamis berdasarkan jumlah label
    function generateColors(count) {
        const colors = [
            '#1e3c72', '#2a5298', '#11998e', '#38ef7d', '#f2994a',
            '#f2c94c', '#eb3349', '#f45c43', '#8e44ad', '#2c3e50',
            '#16a085', '#27ae60', '#2980b9', '#f39c12', '#d35400'
        ];
        let dynamicColors = [];
        for (let i = 0; i < count; i++) {
            // Gunakan warna dari array, jika habis ulangi dari awal
            dynamicColors.push(colors[i % colors.length]);
        }
        return dynamicColors;
    }

    // Chart Bar (Warna Berbeda tiap Subjek)
    new Chart(document.getElementById('bidangChart'), {
        type: 'bar',
        data: {
            labels: subjekLabels,
            datasets: [{
                label: 'Jumlah SOP',
                data: subjekCounts,
                backgroundColor: generateColors(subjekLabels.length), // Menggunakan fungsi warna
                borderRadius: 10,
                barThickness: 30
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    enabled: true,
                    callbacks: {
                        label: function(context) {
                            return ' ' + context.raw + ' Dokumen';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#f0f0f0' },
                    ticks: { stepSize: 1 }
                },
                x: { grid: { display: false } }
            }
        }
    });

    // Chart Doughnut (Status Berkas)
    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: ['Aktif', 'Revisi', 'Nonaktif/Kadaluarsa'],
            datasets: [{
                data: [{{ $aman ?? 0 }}, {{ $review ?? 0 }}, {{ $kritis ?? 0 }}],
                backgroundColor: ['#11998e', '#f2994a', '#eb3349'],
                borderWidth: 0,
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '80%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { usePointStyle: true, padding: 20, font: { size: 12 } }
                }
            }
        }
    });
</script>
@endsection
