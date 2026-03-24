@extends('layouts.theme')

@section('title', 'Painel do Aluno - Smart Attendance')

@section('body-class', 'gradient-bg')
@section('nav-user')
<div class="pal-nav-user" style="margin-right:0;">
    <span class="pal-nav-user-role">Aluno</span>
    <span class="pal-nav-user-name">{{ $aluno->nome ?? 'Estudante' }}</span>
</div>
<button id="open-profile" style="width:36px; height:36px; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); border-radius:3px; display:flex; align-items:center; justify-content:center; cursor:pointer; color:#888; transition:all 0.2s; margin-right:0.5rem;" onmouseover="this.style.borderColor='rgba(255,255,255,0.3)'; this.style.color='#efefef'" onmouseout="this.style.borderColor='rgba(255,255,255,0.1)'; this.style.color='#888'">
    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
</button>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/theme_aluno.css') }}">
@endpush

@section('content')

    {{-- Profile Modal --}}
    <div id="profile-modal" style="display:none; position:fixed; inset:0; z-index:200; align-items:center; justify-content:center; padding:1rem;">
        <div id="close-modal-overlay" style="position:absolute; inset:0; background:rgba(0,0,0,0.7);"></div>
        <div style="position:relative; z-index:10; width:100%; max-width:640px; background:#111; border:1px solid rgba(255,255,255,0.08); border-radius:4px; overflow:hidden; max-height:85vh; overflow-y:auto;">
            <div style="padding:1.75rem 2rem; border-bottom:1px solid rgba(255,255,255,0.07); display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <p style="font-family:'Space Grotesk',monospace; font-size:0.75rem; font-weight:700; letter-spacing:0.2em; text-transform:uppercase; color:#999; margin:0 0 0.3rem;">Registro Acadêmico</p>
                    <h2 style="font-size:1.4rem; font-weight:900; letter-spacing:-0.03em; color:#efefef; margin:0;">Meu Perfil</h2>
                </div>
                <button id="close-profile" style="width:32px; height:32px; background:transparent; border:1px solid rgba(255,255,255,0.1); border-radius:3px; color:#888; cursor:pointer; display:flex; align-items:center; justify-content:center;" onmouseover="this.style.borderColor='#ef4444'; this.style.color='#ef4444'" onmouseout="this.style.borderColor='rgba(255,255,255,0.1)'; this.style.color='#888'">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div style="padding:2rem;">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:2rem;">
                    @foreach([['Nome Completo', $aluno->nome], ['E-mail', $aluno->email], ['Matrícula (RA)', $aluno->ra], ['CPF', $aluno->cpf]] as $field)
                    <div style="background:#0d0d0d; border:1px solid rgba(255,255,255,0.06); border-radius:3px; padding:1rem;">
                        <p style="font-family:'Space Grotesk',monospace; font-size:0.75rem; font-weight:700; letter-spacing:0.15em; text-transform:uppercase; color:#777; margin:0 0 0.4rem;">{{ $field[0] }}</p>
                        <p style="font-size:0.88rem; font-weight:600; color:#efefef; margin:0; word-break:break-all;">{{ $field[1] }}</p>
                    </div>
                    @endforeach
                </div>

                <hr style="border:none; border-top:1px solid rgba(255,255,255,0.06); margin-bottom:1.5rem;">
                <p style="font-family:'Space Grotesk',monospace; font-size:0.75rem; font-weight:700; letter-spacing:0.2em; text-transform:uppercase; color:#999; margin:0 0 1rem;">Matérias e Frequência</p>

                <div style="display:flex; flex-direction:column; gap:0.75rem;">
                    @forelse($aluno->materias as $materia)
                    @php
                        $percent = $materia->limite_faltas > 0 ? ($materia->faltas / $materia->limite_faltas) * 100 : 0;
                        $percent = min(100, $percent);
                        $barColor = $percent > 80 ? '#ef4444' : ($percent > 50 ? '#eab308' : '#22c55e');
                    @endphp
                    <div style="background:#0d0d0d; border:1px solid rgba(255,255,255,0.06); border-radius:3px; padding:1.25rem;">
                        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:0.75rem;">
                            <div>
                                <p style="font-size:0.9rem; font-weight:700; color:#efefef; margin:0 0 0.2rem;">{{ $materia->nome }}</p>
                                <p style="font-size:0.72rem; color:#999; margin:0;">{{ $materia->carga_horaria }}h · {{ $materia->total_aulas }} aulas</p>
                            </div>
                            <div style="text-align:right;">
                                <span style="font-size:1.4rem; font-weight:900; color:{{ $materia->faltas >= $materia->limite_faltas ? '#ef4444' : '#efefef' }};">{{ $materia->faltas }}</span>
                                <span style="font-size:0.72rem; color:#999;"> / {{ $materia->limite_faltas }} faltas</span>
                            </div>
                        </div>
                        <div style="height:3px; background:rgba(255,255,255,0.05); border-radius:2px; overflow:hidden;">
                            <div style="height:100%; width:{{ $percent }}%; background:{{ $barColor }}; transition:width 1s ease;"></div>
                        </div>
                        @if($materia->faltas >= $materia->limite_faltas)
                        <p style="font-family:'Space Grotesk',monospace; font-size:0.75rem; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; color:#ef4444; margin:0.5rem 0 0;">⚠ Limite de faltas atingido</p>
                        @endif
                    </div>
                    @empty
                    <div style="text-align:center; padding:2rem; border:1px dashed rgba(255,255,255,0.08); border-radius:3px;">
                        <p style="color:#777; font-size:0.85rem; margin:0;">Nenhuma matéria vinculada.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <main class="pal-main" style="display:flex; flex-direction:column; align-items:center;">

        {{-- Pending attendance banner --}}
        @if(session('pending_attendance_code'))
        <div class="pal-dashboard-banner" style="width:100%; max-width:900px; margin-top:2.5rem; padding:1.5rem 2rem;">
            <div>
                <p style="font-family:'Space Grotesk',monospace; font-size:0.75rem; font-weight:700; letter-spacing:0.2em; text-transform:uppercase; color:#22c55e; margin:0 0 0.3rem;">Presença Pendente</p>
                <p style="font-size:1rem; font-weight:700; color:#efefef; margin:0;">Você tem uma aula aguardando confirmação.</p>
            </div>
            <a href="{{ route('presenca.confirmar', session('pending_attendance_code')) }}"
                class="pal-dashboard-btn pal-dashboard-btn-solid" style="padding:0.65rem 1.5rem; white-space:nowrap;">
                Confirmar Agora →
            </a>
        </div>
        @endif

        {{-- Hero text --}}
        <div class="pal-content-container" style="margin-top:4rem; margin-bottom:3rem; border-top:1px solid rgba(255,255,255,0.07); padding-top:3rem;">
            <p style="font-family:'Space Grotesk',monospace; font-size:0.75rem; font-weight:700; letter-spacing:0.2em; text-transform:uppercase; color:#999; margin:0 0 0.75rem;">Guia de Utilização</p>
            <h1 class="pal-always-white" style="font-size:clamp(2rem,5vw,3.5rem); font-weight:900; letter-spacing:-0.04em; margin:0;">Como funciona?</h1>
        </div>

        <div class="pal-content-container">
            <div class="pal-how-it-works-grid" style="margin-bottom:3rem;">
                @foreach([
                    ['01', 'Acesso Rápido', 'Acesse o Smart Attendance com seu RA, e-mail institucional ou CPF de forma segura.'],
                    ['02', 'QR Code Dinâmico', 'Localize o QR Code projetado pelo professor durante a aula.'],
                    ['03', 'Confirmação', 'Escaneie o código com a câmera e receba a confirmação instantânea de presença.'],
                ] as $index => [$num, $title, $desc])
                <div class="pal-dashboard-card pal-card-delay-{{ $index + 1 }}">
                    <p style="font-family:'Space Grotesk',monospace; font-size:0.75rem; font-weight:700; letter-spacing:0.15em; color:#777; margin:0 0 1rem;">{{ $num }}</p>
                    <h3 style="font-size:1rem; font-weight:800; color:#efefef; margin:0 0 0.5rem; letter-spacing:-0.02em;">{{ $title }}</h3>
                    <p style="font-size:0.82rem; color:#bbb; line-height:1.6; margin:0;">{{ $desc }}</p>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Actions --}}
        <div class="pal-content-container" style="display:flex; gap:1rem; flex-wrap:wrap;">
            @auth
            <form action="{{ route('logout') }}" method="POST" style="margin:0;">
                @csrf
                <button type="submit" class="pal-dashboard-btn pal-dashboard-btn-ghost" style="padding:0.85rem 2rem;">Encerrar Sessão</button>
            </form>
            @endauth
        </div>

    </main>
@endsection

@push('scripts')
<script>
    const modal = document.getElementById('profile-modal');
    const openBtn = document.getElementById('open-profile');
    const closeBtn = document.getElementById('close-profile');
    const overlay = document.getElementById('close-modal-overlay');
    function toggleModal(show) {
        modal.style.display = show ? 'flex' : 'none';
        document.body.style.overflow = show ? 'hidden' : '';
    }
    openBtn.addEventListener('click', () => toggleModal(true));
    closeBtn.addEventListener('click', () => toggleModal(false));
    overlay.addEventListener('click', () => toggleModal(false));
    document.addEventListener('keydown', e => { if (e.key === 'Escape') toggleModal(false); });
</script>
@endpush
