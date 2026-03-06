@extends('layouts.main')

@section('title', 'Painel Master - Smart Attendance')

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
                    <div class="w-10 h-10 bg-blue-500 rounded-xl flex items-center justify-center text-white font-black italic shadow-lg shadow-blue-500/50">
                        M
                    </div>
                    <h1 class="text-xl font-black tracking-tighter text-white italic hidden md:block">ADMIN MASTER</h1>
                </div>

                <div class="flex items-center gap-6">
                    <div class="hidden sm:flex flex-col items-end">
                        <span class="text-[10px] font-bold tracking-widest text-blue-300 uppercase">Acesso Root</span>
                        <span class="text-white font-bold text-sm">{{ $master->nome ?? 'Administrador' }}</span>
                    </div>

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" 
                            class="px-5 py-2 bg-white/5 hover:bg-white/10 border border-white/10 rounded-xl text-white text-xs font-bold transition-all hover:scale-105 active:scale-95">
                            Sair
                        </button>
                    </form>
                </div>
            </div>
        </nav>

        <main class="max-w-7xl mx-auto w-full p-6 mt-8 relative z-10">
            
            <div class="mb-12 animate-reveal [animation-delay:200ms]">
                <h2 class="text-4xl font-black text-white tracking-tighter mb-2">Visão Geral do Sistema</h2>
                <p class="text-white/40 font-medium">Controle total de usuários, turmas e registros de presença.</p>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12 animate-reveal [animation-delay:300ms]">
                <div class="glass p-6 rounded-3xl border border-white/10 flex items-center gap-5 group hover:bg-white/5 transition-colors">
                    <div class="w-14 h-14 bg-blue-500/20 rounded-2xl flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">👨‍🏫</div>
                    <div>
                        <p class="text-3xl font-black text-white leading-none mb-1">{{ $professoresCount }}</p>
                        <p class="text-xs font-bold text-white/40 uppercase tracking-widest">Professores</p>
                    </div>
                </div>
                <div class="glass p-6 rounded-3xl border border-white/10 flex items-center gap-5 group hover:bg-white/5 transition-colors">
                    <div class="w-14 h-14 bg-purple-500/20 rounded-2xl flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">🧑‍🎓</div>
                    <div>
                        <p class="text-3xl font-black text-white leading-none mb-1">{{ $alunosCount }}</p>
                        <p class="text-xs font-bold text-white/40 uppercase tracking-widest">Alunos</p>
                    </div>
                </div>
                <div class="glass p-6 rounded-3xl border border-white/10 flex items-center gap-5 group hover:bg-white/5 transition-colors">
                    <div class="w-14 h-14 bg-teal-500/20 rounded-2xl flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">📚</div>
                    <div>
                        <p class="text-3xl font-black text-white leading-none mb-1">{{ $materiasCount }}</p>
                        <p class="text-xs font-bold text-white/40 uppercase tracking-widest">Matérias</p>
                    </div>
                </div>
            </div>

            <!-- Main Actions -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8 animate-reveal [animation-delay:400ms]">

                <!-- Professores -->
                <a href="{{ route('master.professores') }}" class="tilt-card glass p-8 rounded-3xl border border-white/10 hover:border-blue-400/30 transition-all group relative overflow-hidden">
                    <div class="absolute -top-12 -right-12 w-32 h-32 bg-blue-600/20 blur-3xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <h3 class="text-2xl font-black text-white mb-2 tracking-tight">Professores</h3>
                    <p class="text-white/40 text-sm leading-relaxed mb-6">Visualizar, pesquisar e gerenciar docentes do sistema.</p>
                    <span class="text-blue-400 font-bold group-hover:gap-2 flex items-center gap-1 transition-all">Explorar →</span>
                </a>

                <!-- Alunos -->
                <a href="{{ route('master.alunos') }}" class="tilt-card glass p-8 rounded-3xl border border-white/10 hover:border-purple-400/30 transition-all group relative overflow-hidden">
                    <div class="absolute -top-12 -right-12 w-32 h-32 bg-purple-600/20 blur-3xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <h3 class="text-2xl font-black text-white mb-2 tracking-tight">Alunos</h3>
                    <p class="text-white/40 text-sm leading-relaxed mb-6">Gerencie matriculas e acesso dos estudantes.</p>
                    <span class="text-purple-400 font-bold group-hover:gap-2 flex items-center gap-1 transition-all">Explorar →</span>
                </a>

                <!-- Matérias -->
                <a href="{{ route('master.materias') }}" class="tilt-card glass p-8 rounded-3xl border border-white/10 hover:border-teal-400/30 transition-all group relative overflow-hidden">
                    <div class="absolute -top-12 -right-12 w-32 h-32 bg-teal-600/20 blur-3xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <h3 class="text-2xl font-black text-white mb-2 tracking-tight">Matérias</h3>
                    <p class="text-white/40 text-sm leading-relaxed mb-6">Configure as disciplinas e vínculos curriculares.</p>
                    <span class="text-teal-400 font-bold group-hover:gap-2 flex items-center gap-1 transition-all">Explorar →</span>
                </a>

            </div>

            <!-- Large Action: Chamada -->
            <a href="{{ route('master.presenca') }}" class="glass p-8 rounded-3xl border border-white/10 hover:bg-white/5 transition-all animate-reveal [animation-delay:600ms] block group">
                <div class="flex flex-col md:flex-row items-center justify-between gap-8">
                    <div class="max-w-xl">
                        <div class="flex items-center gap-3 mb-4">
                            <span class="px-2 py-0.5 bg-orange-500/20 text-orange-400 text-[10px] font-black tracking-widest uppercase rounded">Novo Módulo</span>
                        </div>
                        <h3 class="text-3xl font-black text-white tracking-tighter mb-4">Central de Chamada QR Code</h3>
                        <p class="text-white/40 leading-relaxed mb-6">
                            Gere códigos de aula de forma centralizada e monitore as presenças em tempo real. 
                            Acesse o log completo de atividades e frequências.
                        </p>
                        <span class="inline-block bg-white text-dark_purple font-black px-8 py-3 rounded-xl transition-all group-hover:scale-105 active:scale-95">
                            Acessar Central Control
                        </span>
                    </div>
                    
                    <div class="w-24 h-24 bg-orange-600/20 rounded-3xl flex items-center justify-center text-4xl group-hover:rotate-12 transition-transform shadow-2xl shadow-orange-600/20">
                        📱
                    </div>
                </div>
            </a>

        </main>
    </div>
@endsection

@push('scripts')
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
    });
</script>
@endpush
