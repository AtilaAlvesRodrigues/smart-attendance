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
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}?v={{ filemtime(public_path('css/theme.css')) }}">

    <script>
        // Initialize theme instantly to prevent flash
        if (localStorage.getItem('pal_theme') === 'light') {
            document.documentElement.classList.add('light-mode');
        }
    </script>
    
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
                @unless(View::hasSection('hide-nav-defaults'))
                    @if(request()->routeIs('login.professor.form'))
                        <a href="{{ route('login.aluno.form') }}" class="pal-nav-btn pal-nav-btn-ghost">Aluno</a>
                    @elseif(request()->routeIs('login.aluno.form'))
                        <a href="{{ route('login.professor.form') }}" class="pal-nav-btn pal-nav-btn-ghost">Professor</a>
                    @else
                        <a href="{{ route('login.professor.form') }}" class="pal-nav-btn pal-nav-btn-ghost">Professor</a>
                        <a href="{{ route('login.aluno.form') }}" class="pal-nav-btn pal-nav-btn-solid">Entrar</a>
                    @endif
                @endunless
            @endauth
        </div>
    </nav>
    @endunless

    {{-- ===== PAGE CONTENT ===== --}}
    <div class="{{ View::hasSection('no-nav') ? '' : 'pal-page' }} flex flex-col flex-grow">
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
    <button id="theme-toggle" class="pal-theme-toggle" style="position:fixed; bottom:2rem; right:2rem; z-index:9999; width:48px; height:48px; border-radius:50%; background:var(--pal-near-black); border:1px solid rgba(255,255,255,0.1); display:flex; align-items:center; justify-content:center; color:#efefef; cursor:pointer; box-shadow:0 10px 25px rgba(0,0,0,0.3); transition:all 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275); overflow: hidden;">
        <div id="theme-icon-container" style="position:relative; width:20px; height:20px; transition: transform 0.8s cubic-bezier(0.68, -0.55, 0.265, 1.55);">
            <svg id="theme-icon-moon" style="position:absolute; top:0; left:0; width:100%; height:100%; transition: opacity 0.3s ease; opacity:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
            <svg id="theme-icon-sun" style="position:absolute; top:0; left:0; width:100%; height:100%; transition: opacity 0.3s ease; opacity:1;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
        </div>
    </button>

    {{-- ===== GLOBAL SCRIPTS ===== --}}
    <script src="{{ asset('js/theme.js') }}"></script>

    {{-- Auto-logout partial (kept for backward compat) --}}
    @include('partials.auto_logout')

    @stack('scripts')
</body>
</html>
