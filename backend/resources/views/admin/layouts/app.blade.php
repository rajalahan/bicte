<!doctype html>
<html lang="ne">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') · {{ config('app.name') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Mukta:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root { --np-blue:#0b3d91; --np-red:#dc143c; --np-blue-700:#082c6c; }
        body { font-family: 'Mukta', 'Inter', sans-serif; background: #f4f6fb; }
        .sidebar { background: linear-gradient(180deg,var(--np-blue) 0%,var(--np-blue-700) 100%); color:#fff; min-height:100vh; padding:18px 14px; }
        .sidebar .brand { display:flex; align-items:center; gap:10px; margin-bottom:18px; padding-bottom:14px; border-bottom:1px solid rgba(255,255,255,.15); }
        .sidebar .brand strong { font-size:18px; line-height:1.2; }
        .sidebar a { display:flex; align-items:center; gap:10px; padding:10px 12px; color:rgba(255,255,255,.85); border-radius:10px; text-decoration:none; margin-bottom:4px; font-weight:500; }
        .sidebar a:hover, .sidebar a.active { background: rgba(255,255,255,.15); color:#fff; }
        .sidebar a i { width:22px; text-align:center; }
        .topbar { background:#fff; border-bottom:1px solid #e2e8f0; padding:12px 22px; display:flex; justify-content:space-between; align-items:center; }
        .content { padding: 22px; }
        .card { border: 1px solid #e2e8f0; border-radius: 14px; }
        .badge.bg-new        { background:#dbeafe!important; color:#1e3a8a; }
        .badge.bg-progress   { background:#fef3c7!important; color:#92400e; }
        .badge.bg-resolved   { background:#dcfce7!important; color:#14532d; }
        .badge.bg-closed     { background:#e5e7eb!important; color:#374151; }
        .stat { background:#fff; border:1px solid #e2e8f0; border-radius:14px; padding:18px; }
        .stat__num { font-size:34px; font-weight:800; color:var(--np-blue-700); }
        .stat__label { color:#64748b; }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <aside class="col-md-2 sidebar">
            <div class="brand">
                <i class="bi bi-bank2 fs-3"></i>
                <strong>लहान न.पा.<br><small style="opacity:.85;font-weight:500">Admin Panel</small></strong>
            </div>
            <nav>
                <a href="{{ route('admin.dashboard') }}"   class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"><i class="bi bi-speedometer2"></i> ड्यासबोर्ड</a>
                <a href="{{ route('admin.departments.index') }}" class="{{ request()->routeIs('admin.departments.*') ? 'active' : '' }}"><i class="bi bi-buildings"></i> शाखा</a>
                <a href="{{ route('admin.notices.index') }}"     class="{{ request()->routeIs('admin.notices.*') ? 'active' : '' }}"><i class="bi bi-megaphone-fill"></i> सूचना</a>
                <a href="{{ route('admin.feedback.index') }}"    class="{{ request()->routeIs('admin.feedback.*') ? 'active' : '' }}"><i class="bi bi-chat-left-text-fill"></i> गुनासो</a>
                <a href="{{ route('admin.tokens.index') }}"      class="{{ request()->routeIs('admin.tokens.*') ? 'active' : '' }}"><i class="bi bi-ticket-detailed-fill"></i> टोकन</a>
                <hr style="border-color:rgba(255,255,255,.15)">
                <form method="post" action="{{ route('admin.logout') }}">@csrf
                    <button type="submit" class="btn btn-link text-white p-2 w-100 text-start"><i class="bi bi-box-arrow-right"></i> लग आउट</button>
                </form>
            </nav>
        </aside>

        <main class="col-md-10 p-0">
            <header class="topbar">
                <h5 class="mb-0">@yield('title')</h5>
                <div class="text-muted small"><i class="bi bi-person-circle"></i> {{ auth()->user()?->name }}</div>
            </header>

            <div class="content">
                @if (session('ok'))    <div class="alert alert-success">{{ session('ok') }}</div> @endif
                @if (session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif
                @yield('content')
            </div>
        </main>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
