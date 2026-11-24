<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'TechwareTz Control Center (TCC)')</title>
    <meta name="description" content="@yield('meta_description', 'TechwareTz Control Center (TCC) helps developers deploy projects and manage project databases through one simple hosting workspace.')">
    <meta name="robots" content="@yield('robots', 'noindex, nofollow')">
    <meta name="theme-color" content="#0868d7">
    <link rel="canonical" href="@yield('canonical', request()->url())">
    <link rel="icon" type="image/png" href="{{ asset('images/techwaretz-tcc-mark.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/techwaretz-tcc-mark.png') }}">
    <meta property="og:site_name" content="TechwareTz Control Center (TCC)">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:title" content="@yield('og_title', trim($__env->yieldContent('title', 'TechwareTz Control Center (TCC)')))" >
    <meta property="og:description" content="@yield('meta_description', 'TechwareTz Control Center (TCC) helps developers deploy projects and manage project databases through one simple hosting workspace.')">
    <meta property="og:url" content="@yield('canonical', request()->url())">
    <meta property="og:image" content="{{ asset('images/techwaretz-tcc-mark.png') }}">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="@yield('og_title', trim($__env->yieldContent('title', 'TechwareTz Control Center (TCC)')))" >
    <meta name="twitter:description" content="@yield('meta_description', 'TechwareTz Control Center (TCC) helps developers deploy projects and manage project databases through one simple hosting workspace.')">
    <meta name="twitter:image" content="{{ asset('images/techwaretz-tcc-mark.png') }}">
    @stack('structured_data')
    <style>
        :root{color-scheme:light;--ink:#13233a;--muted:#607187;--line:#dbe5f0;--paper:#fff;--wash:#f2f7fc;--blue:#0868d7;--blue-dark:#0755b3;--cyan:#08a9e8;--mint:#dcfae6;--amber:#fef0c7;--red:#fee4e2}
        *{box-sizing:border-box}body{margin:0;background:var(--wash);color:var(--ink);font:15px/1.5 Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif}
        a{color:var(--blue);text-decoration:none}a:hover{text-decoration:underline}.topbar{height:68px;background:var(--paper);border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between;padding:0 clamp(20px,5vw,72px)}
        .brand{display:inline-flex;align-items:center;white-space:nowrap;font-size:17px;font-weight:750;letter-spacing:-.3px;color:var(--ink)}.brand-mark{display:block;width:34px;height:34px;object-fit:contain;margin-right:9px}
        .top-actions{display:flex;align-items:center;gap:16px;color:var(--muted)}.top-actions form{margin:0}.top-actions .nav-links{display:flex;gap:14px}.link-button{border:0;background:none;color:var(--blue);font:inherit;cursor:pointer}
        main{width:min(1080px,calc(100% - 36px));margin:40px auto 72px}.page-heading{display:flex;align-items:flex-start;justify-content:space-between;gap:20px;margin-bottom:26px}.eyebrow{margin:0 0 5px;color:var(--blue);font-size:12px;font-weight:750;letter-spacing:.08em;text-transform:uppercase}h1{margin:0;font-size:30px;line-height:1.2;letter-spacing:-.7px}h2{margin:0;font-size:17px}p{margin:0}.muted{color:var(--muted)}.panel,.card{background:var(--paper);border:1px solid var(--line);border-radius:14px;box-shadow:0 2px 8px #1018280a}.panel{padding:24px}.cards{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;margin-bottom:20px}.metric{padding:20px}.metric-label{color:var(--muted);font-size:13px}.metric-value{font-size:28px;font-weight:750;margin-top:7px}.metric-note{color:var(--muted);font-size:12px;margin-top:3px}.section-head{display:flex;justify-content:space-between;align-items:center;gap:15px;margin-bottom:16px}.project-list{display:grid;gap:12px}.project-card{padding:18px;border:1px solid var(--line);border-radius:11px}.project-title{display:flex;justify-content:space-between;gap:14px;align-items:center;margin-bottom:7px}.project-meta{display:flex;flex-wrap:wrap;gap:8px 18px;color:var(--muted);font-size:13px;margin-top:12px}.badge{display:inline-flex;align-items:center;border-radius:999px;padding:4px 10px;font-size:12px;font-weight:700;background:#f2f4f7;color:#344054}.badge.pending{background:var(--amber);color:#93370d}.badge.active{background:var(--mint);color:#067647}.badge.suspended{background:var(--red);color:#b42318}.notice{padding:17px 19px;border-radius:11px;background:#eff8ff;border:1px solid #b2ddff;color:#1849a9;margin-bottom:20px}.notice.warning{background:#fffaeb;border-color:#fedf89;color:#93370d}.auth-wrap{width:min(460px,100%);margin:28px auto}.auth-panel{padding:30px}.auth-panel h1{font-size:25px;margin-bottom:8px}.auth-intro{color:var(--muted);margin-bottom:23px}.field{margin-bottom:17px}.field label{display:block;font-weight:650;font-size:13px;margin-bottom:6px}.field input{display:block;width:100%;height:44px;padding:0 12px;border:1px solid #cfd4dc;border-radius:8px;background:#fff;font:inherit}.field input:focus{outline:3px solid #d1e9ff;border-color:#84caff}.button{display:inline-flex;justify-content:center;align-items:center;min-height:42px;padding:0 16px;border:0;border-radius:8px;background:var(--blue);color:white;font:inherit;font-weight:700;cursor:pointer}.button:hover{background:var(--blue-dark);text-decoration:none}.button.full{width:100%}.form-error{color:#b42318;font-size:13px;margin-top:5px}.auth-footer{margin-top:18px;text-align:center;color:var(--muted);font-size:14px}.empty-state{padding:34px 18px;text-align:center;border:1px dashed #cfd4dc;border-radius:11px;color:var(--muted)}.empty-state strong{display:block;color:var(--ink);margin-bottom:5px}.footer{padding:25px;text-align:center;color:var(--muted);font-size:12px}
        .success{padding:13px 16px;border-radius:9px;background:var(--mint);color:#067647;margin-bottom:18px}.validation-summary{padding:13px 16px;border-radius:9px;background:var(--red);color:#b42318;margin-bottom:18px}.table-wrap{overflow-x:auto}table{width:100%;border-collapse:collapse;min-width:720px}th,td{text-align:left;padding:12px;border-bottom:1px solid var(--line);vertical-align:top}th{font-size:12px;color:var(--muted);font-weight:700;text-transform:uppercase;letter-spacing:.04em}td{font-size:14px}.button.small{min-height:32px;padding:0 11px;font-size:12px}.button.secondary{background:#eef4ff;color:#1849a9}.button.danger{background:#b42318}.field select,.field textarea{display:block;width:100%;padding:10px 12px;border:1px solid #cfd4dc;border-radius:8px;background:#fff;font:inherit}.field textarea{min-height:84px;resize:vertical}.field select:focus,.field textarea:focus{outline:3px solid #d1e9ff;border-color:#84caff}.check-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.check-option{display:flex;gap:10px;align-items:flex-start;padding:13px;border:1px solid var(--line);border-radius:9px}.check-option input{margin-top:4px}.check-option strong{display:block}.check-option small{display:block;color:var(--muted);margin-top:2px}.pagination{display:flex;gap:12px;justify-content:center;margin-top:18px}
        @media(max-width:700px){.topbar{height:60px;padding:0 18px}.brand{font-size:14px}.brand-mark{width:29px;height:29px;margin-right:6px}.top-actions{gap:9px;font-size:13px}.top-actions>span{display:none}.top-actions .nav-links{gap:8px}main{margin-top:27px}.cards{grid-template-columns:1fr}.page-heading{display:block}h1{font-size:26px}.panel{padding:18px}.auth-panel{padding:24px}.check-grid{grid-template-columns:1fr}}
    </style>
    <style>
        .workspace-shell{width:min(1400px,calc(100% - 36px));margin:32px auto 60px;display:grid;grid-template-columns:230px minmax(0,1fr);gap:24px;align-items:start}
        .workspace-shell main{width:100%;margin:0}
        .developer-sidebar{position:sticky;top:20px;background:var(--paper);border:1px solid var(--line);border-radius:14px;padding:18px 12px;box-shadow:0 2px 8px #1018280a}
        .admin-sidebar{position:sticky;top:20px;background:var(--paper);border:1px solid var(--line);border-radius:14px;padding:18px 12px;box-shadow:0 2px 8px #1018280a}
        .sidebar-heading{padding:0 10px 10px;color:var(--muted);font-size:12px;font-weight:750;letter-spacing:.06em;text-transform:uppercase}
        .sidebar-link{display:flex;align-items:center;gap:10px;padding:10px;border-radius:8px;color:var(--ink);font-weight:600}
        .sidebar-link:hover,.sidebar-link.active{background:#eaf5ff;color:var(--blue-dark);text-decoration:none}
        .sidebar-icon{display:grid;place-items:center;width:24px;height:24px;border-radius:7px;background:#f2f4f7;color:var(--blue);font-size:12px;font-weight:800}
        .system-settings-card{margin-top:14px;padding:12px 8px;border:1px solid var(--line);border-radius:10px;background:#fafbfc}
        .system-settings-card .sidebar-heading{padding-bottom:4px}
        .badge.rejected{background:var(--red);color:#b42318}.cards.developer-metrics{grid-template-columns:repeat(auto-fit,minmax(145px,1fr))}
        .live-check{display:block;margin-top:6px;font-size:12px}.live-check[hidden]{display:none}.live-check[data-state="checking"]{color:var(--muted)}.live-check[data-state="found"]{color:#067647;font-weight:700}.live-check[data-state="not-found"]{color:#b42318;font-weight:700}.live-check[data-state="unknown"]{color:#93370d}
        @media(max-width:800px){.workspace-shell{grid-template-columns:1fr;gap:16px;margin-top:20px}.developer-sidebar,.admin-sidebar{position:static;display:flex;align-items:center;gap:6px;overflow-x:auto;padding:9px}.sidebar-heading{display:none}.sidebar-link{white-space:nowrap;font-size:13px}.system-settings-card{flex:0 0 auto;margin-top:0;padding:0;border:0;background:transparent}.workspace-shell main{margin:0 0 30px}}
    </style>
</head>
<body>
    <header class="topbar">
        <a class="brand" href="{{ auth()->check() ? (auth()->user()->isAdmin() ? route('dashboard') : route('modules.show', 'github-deploy')) : route('login') }}"><img class="brand-mark" src="{{ asset('images/techwaretz-tcc-mark.png') }}" alt=""><span>TechwareTz Control Center (TCC)</span></a>
        <div class="top-actions">
            @auth
                <span>{{ auth()->user()->name }}</span>
                <form method="post" action="{{ route('logout') }}">@csrf<button class="link-button" type="submit">Sign out</button></form>
            @else
                <a href="{{ route('register') }}">Create account</a>
                <a href="{{ route('login') }}">Sign in</a>
            @endauth
        </div>
    </header>
    @auth
        <div class="workspace-shell">
            <nav class="{{ auth()->user()->isAdmin() ? 'admin-sidebar' : 'developer-sidebar' }}" aria-label="{{ auth()->user()->isAdmin() ? 'Admin navigation' : 'Developer services' }}">
                @if (auth()->user()->isAdmin())
                    <div class="sidebar-heading">Admin</div>
                    <a class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><span class="sidebar-icon">O</span>Overview</a>
                    <a class="sidebar-link {{ request()->routeIs('admin.developers*') ? 'active' : '' }}" href="{{ route('admin.developers') }}"><span class="sidebar-icon">D</span>Developers</a>
                    <a class="sidebar-link {{ request()->routeIs('admin.projects*') ? 'active' : '' }}" href="{{ route('admin.projects') }}"><span class="sidebar-icon">P</span>Projects</a>
                    <a class="sidebar-link {{ request()->routeIs('admin.database-requests*') ? 'active' : '' }}" href="{{ route('admin.database-requests') }}"><span class="sidebar-icon">DB</span>Database requests</a>
                    <a class="sidebar-link {{ request()->routeIs('activities.*') ? 'active' : '' }}" href="{{ route('activities.index') }}"><span class="sidebar-icon">A</span>Activity log</a>
                @else
                    <div class="sidebar-heading">Developer services</div>
                    <a class="sidebar-link {{ request()->route('moduleKey') === 'github-deploy' ? 'active' : '' }}" href="{{ route('modules.show', 'github-deploy') }}"><span class="sidebar-icon">G</span>GitHub deployment</a>
                    <a class="sidebar-link {{ request()->route('moduleKey') === 'database' ? 'active' : '' }}" href="{{ route('modules.show', 'database') }}"><span class="sidebar-icon">D</span>Database</a>
                    <a class="sidebar-link {{ request()->routeIs('activities.*') ? 'active' : '' }}" href="{{ route('activities.index') }}"><span class="sidebar-icon">A</span>Activity log</a>
                @endif
                @if (auth()->user()->email_verified_at)
                    <section class="system-settings-card" aria-label="System settings">
                        <div class="sidebar-heading">System settings</div>
                        <a class="sidebar-link {{ request()->routeIs('password.change.form') ? 'active' : '' }}" href="{{ route('password.change.form') }}"><span class="sidebar-icon">S</span>Change password</a>
                        <a class="sidebar-link {{ request()->routeIs('email.change.*') ? 'active' : '' }}" href="{{ route('email.change.form') }}"><span class="sidebar-icon">@</span>Change email</a>
                    </section>
                @endif
            </nav>
    @endauth
    <main>
        @if (session('success'))<div class="success">{{ session('success') }}</div>@endif
        @if ($errors->any())<div class="validation-summary"><strong>Please review the highlighted fields.</strong>@foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
        @yield('content')
    </main>
    @auth
        </div>
    @endauth
    <footer class="footer">TechwareTz Control Center (TCC) · Project hosting services</footer>
</body>
</html>
