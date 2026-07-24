<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'AbsensiKP')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 250px;
            --sidebar-bg: linear-gradient(180deg, #1e3a5f 0%, #0d2137 100%);
            --topbar-h: 56px;
        }

        * { box-sizing: border-box; }
        body { background: #f4f6f9; margin: 0; font-family: 'Segoe UI', sans-serif; }

        /* ── Sidebar ── */
        .sidebar {
            position: fixed;
            top: 0; left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--sidebar-bg);
            z-index: 1050;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            transition: transform 0.3s ease;
        }
        .sidebar-brand {
            padding: 1.1rem 1.25rem;
            color: #fff;
            font-size: 1.15rem;
            font-weight: 700;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .sidebar nav { padding: 0.5rem 0; }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.75);
            padding: 0.7rem 1.25rem;
            border-radius: 8px;
            margin: 2px 8px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
            white-space: nowrap;
            font-size: 0.9rem;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: #fff;
            background: rgba(255,255,255,0.15);
        }
        .sidebar .nav-link i { width: 18px; text-align: center; flex-shrink: 0; }

        /* ── Overlay (mobile) ── */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1040;
        }
        .sidebar-overlay.show { display: block; }

        /* ── Main Content ── */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── Topbar ── */
        .topbar {
            background: #fff;
            height: var(--topbar-h);
            padding: 0 1.25rem;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
            flex-shrink: 0;
        }
        .topbar-title {
            font-weight: 600;
            color: #374151;
            font-size: 0.95rem;
        }

        /* ── Page body ── */
        .page-body {
            padding: 1.5rem;
            flex: 1;
        }

        /* ── Cards ── */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
        }
        .stat-card {
            border-radius: 12px;
            padding: 1.25rem;
            color: #fff;
        }

        /* ── Table responsive fix ── */
        .table th, .table td {
            vertical-align: middle;
            font-size: 0.875rem;
        }

        /* ── Mobile ── */
        @media (max-width: 767.98px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.open {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
            }
            .page-body {
                padding: 1rem;
            }
            .stat-card { padding: 1rem; }
            .big-clock { font-size: 2.5rem !important; }
        }

        /* ── Tablet ── */
        @media (min-width: 768px) and (max-width: 991.98px) {
            :root { --sidebar-width: 220px; }
        }

        /* ── Alert ── */
        .alert { border-radius: 10px; }
    </style>
    @stack('styles')
</head>
<body>

{{-- Overlay mobile --}}
<div class="sidebar-overlay" id="sidebarOverlay"></div>

{{-- Sidebar --}}
<div class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <span><i class="fas fa-fingerprint me-2"></i>AbsensiKP</span>
        {{-- Tombol tutup sidebar di mobile --}}
        <button class="btn btn-sm text-white d-md-none border-0 p-0" id="btnCloseSidebar">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <nav>
        @yield('sidebar-menu')
    </nav>
</div>

{{-- Main --}}
<div class="main-content" id="mainContent">

    {{-- Topbar --}}
    <div class="topbar">
        <div class="d-flex align-items-center gap-2">
            {{-- Hamburger (semua ukuran, hide di desktop jika mau) --}}
            <button class="btn btn-sm btn-outline-secondary d-md-none" id="btnOpenSidebar">
                <i class="fas fa-bars"></i>
            </button>
            <span class="topbar-title d-none d-sm-block">@yield('title', 'AbsensiKP')</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <div class="d-none d-sm-flex align-items-center gap-1 text-muted small">
                <i class="fas fa-user-circle"></i>
                <span>{{ Auth::user()->name }}</span>
                <span class="badge bg-{{ Auth::user()->isAdmin() ? 'danger' : 'primary' }} ms-1">
                    {{ Auth::user()->isAdmin() ? 'Admin' : 'Karyawan' }}
                </span>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="mb-0">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-danger">
                    <i class="fas fa-sign-out-alt"></i>
                    <span class="d-none d-sm-inline ms-1">Logout</span>
                </button>
            </form>
        </div>
    </div>

    {{-- Alerts --}}
    <div class="px-3 pt-3">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show py-2" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show py-2" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    </div>

    {{-- Content --}}
    <div class="page-body">
        @yield('content')
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const sidebar  = document.getElementById('sidebar');
    const overlay  = document.getElementById('sidebarOverlay');
    const btnOpen  = document.getElementById('btnOpenSidebar');
    const btnClose = document.getElementById('btnCloseSidebar');

    function openSidebar() {
        sidebar.classList.add('open');
        overlay.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
    function closeSidebar() {
        sidebar.classList.remove('open');
        overlay.classList.remove('show');
        document.body.style.overflow = '';
    }

    btnOpen?.addEventListener('click', openSidebar);
    btnClose?.addEventListener('click', closeSidebar);
    overlay?.addEventListener('click', closeSidebar);

    // Tutup sidebar saat klik nav-link di mobile
    document.querySelectorAll('.sidebar .nav-link').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth < 768) closeSidebar();
        });
    });
</script>
@stack('scripts')
</body>
</html>
