@extends('layouts.theme')

@section('title', 'Para Professores - Smart Attendance')

@section('body-class', 'gradient-bg')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/login.css') }}">
@endpush

@section('content')
<div class="login-container">
    <div class="login-wrapper">

        <div class="login-header animate-reveal">
            <p class="pal-eyebrow pal-info-tooltip" data-tooltip="O portal docente é o centro de controle para suas disciplinas e frequências digitais.">Portal do Professor</p>
            <h1 class="pal-title">Smart Attendance</h1>
            <p class="pal-subtitle">Sistema de Presença Inteligente</p>
        </div>

        <div class="glass login-card animate-reveal" style="animation-delay: 0.1s">

            <div class="login-form">
                <div class="mb-6 pal-info-tooltip" data-tooltip="Use seu identificador de docente (Matrícula ou CPF) ou e-mail institucional.">
                    <label class="pal-eyebrow">Matrícula / CPF / E-Mail</label>
                    <input type="text" readonly
                        class="login-input"
                        placeholder="Digite suas credenciais">
                </div>

                <div class="mb-6 pal-info-tooltip" data-tooltip="Senha de acesso vinculada às suas credenciais de professor.">
                    <label class="pal-eyebrow">Senha</label>
                    <div class="login-password-wrapper">
                        <input type="password" readonly
                            class="login-input login-input-password">
                        <button type="button" class="login-password-btn">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </div>
                </div>

                <label class="login-checkbox-label pal-info-tooltip" data-tooltip="Agiliza sua entrada no painel de controle durante o início das aulas.">
                    <input type="checkbox" disabled class="login-checkbox">
                    Permanecer conectado
                </label>

                <a href="{{ route('professor.demo') }}" class="login-btn pal-info-tooltip" style="text-decoration:none; display:flex; align-items:center; justify-content:center;" data-tooltip="Clique para ver como funciona o painel de geração de QR Codes e gestão de turmas.">
                    Entrar no Painel (Demonstração)
                </a>
            </div>

        </div>

        <div class="login-back animate-reveal" style="animation-delay: 0.2s">
            <a href="{{ route('login_form') }}" class="pal-product-link" style="color:var(--pal-gray); border-bottom:1px solid var(--pal-gray);">
                ← Voltar para seleção
            </a>
        </div>

    </div>
</div>
@endsection
