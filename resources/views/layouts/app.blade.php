<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'PasseportSN') — Renouvellement en ligne</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Bootstrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary:      #1a3a6b;
            --primary-dark: #0f2347;
            --accent:       #c8a84b;
            --accent-light: #f0d878;
            --success:      #198754;
            --danger:       #dc3545;
            --warning:      #ffc107;
            --light-bg:     #f8f9fc;
            --sidebar-w:    260px;
            --header-h:     65px;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--light-bg);
            color: #2d3748;
            min-height: 100vh;
        }

        /* ── SIDEBAR ─────────────────────────────── */
        .sidebar {
            position: fixed;
            top: 0; left: 0;
            width: var(--sidebar-w);
            height: 100vh;
            background: var(--primary-dark);
            color: #fff;
            display: flex;
            flex-direction: column;
            z-index: 1000;
            transition: transform .3s ease;
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 20px 24px;
            border-bottom: 1px solid rgba(255,255,255,.1);
        }

        .sidebar-logo .logo-icon {
            width: 42px; height: 42px;
            background: var(--accent);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px; color: var(--primary-dark); font-weight: 800;
        }

        .sidebar-logo span {
            font-family: 'Playfair Display', serif;
            font-size: 1.1rem;
            line-height: 1.2;
        }

        .sidebar-logo small {
            font-size: .68rem;
            opacity: .6;
            display: block;
            font-family: 'Inter', sans-serif;
            font-weight: 400;
            letter-spacing: .05em;
        }

        .sidebar nav { flex: 1; padding: 16px 0; overflow-y: auto; }

        .nav-section-title {
            font-size: .65rem;
            text-transform: uppercase;
            letter-spacing: .12em;
            color: rgba(255,255,255,.35);
            padding: 12px 24px 6px;
            font-weight: 600;
        }

        .sidebar .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 24px;
            color: rgba(255,255,255,.7);
            font-size: .88rem;
            border-radius: 0;
            transition: all .2s;
            position: relative;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: #fff;
            background: rgba(255,255,255,.08);
        }

        .sidebar .nav-link.active::before {
            content: '';
            position: absolute;
            left: 0; top: 0; bottom: 0;
            width: 3px;
            background: var(--accent);
            border-radius: 0 2px 2px 0;
        }

        .sidebar .nav-link i { font-size: 1.05rem; width: 20px; }

        .sidebar-footer {
            padding: 16px 24px;
            border-top: 1px solid rgba(255,255,255,.1);
            font-size: .8rem;
        }

        .badge-notif {
            background: var(--accent);
            color: var(--primary-dark);
            font-size: .65rem;
            padding: 2px 7px;
            border-radius: 10px;
            font-weight: 700;
            margin-left: auto;
        }

        /* ── HEADER ──────────────────────────────── */
        .main-header {
            position: fixed;
            top: 0;
            left: var(--sidebar-w);
            right: 0;
            height: var(--header-h);
            background: #fff;
            border-bottom: 1px solid #e8ecf0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            z-index: 900;
            gap: 16px;
        }

        .header-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--primary);
        }

        .header-actions { display: flex; align-items: center; gap: 12px; }

        .btn-icon {
            width: 38px; height: 38px;
            border-radius: 10px;
            border: 1px solid #e8ecf0;
            background: #fff;
            display: flex; align-items: center; justify-content: center;
            color: #64748b;
            font-size: 1.1rem;
            position: relative;
            cursor: pointer;
            transition: all .2s;
        }
        .btn-icon:hover { background: var(--light-bg); color: var(--primary); }

        .notif-dot {
            position: absolute;
            top: 6px; right: 6px;
            width: 8px; height: 8px;
            background: var(--danger);
            border-radius: 50%;
            border: 2px solid #fff;
        }

        .user-avatar {
            width: 38px; height: 38px;
            border-radius: 10px;
            background: var(--primary);
            color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700;
            font-size: .9rem;
            cursor: pointer;
        }

        /* ── MAIN CONTENT ─────────────────────────── */
        .main-content {
            margin-left: var(--sidebar-w);
            margin-top: var(--header-h);
            padding: 28px;
            min-height: calc(100vh - var(--header-h));
        }

        /* ── CARDS ─────────────────────────────────── */
        .card {
            border: 1px solid #e8ecf0;
            border-radius: 14px;
            box-shadow: 0 1px 3px rgba(0,0,0,.04);
        }

        .card-header {
            background: transparent;
            border-bottom: 1px solid #e8ecf0;
            padding: 18px 24px;
            font-weight: 600;
            font-size: .92rem;
        }

        .stat-card {
            border-radius: 14px;
            padding: 22px;
            color: #fff;
            position: relative;
            overflow: hidden;
        }

        .stat-card::after {
            content: '';
            position: absolute;
            right: -20px; top: -20px;
            width: 100px; height: 100px;
            border-radius: 50%;
            background: rgba(255,255,255,.1);
        }

        .stat-card .stat-icon {
            font-size: 2rem;
            opacity: .8;
            margin-bottom: 12px;
        }

        .stat-card .stat-number {
            font-size: 2rem;
            font-weight: 700;
            line-height: 1;
        }

        .stat-card .stat-label {
            font-size: .8rem;
            opacity: .85;
            margin-top: 4px;
        }

        /* ── STATUS BADGES ────────────────────────── */
        .badge-statut { padding: 5px 12px; border-radius: 20px; font-size: .75rem; font-weight: 600; }

        /* ── TIMELINE ────────────────────────────── */
        .timeline { position: relative; padding-left: 28px; }
        .timeline::before {
            content: '';
            position: absolute;
            left: 8px; top: 8px; bottom: 8px;
            width: 2px;
            background: #e8ecf0;
        }
        .timeline-item { position: relative; margin-bottom: 20px; }
        .timeline-dot {
            position: absolute;
            left: -24px; top: 4px;
            width: 16px; height: 16px;
            border-radius: 50%;
            border: 2px solid #fff;
            box-shadow: 0 0 0 2px #e8ecf0;
        }
        .timeline-dot.active { background: var(--primary); box-shadow: 0 0 0 2px var(--primary); }
        .timeline-dot.done   { background: var(--success); box-shadow: 0 0 0 2px var(--success); }
        .timeline-dot.error  { background: var(--danger);  box-shadow: 0 0 0 2px var(--danger); }

        /* ── FORMS ─────────────────────────────────── */
        .form-label { font-weight: 500; font-size: .87rem; color: #374151; margin-bottom: 6px; }
        .form-control, .form-select {
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: .9rem;
            transition: border-color .2s, box-shadow .2s;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(26,58,107,.1);
        }

        .btn-primary-custom {
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 11px 24px;
            font-weight: 600;
            font-size: .9rem;
            transition: all .2s;
        }
        .btn-primary-custom:hover { background: var(--primary-dark); color: #fff; transform: translateY(-1px); }

        .btn-accent {
            background: var(--accent);
            color: var(--primary-dark);
            border: none;
            border-radius: 10px;
            padding: 11px 24px;
            font-weight: 700;
            transition: all .2s;
        }
        .btn-accent:hover { background: var(--accent-light); transform: translateY(-1px); }

        /* ── RESPONSIVE ───────────────────────────── */
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main-header { left: 0; }
            .main-content { margin-left: 0; padding: 20px 16px; }
        }

        /* ── ALERTS ──────────────────────────────── */
        .alert { border-radius: 12px; font-size: .9rem; }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    </style>

    @stack('styles')
</head>
<body>

{{-- ════ SIDEBAR ════ --}}
<aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <div class="logo-icon">P</div>
        <span>
            PasseportSN
            <small>Renouvellement en ligne</small>
        </span>
    </div>

    <nav>
        @auth
            @if(auth()->user()->isAdmin())
                @include('layouts.sidebar-admin')
            @elseif(auth()->user()->isAgent())
                @include('layouts.sidebar-agent')
            @else
                @include('layouts.sidebar-user')
            @endif
        @endauth
    </nav>

    @auth
        <div class="sidebar-footer">
            <div class="d-flex align-items-center gap-2 mb-2">
                <div class="user-avatar" style="width:30px;height:30px;font-size:.75rem;">
                    {{ strtoupper(substr(auth()->user()->prenom ?? 'U', 0, 1)) }}
                </div>
                <div>
                    <div style="font-size:.8rem;font-weight:600;color:#fff;">{{ auth()->user()->nom_complet ?? 'Utilisateur' }}</div>
                    <div style="font-size:.7rem;color:rgba(255,255,255,.5);">{{ auth()->user()->email }}</div>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-sm w-100" style="background:rgba(255,255,255,.08);color:rgba(255,255,255,.7);border:none;border-radius:8px;">
                    <i class="bi bi-box-arrow-left me-1"></i> Déconnexion
                </button>
            </form>
        </div>
    @endauth
</aside>

{{-- ════ HEADER ════ --}}
<header class="main-header">
    <div class="d-flex align-items-center gap-3">
        <button class="btn-icon d-lg-none" onclick="document.getElementById('sidebar').classList.toggle('open')">
            <i class="bi bi-list"></i>
        </button>
        <h1 class="header-title mb-0">@yield('page-title', 'Tableau de bord')</h1>
    </div>

    <div class="header-actions">
        @auth
            {{-- Notifications --}}
            @php $nbNonLues = auth()->user()->notificationsNonLues()->count(); @endphp
            <div class="dropdown">
                <a href="{{ route('notifications') }}" class="btn-icon text-decoration-none">
                    <i class="bi bi-bell"></i>
                    @if($nbNonLues > 0)
                        <span class="notif-dot"></span>
                    @endif
                </a>
            </div>

            {{-- Avatar --}}
            <div class="dropdown">
                <div class="user-avatar" data-bs-toggle="dropdown">
                    {{ strtoupper(substr(auth()->user()->prenom ?? 'U', 0, 1)) }}
                </div>
                <ul class="dropdown-menu dropdown-menu-end" style="border-radius:12px;">
                    <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person me-2"></i>Profil</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="bi bi-box-arrow-left me-2"></i>Déconnexion
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    @endauth
    </div>
</header>

{{-- ════ MAIN CONTENT ════ --}}
<main class="main-content">
    {{-- Alertes Flash --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="bi bi-check-circle-fill"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @yield('content')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
