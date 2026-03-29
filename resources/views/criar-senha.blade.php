@extends('layouts.theme')

@section('title', 'Criar Senha - Smart Attendance')

@section('body-class', 'gradient-bg')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/login.css') }}">
@endpush

@section('content')
<div class="login-container">
    <div class="login-wrapper">

        <div class="login-header">
            <p class="pal-eyebrow">Primeiro Acesso</p>
            <h1 class="pal-title">Smart Attendance</h1>
            <p class="pal-subtitle">Crie sua senha definitiva para ativar sua conta</p>
        </div>

        <div class="glass login-card">

            @if ($errors->any())
                <div class="login-error" style="margin-bottom: 16px;">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('criar-senha.store') }}" class="login-form">
                @csrf

                <p class="pal-text" style="font-size: 13px; color: var(--pal-gray); margin-bottom: 4px;">
                    Você está realizando o <strong>primeiro acesso</strong>. Escolha uma senha com no mínimo 8 caracteres.
                </p>

                <div>
                    <label class="pal-eyebrow">Nova Senha</label>
                    <div class="login-password-wrapper">
                        <input type="password" name="senha" id="pal-senha" required
                            class="login-input login-input-password"
                            placeholder="Mínimo 8 caracteres"
                            autocomplete="new-password">
                        <button type="button" class="login-password-btn" onclick="toggleSenha('pal-senha')">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </div>
                </div>

                <div>
                    <label class="pal-eyebrow">Confirmar Senha</label>
                    <div class="login-password-wrapper">
                        <input type="password" name="senha_confirmacao" id="pal-senha-conf" required
                            class="login-input login-input-password"
                            placeholder="Repita a nova senha"
                            autocomplete="new-password">
                        <button type="button" class="login-password-btn" onclick="toggleSenha('pal-senha-conf')">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="login-btn" style="background: var(--pal-green, #22c55e); border-color: var(--pal-green, #22c55e);">
                    Criar Senha e Entrar
                </button>
            </form>

        </div>

    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/pages/login.js') }}"></script>
    <script>
        function toggleSenha(id) {
            const input = document.getElementById(id);
            input.type = input.type === 'password' ? 'text' : 'password';
        }
    </script>
@endpush
