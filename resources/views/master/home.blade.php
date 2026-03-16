@extends('layouts.theme')

@section('title', 'Painel Master - Smart Attendance')

@section('body-class', 'gradient-bg')
@section('no-nav')

@section('content')
<div style="min-height:100vh; display:flex; flex-direction:column;">

    {{-- Nav --}}
    <nav style="position:fixed; top:0; left:0; right:0; z-index:100; display:flex; align-items:center; justify-content:space-between; padding:0 2rem; height:60px; background:rgba(13,13,13,0.95); border-bottom:1px solid rgba(255,255,255,0.06); backdrop-filter:blur(12px);">
        <a href="{{ url('/') }}" style="font-family:'Space Grotesk',sans-serif; font-weight:700; font-size:0.85rem; letter-spacing:0.1em; color:#efefef; text-decoration:none; text-transform:uppercase;">Smart<span style="color:#777;">Attendance</span></a>
        <div style="display:flex; align-items:center; gap:1rem;">
            <div style="text-align:right;">
                <p style="font-family:'Space Grotesk',monospace; font-size:0.75rem; font-weight:700; letter-spacing:0.2em; text-transform:uppercase; color:#777; margin:0;">Acesso Root</p>
                <p style="font-size:0.8rem; font-weight:600; color:#efefef; margin:0;">{{ $master->nome ?? 'Administrador' }}</p>
            </div>
            <form action="{{ route('logout') }}" method="POST" style="margin:0;">
                @csrf
                <button type="submit" style="font-size:0.72rem; font-weight:600; padding:0.4rem 0.9rem; background:transparent; color:rgba(255,255,255,0.7); border:1px solid rgba(255,255,255,0.1); border-radius:3px; cursor:pointer;" onmouseover="this.style.borderColor='#ef4444'; this.style.color='#ef4444'" onmouseout="this.style.borderColor='rgba(255,255,255,0.1)'; this.style.color='rgba(255,255,255,0.7)'">Sair</button>
            </form>
        </div>
    </nav>

    {{-- Main --}}
    <main style="padding-top:60px; max-width:1100px; width:100%; margin:0 auto; padding-left:2rem; padding-right:2rem; padding-bottom:4rem;">

        {{-- Header --}}
        <div style="padding:3rem 0 2rem; border-bottom:1px solid rgba(255,255,255,0.07); margin-bottom:2.5rem;">
            <p style="font-family:'Space Grotesk',monospace; font-size:0.75rem; font-weight:700; letter-spacing:0.2em; text-transform:uppercase; color:#999; margin:0 0 0.6rem;">Admin Master</p>
            <h1 style="font-size:clamp(2rem,4vw,3rem); font-weight:900; letter-spacing:-0.04em; color:#efefef; margin:0;">Visão Geral do Sistema</h1>
            <p style="font-size:0.88rem; color:#bbb; margin:0.5rem 0 0;">Controle total de usuários, turmas e registros de presença.</p>
        </div>

        {{-- Stats --}}
        <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:1rem; margin-bottom:2.5rem;">
            @foreach([
                [$professoresCount, 'Professores'],
                [$alunosCount, 'Alunos'],
                [$materiasCount, 'Matérias'],
            ] as [$count, $label])
            <div style="background:#111; border:1px solid rgba(255,255,255,0.07); border-radius:4px; padding:1.75rem;">
                <p style="font-size:2.5rem; font-weight:900; letter-spacing:-0.04em; color:#efefef; margin:0 0 0.4rem; line-height:1;">{{ $count }}</p>
                <p style="font-family:'Space Grotesk',monospace; font-size:0.75rem; font-weight:700; letter-spacing:0.2em; text-transform:uppercase; color:#999; margin:0;">{{ $label }}</p>
            </div>
            @endforeach
        </div>

        {{-- Action Cards --}}
        <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:1rem; margin-bottom:1.5rem;">
            @foreach([
                [route('master.professores'), 'Professores', 'Visualizar e gerenciar docentes do sistema.'],
                [route('master.alunos'), 'Alunos', 'Gerenciar matrículas e acesso dos estudantes.'],
                [route('master.materias'), 'Matérias', 'Configurar disciplinas e vínculos curriculares.'],
            ] as [$url, $title, $desc])
            <a href="{{ $url }}" style="display:block; background:#111; border:1px solid rgba(255,255,255,0.07); border-radius:4px; padding:1.75rem; text-decoration:none; transition:border-color 0.2s;" onmouseover="this.style.borderColor='rgba(255,255,255,0.18)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.07)'">
                <h3 style="font-size:1.05rem; font-weight:800; letter-spacing:-0.02em; color:#efefef; margin:0 0 0.4rem;">{{ $title }}</h3>
                <p style="font-size:0.8rem; color:#bbb; line-height:1.6; margin:0 0 1.25rem;">{{ $desc }}</p>
                <span style="font-family:'Space Grotesk',monospace; font-size:0.75rem; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; color:#999; border-bottom:1px solid #333; padding-bottom:1px;">Explorar →</span>
            </a>
            @endforeach
        </div>

        {{-- Large Action --}}
        <a href="{{ route('master.presenca') }}" style="display:block; background:#111; border:1px solid rgba(255,255,255,0.07); border-radius:4px; padding:2rem; text-decoration:none; transition:border-color 0.2s;" onmouseover="this.style.borderColor='rgba(255,255,255,0.18)'" onmouseout="this.style.borderColor='rgba(255,255,255,0.07)'">
            <div style="display:flex; align-items:center; justify-content:space-between; gap:2rem; flex-wrap:wrap;">
                <div>
                    <p style="font-family:'Space Grotesk',monospace; font-size:0.75rem; font-weight:700; letter-spacing:0.2em; text-transform:uppercase; color:#999; margin:0 0 0.5rem;">Módulo</p>
                    <h3 style="font-size:1.4rem; font-weight:900; letter-spacing:-0.03em; color:#efefef; margin:0 0 0.4rem;">Central de Chamada QR Code</h3>
                    <p style="font-size:0.85rem; color:#bbb; margin:0;">Monitore presenças em tempo real e acesse o log completo de atividades.</p>
                </div>
                <span style="background:#efefef; color:#0d0d0d; padding:0.7rem 1.75rem; font-size:0.82rem; font-weight:700; letter-spacing:0.04em; border-radius:3px; white-space:nowrap;">Acessar Central →</span>
            </div>
        </a>

    </main>
</div>
@endsection
