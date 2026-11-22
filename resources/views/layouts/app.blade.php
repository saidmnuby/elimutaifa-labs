<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Elimutaifa Labs')</title>
    <style>
        :root{color-scheme:light;--ink:#182230;--muted:#667085;--line:#e4e7ec;--paper:#fff;--wash:#f5f7fa;--blue:#175cd3;--blue-dark:#1849a9;--mint:#dcfae6;--amber:#fef0c7;--red:#fee4e2}
        *{box-sizing:border-box}body{margin:0;background:var(--wash);color:var(--ink);font:15px/1.5 Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif}
        a{color:var(--blue);text-decoration:none}a:hover{text-decoration:underline}.topbar{height:68px;background:var(--paper);border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between;padding:0 clamp(20px,5vw,72px)}
        .brand{font-size:17px;font-weight:750;letter-spacing:-.3px;color:var(--ink)}.brand-mark{display:inline-grid;place-items:center;width:30px;height:30px;margin-right:9px;border-radius:9px;background:#eaf2ff;color:var(--blue);font-size:15px}
        .top-actions{display:flex;align-items:center;gap:16px;color:var(--muted)}.top-actions form{margin:0}.link-button{border:0;background:none;color:var(--blue);font:inherit;cursor:pointer}
        main{width:min(1080px,calc(100% - 36px));margin:40px auto 72px}.page-heading{display:flex;align-items:flex-start;justify-content:space-between;gap:20px;margin-bottom:26px}.eyebrow{margin:0 0 5px;color:var(--blue);font-size:12px;font-weight:750;letter-spacing:.08em;text-transform:uppercase}h1{margin:0;font-size:30px;line-height:1.2;letter-spacing:-.7px}h2{margin:0;font-size:17px}p{margin:0}.muted{color:var(--muted)}.panel,.card{background:var(--paper);border:1px solid var(--line);border-radius:14px;box-shadow:0 2px 8px #1018280a}.panel{padding:24px}.cards{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;margin-bottom:20px}.metric{padding:20px}.metric-label{color:var(--muted);font-size:13px}.metric-value{font-size:28px;font-weight:750;margin-top:7px}.metric-note{color:var(--muted);font-size:12px;margin-top:3px}.section-head{display:flex;justify-content:space-between;align-items:center;gap:15px;margin-bottom:16px}.project-list{display:grid;gap:12px}.project-card{padding:18px;border:1px solid var(--line);border-radius:11px}.project-title{display:flex;justify-content:space-between;gap:14px;align-items:center;margin-bottom:7px}.project-meta{display:flex;flex-wrap:wrap;gap:8px 18px;color:var(--muted);font-size:13px;margin-top:12px}.badge{display:inline-flex;align-items:center;border-radius:999px;padding:4px 10px;font-size:12px;font-weight:700;background:#f2f4f7;color:#344054}.badge.pending{background:var(--amber);color:#93370d}.badge.active{background:var(--mint);color:#067647}.badge.suspended{background:var(--red);color:#b42318}.notice{padding:17px 19px;border-radius:11px;background:#eff8ff;border:1px solid #b2ddff;color:#1849a9;margin-bottom:20px}.notice.warning{background:#fffaeb;border-color:#fedf89;color:#93370d}.auth-wrap{width:min(460px,100%);margin:28px auto}.auth-panel{padding:30px}.auth-panel h1{font-size:25px;margin-bottom:8px}.auth-intro{color:var(--muted);margin-bottom:23px}.field{margin-bottom:17px}.field label{display:block;font-weight:650;font-size:13px;margin-bottom:6px}.field input{display:block;width:100%;height:44px;padding:0 12px;border:1px solid #cfd4dc;border-radius:8px;background:#fff;font:inherit}.field input:focus{outline:3px solid #d1e9ff;border-color:#84caff}.button{display:inline-flex;justify-content:center;align-items:center;min-height:42px;padding:0 16px;border:0;border-radius:8px;background:var(--blue);color:white;font:inherit;font-weight:700;cursor:pointer}.button:hover{background:var(--blue-dark);text-decoration:none}.button.full{width:100%}.form-error{color:#b42318;font-size:13px;margin-top:5px}.auth-footer{margin-top:18px;text-align:center;color:var(--muted);font-size:14px}.empty-state{padding:34px 18px;text-align:center;border:1px dashed #cfd4dc;border-radius:11px;color:var(--muted)}.empty-state strong{display:block;color:var(--ink);margin-bottom:5px}.footer{padding:25px;text-align:center;color:var(--muted);font-size:12px}
        @media(max-width:700px){.topbar{height:60px;padding:0 18px}.top-actions{gap:9px;font-size:13px}main{margin-top:27px}.cards{grid-template-columns:1fr}.page-heading{display:block}h1{font-size:26px}.panel{padding:18px}.auth-panel{padding:24px}}
    </style>
</head>
<body>
    <header class="topbar">
        <a class="brand" href="{{ auth()->check() ? route('dashboard') : route('login') }}"><span class="brand-mark">E</span>elimutaifa labs</a>
        <div class="top-actions">
            @auth
                <span>{{ auth()->user()->name }}</span>
                <form method="post" action="{{ route('logout') }}">@csrf<button class="link-button" type="submit">Sign out</button></form>
            @else
                <a href="{{ route('login') }}">Sign in</a>
            @endauth
        </div>
    </header>
    <main>@yield('content')</main>
    <footer class="footer">Elimutaifa Labs · Project hosting services</footer>
</body>
</html>
