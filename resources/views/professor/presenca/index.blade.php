@extends('layouts.theme')

@section('title', 'Selecionar Matéria - Smart Attendance')

@section('body-class', 'gradient-bg relative min-h-screen flex flex-col')
@section('nav-left')
    <a href="{{ route('dashboard.professor') }}" class="pal-nav-btn pal-nav-btn-ghost">
        ← Dashboard
    </a>
@endsection

@section('nav-user')
<div class="pal-nav-actions" style="gap:0.5rem">
    <div class="pal-nav-user">
        <span class="pal-nav-user-role">Professor</span>
        <span class="pal-nav-user-name">{{ $professor->nome ?? 'Docente' }}</span>
    </div>
    <button id="open-profile" class="pal-profile-btn" type="button">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
    </button>
</div>
@endsection

@section('content')

    {{-- Profile Modal --}}
    <div id="profile-modal" class="pal-modal-overlay" style="display:none;">
        <div id="close-modal-overlay" style="position:absolute; inset:0;"></div>
        <div class="pal-modal-content">
            <div class="pal-modal-header">
                <div>
                    <p class="pal-eyebrow" style="margin-bottom:0.3rem;">Painel Docente</p>
                    <h2 class="pal-always-white" style="font-size:1.4rem; font-weight:900; letter-spacing:-0.03em; margin:0;">Meu Perfil</h2>
                </div>
                <button id="close-profile" class="pal-profile-btn" style="border-color:rgba(255,255,255,0.1); color:#888;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="pal-modal-body">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:2rem;">
                    <div class="pal-profile-field">
                        <p class="pal-profile-field-label">Nome Completo</p>
                        <p class="pal-profile-field-value">{{ $professor->nome ?? '—' }}</p>
                    </div>
                    <div class="pal-profile-field">
                        <p class="pal-profile-field-label">CPF</p>
                        <p class="pal-profile-field-value">{{ $professor->cpf ?? '—' }}</p>
                    </div>
                    <div class="pal-profile-field" style="grid-column:span 2;">
                        <p class="pal-profile-field-label">E-mail</p>
                        <p class="pal-profile-field-value">{{ $professor->email ?? '—' }}</p>
                    </div>
                </div>
                <hr class="pal-divider" style="margin-bottom:1.5rem;">
                <p class="pal-eyebrow" style="margin-bottom:1rem;">Disciplinas Vinculadas</p>
                <div style="display:flex; flex-direction:column; gap:0.75rem;">
                    @forelse($professor->materias ?? [] as $materia)
                    <div class="pal-profile-field">
                        <p class="pal-profile-field-value" style="font-size:0.9rem;">{{ $materia->nome }}</p>
                        <p style="font-size:0.72rem; color:#999; margin:0.2rem 0 0;">{{ $materia->carga_horaria }}h · {{ $materia->total_aulas }} aulas</p>
                    </div>
                    @empty
                    <p style="color:#777; font-size:0.85rem;">Nenhuma disciplina vinculada.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

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
                                   class="pal-btn-action"
                                   style="background:#22c55e; color:#fff; box-shadow:0 8px 25px rgba(34,197,94,0.25);">
                                    Acessar QR Code ✓
                                </a>
                            @else
                                @if($hasActiveCode)
                                    <button onclick="mostrarPopup('{{ $materia->nome }}')"
                                       class="pal-btn-action-disabled">
                                        Indisponível — Conflito Ativo
                                    </button>
                                @else
                                    <a href="{{ route('professor.presenca.gerar', $materia->id) }}" 
                                       target="_blank"
                                       class="pal-btn-action">
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
        // Profile Modal
        const profileModal = document.getElementById('profile-modal');
        const openProfileBtn = document.getElementById('open-profile');
        const closeProfileBtn = document.getElementById('close-profile');
        const closeModalOverlay = document.getElementById('close-modal-overlay');
        function toggleProfileModal(show) {
            profileModal.style.display = show ? 'flex' : 'none';
            document.body.style.overflow = show ? 'hidden' : '';
        }
        if (openProfileBtn) openProfileBtn.addEventListener('click', () => toggleProfileModal(true));
        if (closeProfileBtn) closeProfileBtn.addEventListener('click', () => toggleProfileModal(false));
        if (closeModalOverlay) closeModalOverlay.addEventListener('click', () => toggleProfileModal(false));

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
