<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Smart Attendance')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        body { font-family: "Inter", sans-serif; transition: background-color 0.5s ease, color 0.5s ease; }
        
        .glass {
            background: rgba(75, 0, 130, 0.3); /* Purple background (Deep Indigo variant) */
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3);
        }

        .gradient-bg { 
            background: #4B0082; /* Deep Indigo Purple from image */
            min-height: 100vh;
        }

        /* Light Theme Overrides (Better Readability) */
        body.theme-light {
            background-color: #f8fafc;
            color: #1e293b;
        }
        body.theme-light .gradient-bg {
            background: #f1f5f9;
        }
        body.theme-light .glass {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(0, 0, 0, 0.08);
            color: #1e293b;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
        }
        
        /* Handling all text-white opacity variants */
        body.theme-light .text-white,
        body.theme-light .text-white\/90,
        body.theme-light .text-white\/80,
        body.theme-light .text-white\/70,
        body.theme-light .text-white\/60 { color: #1e293b !important; }
        
        body.theme-light .text-white\/50,
        body.theme-light .text-white\/40,
        body.theme-light .text-white\/30,
        body.theme-light .text-white\/20,
        body.theme-light .text-white\/10 { color: #475569 !important; }

        body.theme-light .text-purple-300,
        body.theme-light .text-indigo-300 { color: #7e22ce; }
        
        /* Fixing background/border utilities in light mode */
        body.theme-light .bg-white\/5,
        body.theme-light .bg-white\/10 { background: rgba(0, 0, 0, 0.05); }
        body.theme-light .border-white\/10 { border-color: rgba(0, 0, 0, 0.1); }
        
        body.theme-light .blob, body.theme-light .blob-2 { opacity: 0.1; }

        /* Theme Toggle Animations */
        #theme-toggle {
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        #theme-toggle:hover {
            transform: scale(1.15) rotate(5deg);
        }
        .theme-icon {
            display: inline-block;
            transition: transform 0.5s ease;
            font-size: 1.6rem;
        }

        /* Night Animation on Hover (while in light theme) */
        body.theme-light #theme-toggle:hover .theme-icon-dark {
            animation: moon-shine 2s infinite ease-in-out;
        }
        @keyframes moon-shine {
            0%, 100% { transform: rotate(-10deg) scale(1); filter: drop-shadow(0 0 8px rgba(139, 92, 246, 0.5)); }
            50% { transform: rotate(10deg) scale(1.2); filter: drop-shadow(0 0 20px rgba(139, 92, 246, 0.8)); }
        }

        /* Day Animation on Hover (while in dark theme) */
        body:not(.theme-light) #theme-toggle:hover .theme-icon-light {
            animation: sun-burn 3s infinite linear;
        }
        @keyframes sun-burn {
            from { transform: rotate(0deg) scale(1.2); filter: drop-shadow(0 0 10px #fbbf24); }
            to { transform: rotate(360deg) scale(1.2); filter: drop-shadow(0 0 20px #fbbf24); }
        }

        .blob {
            position: absolute;
            width: 500px;
            height: 500px;
            background: linear-gradient(180deg, rgba(168, 85, 247, 0.4) 0%, rgba(126, 34, 206, 0.4) 100%);
            filter: blur(80px);
            border-radius: 50%;
            z-index: -1;
            animation: move 25s infinite alternate;
        }

        @keyframes move {
            from { transform: translate(-10%, -10%) scale(1); }
            to { transform: translate(20%, 20%) scale(1.2); }
        }

        .blob-2 {
            position: absolute;
            bottom: -150px;
            right: -150px;
            width: 400px;
            height: 400px;
            background: linear-gradient(180deg, rgba(192, 38, 211, 0.3) 0%, rgba(126, 34, 206, 0.3) 100%);
            filter: blur(80px);
            border-radius: 50%;
            z-index: -1;
            animation: move2 30s infinite alternate;
        }

        @keyframes move2 {
            from { transform: translate(10%, 10%) scale(1.2); }
            to { transform: translate(-20%, -20%) scale(1); }
        }

        .animate-reveal {
            animation: reveal 0.8s cubic-bezier(0.22, 1, 0.36, 1) forwards;
        }

        .animate-fade-in {
            opacity: 0;
            animation: fadeIn 0.8s ease-out forwards;
        }

        @keyframes reveal {
            from { transform: translateY(40px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: transparent; 
        }
        ::-webkit-scrollbar-thumb {
            background: #a855f7; 
            border-radius: 10px;
        }
    </style>
    @stack('styles')
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#a855f7', 
                        dark_purple: '#4B0082',
                    },
                }
            }
        }
    </script>
    @stack('head-scripts')
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="@yield('body-class', 'bg-gray-100') text-gray-800 antialiased min-h-screen flex flex-col theme-transition">

    {{-- Botão de Mudar Tema (Reposicionado para evitar sobreposição) --}}
    @if(!Route::is('login_form') && !Route::is('login.aluno.form') && !Route::is('login.professor.form'))
    <button id="theme-toggle" 
        class="fixed bottom-8 right-8 z-[100] w-14 h-14 glass rounded-3xl flex items-center justify-center transition-all group overflow-hidden border border-white/20 shadow-2xl"
        title="Mudar Tema">
        <span class="theme-icon theme-icon-light hidden">☀️</span>
        <span class="theme-icon theme-icon-dark">🌙</span>
    </button>
    @endif

    <div id="cursor-glow" class="fixed w-[400px] h-[400px] bg-purple-600/10 blur-[100px] rounded-full pointer-events-none z-0 transition-opacity duration-500 opacity-0"></div>

    <div class="flex-grow flex flex-col relative z-10">
        @yield('content')
    </div>

    <footer class="@yield('footer-class', 'py-6 text-center text-sm text-gray-500')">
        <div class="flex flex-col items-center justify-center">
            <span>&copy; {{ date('Y') }} Smart Attendance. Todos os direitos reservados.</span>
            <span class="hidden print:block text-xs mt-1 text-gray-400">
                Gerado em {{ now()->format('d/m/Y H:i:s') }} 
            </span>
        </div>
    </footer>

    <script>
        // Cursor Glow
        document.addEventListener('mousemove', (e) => {
            const glow = document.getElementById('cursor-glow');
            if(glow) {
                glow.style.left = `${e.clientX - 200}px`;
                glow.style.top = `${e.clientY - 200}px`;
                glow.style.opacity = '1';
            }
        });

        // Theme Toggle Logic
        const themeToggle = document.getElementById('theme-toggle');
        if(themeToggle) {
            themeToggle.addEventListener('click', () => {
                document.body.classList.toggle('theme-light');
                const isLight = document.body.classList.contains('theme-light');
                localStorage.setItem('theme', isLight ? 'light' : 'dark');
                
                themeToggle.querySelector('.theme-icon-light').classList.toggle('hidden', !isLight);
                themeToggle.querySelector('.theme-icon-dark').classList.toggle('hidden', isLight);
            });

            // Persist Theme
            if(localStorage.getItem('theme') === 'light') {
                document.body.classList.add('theme-light');
                themeToggle.querySelector('.theme-icon-light').classList.remove('hidden');
                themeToggle.querySelector('.theme-icon-dark').classList.add('hidden');
            }
        }
    </script>

    {{-- Script de auto-logout ao fechar aba --}}
    @include('partials.auto_logout')

    @stack('scripts')
</body>
</html>
