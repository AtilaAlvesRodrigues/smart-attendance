<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 — Página Não Encontrada | Smart Attendance</title>
    <meta name="description" content="A página que você procurou não foi encontrada.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,300;0,400;0,700;0,900;1,900&family=Space+Grotesk:wght@300;400;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
    <style>
        .blob-404 {
            position: fixed;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle at center, rgba(99,102,241,0.12) 0%, transparent 70%);
            border-radius: 50%;
            filter: blur(60px);
            pointer-events: none;
            animation: blobFloat 8s ease-in-out infinite;
        }
        .blob-404-2 {
            position: fixed;
            width: 400px;
            height: 400px;
            top: auto;
            bottom: -100px;
            right: -100px;
            background: radial-gradient(circle at center, rgba(139,92,246,0.08) 0%, transparent 70%);
            border-radius: 50%;
            filter: blur(60px);
            pointer-events: none;
            animation: blobFloat 10s ease-in-out infinite reverse;
        }
        @keyframes blobFloat {
            0%, 100% { transform: translate(0px, 0px) scale(1); }
            33%       { transform: translate(20px, -30px) scale(1.03); }
            66%       { transform: translate(-15px, 15px) scale(0.97); }
        }
        .glitch {
            position: relative;
            animation: glitch-skew 4s infinite linear alternate-reverse;
        }
        .glitch::before, .glitch::after {
            content: '404';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
        }
        .glitch::before {
            color: rgba(99,102,241,0.6);
            animation: glitch-effect 3s infinite;
            clip-path: polygon(0 0, 100% 0, 100% 35%, 0 35%);
            transform: translate(-4px, 0);
        }
        .glitch::after {
            color: rgba(139,92,246,0.6);
            animation: glitch-effect 2s infinite reverse;
            clip-path: polygon(0 65%, 100% 65%, 100% 100%, 0 100%);
            transform: translate(4px, 0);
        }
        @keyframes glitch-effect {
            0%   { transform: translate(0); }
            20%  { transform: translate(-4px, 2px); }
            40%  { transform: translate(-4px, -2px); }
            60%  { transform: translate(4px, 2px); }
            80%  { transform: translate(4px, -2px); }
            100% { transform: translate(0); }
        }
        @keyframes glitch-skew {
            0%  { transform: skew(0deg); }
            10% { transform: skew(-1deg); }
            90% { transform: skew(0.5deg); }
            100%{ transform: skew(0deg); }
        }
        .pulse-ring {
            position: absolute;
            border-radius: 50%;
            border: 1px solid rgba(255,255,255,0.05);
            animation: pulse-expand 3s ease-out infinite;
        }
        .pulse-ring:nth-child(2) { animation-delay: 1s; }
        .pulse-ring:nth-child(3) { animation-delay: 2s; }
        @keyframes pulse-expand {
            0%   { width: 80px;  height: 80px;  opacity: 0.6; }
            100% { width: 300px; height: 300px; opacity: 0; }
        }
    </style>
</head>
<body class="gradient-bg relative min-h-screen flex flex-col items-center justify-center overflow-hidden">

    {{-- Cursor glow --}}
    <div id="pal-cursor-glow"></div>

    {{-- Background blobs --}}
    <div class="blob-404" style="top:-150px;left:-150px;"></div>
    <div class="blob-404-2"></div>

    {{-- Main content --}}
    <main class="relative z-10 text-center px-6 flex flex-col items-center gap-8 animate-reveal">

        {{-- Pulse rings behind number --}}
        <div class="relative flex items-center justify-center" style="height:120px;">
            <div class="pulse-ring" style="top:50%;left:50%;margin:-40px 0 0 -40px;"></div>
            <div class="pulse-ring" style="top:50%;left:50%;margin:-40px 0 0 -40px;"></div>
            <div class="pulse-ring" style="top:50%;left:50%;margin:-40px 0 0 -40px;"></div>

            {{-- Glitch 404 --}}
            <span class="glitch pal-text font-black italic tracking-tighter select-none"
                  style="font-size:clamp(5rem,15vw,8rem);line-height:1;">404</span>
        </div>

        {{-- Message --}}
        <div class="flex flex-col gap-3 max-w-lg">
            <p class="pal-eyebrow">Erro de Navegação</p>
            <h1 class="pal-title" style="font-size:clamp(1.5rem,4vw,2.5rem);">
                Essa página escapou do QR Code
            </h1>
            <p class="pal-subtitle text-base">
                A rota que você tentou acessar não existe ou foi removida.
                Verifique o endereço ou volte para um local seguro.
            </p>
        </div>

        {{-- Action buttons --}}
        <div class="flex flex-wrap gap-4 justify-center">
            <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('login_form') }}"
               class="pal-btn-secondary flex items-center gap-2">
                ← Voltar
            </a>
            <a href="{{ route('login_form') }}"
               class="pal-btn-primary flex items-center gap-2">
                🏠 Ir para o Início
            </a>
        </div>

        {{-- Footer note --}}
        <p class="pal-subtitle" style="font-size:0.65rem;margin-top: 2rem;">
            Smart Attendance System &mdash; Código de Erro HTTP 404
        </p>

    </main>

    <script>
        // Cursor glow (same as main theme)
        const glow = document.getElementById('pal-cursor-glow');
        if (glow) {
            document.addEventListener('mousemove', e => {
                glow.style.left = e.clientX + 'px';
                glow.style.top  = e.clientY + 'px';
            });
        }
    </script>

</body>
</html>
