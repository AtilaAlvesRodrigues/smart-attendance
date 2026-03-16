@extends('layouts.theme')

@section('title', 'Presença já Registrada - Smart Attendance')

@section('body-class', 'gradient-bg')

@section('content')
<div style="min-height:100vh; display:flex; align-items:center; justify-content:center; padding:1.5rem;">
    <div style="width:100%; max-width:460px; text-align:center;">

        {{-- Icon --}}
        <div style="display:inline-flex; align-items:center; justify-content:center; width:64px; height:64px; border:1px solid rgba(255,255,255,0.12); border-radius:4px; margin-bottom:2rem;">
            <svg width="28" height="28" fill="none" stroke="#efefef" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>

        <p style="font-family:'Space Grotesk',monospace; font-size:0.75rem; font-weight:700; letter-spacing:0.2em; text-transform:uppercase; color:#999; margin-bottom:0.75rem;">Presença Duplicada</p>
        <h1 style="font-size:clamp(2rem,5vw,3rem); font-weight:900; letter-spacing:-0.04em; color:#efefef; margin:0 0 0.75rem;">Tudo Certo.</h1>
        <p style="font-size:0.9rem; color:#bbb; margin-bottom:2.5rem;">Você já registrou sua presença nesta sessão anteriormente. Não é necessário realizar o procedimento novamente.</p>

        <div style="background:rgba(18,18,18,0.98); border:1px solid rgba(255,255,255,0.08); border-radius:4px; padding:1.25rem; margin-bottom:2.5rem;">
            <p style="font-family:'Space Grotesk',monospace; font-size:0.75rem; font-weight:700; letter-spacing:0.2em; text-transform:uppercase; color:#999; margin:0 0 0.4rem;">Status do Registro</p>
            <p style="font-size:1rem; font-weight:700; color:#efefef; margin:0;">Presença Confirmada</p>
        </div>

        <a href="{{ route('dashboard.aluno') }}"
            style="display:inline-block; width:100%; background:#efefef; color:#0d0d0d; border:1px solid #efefef; padding:0.85rem; font-size:0.85rem; font-weight:700; letter-spacing:0.04em; border-radius:3px; text-decoration:none; box-sizing:border-box;"
            onmouseover="this.style.background='#d4d4d4'"
            onmouseout="this.style.background='#efefef'">
            Voltar ao Início
        </a>

        <p style="font-family:'Space Grotesk',monospace; font-size:0.75rem; font-weight:700; letter-spacing:0.15em; text-transform:uppercase; color:#222; margin-top:1.5rem;">
            Sincronizado com o sistema docente
        </p>

    </div>
</div>
@endsection
