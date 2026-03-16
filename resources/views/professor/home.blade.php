@extends('layouts.theme')

@section('title', 'Painel do Professor - Smart Attendance')

@section('body-class', 'gradient-bg')
@section('no-nav')

@section('content')
<div style="min-height:100vh; display:flex; flex-direction:column;">

    {{-- Nav --}}
    <nav style="position:fixed; top:0; left:0; right:0; z-index:100; display:flex; align-items:center; justify-content:space-between; padding:0 2rem; height:60px; background:rgba(13,13,13,0.95); border-bottom:1px solid rgba(255,255,255,0.06); backdrop-filter:blur(12px);">
        <a href="{{ url('/') }}" style="font-family:'Space Grotesk',sans-serif; font-weight:700; font-size:0.85rem; letter-spacing:0.1em; color:#efefef; text-decoration:none; text-transform:uppercase;">Smart<span style="color:#777;">Attendance</span></a>
        <div style="display:flex; align-items:center; gap:1rem;">
            <div style="text-align:right;">
                <p style="font-family:'Space Grotesk',monospace; font-size:0.75rem; font-weight:700; letter-spacing:0.2em; text-transform:uppercase; color:#777; margin:0;">Professor Conectado</p>
                <p style="font-size:0.8rem; font-weight:600; color:#efefef; margin:0;">{{ $professor->nome ?? 'Docente' }}</p>
            </div>
            <form action="{{ route('logout') }}" method="POST" style="margin:0;">
                @csrf
                <button type="submit" style="font-size:0.72rem; font-weight:600; letter-spacing:0.04em; padding:0.4rem 0.9rem; background:transparent; color:rgba(255,255,255,0.7); border:1px solid rgba(255,255,255,0.1); border-radius:3px; cursor:pointer; transition:all 0.2s;" onmouseover="this.style.borderColor='#ef4444'; this.style.color='#ef4444'" onmouseout="this.style.borderColor='rgba(255,255,255,0.1)'; this.style.color='rgba(255,255,255,0.7)'">Sair</button>
            </form>
        </div>
    </nav>

    {{-- Main --}}
    <main style="padding-top:60px; max-width:1100px; width:100%; margin:0 auto; padding-left:2rem; padding-right:2rem; padding-bottom:4rem;">

        {{-- Header --}}
        <div style="padding:3rem 0 2rem; border-bottom:1px solid rgba(255,255,255,0.07); margin-bottom:2.5rem;">
            <p style="font-family:'Space Grotesk',monospace; font-size:0.75rem; font-weight:700; letter-spacing:0.2em; text-transform:uppercase; color:#999; margin:0 0 0.6rem;">Painel Docente</p>
            <h1 style="font-size:clamp(2rem,4vw,3rem); font-weight:900; letter-spacing:-0.04em; color:#efefef; margin:0;">Painel de Controle</h1>
            <p style="font-size:0.88rem; color:#bbb; margin:0.5rem 0 0;">Gerencie suas aulas e registros de presença em tempo real.</p>
        </div>

        {{-- Cards --}}
        <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:1rem; margin-bottom:2.5rem;">

            {{-- Card: QR --}}
            <div style="background:#111; border:1px solid rgba(255,255,255,0.07); border-radius:4px; padding:1.75rem; transition:border-color 0.2s;" onmouseover="this.style.borderColor='rgba(255,255,255,0.18)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.07)'">
                <div style="width:40px; height:40px; border:1px solid rgba(255,255,255,0.1); border-radius:3px; display:flex; align-items:center; justify-content:center; margin-bottom:1.25rem;">
                    <svg width="18" height="18" fill="none" stroke="#888" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                </div>
                <h3 style="font-size:1.1rem; font-weight:800; letter-spacing:-0.02em; color:#efefef; margin:0 0 0.4rem;">Gerar QR Code</h3>
                <p style="font-size:0.8rem; color:#bbb; line-height:1.6; margin:0 0 1.25rem;">Inicie uma nova sessão de presença para sua turma.</p>
                <a href="{{ route('professor.presenca.index') }}"
                    style="display:inline-flex; align-items:center; gap:0.4rem; background:#efefef; color:#0d0d0d; padding:0.55rem 1.1rem; font-size:0.78rem; font-weight:700; letter-spacing:0.04em; border-radius:3px; text-decoration:none;"
                    onmouseover="this.style.background='#d4d4d4'" onmouseout="this.style.background='#efefef'">Iniciar Agora →</a>
            </div>

            {{-- Card: Relatórios --}}
            <div style="background:#111; border:1px solid rgba(255,255,255,0.07); border-radius:4px; padding:1.75rem; transition:border-color 0.2s;" onmouseover="this.style.borderColor='rgba(255,255,255,0.18)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.07)'">
                <div style="width:40px; height:40px; border:1px solid rgba(255,255,255,0.1); border-radius:3px; display:flex; align-items:center; justify-content:center; margin-bottom:1.25rem;">
                    <svg width="18" height="18" fill="none" stroke="#888" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
                <h3 style="font-size:1.1rem; font-weight:800; letter-spacing:-0.02em; color:#efefef; margin:0 0 0.4rem;">Relatórios</h3>
                <p style="font-size:0.8rem; color:#bbb; line-height:1.6; margin:0 0 1.25rem;">Consulte o histórico de frequências e exporte dados.</p>
                <a href="{{ route('professor.relatorios') }}"
                    style="display:inline-flex; align-items:center; gap:0.4rem; background:transparent; color:rgba(255,255,255,0.5); padding:0.55rem 1.1rem; font-size:0.78rem; font-weight:700; letter-spacing:0.04em; border:1px solid rgba(255,255,255,0.1); border-radius:3px; text-decoration:none; transition:all 0.2s;"
                    onmouseover="this.style.borderColor='rgba(255,255,255,0.3)'; this.style.color='#efefef'" onmouseout="this.style.borderColor='rgba(255,255,255,0.1)'; this.style.color='rgba(255,255,255,0.5)'">Ver Histórico</a>
            </div>

            {{-- Card: Turmas --}}
            <div style="background:#111; border:1px solid rgba(255,255,255,0.07); border-radius:4px; padding:1.75rem; transition:border-color 0.2s;" onmouseover="this.style.borderColor='rgba(255,255,255,0.18)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.07)'">
                <div style="width:40px; height:40px; border:1px solid rgba(255,255,255,0.1); border-radius:3px; display:flex; align-items:center; justify-content:center; margin-bottom:1.25rem;">
                    <svg width="18" height="18" fill="none" stroke="#888" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
                <h3 style="font-size:1.1rem; font-weight:800; letter-spacing:-0.02em; color:#efefef; margin:0 0 0.4rem;">Minhas Turmas</h3>
                <p style="font-size:0.8rem; color:#bbb; line-height:1.6; margin:0 0 1.25rem;">Configure suas disciplinas e gerencie dados dos alunos.</p>
                <a href="{{ route('professor.gerenciar.index') }}"
                    style="display:inline-flex; align-items:center; gap:0.4rem; background:transparent; color:rgba(255,255,255,0.5); padding:0.55rem 1.1rem; font-size:0.78rem; font-weight:700; letter-spacing:0.04em; border:1px solid rgba(255,255,255,0.1); border-radius:3px; text-decoration:none; transition:all 0.2s;"
                    onmouseover="this.style.borderColor='rgba(255,255,255,0.3)'; this.style.color='#efefef'" onmouseout="this.style.borderColor='rgba(255,255,255,0.1)'; this.style.color='rgba(255,255,255,0.5)'">Configurar</a>
            </div>
        </div>

        {{-- Active session section --}}
        <div style="background:#111; border:1px solid rgba(255,255,255,0.07); border-radius:4px; padding:2rem; display:flex; align-items:center; justify-content:space-between; gap:2rem; flex-wrap:wrap;">
            <div style="flex:1; min-width:260px;">
                @if(isset($activeCode))
                <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.75rem;">
                    <span style="width:6px; height:6px; background:#22c55e; border-radius:50%; display:inline-block;"></span>
                    <p style="font-family:'Space Grotesk',monospace; font-size:0.75rem; font-weight:700; letter-spacing:0.2em; text-transform:uppercase; color:#22c55e; margin:0;">Chamada em Andamento</p>
                </div>
                <h2 style="font-size:1.4rem; font-weight:900; letter-spacing:-0.03em; color:#efefef; margin:0 0 0.5rem;">QR Code Ativo</h2>
                <p style="font-size:0.85rem; color:#bbb; margin:0;">Sessão ativa para <strong style="color:#efefef;">{{ $activeMateria->nome }}</strong>. O código expira em breve.</p>
                @else
                <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.75rem;">
                    <span style="width:6px; height:6px; background:#888; border-radius:50%; display:inline-block;"></span>
                    <p style="font-family:'Space Grotesk',monospace; font-size:0.75rem; font-weight:700; letter-spacing:0.2em; text-transform:uppercase; color:#999; margin:0;">Sistema em Espera</p>
                </div>
                <h2 style="font-size:1.4rem; font-weight:900; letter-spacing:-0.03em; color:#efefef; margin:0 0 0.5rem;">Geração de Código</h2>
                <p style="font-size:0.85rem; color:#bbb; margin:0;">Inicie uma sessão para gerar o QR Code dinâmico para os alunos escanearem.</p>
                @endif
            </div>

            @if(isset($activeCode))
            <a href="{{ route('professor.presenca.gerar', $activeMateria->id) }}"
                style="width:180px; height:180px; background:white; border-radius:4px; display:flex; align-items:center; justify-content:center; flex-shrink:0; text-decoration:none;">
                <div id="qrcode"></div>
            </a>
            @else
            <div style="width:180px; height:180px; border:1px dashed rgba(255,255,255,0.08); border-radius:4px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <p style="font-family:'Space Grotesk',monospace; font-size:0.75rem; font-weight:700; letter-spacing:0.15em; text-transform:uppercase; color:#222; margin:0; text-align:center;">Aguardando<br>Início</p>
            </div>
            @endif
        </div>

    </main>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    @if(isset($activeCode))
    document.addEventListener('DOMContentLoaded', () => {
        new QRCode(document.getElementById("qrcode"), {
            text: "{{ route('presenca.confirmar', $activeCode) }}",
            width: 160, height: 160,
            colorDark: "#000000", colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });
    });
    @endif
</script>
@endpush
