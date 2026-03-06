@extends('layouts.main')

@section('title', 'Gerando Frequência - Smart Attendance')

@section('body-class', 'gradient-bg relative min-h-screen flex flex-col')

@section('content')
    <div class="flex-grow flex flex-col relative overflow-hidden">
        
        <!-- Background Elements -->
        <div class="blob top-[-100px] left-[-100px]"></div>
        <div class="blob-2"></div>

        <!-- Glass Navbar -->
        <nav class="glass mx-6 mt-6 p-4 rounded-2xl border border-white/10 relative z-20 backdrop-blur-xl animate-reveal">
            <div class="max-w-7xl mx-auto flex justify-between items-center gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 bg-green-500 rounded-xl flex items-center justify-center text-white font-black italic shadow-lg shadow-green-500/50">
                        QR
                    </div>
                    <div>
                        <h1 class="text-xl font-black tracking-tighter text-white italic leading-none">{{ $materia->nome }}</h1>
                        <p class="text-[10px] font-bold text-white/40 uppercase tracking-widest mt-1">Chamada em Tempo Real</p>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    {{-- Timer --}}
                    <div id="timer-container" class="hidden md:flex items-center gap-3 bg-white/5 border border-white/10 py-2 px-4 rounded-xl backdrop-blur-md">
                        <div class="w-2 h-2 bg-green-400 rounded-full animate-ping" id="timer-dot"></div>
                        <span id="timer-text" class="text-xs font-black text-white tracking-widest tabular-nums">--:--:--</span>
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('professor.presenca.index') }}" 
                           class="hidden sm:block px-4 py-2 bg-white/5 border border-white/10 rounded-xl text-white text-[10px] font-black uppercase tracking-widest transition-all hover:bg-white/10">
                            Disciplinas
                        </a>
                        <a href="{{ route('dashboard.professor') }}" 
                           class="px-4 py-2 bg-white text-dark_purple rounded-xl text-[10px] font-black uppercase tracking-widest transition-all hover:scale-105 active:scale-95">
                            Home
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <main class="max-w-7xl mx-auto w-full p-6 mt-8 grid grid-cols-1 lg:grid-cols-12 gap-8 relative z-10">
            
            {{-- Coluna Esquerda: QR Code --}}
            <div class="lg:col-span-5 flex flex-col gap-8 animate-reveal [animation-delay:200ms]">
                <div class="glass p-10 rounded-[2.5rem] border border-white/10 flex flex-col items-center justify-center text-center relative overflow-hidden">
                    <div class="absolute -top-24 -right-24 w-48 h-48 bg-purple-600/10 blur-3xl"></div>
                    
                    <h2 class="text-2xl font-black text-white tracking-tight mb-8 italic">QR Code de Frequência</h2>
                    
                    <div class="relative group">
                        <div class="absolute -inset-0.5 bg-gradient-to-r from-purple-500 to-indigo-500 rounded-3xl blur opacity-30 group-hover:opacity-50 transition duration-1000 group-hover:duration-200"></div>
                        <div id="qrcode" class="relative p-6 rounded-3xl bg-white shadow-2xl"></div>
                    </div>

                    {{-- Timer grande abaixo do QR --}}
                    <div id="timer-big" class="mt-10 w-full">
                        <div class="flex justify-between items-end mb-3">
                            <span class="text-[10px] font-black text-white/30 uppercase tracking-[0.2em]">Tempo Restante</span>
                            <span id="timer-big-text" class="text-3xl font-black text-white tabular-nums tracking-tighter">--:--:--</span>
                        </div>
                        <div class="w-full bg-white/5 border border-white/10 rounded-full h-3 overflow-hidden p-0.5">
                            <div id="timer-bar" class="bg-gradient-to-r from-green-500 to-emerald-400 h-full rounded-full transition-all duration-1000 shadow-lg shadow-green-500/20" style="width: 100%;"></div>
                        </div>
                    </div>

                    <div class="mt-8 flex flex-col items-center gap-4">
                        <div class="px-4 py-2 bg-white/5 border border-white/10 rounded-xl">
                            <code class="text-purple-300 font-black tracking-widest text-sm uppercase">CÓDIGO: {{ $codigo_aula }}</code>
                        </div>
                        <p class="text-white/40 text-[10px] font-bold uppercase tracking-widest leading-relaxed mb-4">
                            Aponte a câmera para o código ou use o link abaixo:
                        </p>
                        <a href="{{ route('presenca.confirmar', $codigo_aula) }}" target="_blank"
                           class="inline-flex items-center gap-2 px-6 py-3 bg-purple-500/10 hover:bg-purple-500/20 border border-purple-500/20 text-purple-300 hover:text-white rounded-xl transition-all duration-300 text-[10px] font-black uppercase tracking-[0.15em] group">
                            <span>Registrar Presença (Link)</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 transform group-hover:translate-x-1 group-hover:-translate-y-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </a>
                    </div>
                </div>

                <div class="glass p-8 rounded-3xl border border-white/10 animate-reveal [animation-delay:400ms]">
                    <h3 class="font-black text-white mb-6 text-sm uppercase tracking-widest italic flex items-center gap-2">
                        <span class="w-1.5 h-1.5 bg-purple-500 rounded-full"></span>
                        Detalhes da Aula
                    </h3>
                    <div class="grid grid-cols-2 gap-6">
                        <div class="flex flex-col">
                            <span class="text-[10px] font-bold text-white/20 uppercase tracking-widest mb-1">Semestre</span>
                            <span class="text-white font-bold">{{ $semestre }}º Semestre</span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-[10px] font-bold text-white/20 uppercase tracking-widest mb-1">Período</span>
                            <span class="text-white font-bold">{{ $horario == 'M' ? 'Matutino' : ($horario == 'V' ? 'Vespertino' : 'Noturno') }}</span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-[10px] font-bold text-white/20 uppercase tracking-widest mb-1">Data</span>
                            <span class="text-white font-bold">{{ \Carbon\Carbon::now()->format('d/m/Y') }}</span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-[10px] font-bold text-white/20 uppercase tracking-widest mb-1">Status</span>
                            <span class="text-green-400 font-bold flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span>
                                Transmissão Ativa
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Coluna Direita: Lista de Presença --}}
            <div class="lg:col-span-7 flex flex-col gap-6 animate-reveal [animation-delay:400ms]">
                <div class="glass p-8 rounded-[2.5rem] border border-white/10 flex-grow flex flex-col min-h-[600px] overflow-hidden">
                    <div class="flex justify-between items-center mb-8">
                        <div>
                            <h2 class="text-3xl font-black text-white tracking-tighter italic">Alunos Presentes</h2>
                            <p class="text-white/40 text-xs font-medium tracking-tight mt-1">Sincronização automática a cada 3s.</p>
                        </div>
                        <div class="flex items-center gap-3 bg-purple-500 p-3 rounded-2xl shadow-xl shadow-purple-900/40 transform -rotate-3">
                            <span class="text-white text-3xl font-black leading-none" id="contador-alunos">0</span>
                        </div>
                    </div>

                    <div class="flex-grow overflow-y-auto custom-scrollbar">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="text-[10px] font-black text-white/20 uppercase tracking-[0.2em] border-b border-white/5">
                                    <th class="pb-4 px-2">Aluno</th>
                                    <th class="pb-4 px-2 text-center">Registro</th>
                                    <th class="pb-4 px-2 text-center">Horário</th>
                                    <th class="pb-4 px-2 text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody id="lista-presenca" class="divide-y divide-white/5">
                                <tr id="empty-state">
                                    <td colspan="4" class="py-20 text-center">
                                        <div class="flex flex-col items-center gap-4 opacity-20">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <span class="text-xs font-black uppercase tracking-[0.2em]">Aguardando leituras...</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    {{-- Glass Expirado Overlay --}}
    <div id="expired-overlay" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-6 backdrop-blur-xl bg-black/60">
        <div class="glass p-12 rounded-[3rem] border-2 border-white/10 max-w-md w-full text-center relative overflow-hidden animate-reveal">
            <div class="absolute -top-24 -right-24 w-48 h-48 bg-red-500/10 blur-3xl"></div>
            
            <div class="w-20 h-20 bg-red-500/20 rounded-3xl flex items-center justify-center text-4xl mx-auto mb-8 animate-pulse">
                ⏰
            </div>
            
            <h2 class="text-4xl font-black text-white tracking-tighter mb-4 italic">Chamada Encerrada</h2>
            <p class="text-white/40 leading-relaxed mb-10 font-medium">
                O tempo de validade deste QR Code expirou. Para uma nova chamada, inicie outra sessão.
            </p>
            
            <a href="{{ route('professor.presenca.index') }}" 
               class="block w-full bg-white text-dark_purple hover:scale-[1.02] active:scale-95 transition-all font-black py-4 rounded-xl text-lg shadow-2xl">
                Voltar às Disciplinas
            </a>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        // Gerar QR Code
        const qrContent = "{{ route('presenca.confirmar', $codigo_aula) }}";
        new QRCode(document.getElementById("qrcode"), {
            text: qrContent,
            width: 210,
            height: 210,
            colorDark : "#000000",
            colorLight : "#ffffff",
            correctLevel : QRCode.CorrectLevel.H
        });

        // ========== TIMER ==========
        const expiraEm = {{ $expiraEmTimestamp }};
        const timerText = document.getElementById('timer-text');
        const timerBigText = document.getElementById('timer-big-text');
        const timerBar = document.getElementById('timer-bar');
        const timerDot = document.getElementById('timer-dot');
        const expiredOverlay = document.getElementById('expired-overlay');
        const duracaoTotal = 2 * 60 * 60;

        function atualizarTimer() {
            const agora = Math.floor(Date.now() / 1000);
            const restante = expiraEm - agora;

            if (restante <= 0) {
                timerText.textContent = 'EXPIRADO';
                timerBigText.textContent = '00:00:00';
                timerBar.style.width = '0%';
                timerBar.className = "h-full rounded-full transition-all duration-1000 bg-red-500";
                timerDot.className = "w-2 h-2 bg-red-400 rounded-full";
                expiredOverlay.classList.remove('hidden');
                return;
            }

            const horas = Math.floor(restante / 3600);
            const minutos = Math.floor((restante % 3600) / 60);
            const segundos = restante % 60;
            const formatted = `${String(horas).padStart(2, '0')}:${String(minutos).padStart(2, '0')}:${String(segundos).padStart(2, '0')}`;

            timerText.textContent = formatted;
            timerBigText.textContent = formatted;

            const progresso = Math.max(0, (restante / duracaoTotal) * 100);
            timerBar.style.width = progresso + '%';

            if (restante < 600) {
                timerBar.className = "h-full rounded-full transition-all duration-1000 bg-gradient-to-r from-yellow-500 to-orange-400 shadow-lg shadow-yellow-500/20";
                timerDot.className = "w-2 h-2 bg-yellow-400 rounded-full animate-ping";
            }
            if (restante < 120) {
                timerBar.className = "h-full rounded-full transition-all duration-1000 bg-gradient-to-r from-red-600 to-pink-500 shadow-lg shadow-red-500/20";
                timerDot.className = "w-2 h-2 bg-red-500 rounded-full animate-ping";
            }
        }

        setInterval(atualizarTimer, 1000);
        atualizarTimer();

        // ========== POLLING PRESENÇA ==========
        const tabelaBody = document.getElementById('lista-presenca');
        const contador = document.getElementById('contador-alunos');
        const emptyState = document.getElementById('empty-state');
        const checkUrl = "{{ route('professor.presenca.check', $codigo_aula) }}";

        function atualizarPresencas() {
            fetch(checkUrl)
                .then(response => response.json())
                .then(data => {
                    if (data.length > 0) {
                        if (emptyState) emptyState.style.display = 'none';
                        tabelaBody.innerHTML = '';
                        
                        // Sort by latest
                        data.sort((a,b) => new Date(b.created_at) - new Date(a.created_at));

                        data.forEach((presenca, index) => {
                            const row = document.createElement('tr');
                            row.className = `group hover:bg-white/5 transition-all duration-300 animate-reveal`;
                            row.style.animationDelay = `${index * 50}ms`;
                            
                            const dataHora = new Date(presenca.created_at);
                            const horaFormatada = dataHora.toLocaleTimeString('pt-BR');

                            row.innerHTML = `
                                <td class="py-4 px-2">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 bg-white/5 rounded-lg flex items-center justify-center text-[10px] font-black text-white group-hover:bg-purple-500/20 transition-colors capitalize">
                                            ${presenca.aluno ? presenca.aluno.nome.charAt(0) : '?'}
                                        </div>
                                        <span class="text-sm font-bold text-white tracking-tight">${presenca.aluno ? presenca.aluno.nome : 'Desconhecido'}</span>
                                    </div>
                                </td>
                                <td class="py-4 px-2 text-center text-xs font-medium text-white/40 font-mono tracking-wider">${presenca.aluno_ra}</td>
                                <td class="py-4 px-2 text-center text-xs font-black text-white/60 tabular-nums tracking-widest">${horaFormatada}</td>
                                <td class="py-4 px-2 text-right">
                                    <span class="inline-flex items-center gap-1.5 px-2 py-1 bg-green-500/10 text-green-400 rounded-lg text-[9px] font-black uppercase tracking-widest">
                                        <span class="w-1 h-1 bg-green-500 rounded-full"></span>
                                        Confirmado
                                    </span>
                                </td>
                            `;
                            tabelaBody.appendChild(row);
                        });

                        contador.innerText = data.length;
                    }
                })
                .catch(error => console.error('Erro ao buscar presenças:', error));
        }

        setInterval(atualizarPresencas, 3000);
        atualizarPresencas();
    </script>
@endpush
