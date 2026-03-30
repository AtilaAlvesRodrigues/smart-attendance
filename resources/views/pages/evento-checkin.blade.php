@extends('layouts.theme')

@section('title', 'Check-in de Evento - Smart Attendance')

@section('body-class', 'gradient-bg')

@section('hide-nav-defaults', true)

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/login.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pages/evento-checkin.css') }}">
@endpush

@section('content')
<div class="pal-main event-container">
    <div class="pal-content-container event-wrapper animate-reveal">
        
        <div class="login-header event-header" style="padding:0 1rem;">
            <p class="pal-eyebrow" style="margin-bottom:0.5rem;">Credenciamento Digital</p>
            <h1 class="pal-title event-title">Check-in no Evento</h1>
            <p class="pal-subtitle" style="font-size:0.9rem;">Preencha seus dados para confirmar sua participação.</p>
        </div>

        <div id="checkin-card" class="glass event-card" style="margin:0 1rem;">
            <form id="evento-form" class="login-form" onsubmit="handleCheckin(event)">
                <div>
                    <label class="login-label">Nome Completo</label>
                    <input type="text" id="visitor-name" required
                        class="login-input"
                        placeholder="Como deseja ser chamado?">
                </div>

                {{-- Honeypot Field --}}
                <div style="display:none;">
                    <input type="text" id="hp_field" name="hp_field" tabindex="-1" autocomplete="off">
                </div>

                <div>
                    <label class="login-label">E-mail</label>
                    <input type="email" id="visitor-email" required
                        class="login-input"
                        placeholder="seu@email.com">
                </div>

                <div id="error-msg" class="login-error" style="display:none;">
                    Erro: Você já realizou check-in neste dispositivo ou com este e-mail.
                </div>

                <button type="submit" class="pal-btn-action event-btn-submit w-full justify-center">
                    Confirmar Presença
                </button>
            </form>
        </div>

        <div id="success-card" class="glass event-success-card animate-reveal" style="display:none; margin:0 1rem;">
            <div style="width:64px; height:64px; background:rgba(34, 197, 94, 0.1); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 2rem; color:#22c55e;">
                <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
            </div>
            <h2 class="pal-title" style="font-size:1.8rem; margin-bottom:1rem;">Sucesso!</h2>
            <p class="pal-subtitle" style="margin-bottom:2rem;">Sua presença foi registrada com sucesso. Aproveite o evento!</p>
            <p class="pal-eyebrow" style="opacity:0.5;">ID: #EV-{{ mt_rand(1000, 9999) }}</p>
        </div>

        <div class="event-footer">
            <p class="pal-eyebrow" style="font-size:0.6rem; opacity:0.4;">SMART ATTENDANCE — EVENTOS</p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>window.eventoToken = "{{ $token }}";</script>
    <script src="{{ asset('js/pages/evento-checkin.js') }}"></script>
@endpush
