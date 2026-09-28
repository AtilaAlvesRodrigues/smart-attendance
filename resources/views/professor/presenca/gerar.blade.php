@extends('layouts.theme')

@section('title', 'Chamada de ' . $materia->nome . ' - Smart Attendance')

@section('body-class', 'gradient-bg relative min-h-screen flex flex-col')
@section('nav-left')
    <a href="{{ route('professor.presenca.index') }}" class="pal-nav-btn pal-nav-btn-ghost hidden sm:inline-flex">
        ← Trocar matéria
    </a>
@endsection

@section('nav-user')
    <div class="pal-nav-user">
        <span class="pal-nav-user-role">Professor</span>
        <span class="pal-nav-user-name">{{ $professor->nome ?? 'Professor' }}</span>
    </div>
@endsection

@php
    [$numSemestre, $anoSemestre] = array_pad(explode('/', $semestre), 2, now()->year);
    $periodo = $horario == 'M' ? 'Manhã' : ($horario == 'V' ? 'Tarde' : 'Noite');
@endphp

@section('content')
    <div class="flex-grow flex flex-col relative overflow-hidden">

        <div class="blob top-[-100px] left-[-100px]"></div>
        <div class="blob-2"></div>

        <main class="max-w-7xl mx-auto w-full p-6 mt-4 grid grid-cols-1 lg:grid-cols-12 gap-8 relative z-10 flex-grow">

            <div class="lg:col-span-12 animate-reveal [animation-delay:100ms]">
                <p class="pal-eyebrow" style="margin-bottom:0.5rem; color:#22c55e; display:flex; align-items:center; gap:0.5rem;">
                    <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse" aria-hidden="true"></span>
                    Chamada aberta
                </p>
                <h1 class="pal-title">{{ $materia->nome }}</h1>
                <p class="pal-subtitle">Projete esta tela na sala. Cada aluno aponta a câmera do celular para o QR Code e confirma a presença.</p>
            </div>

            {{-- Coluna esquerda: QR Code --}}
            <div class="lg:col-span-5 flex flex-col gap-6 animate-reveal [animation-delay:200ms]">
                <section class="glass p-8 rounded-sm border border-white/10 flex flex-col items-center text-center" aria-labelledby="titulo-qr">
                    <h2 id="titulo-qr" class="sr-only">QR Code da chamada</h2>

                    <div id="qrcode" class="p-5 rounded-sm bg-white shadow-2xl" role="img" aria-label="QR Code para registrar presença em {{ $materia->nome }}"></div>

                    <button type="button" id="btn-projetar" class="pal-dashboard-btn pal-dashboard-btn-solid mt-6"
                            style="background:#efefef; color:#050505; padding:0.8rem 1.5rem; font-size:0.95rem; font-weight:800; display:inline-flex; align-items:center; gap:0.5rem;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4h4M20 8V4h-4M4 16v4h4M20 16v4h-4"/></svg>
                        Projetar em tela cheia
                    </button>

                    {{-- Validade --}}
                    <div class="mt-8 w-full">
                        <div class="flex justify-between items-end mb-2">
                            <span class="pal-text font-bold" style="font-size:0.95rem;">QR Code válido por mais</span>
                            <span id="timer-big-text" class="text-3xl font-black pal-text tabular-nums tracking-tighter" aria-live="off">--:--:--</span>
                        </div>
                        <div class="w-full bg-white/5 border border-white/10 rounded-full h-3 overflow-hidden p-0.5">
                            <div id="timer-bar" class="bg-gradient-to-r from-green-500 to-emerald-400 h-full rounded-full transition-all duration-1000" style="width: 100%;"></div>
                        </div>
                    </div>

                    {{-- Alternativa sem câmera --}}
                    <div class="mt-8 w-full pt-6 border-t border-white/10 flex flex-col items-center gap-3">
                        <p class="pal-subtitle" style="margin:0;">Aluno sem câmera? Envie o link da chamada:</p>
                        <button type="button" id="btn-copiar-link"
                                class="inline-flex items-center gap-2 px-5 py-3 bg-white/5 hover:bg-white/10 border border-white/10 pal-text rounded-sm transition-all text-sm font-bold">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            <span id="btn-copiar-texto">Copiar link</span>
                        </button>
                        <p class="pal-subtitle" style="margin:0; font-size:0.85rem;">Código da aula: <code class="pal-text font-bold">{{ $codigo_aula }}</code></p>
                    </div>
                </section>

                <section class="glass p-6 rounded-sm border border-white/10" aria-labelledby="titulo-detalhes">
                    <h2 id="titulo-detalhes" class="pal-text font-black mb-4" style="font-size:1rem;">Detalhes da aula</h2>
                    <dl class="grid grid-cols-2 gap-4">
                        <div>
                            <dt class="pal-subtitle" style="margin:0; font-size:0.85rem;">Data</dt>
                            <dd class="pal-text font-bold">{{ now()->format('d/m/Y') }}</dd>
                        </div>
                        <div>
                            <dt class="pal-subtitle" style="margin:0; font-size:0.85rem;">Turno</dt>
                            <dd class="pal-text font-bold">{{ $periodo }}</dd>
                        </div>
                        <div>
                            <dt class="pal-subtitle" style="margin:0; font-size:0.85rem;">Semestre</dt>
                            <dd class="pal-text font-bold">{{ $numSemestre }}º semestre de {{ $anoSemestre }}</dd>
                        </div>
                        <div>
                            <dt class="pal-subtitle" style="margin:0; font-size:0.85rem;">Sala</dt>
                            <dd class="pal-text font-bold">{{ $materia->sala ?? '—' }}</dd>
                        </div>
                    </dl>
                </section>
            </div>

            {{-- Coluna direita: presentes --}}
            <section class="lg:col-span-7 flex flex-col gap-6 animate-reveal [animation-delay:400ms]" aria-labelledby="titulo-presentes">
                <div class="glass p-8 rounded-sm border border-white/10 flex-grow flex flex-col min-h-[520px] overflow-hidden">
                    <div class="flex justify-between items-start gap-4 mb-6">
                        <div>
                            <h2 id="titulo-presentes" class="text-3xl font-black pal-text tracking-tighter">Presentes</h2>
                            <p class="pal-subtitle mt-1">A lista se atualiza sozinha a cada 3 segundos.</p>
                        </div>
                        <div class="pal-count-badge flex flex-col items-center p-3 rounded-sm shadow-xl" aria-live="polite">
                            <span class="pal-count-number text-3xl font-black leading-none" id="contador-alunos">0</span>
                            <span class="pal-count-number" style="font-size:0.75rem; font-weight:700;" id="contador-rotulo">alunos</span>
                        </div>
                    </div>

                    <div class="flex-grow overflow-y-auto custom-scrollbar">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="pal-subtitle border-b border-white/10" style="font-size:0.85rem;">
                                    <th scope="col" class="pb-3 px-2 font-bold">Aluno</th>
                                    <th scope="col" class="pb-3 px-2 font-bold text-center">RA</th>
                                    <th scope="col" class="pb-3 px-2 font-bold text-center">Horário</th>
                                    <th scope="col" class="pb-3 px-2 font-bold text-right">Situação</th>
                                </tr>
                            </thead>
                            <tbody id="lista-presenca" class="divide-y divide-white/5"></tbody>
                        </table>
                        <div id="empty-state" class="py-16 text-center flex flex-col items-center gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 pal-text" style="opacity:0.6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            <p class="pal-text font-bold" style="margin:0;">Ninguém registrou presença ainda</p>
                            <p class="pal-subtitle" style="margin:0; max-width:24rem;">Assim que um aluno escanear o QR Code, o nome dele aparece aqui.</p>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    {{-- Modo projeção --}}
    <div id="modo-projecao" class="hidden fixed inset-0 z-[200] flex-col items-center justify-center gap-6 p-6" style="background:#050505;" role="dialog" aria-modal="true" aria-labelledby="projecao-titulo">
        <button type="button" id="btn-sair-projecao" class="absolute top-4 right-4 pal-nav-btn pal-nav-btn-ghost" style="color:#efefef; border-color:rgba(255,255,255,0.3);">
            Sair da tela cheia (Esc)
        </button>
        <h2 id="projecao-titulo" class="font-black text-center" style="color:#efefef; font-size:clamp(1.5rem,4vw,3rem); letter-spacing:-0.03em; margin:0;">{{ $materia->nome }}</h2>
        <div id="qrcode-projecao" class="bg-white rounded" style="padding:clamp(12px,2vw,28px);"></div>
        <p class="text-center" style="color:#d4d4d4; font-size:clamp(1rem,2vw,1.5rem); margin:0;">
            Aponte a câmera do celular para o QR Code ·
            <strong style="color:#4ade80;"><span id="contador-projecao">0</span> presentes</strong> ·
            válido por <span id="timer-projecao" class="tabular-nums">--:--:--</span>
        </p>
    </div>

    {{-- Chamada encerrada --}}
    <div id="expired-overlay" class="hidden fixed inset-0 z-[300] flex items-center justify-center p-6 backdrop-blur-xl bg-black/60" role="alertdialog" aria-modal="true" aria-labelledby="expirado-titulo">
        <div class="glass p-12 rounded-sm border-2 border-white/10 max-w-md w-full text-center">
            <div class="w-20 h-20 bg-red-500/20 rounded-sm flex items-center justify-center text-4xl mx-auto mb-8" aria-hidden="true">⏰</div>
            <h2 id="expirado-titulo" class="pal-title mb-4">Chamada encerrada</h2>
            <p class="pal-subtitle mb-10">O QR Code desta aula expirou e não aceita mais presenças. Para uma nova chamada, escolha a matéria outra vez.</p>
            <a href="{{ route('professor.presenca.index') }}" class="pal-btn-primary w-full justify-center py-4">Voltar às matérias</a>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        const qrContent = @json($presencaUrl);
        new QRCode(document.getElementById('qrcode'), {
            text: qrContent, width: 260, height: 260,
            colorDark: '#000000', colorLight: '#ffffff', correctLevel: QRCode.CorrectLevel.H
        });

        // ========== MODO PROJEÇÃO ==========
        const projecao = document.getElementById('modo-projecao');
        const qrProjecao = document.getElementById('qrcode-projecao');
        let qrProjecaoPronto = false;

        function abrirProjecao() {
            projecao.classList.remove('hidden');
            projecao.classList.add('flex');
            if (!qrProjecaoPronto) {
                const lado = Math.floor(Math.min(window.innerHeight * 0.62, window.innerWidth * 0.8));
                new QRCode(qrProjecao, { text: qrContent, width: lado, height: lado, colorDark: '#000000', colorLight: '#ffffff', correctLevel: QRCode.CorrectLevel.H });
                qrProjecaoPronto = true;
            }
            if (projecao.requestFullscreen) projecao.requestFullscreen().catch(() => {});
            document.getElementById('btn-sair-projecao').focus();
        }
        function fecharProjecao() {
            projecao.classList.add('hidden');
            projecao.classList.remove('flex');
            if (document.fullscreenElement) document.exitFullscreen().catch(() => {});
            document.getElementById('btn-projetar').focus();
        }
        document.getElementById('btn-projetar').addEventListener('click', abrirProjecao);
        document.getElementById('btn-sair-projecao').addEventListener('click', fecharProjecao);
        document.addEventListener('keydown', e => { if (e.key === 'Escape' && !projecao.classList.contains('hidden')) fecharProjecao(); });
        document.addEventListener('fullscreenchange', () => { if (!document.fullscreenElement && !projecao.classList.contains('hidden')) fecharProjecao(); });

        // ========== COPIAR LINK ==========
        const btnCopiarTexto = document.getElementById('btn-copiar-texto');
        document.getElementById('btn-copiar-link').addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(qrContent);
                btnCopiarTexto.textContent = 'Link copiado!';
            } catch {
                window.prompt('Copie o link da chamada:', qrContent);
            }
            setTimeout(() => { btnCopiarTexto.textContent = 'Copiar link'; }, 2500);
        });

        // ========== VALIDADE ==========
        const expiraEm = {{ $expiraEmTimestamp }};
        const timerBigText = document.getElementById('timer-big-text');
        const timerProjecao = document.getElementById('timer-projecao');
        const timerBar = document.getElementById('timer-bar');
        const expiredOverlay = document.getElementById('expired-overlay');
        const duracaoTotal = 2 * 60 * 60;
        let timerId;

        function atualizarTimer() {
            const restante = expiraEm - Math.floor(Date.now() / 1000);

            if (restante <= 0) {
                timerBigText.textContent = timerProjecao.textContent = '00:00:00';
                timerBar.style.width = '0%';
                timerBar.className = 'h-full rounded-full bg-red-500';
                if (!projecao.classList.contains('hidden')) fecharProjecao();
                expiredOverlay.classList.remove('hidden');
                clearInterval(timerId);
                clearInterval(pollId);
                return;
            }

            const h = Math.floor(restante / 3600), m = Math.floor((restante % 3600) / 60), s = restante % 60;
            timerBigText.textContent = timerProjecao.textContent =
                `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
            timerBar.style.width = Math.max(0, (restante / duracaoTotal) * 100) + '%';

            if (restante < 120) timerBar.className = 'h-full rounded-full transition-all duration-1000 bg-gradient-to-r from-red-600 to-pink-500';
            else if (restante < 600) timerBar.className = 'h-full rounded-full transition-all duration-1000 bg-gradient-to-r from-yellow-500 to-orange-400';
        }

        // ========== PRESENTES (polling) ==========
        const tabelaBody = document.getElementById('lista-presenca');
        const contador = document.getElementById('contador-alunos');
        const contadorRotulo = document.getElementById('contador-rotulo');
        const contadorProjecao = document.getElementById('contador-projecao');
        const emptyState = document.getElementById('empty-state');
        const checkUrl = @json(route('professor.presenca.check', $codigo_aula));

        // Monta as células com textContent: o nome vem do cadastro e nunca é interpretado como HTML.
        function celula(texto, classe) {
            const td = document.createElement('td');
            td.className = classe;
            td.textContent = texto;
            return td;
        }

        function linhaPresenca(presenca) {
            const nome = presenca.aluno ? presenca.aluno.nome : 'Aluno removido';
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-white/5 transition-colors';

            const tdNome = document.createElement('td');
            tdNome.className = 'py-3 px-2';
            const wrap = document.createElement('div');
            wrap.className = 'flex items-center gap-3';
            const inicial = document.createElement('span');
            inicial.className = 'w-8 h-8 bg-white/5 border border-white/10 rounded-sm flex items-center justify-center text-sm font-black pal-text';
            inicial.setAttribute('aria-hidden', 'true');
            inicial.textContent = nome.charAt(0).toUpperCase();
            const nomeEl = document.createElement('span');
            nomeEl.className = 'font-bold pal-text';
            nomeEl.textContent = nome;
            wrap.append(inicial, nomeEl);
            tdNome.append(wrap);

            const tdStatus = document.createElement('td');
            tdStatus.className = 'py-3 px-2 text-right';
            const badge = document.createElement('span');
            badge.className = 'inline-flex items-center gap-1.5 px-2 py-1 bg-green-500/15 text-green-400 rounded-sm text-sm font-bold';
            badge.textContent = '✓ Presente';
            tdStatus.append(badge);

            tr.append(
                tdNome,
                celula(presenca.aluno ? presenca.aluno.ra : '—', 'py-3 px-2 text-center pal-subtitle font-mono'),
                celula(new Date(presenca.created_at).toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' }), 'py-3 px-2 text-center pal-subtitle tabular-nums'),
                tdStatus,
            );
            return tr;
        }

        async function atualizarPresencas() {
            try {
                const resposta = await fetch(checkUrl, { headers: { 'Accept': 'application/json' } });
                if (!resposta.ok) return;
                const data = await resposta.json();
                data.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));

                tabelaBody.replaceChildren(...data.map(linhaPresenca));
                emptyState.style.display = data.length ? 'none' : 'flex';
                contador.textContent = contadorProjecao.textContent = data.length;
                contadorRotulo.textContent = data.length === 1 ? 'aluno' : 'alunos';
            } catch (erro) {
                console.error('Erro ao buscar presenças:', erro);
            }
        }

        timerId = setInterval(atualizarTimer, 1000);
        const pollId = setInterval(atualizarPresencas, 3000);
        atualizarTimer();
        atualizarPresencas();
    </script>
@endpush
