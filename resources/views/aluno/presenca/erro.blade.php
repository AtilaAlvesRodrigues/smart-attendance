@extends('layouts.theme')

@section('title', 'Erro na Presença - Smart Attendance')

@section('body-class', 'gradient-bg')

@section('content')
<div style="min-height:100vh; display:flex; align-items:center; justify-content:center; padding:1.5rem;">
    <div style="width:100%; max-width:460px; text-align:center;">

        {{-- Icon --}}
        <div style="display:inline-flex; align-items:center; justify-content:center; width:64px; height:64px; border:1px solid rgba(239,68,68,0.25); border-radius:4px; margin-bottom:2rem;">
            <svg width="28" height="28" fill="none" stroke="#f87171" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </div>

        <p style="font-family:'Space Grotesk',monospace; font-size:0.75rem; font-weight:700; letter-spacing:0.2em; text-transform:uppercase; color:#999; margin-bottom:0.75rem;">Erro de Registro</p>
        <h1 style="font-size:clamp(2rem,5vw,3rem); font-weight:900; letter-spacing:-0.04em; color:#efefef; margin:0 0 0.75rem;">Ops.</h1>
        <p style="font-size:0.9rem; color:#f87171; font-weight:500; margin-bottom:2.5rem;">{{ $mensagem ?? 'Ocorreu um erro ao confirmar sua presença. Tente novamente ou procure o professor.' }}</p>

        <div style="display:flex; flex-direction:column; gap:0.75rem;">
            <a href="{{ route('dashboard.aluno') }}"
                style="display:inline-block; width:100%; background:#efefef; color:#0d0d0d; border:1px solid #efefef; padding:0.85rem; font-size:0.85rem; font-weight:700; letter-spacing:0.04em; border-radius:3px; text-decoration:none; box-sizing:border-box;"
                onmouseover="this.style.background='#d4d4d4'"
                onmouseout="this.style.background='#efefef'">
                Voltar ao Início
            </a>

            <p style="font-family:'Space Grotesk',monospace; font-size:0.75rem; font-weight:700; letter-spacing:0.15em; text-transform:uppercase; color:#222; margin:0.5rem 0 0;">
                Código: SEC-PRES-01
            </p>
        </div>

    </div>
</div>
@endsection
