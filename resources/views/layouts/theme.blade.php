<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Smart Attendance')</title>
    <meta name="description" content="Smart Attendance — Sistema inteligente de controle de presença via QR Code.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,700;1,900&family=Space+Grotesk:wght@300;400;500;700&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script>
        // Initialize theme instantly to prevent flash
        if (localStorage.getItem('pal_theme') === 'light') {
            document.documentElement.classList.add('light-mode');
        }
    </script>
    <style>
        /* Light Mode CSS Variables */
        html.light-mode {
            --pal-black:      #f5f5f7;
            --pal-near-black: #ffffff;
            --pal-white:      #111111;
            --pal-off-white:  #0d0d0d;
            --pal-gray:       #666666;
            --pal-light-gray: #333333;
            --pal-section-light: #e0e0e0;
        }

        /* Light Mode Overrides (High Specificity to override inline and tailwind) */
        html.light-mode body { background-color: #f5f5f7 !important; color: #111111 !important; }
        html.light-mode .glass { background: #ffffff !important; border-color: rgba(0,0,0,0.1) !important; box-shadow: 0 4px 15px rgba(0,0,0,0.03) !important; }
        
        /* Tailwind Overrides */
        html.light-mode .text-white { color: #111111 !important; }
        html.light-mode .text-white\/30, html.light-mode .text-white\/40, html.light-mode .text-white\/50, html.light-mode .text-white\/60, html.light-mode .text-white\/70, html.light-mode .text-white\/80 { color: #555555 !important; }
        html.light-mode .bg-white { background-color: #111111 !important; color: #ffffff !important; }
        html.light-mode .bg-white\/5, html.light-mode .bg-white\/10 { background-color: rgba(0,0,0,0.04) !important; color: #111111 !important; }
        html.light-mode .border-white\/5, html.light-mode .border-white\/10, html.light-mode .border-white\/20, html.light-mode .border-white\/30 { border-color: rgba(0,0,0,0.1) !important; }
        
        /* Inline Style Overrides */
        html.light-mode [style*="background:#111"] { background-color: #ffffff !important; border-color: rgba(0,0,0,0.1) !important; }
        html.light-mode [style*="background:#0d0d0d"] { background-color: #fcfcfc !important; }
        html.light-mode [style*="color:#efefef"] { color: #111111 !important; }
        html.light-mode [style*="color:#efefef}"] { color: #111111 !important; }
        html.light-mode [style*="color:#777"], html.light-mode [style*="color:#888"], html.light-mode [style*="color:#999"], html.light-mode [style*="color:#aaa"], html.light-mode [style*="color:#bbb"], html.light-mode [style*="color:#555"], html.light-mode [style*="color:#444"], html.light-mode [style*="color:#333"] { color: #555555 !important; }
        html.light-mode [style*="border:1px solid rgba(255,255,255"] { border-color: rgba(0,0,0,0.1) !important; }
        html.light-mode [style*="border-bottom:1px solid rgba(255,255,255"] { border-bottom-color: rgba(0,0,0,0.1) !important; }
        html.light-mode [style*="background:#efefef"] { background-color: #111111 !important; color: #ffffff !important; border-color: #111 !important; }
        html.light-mode [style*="color:#0d0d0d"] { color: #ffffff !important; }
        html.light-mode [style*="stroke:#efefef"] { stroke: #111111 !important; }
        html.light-mode svg rect[fill="white"] { fill: #111111 !important; }
        html.light-mode svg rect[fill="#0d0d0d"] { fill: #ffffff !important; }
        
        html.light-mode .pal-nav { background: rgba(245, 245, 247, 0.95); border-bottom-color: rgba(0,0,0,0.1); }
        html.light-mode .pal-nav-logo span { color: #777; }
        html.light-mode .pal-site-footer p { color: #555555 !important; }

        /* ===== RESET & BASE ===== */
        *, *::before, *::after { box-sizing: border-box; }

        :root {
            --pal-black:      #0d0d0d;
            --pal-near-black: #111111;
            --pal-white:      #efefef;
            --pal-off-white:  #f5f5f0;
            --pal-gray:       #888888;
            --pal-light-gray: #d4d4d4;
            --pal-section-light: #f0f0ec;
        }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--pal-black);
            color: var(--pal-white);
            min-height: 100vh;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        /* ===== SCROLLBAR ===== */
        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-track { background: var(--pal-black); }
        ::-webkit-scrollbar-thumb { background: #333; border-radius: 2px; }

        /* ===== GLASS (Palantir-style dark flat card) ===== */
        .glass {
            background: rgba(18, 18, 18, 0.98);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: none;
        }

        /* ===== GRADIENT BG (pure black — no gradient) ===== */
        .gradient-bg {
            background: var(--pal-black);
            min-height: 100vh;
        }

        /* ===== BLOBS (hidden — Palantir uses no color blobs) ===== */
        .blob, .blob-2 {
            display: none;
        }

        /* ===== ANIMATIONS ===== */
        .animate-reveal {
            animation: reveal 0.8s cubic-bezier(0.22, 1, 0.36, 1) forwards;
        }
        .animate-fade-in {
            opacity: 0;
            animation: fadeIn 0.8s ease-out forwards;
        }
        @keyframes reveal {
            from { transform: translateY(30px); opacity: 0; }
            to   { transform: translateY(0);    opacity: 1; }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to   { opacity: 1; }
        }

        /* ===== TILT CARDS ===== */
        .tilt-card {
            transform-style: preserve-3d;
            will-change: transform;
        }

        /* ===== PAL NAV (Palantir-style fixed top nav) ===== */
        .pal-nav {
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2.5rem;
            height: 60px;
            background: rgba(13, 13, 13, 0.92);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border-bottom: 1px solid rgba(255,255,255,0.06);
        }
        .pal-nav-logo {
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 700;
            font-size: 0.9rem;
            letter-spacing: 0.1em;
            color: var(--pal-white);
            text-decoration: none;
            text-transform: uppercase;
        }
        .pal-nav-logo span { color: #555; }
        .pal-nav-actions { display: flex; align-items: center; gap: 0.75rem; }
        .pal-nav-btn {
            font-family: 'Inter', sans-serif;
            font-size: 0.78rem;
            font-weight: 600;
            letter-spacing: 0.04em;
            padding: 0.45rem 1.1rem;
            border-radius: 3px;
            text-decoration: none;
            transition: all 0.2s;
            cursor: pointer;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }
        .pal-nav-btn-solid {
            background: var(--pal-white);
            color: var(--pal-black);
            border: 1px solid var(--pal-white);
        }
        .pal-nav-btn-solid:hover { background: var(--pal-off-white); }
        .pal-nav-btn-ghost {
            background: transparent;
            color: rgba(255,255,255,0.7);
            border: 1px solid rgba(255,255,255,0.15);
        }
        .pal-nav-btn-ghost:hover {
            border-color: rgba(255,255,255,0.5);
            color: white;
            background: rgba(255,255,255,0.05);
        }
        .pal-nav-btn-danger {
            background: transparent;
            color: rgba(255,255,255,0.5);
            border: 1px solid rgba(255,255,255,0.1);
        }
        .pal-nav-btn-danger:hover { border-color: #ef4444; color: #ef4444; }

        /* Nav user info */
        .pal-nav-user {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            margin-right: 0.5rem;
        }
        .pal-nav-user-role {
            font-family: 'Space Grotesk', monospace;
            font-size: 0.6rem;
            font-weight: 700;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #555;
        }
        .pal-nav-user-name {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--pal-white);
        }

        /* ===== PAGE WRAPPER (padding top for fixed nav) ===== */
        .pal-page {
            padding-top: 60px;
            min-height: 100vh;
        }

        /* ===== MAIN CONTENT AREA ===== */
        .pal-main {
            max-width: 1280px;
            margin: 0 auto;
            padding: 3rem 2.5rem;
        }

        /* ===== SECTION HEADING STYLE ===== */
        .pal-page-title {
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 900;
            letter-spacing: -0.04em;
            color: var(--pal-white);
            line-height: 1.05;
        }
        .pal-page-sub {
            font-size: 0.9rem;
            color: var(--pal-gray);
            margin-top: 0.5rem;
        }

        /* ===== TAG / OVERLINE ===== */
        .pal-tag {
            font-family: 'Space Grotesk', monospace;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: var(--pal-gray);
        }

        /* ===== BUTTONS ===== */
        .pal-btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            padding: 0.75rem 1.75rem;
            background: var(--pal-white);
            color: var(--pal-black);
            border: 1px solid var(--pal-white);
            border-radius: 3px;
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .pal-btn-primary:hover { background: var(--pal-off-white); transform: translateY(-1px); }
        .pal-btn-primary:active { transform: scale(0.97); }

        .pal-btn-outline {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            padding: 0.75rem 1.75rem;
            background: transparent;
            color: var(--pal-white);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 3px;
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .pal-btn-outline:hover { border-color: var(--pal-white); transform: translateY(-1px); }
        .pal-btn-outline:active { transform: scale(0.97); }

        /* ===== STAT CARDS ===== */
        .pal-stat-card {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: 4px;
            padding: 1.75rem;
            transition: background 0.2s;
        }
        .pal-stat-card:hover { background: rgba(255,255,255,0.05); }
        .pal-stat-card .number {
            font-size: 2.5rem;
            font-weight: 900;
            letter-spacing: -0.04em;
            line-height: 1;
            color: var(--pal-white);
        }
        .pal-stat-card .label {
            font-family: 'Space Grotesk', monospace;
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: var(--pal-gray);
            margin-top: 0.5rem;
        }

        /* ===== DIVIDER ===== */
        .pal-divider {
            border: none;
            border-top: 1px solid rgba(255,255,255,0.07);
            margin: 0;
        }

        /* ===== FOOTER ===== */
        .pal-site-footer {
            border-top: 1px solid rgba(255,255,255,0.06);
            padding: 1.5rem 2.5rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        .pal-site-footer p {
            font-size: 0.72rem;
            color: rgba(255,255,255,0.2);
            letter-spacing: 0.05em;
            text-align: center;
        }
        .pal-footer-logo {
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 700;
            font-size: 0.8rem;
            letter-spacing: 0.1em;
            color: rgba(255,255,255,0.2);
            text-transform: uppercase;
        }

        /* ===== INDEX-SPECIFIC PALANTIR COMPONENTS ===== */
        .pal-container { max-width: 1200px; margin: 0 auto; padding: 0 2.5rem; }

        .pal-hero { min-height: calc(100vh - 60px); display: flex; flex-direction: column; justify-content: center; background: var(--pal-near-black); position: relative; overflow: hidden; }
        .pal-hero .pal-container { display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; align-items: center; padding-top: 5rem; padding-bottom: 5rem; }
        .pal-overline { font-size: 0.72rem; font-weight: 600; letter-spacing: 0.15em; text-transform: uppercase; color: var(--pal-gray); margin-bottom: 1.5rem; font-family: 'Space Grotesk', monospace; }
        .pal-hero-title { font-size: clamp(3rem, 6vw, 5.5rem); font-weight: 900; line-height: 1.0; letter-spacing: -0.03em; color: var(--pal-white); margin-bottom: 2rem; }
        .pal-hero-title em { font-style: italic; color: var(--pal-light-gray); }
        .pal-hero-sub { font-size: 1.05rem; line-height: 1.7; color: var(--pal-gray); max-width: 460px; margin-bottom: 2.5rem; }
        .pal-hero-actions { display: flex; gap: 1rem; flex-wrap: wrap; }
        .pal-hero-visual { display: flex; align-items: center; justify-content: center; }
        .pal-qr-mock { width: 280px; height: 280px; border: 1px solid rgba(255,255,255,0.1); border-radius: 4px; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.02); position: relative; animation: pal-pulse-border 4s ease-in-out infinite; }
        @keyframes pal-pulse-border {
            0%, 100% { border-color: rgba(255,255,255,0.1); box-shadow: none; }
            50%       { border-color: rgba(255,255,255,0.3); box-shadow: 0 0 40px rgba(255,255,255,0.04); }
        }
        .pal-qr-mock::before, .pal-qr-mock::after { content: ''; position: absolute; width: 20px; height: 20px; border-color: white; border-style: solid; }
        .pal-qr-mock::before { top: -1px; left: -1px; border-width: 2px 0 0 2px; }
        .pal-qr-mock::after  { bottom: -1px; right: -1px; border-width: 0 2px 2px 0; }
        .pal-qr-inner { display: flex; flex-direction: column; align-items: center; gap: 1rem; }
        .pal-qr-label { font-family: 'Space Grotesk', monospace; font-size: 0.65rem; font-weight: 700; letter-spacing: 0.2em; color: var(--pal-gray); }
        .pal-qr-sub { font-size: 0.7rem; color: rgba(255,255,255,0.3); letter-spacing: 0.05em; }
        .pal-hero-scroll-hint { position: absolute; bottom: 2rem; left: 50%; transform: translateX(-50%); display: flex; flex-direction: column; align-items: center; gap: 0.5rem; color: rgba(255,255,255,0.25); font-size: 0.68rem; letter-spacing: 0.1em; font-family: 'Space Grotesk', monospace; animation: pal-bounce 2s ease-in-out infinite; }
        @keyframes pal-bounce {
            0%, 100% { transform: translateX(-50%) translateY(0); }
            50%       { transform: translateX(-50%) translateY(6px); }
        }

        .pal-marquee-strip { background: var(--pal-white); padding: 0.85rem 0; overflow: hidden; white-space: nowrap; }
        .pal-marquee-track { display: inline-flex; gap: 2rem; animation: pal-marquee 20s linear infinite; }
        .pal-marquee-track span { font-family: 'Space Grotesk', monospace; font-size: 0.72rem; font-weight: 700; letter-spacing: 0.15em; text-transform: uppercase; color: var(--pal-black); }
        .pal-sep { opacity: 0.35; }
        @keyframes pal-marquee { from { transform: translateX(0); } to { transform: translateX(-50%); } }

        .pal-statement-section { background: var(--pal-black); padding: 8rem 0; }
        .pal-statement-text { font-size: clamp(1.8rem, 3.5vw, 2.8rem); font-weight: 300; line-height: 1.4; letter-spacing: -0.02em; color: var(--pal-white); max-width: 900px; }

        .pal-products-section { background: var(--pal-black); border-top: 1px solid rgba(255,255,255,0.07); }
        .pal-products-header { padding: 4rem 0 2rem; }
        .pal-product-item { display: grid; grid-template-columns: 160px 1fr; gap: 3rem; padding: 4rem 0; border-top: 1px solid rgba(255,255,255,0.06); align-items: start; transition: background 0.3s; }
        .pal-product-item:hover { background: rgba(255,255,255,0.015); }
        .pal-product-number { font-family: 'Space Grotesk', monospace; font-size: 1rem; font-weight: 400; color: rgba(255,255,255,0.18); letter-spacing: 0.05em; padding-top: 0.5rem; }
        .pal-product-title { font-size: clamp(2.5rem, 5vw, 4.5rem); font-weight: 900; letter-spacing: -0.04em; line-height: 1; color: var(--pal-white); margin-bottom: 1.5rem; }
        .pal-product-desc { font-size: 1rem; line-height: 1.7; color: var(--pal-gray); max-width: 600px; margin-bottom: 1.5rem; }
        .pal-product-desc strong { color: var(--pal-light-gray); font-weight: 600; }
        .pal-product-link { font-size: 0.85rem; font-weight: 700; letter-spacing: 0.05em; color: var(--pal-white); text-decoration: none; border-bottom: 1px solid rgba(255,255,255,0.25); padding-bottom: 2px; transition: border-color 0.2s; }
        .pal-product-link:hover { border-color: var(--pal-white); }

        .pal-light-section { background: var(--pal-section-light); padding: 8rem 0; }
        .pal-section-top { margin-bottom: 4rem; }
        .pal-tag-dark { color: #666; }
        .pal-section-title-dark { font-size: clamp(2.5rem, 5vw, 4rem); font-weight: 900; letter-spacing: -0.04em; color: var(--pal-black); margin-top: 1rem; }

        .pal-profiles-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
        .pal-profile-card { display: flex; flex-direction: column; padding: 3rem; background: var(--pal-white); border: 1px solid rgba(0,0,0,0.08); border-radius: 4px; text-decoration: none; color: var(--pal-black); transition: all 0.25s ease; position: relative; overflow: hidden; }
        .pal-profile-card::after { content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 2px; background: var(--pal-black); transform: scaleX(0); transform-origin: left; transition: transform 0.3s ease; }
        .pal-profile-card:hover { transform: translateY(-4px); box-shadow: 0 20px 60px rgba(0,0,0,0.12); }
        .pal-profile-card:hover::after { transform: scaleX(1); }
        .pal-profile-number { font-family: 'Space Grotesk', monospace; font-size: 0.75rem; font-weight: 700; letter-spacing: 0.1em; color: var(--pal-gray); margin-bottom: 2rem; }
        .pal-profile-icon { width: 48px; height: 48px; margin-bottom: 1.5rem; color: var(--pal-black); }
        .pal-profile-icon svg { width: 100%; height: 100%; transition: transform 0.3s ease; }
        .pal-profile-card:hover .pal-profile-icon svg { transform: scale(1.1); }
        .pal-profile-title { font-size: 2rem; font-weight: 900; letter-spacing: -0.03em; color: var(--pal-black); margin-bottom: 0.75rem; }
        .pal-profile-sub { font-size: 0.88rem; line-height: 1.6; color: #666; margin-bottom: 2rem; flex-grow: 1; }
        .pal-profile-cta { font-size: 0.82rem; font-weight: 700; letter-spacing: 0.05em; color: var(--pal-black); border-bottom: 1px solid rgba(0,0,0,0.25); padding-bottom: 2px; align-self: flex-start; }

        .pal-stats-section { background: var(--pal-near-black); border-top: 1px solid rgba(255,255,255,0.06); padding: 7rem 0; }
        .pal-stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem; }
        .pal-stat { display: flex; flex-direction: column; gap: 0.75rem; padding: 2rem 0; border-top: 1px solid rgba(255,255,255,0.08); }
        .pal-stat-number { font-size: clamp(2rem, 4vw, 3.5rem); font-weight: 900; letter-spacing: -0.04em; color: var(--pal-white); }
        .pal-stat-label { font-size: 0.82rem; color: var(--pal-gray); letter-spacing: 0.02em; }

        .pal-footer-cta { background: var(--pal-black); padding: 8rem 0; border-top: 1px solid rgba(255,255,255,0.06); }
        .pal-cta-title { font-size: clamp(2.5rem, 5vw, 4.5rem); font-weight: 900; letter-spacing: -0.04em; color: var(--pal-white); margin-bottom: 2.5rem; }
        .pal-cta-actions { display: flex; gap: 1rem; }

        /* ===== CURSOR GLOW ===== */
        #pal-cursor-glow {
            position: fixed;
            width: 400px; height: 400px;
            background: rgba(168, 85, 247, 0.06);
            filter: blur(100px);
            border-radius: 50%;
            pointer-events: none;
            z-index: 0;
            transition: opacity 0.5s;
            opacity: 0;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 900px) {
            .pal-hero .pal-container { grid-template-columns: 1fr; gap: 3rem; }
            .pal-product-item { grid-template-columns: 80px 1fr; gap: 1.5rem; }
            .pal-profiles-grid { grid-template-columns: 1fr; }
            .pal-stats-grid { grid-template-columns: 1fr; }
            .pal-nav { padding: 0 1.25rem; }
            .pal-container { padding: 0 1.25rem; }
            .pal-main { padding: 2rem 1.25rem; }
        }
        @media (max-width: 600px) {
            .pal-hero-title { font-size: 2.8rem; }
            .pal-product-item { grid-template-columns: 1fr; }
            .pal-product-number { display: none; }
            .pal-hero-actions, .pal-cta-actions { flex-direction: column; }
        }
    </style>
    @stack('styles')
    @stack('head-scripts')
</head>
<body class="@yield('body-class')">

    {{-- CURSOR GLOW --}}
    <div id="pal-cursor-glow"></div>

    {{-- ===== GLOBAL NAV (hidden if page has @section('no-nav')) ===== --}}
    @unless(View::hasSection('no-nav'))
    <nav class="pal-nav">
        <a href="{{ url('/') }}" class="pal-nav-logo">Smart<span>Attendance</span></a>

        <div class="pal-nav-actions">
            @yield('nav-left')

            @auth
                @yield('nav-user')
                <form action="{{ route('logout') }}" method="POST" style="margin:0">
                    @csrf
                    <button type="submit" class="pal-nav-btn pal-nav-btn-danger">Sair</button>
                </form>
            @else
                @yield('nav-right')
                <a href="{{ route('login.professor.form') }}" class="pal-nav-btn pal-nav-btn-ghost">Professor</a>
                <a href="{{ route('login.aluno.form') }}" class="pal-nav-btn pal-nav-btn-solid">Entrar</a>
            @endauth
        </div>
    </nav>
    @endunless

    {{-- ===== PAGE CONTENT ===== --}}
    <div class="{{ View::hasSection('no-nav') ? '' : 'pal-page' }} flex flex-col min-h-screen">
        @yield('content')
    </div>

    {{-- ===== SITE FOOTER (hidden if page has @section('no-nav')) ===== --}}
    @unless(View::hasSection('no-nav'))
    <footer class="pal-site-footer">
        <span class="pal-footer-logo">Smart Attendance</span>
        <p>© {{ date('Y') }} Smart Attendance. Todos os direitos reservados.</p>
    </footer>
    @endunless

    {{-- ===== THEME TOGGLE BUTTON ===== --}}
    <button id="theme-toggle" style="position:fixed; bottom:2rem; right:2rem; z-index:9999; width:48px; height:48px; border-radius:50%; background:var(--pal-near-black); border:1px solid rgba(255,255,255,0.1); display:flex; align-items:center; justify-content:center; color:#efefef; cursor:pointer; box-shadow:0 10px 25px rgba(0,0,0,0.3); transition:all 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275); overflow: hidden;">
        <div id="theme-icon-container" style="position:relative; width:20px; height:20px; transition: transform 0.8s cubic-bezier(0.68, -0.55, 0.265, 1.55);">
            <svg id="theme-icon-moon" style="position:absolute; top:0; left:0; width:100%; height:100%; transition: opacity 0.3s ease; opacity:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
            <svg id="theme-icon-sun" style="position:absolute; top:0; left:0; width:100%; height:100%; transition: opacity 0.3s ease; opacity:1;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
        </div>
    </button>

    {{-- ===== GLOBAL SCRIPTS ===== --}}
    <script>
        // Theme Toggle Logic
        const themeBtn = document.getElementById('theme-toggle');
        const iconMoon = document.getElementById('theme-icon-moon');
        const iconSun = document.getElementById('theme-icon-sun');
        const iconContainer = document.getElementById('theme-icon-container');
        const html = document.documentElement;

        function updateIcon() {
            if (html.classList.contains('light-mode')) {
                iconMoon.style.opacity = '1';
                iconSun.style.opacity = '0';
                iconContainer.style.transform = 'rotate(360deg)';
                themeBtn.style.background = '#ffffff';
                themeBtn.style.color = '#111111';
                themeBtn.style.borderColor = 'rgba(0,0,0,0.1)';
            } else {
                iconMoon.style.opacity = '0';
                iconSun.style.opacity = '1';
                iconContainer.style.transform = 'rotate(0deg)';
                themeBtn.style.background = '#111111';
                themeBtn.style.color = '#efefef';
                themeBtn.style.borderColor = 'rgba(255,255,255,0.1)';
            }
        }

        updateIcon();

        themeBtn.addEventListener('click', () => {
            html.classList.toggle('light-mode');
            if (html.classList.contains('light-mode')) {
                localStorage.setItem('pal_theme', 'light');
            } else {
                localStorage.setItem('pal_theme', 'dark');
            }
            updateIcon();
        });

        // Cursor glow
        document.addEventListener('mousemove', (e) => {
            const g = document.getElementById('pal-cursor-glow');
            if (g) {
                g.style.left = (e.clientX - 200) + 'px';
                g.style.top  = (e.clientY - 200) + 'px';
                g.style.opacity = '1';
            }
        });
    </script>

    {{-- Auto-logout partial (kept for backward compat) --}}
    @include('partials.auto_logout')

    @stack('scripts')
</body>
</html>
