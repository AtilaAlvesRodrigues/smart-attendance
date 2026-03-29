@extends('layouts.theme')

@section('title', 'Login Professor - Smart Attendance')

@section('body-class', 'gradient-bg')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/login.css') }}">
@endpush

@section('content')
<div class="login-container">
    <div class="login-wrapper">

        <div class="login-header">
            <p class="pal-eyebrow">Portal do Professor</p>
            <h1 class="pal-title">Smart Attendance</h1>
            <p class="pal-subtitle">Instituição — Sistema de Presença Inteligente</p>
        </div>

        <div class="glass login-card">

            <form method="POST" action="{{ route('login.professor') }}" class="login-form">
                @csrf

                @error('cpf_email')
                <div class="login-error">
                    {{ $message }}
                </div>
                @enderror

                <div>
                    <label class="pal-eyebrow">Matrícula / CPF / E-Mail</label>
                    <input type="text" name="cpf_email" value="{{ old('cpf_email') }}" required
                        class="login-input"
                        placeholder="Digite suas credenciais">
                </div>

                <div>
                    <label class="pal-eyebrow">Senha</label>
                    <div class="login-password-wrapper">
                        <input type="password" name="password" id="pal-password-prof" required
                            class="login-input login-input-password">
                        <button type="button" class="login-password-btn">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </div>
                </div>

                <p style="display:flex; align-items:flex-start; gap:0.4rem; font-family:'Inter',sans-serif; font-size:0.75rem; color:#888888; line-height:1.5; margin:0;">
                    <svg style="flex-shrink:0; margin-top:1px; color:#a855f7;" width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                    Primeiro acesso? Use o token enviado por e-mail no campo de senha.
                </p>

                <label class="login-checkbox-label">
                    <input type="checkbox" name="remember" class="login-checkbox">
                    Permanecer conectado
                </label>

                <button type="submit" class="login-btn">
                    Entrar no Painel
                </button>

                <div style="display:flex; justify-content:flex-start; gap:1rem; flex-wrap:wrap;">
                    <a href="{{ route('esqueci-senha.show', 'professor') }}"
                        style="font-size:0.75rem; color:var(--pal-gray); text-decoration:none; border-bottom:1px solid transparent; transition:border-color 0.2s;"
                        onmouseover="this.style.borderColor='var(--pal-gray)'"
                        onmouseout="this.style.borderColor='transparent'">
                        Esqueci minha senha
                    </a>
                </div>
            </form>

        </div>

        <div class="login-back">
            <a href="{{ route('login_form') }}" class="pal-product-link" style="color:var(--pal-gray); border-bottom:1px solid var(--pal-gray);">
                ← Trocar Perfil
            </a>
        </div>

    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/pages/login.js') }}"></script>
@endpush
