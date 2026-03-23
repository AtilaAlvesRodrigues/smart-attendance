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
            <p class="pal-subtitle">CEUB — Sistema de Presença Inteligente</p>
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

                <label class="login-checkbox-label">
                    <input type="checkbox" name="remember" class="login-checkbox">
                    Permanecer conectado
                </label>

                <button type="submit" class="login-btn">
                    Entrar no Painel
                </button>
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
