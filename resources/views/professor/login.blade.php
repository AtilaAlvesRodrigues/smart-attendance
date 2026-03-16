@extends('layouts.theme')

@section('title', 'Login Professor - Smart Attendance')

@section('body-class', 'gradient-bg')

@section('content')
<div style="min-height:calc(100vh - 60px); display:flex; align-items:center; justify-content:center; padding:1.5rem;">
    <div style="width:100%; max-width:420px;">

        {{-- Logo / overline --}}
        <div style="margin-bottom:2.5rem; text-align:center;">
            <p style="font-family:'Space Grotesk',monospace; font-size:0.75rem; font-weight:700; letter-spacing:0.2em; text-transform:uppercase; color:#999; margin-bottom:0.75rem;">Painel do Docente</p>
            <h1 style="font-size:2rem; font-weight:900; letter-spacing:-0.04em; color:#efefef; margin:0;">Smart Attendance</h1>
            <p style="font-size:0.82rem; color:#bbb; margin-top:0.4rem;">CEUB — Sistema de Presença Inteligente</p>
        </div>

        {{-- Card --}}
        <div class="glass" style="border-radius:4px; padding:2.5rem;">

            <form method="POST" action="{{ route('login.professor') }}" style="display:flex; flex-direction:column; gap:1rem;">
                @csrf

                @error('cpf_email')
                <div style="background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.25); color:#f87171; padding:0.75rem 1rem; font-size:0.8rem; font-weight:600; border-radius:3px;">
                    {{ $message }}
                </div>
                @enderror

                <div>
                    <label style="display:block; font-family:'Space Grotesk',monospace; font-size:0.75rem; font-weight:700; letter-spacing:0.2em; text-transform:uppercase; color:#999; margin-bottom:0.5rem;">CPF ou E-Mail</label>
                    <input type="text" name="cpf_email" value="{{ old('cpf_email') }}" required
                        style="width:100%; background:#111; border:1px solid rgba(255,255,255,0.1); color:#efefef; padding:0.75rem 1rem; font-size:0.88rem; font-family:'Inter',sans-serif; border-radius:3px; outline:none; box-sizing:border-box;"
                        onfocus="this.style.borderColor='rgba(255,255,255,0.35)'"
                        onblur="this.style.borderColor='rgba(255,255,255,0.1)'"
                        placeholder="ex: 123.456.789-00 ou nome@email.com">
                </div>

                <div>
                    <label style="display:block; font-family:'Space Grotesk',monospace; font-size:0.75rem; font-weight:700; letter-spacing:0.2em; text-transform:uppercase; color:#999; margin-bottom:0.5rem;">Senha</label>
                    <div style="position:relative;">
                        <input type="password" name="password" id="pal-password-prof" required
                            style="width:100%; background:#111; border:1px solid rgba(255,255,255,0.1); color:#efefef; padding:0.75rem 3rem 0.75rem 1rem; font-size:0.88rem; font-family:'Inter',sans-serif; border-radius:3px; outline:none; box-sizing:border-box;"
                            onfocus="this.style.borderColor='rgba(255,255,255,0.35)'"
                            onblur="this.style.borderColor='rgba(255,255,255,0.1)'">
                        <button type="button" onclick="var i=document.getElementById('pal-password-prof');i.type=i.type==='password'?'text':'password';"
                            style="position:absolute; right:0.75rem; top:50%; transform:translateY(-50%); background:none; border:none; color:#999; cursor:pointer; padding:0.25rem; line-height:1;">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </div>
                </div>

                <label style="display:flex; align-items:center; gap:0.5rem; color:#999; font-size:0.78rem; cursor:pointer; margin-top:0.25rem;">
                    <input type="checkbox" name="remember" style="accent-color:#efefef; width:14px; height:14px;">
                    Permanecer conectado
                </label>

                <button type="submit"
                    style="margin-top:0.5rem; width:100%; background:#efefef; color:#0d0d0d; border:1px solid #efefef; padding:0.85rem; font-size:0.85rem; font-weight:700; letter-spacing:0.04em; border-radius:3px; cursor:pointer; transition:background 0.2s;"
                    onmouseover="this.style.background='#d4d4d4'"
                    onmouseout="this.style.background='#efefef'">
                    Entrar no Painel
                </button>
            </form>

        </div>

        {{-- Back link --}}
        <div style="margin-top:1.5rem; text-align:center;">
            <a href="{{ route('login_form') }}"
                class="pal-product-link" style="color:var(--pal-gray); border-bottom:1px solid var(--pal-gray);">
                ← Trocar Perfil
            </a>
        </div>

    </div>
</div>
@endsection
