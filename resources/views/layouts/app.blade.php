<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <meta name="description" content="AntarIn - Platform Pengiriman Cepat, Aman & Terpercaya">
    <title>@yield('title', 'AntarIn') | AntarIn</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --orange:        #f97316;
            --orange-dark:   #ea580c;
            --orange-light:  #fff7ed;
            --orange-mid:    #fed7aa;
            --green:         #22c55e;
            --red:           #ef4444;
            --yellow:        #eab308;
            --blue:          #3b82f6;
            --gray-50:       #f9fafb;
            --gray-100:      #f3f4f6;
            --gray-200:      #e5e7eb;
            --gray-300:      #d1d5db;
            --gray-500:      #6b7280;
            --gray-700:      #374151;
            --gray-800:      #1f2937;
            --gray-900:      #111827;
            --white:         #ffffff;
            --shadow-sm:     0 1px 3px rgba(0,0,0,.08), 0 1px 2px rgba(0,0,0,.06);
            --shadow-md:     0 4px 12px rgba(0,0,0,.1);
            --shadow-orange: 0 8px 24px rgba(249,115,22,.25);
            --radius-sm:     8px;
            --radius-md:     12px;
            --radius-lg:     16px;
            --radius-xl:     20px;
            --radius-full:   9999px;
        }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'Inter', sans-serif;
            background: #e8eaf0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }

        /* ── Mobile Frame ────────────────────────────── */
        .app-shell {
            width: 100%;
            max-width: 430px;
            min-height: 100vh;
            background: var(--gray-50);
            position: relative;
            box-shadow: 0 0 40px rgba(0,0,0,.15);
            overflow-x: hidden;
        }

        /* ── Scrollbar hide ───────────────────────────── */
        ::-webkit-scrollbar { width: 0; background: transparent; }

        /* ── Typography ──────────────────────────────── */
        h1,h2,h3,h4 { line-height: 1.3; color: var(--gray-900); }

        /* ── Buttons ─────────────────────────────────── */
        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 6px;
            padding: 10px 20px; border-radius: var(--radius-md); font-weight: 600;
            font-size: 14px; cursor: pointer; border: none; transition: all .2s ease;
            text-decoration: none;
        }
        .btn-primary   { background: var(--orange); color: #fff; box-shadow: var(--shadow-orange); }
        .btn-primary:hover { background: var(--orange-dark); transform: translateY(-1px); }
        .btn-outline   { background: transparent; color: var(--gray-700); border: 1.5px solid var(--gray-300); }
        .btn-outline:hover { background: var(--gray-100); }
        .btn-danger    { background: var(--red); color: #fff; }
        .btn-sm        { padding: 7px 14px; font-size: 12px; }
        .btn-full      { width: 100%; }

        /* ── Cards ───────────────────────────────────── */
        .card {
            background: var(--white); border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm); border: 1px solid var(--gray-200);
            overflow: hidden;
        }

        /* ── Badges ──────────────────────────────────── */
        .badge {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 3px 10px; border-radius: var(--radius-full);
            font-size: 11px; font-weight: 600; white-space: nowrap;
        }
        .badge-green  { background: #dcfce7; color: #15803d; }
        .badge-red    { background: #fee2e2; color: #b91c1c; }
        .badge-orange { background: #ffedd5; color: #c2410c; }
        .badge-blue   { background: #dbeafe; color: #1d4ed8; }
        .badge-gray   { background: var(--gray-200); color: var(--gray-700); }
        .badge-yellow { background: #fef9c3; color: #a16207; }

        /* ── Form Controls ───────────────────────────── */
        .form-group { margin-bottom: 16px; }
        .form-label { display: block; font-size: 13px; font-weight: 600; color: var(--gray-700); margin-bottom: 6px; }
        .form-control {
            width: 100%; padding: 12px 14px; border: 1.5px solid var(--gray-200);
            border-radius: var(--radius-md); font-size: 14px; font-family: inherit;
            background: var(--white); color: var(--gray-900);
            transition: border-color .2s, box-shadow .2s;
            outline: none;
        }
        .form-control:focus { border-color: var(--orange); box-shadow: 0 0 0 3px rgba(249,115,22,.15); }
        .form-control.is-invalid { border-color: var(--red); }
        .invalid-feedback { color: var(--red); font-size: 12px; margin-top: 4px; }
        .input-icon-wrap { position: relative; }
        .input-icon-wrap .icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--gray-500); font-size: 14px; }
        .input-icon-wrap .form-control { padding-left: 40px; }

        /* ── Alerts ──────────────────────────────────── */
        .alert {
            padding: 12px 16px; border-radius: var(--radius-md);
            font-size: 13px; margin-bottom: 16px;
            display: flex; align-items: flex-start; gap: 10px;
        }
        .alert-success { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
        .alert-error   { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
        .alert-warning { background: #fef9c3; color: #92400e; border: 1px solid #fde68a; }
        .alert-info    { background: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe; }

        /* ── Pulse animation ─────────────────────────── */
        @keyframes pulse-ring {
            0%   { box-shadow: 0 0 0 0 rgba(34,197,94,.7); }
            70%  { box-shadow: 0 0 0 8px rgba(34,197,94,0); }
            100% { box-shadow: 0 0 0 0 rgba(34,197,94,0); }
        }
        .pulse { animation: pulse-ring 2s infinite; }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .fade-in { animation: fadeInUp .3s ease both; }

        /* ── Sticky Header ───────────────────────────── */
        .sticky-header {
            position: sticky; top: 0; z-index: 50;
            background: var(--white);
            border-bottom: 1px solid var(--gray-200);
            box-shadow: 0 2px 8px rgba(0,0,0,.06);
        }

        /* ── Bottom Nav ──────────────────────────────── */
        .bottom-nav {
            position: fixed; bottom: 0; left: 50%; transform: translateX(-50%);
            width: 100%; max-width: 430px; z-index: 50;
            background: var(--white); border-top: 1px solid var(--gray-200);
            display: flex; justify-content: space-around; align-items: center;
            padding: 8px 0 16px;
            box-shadow: 0 -4px 12px rgba(0,0,0,.06);
        }
        .nav-item {
            display: flex; flex-direction: column; align-items: center; gap: 4px;
            color: var(--gray-500); font-size: 10px; font-weight: 500;
            text-decoration: none; padding: 4px 16px; border-radius: var(--radius-md);
            transition: all .2s; cursor: pointer;
        }
        .nav-item i { font-size: 20px; }
        .nav-item.active, .nav-item:hover { color: var(--orange); }
        .nav-item.active i { color: var(--orange); }
        .nav-badge {
            position: absolute; top: -4px; right: -4px;
            width: 8px; height: 8px; border-radius: 50%;
            background: var(--red); border: 2px solid var(--white);
        }

        /* ── Wallet gradient ─────────────────────────── */
        .wallet-card {
            background: linear-gradient(135deg, var(--orange) 0%, var(--orange-dark) 100%);
            border-radius: var(--radius-lg); padding: 16px; color: var(--white);
            box-shadow: var(--shadow-orange);
        }

        /* ── Order Card ──────────────────────────────── */
        .order-card { position: relative; cursor: pointer; transition: transform .2s, box-shadow .2s; }
        .order-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
        .order-card.suspicious { border-color: #fca5a5; background: #fffaf9; }
        .order-card.filtered   { opacity: 0.55; pointer-events: none; }

        .order-badge-corner {
            position: absolute; top: 0; right: 0;
            padding: 4px 10px; font-size: 10px; font-weight: 700;
            border-radius: 0 var(--radius-md) 0 var(--radius-md);
        }

        /* ── Route line ──────────────────────────────── */
        .route-line { display: flex; flex-direction: column; align-items: center; gap: 2px; padding-top: 3px; }
        .route-dot  { width: 8px; height: 8px; border-radius: 50%; }
        .route-line-seg { width: 1.5px; height: 24px; background: var(--gray-200); }

        /* ── Page padding ────────────────────────────── */
        .page-content { padding: 16px; padding-bottom: 90px; }

        /* ── Section label ───────────────────────────── */
        .section-label {
            font-size: 11px; font-weight: 700; color: var(--gray-500);
            text-transform: uppercase; letter-spacing: .06em; margin-bottom: 10px;
        }

        /* ── Divider ─────────────────────────────────── */
        .divider { height: 1px; background: var(--gray-200); margin: 16px 0; }

        /* ── Toast ───────────────────────────────────── */
        #toast {
            position: fixed; top: 70px; left: 50%; transform: translateX(-50%) translateY(-20px);
            background: var(--gray-900); color: #fff; padding: 10px 20px;
            border-radius: var(--radius-full); font-size: 13px; font-weight: 500;
            opacity: 0; transition: all .3s; z-index: 200; white-space: nowrap;
            pointer-events: none;
        }
        #toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }

        /* ── Modal overlay ───────────────────────────── */
        .modal-overlay {
            position: fixed; inset: 0; background: rgba(0,0,0,.5);
            z-index: 100; display: none; align-items: flex-end; justify-content: center;
        }
        .modal-overlay.open { display: flex; }
        .modal-sheet {
            background: var(--white); width: 100%; max-width: 430px;
            border-radius: var(--radius-xl) var(--radius-xl) 0 0;
            padding: 24px 20px 32px;
            animation: slideUp .3s ease;
        }
        @keyframes slideUp {
            from { transform: translateY(100%); }
            to   { transform: translateY(0); }
        }

        /* ── Status pill ─────────────────────────────── */
        @keyframes blink {
            0%,100% { opacity: 1; } 50% { opacity: .5; }
        }
        .blink { animation: blink 1.5s infinite; }
    </style>

    @stack('styles')
</head>
<body>
<div class="app-shell">

    <!-- Toast -->
    <div id="toast"></div>

    @yield('content')

</div>

<script>
function showToast(msg, duration = 3000) {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), duration);
}
@if(session('success'))
    document.addEventListener('DOMContentLoaded', () => showToast(@json(session('success'))));
@endif
@if(session('error'))
    document.addEventListener('DOMContentLoaded', () => showToast(@json(session('error'))));
@endif
@if(session('info'))
    document.addEventListener('DOMContentLoaded', () => showToast(@json(session('info'))));
@endif
</script>

@stack('scripts')
</body>
</html>
