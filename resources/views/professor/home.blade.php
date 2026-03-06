@extends('layouts.main')

@section('title', 'Painel do Professor - Smart Attendance')

@section('body-class', 'gradient-bg relative min-h-screen flex flex-col')

@section('content')
    <div class="flex-grow flex flex-col relative overflow-hidden">
        
        <!-- Background Elements -->
        <div class="blob top-[-100px] left-[-100px]"></div>
        <div class="blob-2"></div>

        <!-- Glass Navbar -->
        <nav class="glass mx-6 mt-6 p-4 rounded-2xl border border-white/10 relative z-20 backdrop-blur-xl animate-reveal">
            <div class="max-w-7xl mx-auto flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 bg-indigo-500 rounded-xl flex items-center justify-center text-white font-black italic shadow-lg shadow-indigo-500/50">
                        C
                    </div>
                    <h1 class="text-xl font-black tracking-tighter text-white italic hidden md:block">SMART ATTENDANCE</h1>
                </div>

                <div class="flex items-center gap-6">
                    <div class="hidden sm:flex flex-col items-end">
                        <span class="text-[10px] font-bold tracking-widest text-indigo-300 uppercase">Professor Conectado</span>
                        <span class="text-white font-bold text-sm">{{ $professor->nome ?? 'Docente' }}</span>
                    </div>

                    <a href="{{ route('logout') }}"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                        class="px-5 py-2 bg-white/5 hover:bg-white/10 border border-white/10 rounded-xl text-white text-xs font-bold transition-all hover:scale-105 active:scale-95">
                        Sair
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </div>
            </div>
        </nav>

        <main class="max-w-7xl mx-auto w-full p-6 mt-8 relative z-10">
            
            <div class="mb-12 animate-reveal [animation-delay:200ms]">
                <h2 class="text-4xl font-black text-white tracking-tighter mb-2">Painel de Controle</h2>
                <p class="text-white/40 font-medium">Gerencie suas aulas e registros de presença em tempo real.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12 animate-reveal [animation-delay:400ms]">

                <!-- Card: Gerar QR -->
                <div class="tilt-card glass p-8 rounded-3xl border border-white/10 hover:border-indigo-400/30 transition-all group overflow-hidden relative">
                    <div class="absolute -top-12 -right-12 w-32 h-32 bg-indigo-600/20 blur-3xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    
                    <div class="w-14 h-14 bg-indigo-600/20 rounded-2xl flex items-center justify-center text-2xl mb-6 group-hover:scale-110 transition-transform">
                        ⚡
                    </div>
                    <h3 class="text-2xl font-black text-white mb-2 tracking-tight">Gerar QR Code</h3>
                    <p class="text-white/40 text-sm leading-relaxed mb-6">Inicie uma nova sessão de presença para sua turma atual.</p>
                    
                    <a href="{{ route('professor.presenca.index') }}"
                        class="inline-flex items-center gap-3 bg-indigo-600 hover:bg-indigo-500 text-white font-bold px-6 py-3 rounded-xl transition-all hover:gap-5">
                        <span>Iniciar Agora</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                </div>

                <!-- Card: Histórico -->
                <div class="tilt-card glass p-8 rounded-3xl border border-white/10 hover:border-purple-400/30 transition-all group overflow-hidden relative">
                    <div class="absolute -top-12 -right-12 w-32 h-32 bg-purple-600/20 blur-3xl opacity-0 group-hover:opacity-100 transition-opacity"></div>

                    <div class="w-14 h-14 bg-purple-600/20 rounded-2xl flex items-center justify-center text-2xl mb-6 group-hover:scale-110 transition-transform">
                        📊
                    </div>
                    <h3 class="text-2xl font-black text-white mb-2 tracking-tight">Relatórios</h3>
                    <p class="text-white/40 text-sm leading-relaxed mb-6">Consulte o histórico de frequências e exporte dados.</p>
                    
                    <a href="{{ route('professor.relatorios') }}"
                        class="inline-flex items-center gap-3 border border-white/10 hover:bg-white/5 text-white/80 font-bold px-6 py-3 rounded-xl transition-all">
                        <span>Ver Histórico</span>
                    </a>
                </div>

                <!-- Card: Matérias -->
                <div class="tilt-card glass p-8 rounded-3xl border border-white/10 hover:border-fuchsia-400/30 transition-all group overflow-hidden relative">
                    <div class="absolute -top-12 -right-12 w-32 h-32 bg-fuchsia-600/20 blur-3xl opacity-0 group-hover:opacity-100 transition-opacity"></div>

                    <div class="w-14 h-14 bg-fuchsia-600/20 rounded-2xl flex items-center justify-center text-2xl mb-6 group-hover:scale-110 transition-transform">
                        📚
                    </div>
                    <h3 class="text-2xl font-black text-white mb-2 tracking-tight">Minhas Turmas</h3>
                    <p class="text-white/40 text-sm leading-relaxed mb-6">Configure suas disciplinas e gerencie dados dos alunos.</p>
                    
                    <a href="{{ route('professor.gerenciar.index') }}"
                        class="inline-flex items-center gap-3 border border-white/10 hover:bg-white/5 text-white/80 font-bold px-6 py-3 rounded-xl transition-all">
                        <span>Configurar</span>
                    </a>
                </div>

            </div>

            <!-- Active Geração Area -->
            <section class="glass p-10 rounded-3xl border border-white/10 animate-reveal [animation-delay:600ms]">
                <div class="flex flex-col md:flex-row items-center justify-between gap-8">
                    <div class="max-w-md">
                        @if(isset($activeCode))
                            <div class="flex items-center gap-3 mb-4">
                                <span class="w-2 h-2 bg-green-400 rounded-full animate-ping"></span>
                                <span class="text-xs font-black tracking-widest text-green-500 uppercase">Chamada em Andamento</span>
                            </div>
                            <h3 class="text-3xl font-black text-white tracking-tight mb-4">QR Code Ativo</h3>
                            <p class="text-white/40 leading-relaxed">
                                Você possui uma sessão de presença ativa para <strong>{{ $activeMateria->nome }}</strong>. 
                                O código expira em breve. Clique no QR Code para gerenciar a lista.
                            </p>
                        @else
                            <div class="flex items-center gap-3 mb-4">
                                <span class="w-2 h-2 bg-yellow-400 rounded-full animate-ping"></span>
                                <span class="text-xs font-black tracking-widest text-yellow-500 uppercase">Sistema em Espera</span>
                            </div>
                            <h3 class="text-3xl font-black text-white tracking-tight mb-4">Geração de Código Ativa</h3>
                            <p class="text-white/40 leading-relaxed">
                                Ao iniciar uma sessão, o QR Code dinâmico será geratado e exibido nesta área. 
                                Os alunos poderão escanear em tempo real para registrar a presença.
                            </p>
                        @endif
                    </div>
                    
                    @if(isset($activeCode))
                        <a href="{{ route('professor.presenca.gerar', $activeMateria->id) }}" 
                           class="w-full md:w-64 aspect-square bg-white p-4 rounded-[2rem] flex items-center justify-center relative overflow-hidden group shadow-2xl shadow-indigo-500/40 hover:scale-105 transition-all duration-500">
                            <div class="absolute inset-0 bg-indigo-500 opacity-0 group-hover:opacity-10 transition-opacity"></div>
                            <div id="qrcode" class="relative z-10 p-2"></div>
                        </a>
                    @else
                        <div class="w-full md:w-64 aspect-square bg-white/5 border-2 border-dashed border-white/10 rounded-2xl flex items-center justify-center relative overflow-hidden group">
                            <div class="absolute inset-0 bg-indigo-500/5 group-hover:bg-indigo-500/10 transition-colors"></div>
                            <div class="text-center relative z-10 px-6">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-white/10 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                </svg>
                                <p class="text-white/10 text-[10px] font-black tracking-widest uppercase">Aguardando Início</p>
                            </div>
                        </div>
                    @endif
                </div>
            </section>

        </main>
    </div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const cards = document.querySelectorAll('.tilt-card');
        
        cards.forEach(card => {
            card.addEventListener('mousemove', e => {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                const centerX = rect.width / 2;
                const centerY = rect.height / 2;
                const rotateX = (y - centerY) / 20;
                const rotateY = (centerX - x) / 20;
                card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale3d(1.02, 1.02, 1.02)`;
            });
            card.addEventListener('mouseleave', () => {
                card.style.transform = `perspective(1000px) rotateX(0deg) rotateY(0deg) scale3d(1, 1, 1)`;
            });
        });

        @if(isset($activeCode))
            // Gerar QR Code
            const qrContent = "{{ route('presenca.confirmar', $activeCode) }}";
            new QRCode(document.getElementById("qrcode"), {
                text: qrContent,
                width: 200,
                height: 200,
                colorDark : "#000000",
                colorLight : "#ffffff",
                correctLevel : QRCode.CorrectLevel.H
            });
        @endif
    });
</script>
@endpush
