@extends('layouts.main')

@section('title', 'Selecionar Matéria - Smart Attendance')

@section('body-class', 'gradient-bg relative min-h-screen flex flex-col')

@section('content')
    <div class="flex-grow flex flex-col relative overflow-hidden">
        
        <!-- Background Elements -->
        <div class="blob top-[-100px] right-[-100px]"></div>
        <div class="blob-2"></div>

        <!-- Glass Navbar -->
        <nav class="glass mx-6 mt-6 p-4 rounded-2xl border border-white/10 relative z-20 backdrop-blur-xl animate-reveal">
            <div class="max-w-7xl mx-auto flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 bg-purple-500 rounded-xl flex items-center justify-center text-white font-black italic shadow-lg shadow-purple-500/50">
                        P
                    </div>
                    <h1 class="text-xl font-black tracking-tighter text-white italic hidden md:block">PRESENÇA DIGITAL</h1>
                </div>

                <a href="{{ route('dashboard.professor') }}" 
                    class="px-5 py-2 bg-white/5 hover:bg-white/10 border border-white/10 rounded-xl text-white text-xs font-bold transition-all hover:scale-105 active:scale-95">
                    Voltar ao Dashboard
                </a>
            </div>
        </nav>

        <main class="max-w-4xl mx-auto w-full p-6 mt-12 relative z-10">
            
            <div class="mb-12 animate-reveal [animation-delay:200ms]">
                <h2 class="text-4xl font-black text-white tracking-tighter mb-2 italic">Selecione a Disciplina</h2>
                <p class="text-white/40 font-medium tracking-tight">Escolha qual matéria você irá registrar presença hoje.</p>
            </div>

            @if($materias->isEmpty())
                <div class="glass p-10 rounded-3xl border border-yellow-500/30 text-center animate-reveal [animation-delay:400ms]">
                    <div class="text-5xl mb-6">🏜️</div>
                    <h3 class="text-2xl font-black text-white mb-2">Sem Matérias</h3>
                    <p class="text-white/40">Você ainda não possui turmas vinculadas ao seu perfil.</p>
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
                                    <h3 class="text-2xl font-black text-white tracking-tight leading-tight group-hover:text-purple-300 transition-colors">{{ $materia->nome }}</h3>
                                    <div class="mt-3 flex items-center gap-2">
                                        <span class="px-3 py-1 bg-white/5 border border-white/10 rounded-lg text-[10px] font-black text-white/50 tracking-widest uppercase">
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
                                   class="block w-full bg-green-600 hover:bg-green-500 text-white text-center py-4 rounded-xl font-black text-lg transition-all shadow-xl shadow-green-900/40 hover:scale-[1.02] active:scale-95">
                                    Acessar QR Code
                                </a>
                            @else
                                @if($hasActiveCode)
                                    <button onclick="mostrarPopup('{{ $materia->nome }}')"
                                       class="block w-full bg-white/5 border border-white/10 text-white/20 text-center py-4 rounded-xl font-black text-lg cursor-not-allowed">
                                        Indisponível (Conflito)
                                    </button>
                                @else
                                    <a href="{{ route('professor.presenca.gerar', $materia->id) }}" 
                                       target="_blank"
                                       class="block w-full bg-purple-600 hover:bg-purple-500 text-white text-center py-4 rounded-xl font-black text-lg transition-all shadow-xl shadow-purple-900/40 hover:scale-[1.02] active:scale-95">
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
            
            <div class="w-20 h-20 bg-yellow-500/20 rounded-3xl flex items-center justify-center text-4xl mx-auto mb-8 animate-bounce">
                ⚠️
            </div>
            
            <h2 class="text-3xl font-black text-white tracking-tighter mb-4 italic">Conflito Ativo</h2>
            <p class="text-white/40 leading-relaxed mb-6 font-medium">
                Você já possui um QR Code de frequência ativo para outra disciplina no sistema.
            </p>
            
            <div id="popup-materia" class="mb-10 px-4 py-2 bg-white/5 border border-white/10 rounded-xl text-yellow-300 font-bold text-sm inline-block"></div>
            
            <button onclick="fecharPopup()" 
                class="w-full bg-white/10 hover:bg-white text-white hover:text-dark_purple font-black py-4 rounded-xl transition-all hover:scale-[1.02] active:scale-95">
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
