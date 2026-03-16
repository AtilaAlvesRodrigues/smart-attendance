@extends('layouts.theme')

@section('title', 'Painel do Aluno - Smart Attendance')

@section('body-class', 'gradient-bg')
@section('no-nav')

@section('content')
<div style="min-height:100vh; display:flex; flex-direction:column;">

    {{-- Top Nav --}}
    <nav style="position:fixed; top:0; left:0; right:0; z-index:100; display:flex; align-items:center; justify-content:space-between; padding:0 2rem; height:60px; background:rgba(13,13,13,0.95); border-bottom:1px solid rgba(255,255,255,0.06); backdrop-filter:blur(12px);">
        <a href="{{ url('/') }}" style="font-family:'Space Grotesk',sans-serif; font-weight:700; font-size:0.85rem; letter-spacing:0.1em; color:#efefef; text-decoration:none; text-transform:uppercase;">Smart<span style="color:#777;">Attendance</span></a>
        <div style="display:flex; align-items:center; gap:1rem;">
            <div style="text-align:right;">
                <p style="font-family:'Space Grotesk',monospace; font-size:0.75rem; font-weight:700; letter-spacing:0.2em; text-transform:uppercase; color:#777; margin:0;">Aluno</p>
                <p style="font-size:0.8rem; font-weight:600; color:#efefef; margin:0;">{{ $aluno->nome ?? 'Estudante' }}</p>
            </div>
            <button id="open-profile" style="width:36px; height:36px; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); border-radius:3px; display:flex; align-items:center; justify-content:center; cursor:pointer; color:#888; transition:all 0.2s;" onmouseover="this.style.borderColor='rgba(255,255,255,0.3)'; this.style.color='#efefef'" onmouseout="this.style.borderColor='rgba(255,255,255,0.1)'; this.style.color='#888'">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </button>
            <form action="{{ route('logout') }}" method="POST" style="margin:0;">
                @csrf
                <button type="submit" style="font-family:'Inter',sans-serif; font-size:0.72rem; font-weight:600; letter-spacing:0.04em; padding:0.4rem 0.9rem; background:transparent; color:rgba(255,255,255,0.7); border:1px solid rgba(255,255,255,0.1); border-radius:3px; cursor:pointer; transition:all 0.2s;" onmouseover="this.style.borderColor='#ef4444'; this.style.color='#ef4444'" onmouseout="this.style.borderColor='rgba(255,255,255,0.1)'; this.style.color='rgba(255,255,255,0.7)'">Sair</button>
            </form>
        </div>
    </nav>

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
    <main style="padding-top:60px; flex:1; display:flex; flex-direction:column; align-items:center; padding-left:2rem; padding-right:2rem; padding-bottom:4rem;">

        {{-- Pending attendance banner --}}
        @if(session('pending_attendance_code'))
        <div style="width:100%; max-width:900px; margin-top:2.5rem; background:#111; border:1px solid rgba(255,255,255,0.12); border-radius:4px; padding:1.5rem 2rem; display:flex; align-items:center; justify-content:space-between; gap:1.5rem; flex-wrap:wrap;">
            <div>
                <p style="font-family:'Space Grotesk',monospace; font-size:0.75rem; font-weight:700; letter-spacing:0.2em; text-transform:uppercase; color:#22c55e; margin:0 0 0.3rem;">Presença Pendente</p>
                <p style="font-size:1rem; font-weight:700; color:#efefef; margin:0;">Você tem uma aula aguardando confirmação.</p>
            </div>
            <a href="{{ route('presenca.confirmar', session('pending_attendance_code')) }}"
                style="background:#efefef; color:#0d0d0d; padding:0.65rem 1.5rem; font-size:0.82rem; font-weight:700; letter-spacing:0.04em; border-radius:3px; text-decoration:none; white-space:nowrap;"
                onmouseover="this.style.background='#d4d4d4'" onmouseout="this.style.background='#efefef'">
                Confirmar Agora →
            </a>
        </div>
        @endif

        {{-- Hero text --}}
        <div style="width:100%; max-width:900px; margin-top:4rem; margin-bottom:3rem; border-top:1px solid rgba(255,255,255,0.07); padding-top:3rem;">
            <p style="font-family:'Space Grotesk',monospace; font-size:0.75rem; font-weight:700; letter-spacing:0.2em; text-transform:uppercase; color:#999; margin:0 0 0.75rem;">Guia de Utilização</p>
            <h1 style="font-size:clamp(2rem,5vw,3.5rem); font-weight:900; letter-spacing:-0.04em; color:#efefef; margin:0;">Como funciona?</h1>
        </div>

        {{-- Steps grid --}}
        <div style="width:100%; max-width:900px; display:grid; grid-template-columns:repeat(3,1fr); gap:1rem; margin-bottom:3rem;">
            @foreach([
                ['01', 'Acesso Rápido', 'Acesse o Smart Attendance com seu RA, e-mail institucional ou CPF de forma segura.'],
                ['02', 'QR Code Dinâmico', 'Localize o QR Code projetado pelo professor durante a aula.'],
                ['03', 'Confirmação', 'Escaneie o código com a câmera e receba a confirmação instantânea de presença.'],
            ] as [$num, $title, $desc])
            <div style="background:#111; border:1px solid rgba(255,255,255,0.07); border-radius:4px; padding:1.75rem; transition:border-color 0.2s;" onmouseover="this.style.borderColor='rgba(255,255,255,0.18)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.07)'">
                <p style="font-family:'Space Grotesk',monospace; font-size:0.75rem; font-weight:700; letter-spacing:0.15em; color:#777; margin:0 0 1rem;">{{ $num }}</p>
                <h3 style="font-size:1rem; font-weight:800; color:#efefef; margin:0 0 0.5rem; letter-spacing:-0.02em;">{{ $title }}</h3>
                <p style="font-size:0.82rem; color:#bbb; line-height:1.6; margin:0;">{{ $desc }}</p>
            </div>
            @endforeach
        </div>

        {{-- Actions --}}
        <div style="width:100%; max-width:900px; display:flex; gap:1rem; flex-wrap:wrap;">
            <a href="{{ Auth::check() ? route('dashboard') : route('login_form') }}"
                style="background:#efefef; color:#0d0d0d; padding:0.85rem 2rem; font-size:0.85rem; font-weight:700; letter-spacing:0.04em; border-radius:3px; text-decoration:none; border:1px solid #efefef;"
                onmouseover="this.style.background='#d4d4d4'" onmouseout="this.style.background='#efefef'">
                Prosseguir
            </a>
            @auth
            <form action="{{ route('logout') }}" method="POST" style="margin:0;">
                @csrf
                <button type="submit" style="background:transparent; color:rgba(255,255,255,0.7); padding:0.85rem 2rem; font-size:0.85rem; font-weight:700; letter-spacing:0.04em; border:1px solid rgba(255,255,255,0.1); border-radius:3px; cursor:pointer; transition:all 0.2s;" onmouseover="this.style.borderColor='rgba(255,255,255,0.3)'; this.style.color='#efefef'" onmouseout="this.style.borderColor='rgba(255,255,255,0.1)'; this.style.color='rgba(255,255,255,0.7)'">Encerrar Sessão</button>
            </form>
            @endauth
        </div>

    </main>
</div>
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
