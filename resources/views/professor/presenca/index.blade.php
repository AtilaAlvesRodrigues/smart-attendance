@extends('layouts.theme')

@section('title', 'Selecionar Matéria - Smart Attendance')

@section('body-class', 'gradient-bg relative min-h-screen flex flex-col')
@section('nav-left')
    <a href="{{ route('dashboard.professor') }}" class="pal-nav-btn pal-nav-btn-ghost">
        ← Dashboard
    </a>
@endsection

@section('nav-user')
    <div class="pal-nav-user">
        <span class="pal-nav-user-role">Professor</span>
        <span class="pal-nav-user-name">{{ $professor->nome ?? 'Docente' }}</span>
    </div>
@endsection

@section('content')
    <div class="flex-grow flex flex-col relative overflow-hidden">
        
        <!-- Background Elements -->
        <div class="blob top-[-100px] right-[-100px]"></div>
        <div class="blob-2"></div>

        <main class="max-w-4xl mx-auto w-full p-6 mt-12 relative z-10">
            
            <div class="mb-12 animate-reveal [animation-delay:200ms]">
                <p class="pal-eyebrow" style="margin-bottom:0.5rem">Módulo Presença Digital</p>
                <h2 class="pal-title">Selecione a Disciplina</h2>
                <p class="pal-subtitle">Escolha qual matéria você irá registrar presença hoje.</p>
            </div>

            @if($materias->isEmpty())
                <div class="glass p-10 rounded-sm border border-yellow-500/30 text-center animate-reveal [animation-delay:400ms]">
                    <div class="text-5xl mb-6">🏜️</div>
                    <h3 class="text-2xl font-black pal-text mb-2">Sem Matérias</h3>
                    <p class="pal-subtitle">Você ainda não possui turmas vinculadas ao seu perfil.</p>
                </div>
            @else
                @php $hasActiveCode = $materias->contains(fn($m) => $m->active_code); @endphp

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 animate-reveal [animation-delay:400ms]">
                    @foreach($materias as $materia)
                        <div class="tilt-card glass p-8 rounded-3x border {{ $materia->active_code ? 'border-green-500/30' : 'border-white/10' }} group overflow-hidden relative transition-all">
                            
                            @if($materia->active_code)
                                <div class="absolute -top-12 -right-12 w-32 h-32 bg-green-500/20 blur-3xl"></div>
                            @endif

                            <div class="flex justify-between items-start mb-6">
                                <div>
                                    <h3 class="text-2xl font-black pal-text tracking-tight leading-tight group-hover:text-indigo-400 transition-colors">{{ $materia->nome }}</h3>
                                    <div class="mt-3 flex items-center gap-2">
                                        <span class="px-3 py-1 bg-white/5 border border-white/10 rounded-sm text-xs font-black pal-subtitle tracking-widest uppercase">
                                            {{ $materia->horario_matutino ? '☀️ Matutino' : ($materia->horario_noturno ? '🌙 Noturno' : '⛅ Vespertino') }}
                                        </span>
                                    </div>
                                </div>
                                
                                @if($materia->active_code)
                                    <div class="flex items-center gap-2 px-3 py-1 bg-green-500/20 border border-green-500/30 rounded-full">
                                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span>
                                        <span class="text-[9px] font-black text-green-400 tracking-widest uppercase">Ativo</span>
                                    </div>
                                @endif
                            </div>

                            @if($materia->active_code)
                                <a href="{{ route('professor.presenca.gerar', $materia->id) }}" 
                                   target="_blank"
                                   class="block w-full bg-green-600 hover:bg-green-500 text-white text-center py-4 rounded-sm font-black text-lg transition-all shadow-xl shadow-green-900/40 hover:scale-[1.02] active:scale-95">
                                    Acessar QR Code
                                </a>
                            @else
                                @if($hasActiveCode)
                                    <button onclick="mostrarPopup('{{ $materia->nome }}')"
                                       class="block w-full bg-white/5 border border-white/10 text-white/70 text-center py-4 rounded-sm font-black text-lg cursor-not-allowed">
                                        Indisponível (Conflito)
                                    </button>
                                @else
                                    <a href="{{ route('professor.presenca.gerar', $materia->id) }}" 
                                       target="_blank"
                                       class="block w-full bg-white hover:bg-white/90 text-black text-center py-4 rounded-sm font-black text-lg transition-all shadow-xl shadow-white/20 hover:scale-[1.02] active:scale-95">
                                        Gerar QR Code
                                    </a>
                                @endif
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </main>
    </div>

    {{-- Glass Popup --}}
    <div id="popup-overlay" class="hidden fixed inset-0 z-50 flex items-center justify-center p-6 backdrop-blur-md bg-black/40">
        <div class="glass p-10 rounded-[2.5rem] border-2 border-white/10 max-w-md w-full text-center relative overflow-hidden animate-reveal">
            <div class="absolute -top-24 -right-24 w-48 h-48 bg-yellow-500/10 blur-3xl"></div>
            
            <div class="w-20 h-20 bg-yellow-500/20 rounded-sm flex items-center justify-center text-4xl mx-auto mb-8 animate-bounce">
                ⚠️
            </div>
            
            <h2 class="text-3xl font-black pal-text tracking-tighter mb-4 italic">Conflito Ativo</h2>
            <p class="pal-subtitle leading-relaxed mb-6 font-medium">
                Você já possui um QR Code de frequência ativo para outra disciplina no sistema.
            </p>
            
            <div id="popup-materia" class="mb-10 px-4 py-2 bg-white/5 border border-white/10 rounded-sm text-yellow-300 font-bold text-sm inline-block"></div>
            
            <button onclick="fecharPopup()" 
                class="w-full bg-[#efefef] text-[#050505] hover:bg-[#0d0d0d] hover:text-[#efefef] font-black py-4 rounded-sm transition-all hover:scale-[1.02] active:scale-95 border border-white/10">
                Entendido
            </button>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function mostrarPopup(nomeMateria) {
            document.getElementById('popup-materia').textContent = nomeMateria;
            document.getElementById('popup-overlay').classList.remove('hidden');
        }

        function fecharPopup() {
            document.getElementById('popup-overlay').classList.add('hidden');
        }

        document.getElementById('popup-overlay').addEventListener('click', function(e) {
            if (e.target === this) fecharPopup();
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') fecharPopup();
        });

        // Tilt effect
        document.addEventListener('DOMContentLoaded', () => {
            const cards = document.querySelectorAll('.tilt-card');
            cards.forEach(card => {
                card.addEventListener('mousemove', e => {
                    const rect = card.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;
                    const rotateX = (y - rect.height/2) / 15;
                    const rotateY = (rect.width/2 - x) / 15;
                    card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale3d(1.02, 1.02, 1.02)`;
                });
                card.addEventListener('mouseleave', () => {
                    card.style.transform = `perspective(1000px) rotateX(0deg) rotateY(0deg) scale3d(1, 1, 1)`;
                });
            });
        });
    </script>
@endpush
