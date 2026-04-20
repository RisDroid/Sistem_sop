<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Monev SOP | BPS Banten</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        :root {
            --bps-blue: #0d47a1;
            --sidebar-width: 270px;
            --top-navbar-height: 70px;
            --bg-body: #f8fafc;
            --border-color: #e2e8f0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-body);
            color: #1e293b;
            margin: 0;
            overflow-x: hidden;
        }

        #wrapper { display: flex; min-height: 100vh; }

        /* --- SIDEBAR --- */
        #sidebar {
            width: var(--sidebar-width);
            background: #ffffff;
            height: 100vh;
            position: fixed;
            left: 0; top: 0;
            z-index: 1050;
            border-right: 1px solid var(--border-color);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
        }

        #sidebar.minimized { transform: translateX(calc(var(--sidebar-width) * -1)); }

        .sidebar-header {
            height: var(--top-navbar-height);
            display: flex;
            align-items: center;
            padding: 0 20px;
            border-bottom: 1px solid var(--border-color);
        }

        .brand-logo { height: 32px; width: auto; margin-right: 12px; }
        .brand-name { font-weight: 700; font-size: 15px; color: #0f172a; display: block; }
        .brand-sub { font-size: 11px; color: #64748b; font-weight: 500; }

        /* --- TOGGLE BUTTON --- */
        #btn-toggle-custom {
            background: #fff;
            border: 1px solid var(--border-color);
            color: #64748b;
            width: 38px; height: 38px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            position: fixed;
            left: 250px;
            top: 16px;
            z-index: 1100;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        #sidebar.minimized + #content #btn-toggle-custom { left: 15px; }

        /* --- MENU --- */
        .nav-menu { padding: 15px 0; overflow-y: auto; flex-grow: 1; }
        .menu-label {
            font-size: 11px; font-weight: 700; color: #94a3b8;
            text-transform: uppercase; letter-spacing: 1px;
            margin: 20px 24px 8px;
        }

        .nav-menu li a {
            padding: 10px 16px;
            display: flex; align-items: center;
            color: #475569; font-weight: 500; text-decoration: none;
            margin: 2px 14px; border-radius: 8px;
            font-size: 14px; transition: 0.2s;
        }

        .nav-menu li a i { font-size: 1.2rem; margin-right: 12px; }
        .nav-menu li a:hover { background: #f1f5f9; color: var(--bps-blue); }
        .nav-menu li.active > a { background: var(--bps-blue); color: #ffffff; }
        .role-pill {
            margin: 14px;
            padding: 14px 16px;
            border-radius: 16px;
            border: 1px solid rgba(15, 23, 42, 0.05);
        }
        .role-pill .small {
            color: #475569;
        }

        /* --- CONTENT AREA --- */
        #content {
            flex-grow: 1;
            margin-left: var(--sidebar-width);
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            min-width: 0;
        }
        #sidebar.minimized + #content { margin-left: 0; }

        .top-navbar {
            height: var(--top-navbar-height);
            background: #ffffff;
            padding: 0 30px 0 70px;
            display: flex; align-items: center;
            border-bottom: 1px solid var(--border-color);
            position: sticky; top: 0; z-index: 1000;
        }

        .user-info { display: flex; align-items: center; gap: 12px; }
        .avatar-box {
            width: 35px; height: 35px;
            background: #e2e8f0; color: var(--bps-blue);
            border-radius: 8px; font-weight: 700;
            display: flex; align-items: center; justify-content: center;
        }

        footer {
            padding: 20px 30px;
            font-size: 13px;
            color: #94a3b8;
            border-top: 1px solid var(--border-color);
            background: #fff;
            margin-top: auto;
        }

        @media (max-width: 992px) {
            #sidebar { transform: translateX(-100%); }
            #sidebar.show-mobile { transform: translateX(0); }
            #content { margin-left: 0 !important; }
            #btn-toggle-custom { left: 15px !important; }
        }
    </style>
</head>
<body>
    @php($role = strtolower(Auth::user()->role ?? 'admin'))
    @php($roleTheme = [
        'admin' => ['accent' => '#0d47a1', 'soft' => '#dbeafe', 'label' => 'Kontrol Penuh Sistem'],
        'operator' => ['accent' => '#0f766e', 'soft' => '#ccfbf1', 'label' => 'Pusat Operasional Tim'],
        'viewer' => ['accent' => '#7c3aed', 'soft' => '#ede9fe', 'label' => 'Mode Lihat Dokumen'],
    ][$role] ?? ['accent' => '#0d47a1', 'soft' => '#dbeafe', 'label' => 'Kontrol Sistem'])

    <div id="wrapper">
        <nav id="sidebar">
            <div class="sidebar-header">
                <img src="https://upload.wikimedia.org/wikipedia/commons/2/28/Lambang_Badan_Pusat_Statistik_%28BPS%29_Indonesia.svg" class="brand-logo" alt="Logo">
                <div class="brand-text">
                    <span class="brand-name">BPS BANTEN</span>
                    <span class="brand-sub">E-Monev SOP</span>
                </div>
            </div>

            <div class="role-pill" style="background: {{ $roleTheme['soft'] }}; color: {{ $roleTheme['accent'] }};">
                <div class="fw-bold text-uppercase small">{{ strtoupper($role) }}</div>
                <div class="small">{{ $roleTheme['label'] }}</div>
            </div>

            <ul class="list-unstyled nav-menu">
                <li class="{{ request()->is('*/dashboard') ? 'active' : '' }}">
                    <a href="{{ route('dashboard') }}">
                        <i class="bi bi-grid-fill"></i> <span>Dashboard</span>
                    </a>
                </li>

                @if($role !== 'viewer')
                    <div class="menu-label">Repositori</div>
                    <li class="{{ request()->routeIs($role . '.sop.*') ? 'active' : '' }}">
                        <a href="{{ route($role . '.sop.index') }}">
                            <i class="bi bi-file-earmark-text"></i> <span>Data SOP</span>
                        </a>
                    </li>
                    <li class="{{ request()->routeIs($role . '.monitoring.*') ? 'active' : '' }}">
                        <a href="{{ route($role . '.monitoring.index') }}">
                            <i class="bi bi-graph-up"></i> <span>Monitoring</span>
                        </a>
                    </li>
                    <li class="{{ request()->routeIs($role . '.evaluasi.*') ? 'active' : '' }}">
                        <a href="{{ route($role . '.evaluasi.index') }}">
                            <i class="bi bi-ui-checks-grid"></i> <span>Evaluasi</span>
                        </a>
                    </li>
                @endif

                @if($role === 'admin')
                    <div class="menu-label">Sistem</div>
                    <li class="{{ request()->routeIs('admin.user.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.user.index') }}">
                            <i class="bi bi-people"></i> <span>Manajemen User</span>
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('admin.timkerja.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.timkerja.index') }}">
                            <i class="bi bi-building"></i> <span>Manajemen Tim Kerja</span>
                        </a>
                    </li>
                    <li class="{{ request()->routeIs('admin.subjek.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.subjek.index') }}">
                            <i class="bi bi-tag"></i> <span>Manajemen Subjek</span>
                        </a>
                    </li>
                @endif
            </ul>

            <div class="p-3 border-top">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-light w-100 text-danger fw-bold border-0 py-2 small">
                        <i class="bi bi-box-arrow-left me-2"></i> Keluar
                    </button>
                </form>
            </div>
        </nav>

        <div id="content">
            <div id="btn-toggle-custom">
                <i class="bi bi-list"></i>
            </div>

            <header class="top-navbar">
                <div class="top-title d-none d-md-block fw-semibold text-secondary">
                    Badan Pusat Statistik Provinsi Banten
                </div>

                <div class="ms-auto">
                    <div class="dropdown">
                        <div class="user-info dropdown-toggle" style="cursor:pointer" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="text-end d-none d-sm-block">
                                <div class="fw-bold small text-dark">{{ Auth::user()->nama }}</div>
                                <div class="text-muted" style="font-size: 10px;">{{ strtoupper(Auth::user()->role) }} • {{ $roleTheme['label'] }}</div>
                            </div>
                            <div class="avatar-box shadow-sm">
                                {{ strtoupper(substr(Auth::user()->nama, 0, 1)) }}
                            </div>
                        </div>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-3 animate slideIn">
                            <li><a class="dropdown-item py-2" href="#"><i class="bi bi-person me-2"></i>Profil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger fw-bold">
                                        <i class="bi bi-box-arrow-left me-2"></i>Keluar
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>

            <main class="p-4">
                @yield('content')
            </main>

            <footer>
                <div class="d-flex justify-content-between align-items-center">
                    <span>&copy; 2026 Badan Pusat Statistik Provinsi Banten</span>
                    <span class="opacity-50">v1.0.0</span>
                </div>
            </footer>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bundle.min.js"></script>

    <script>
        $(document).ready(function () {
            const sidebar = $('#sidebar');
            const btnToggle = $('#btn-toggle-custom');

            // Sidebar Toggle Logic
            btnToggle.on('click', function() {
                if($(window).width() > 992) {
                    sidebar.toggleClass('minimized');
                } else {
                    sidebar.toggleClass('show-mobile');
                }
            });

            // Close sidebar mobile when click outside
            $(document).on('click', function (e) {
                if ($(window).width() <= 992) {
                    if (!sidebar.is(e.target) && sidebar.has(e.target).length === 0 && !btnToggle.is(e.target) && btnToggle.has(e.target).length === 0) {
                        sidebar.removeClass('show-mobile');
                    }
                }
            });

        });
    </script>
</body>
</html>
