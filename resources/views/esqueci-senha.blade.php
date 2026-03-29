@extends('layouts.theme')

@section('title', 'Recuperar Acesso - Smart Attendance')

@section('body-class', 'gradient-bg')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/login.css') }}">
@endpush

@section('content')
<div class="login-container">
    <div class="login-wrapper">

        <div class="login-header">
            <p class="pal-eyebrow">{{ $tipo === 'aluno' ? 'Portal do Aluno' : 'Portal do Professor' }}</p>
            <h1 class="pal-title">Smart Attendance</h1>
            <p class="pal-subtitle">Recuperação de Acesso</p>
        </div>

        <div class="glass login-card">

            @if (session('success'))
                <div style="background: rgba(34,197,94,0.08); border: 1px solid rgba(34,197,94,0.25); border-radius: 10px; padding: 14px 18px; font-size: 13px; color: #22c55e; margin-bottom: 16px;">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="login-error" style="margin-bottom: 16px;">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('esqueci-senha.send', $tipo) }}" class="login-form">
                @csrf

                <p class="pal-text" style="font-size: 13px; color: var(--pal-gray); margin-bottom: 4px;">
                    Informe o e-mail cadastrado. Se ele existir no sistema, você receberá um token de acesso temporário.
                </p>

                <div>
                    <label class="pal-eyebrow">E-mail cadastrado</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        class="login-input"
                        placeholder="nome@email.com"
                        autocomplete="email">
                </div>

                <button type="submit" class="login-btn">
                    Enviar Token de Recuperação
                </button>
            </form>

        </div>

        <div class="login-back">
            <a href="{{ $tipo === 'aluno' ? route('login.aluno.form') : route('login.professor.form') }}"
                class="pal-product-link" style="color:var(--pal-gray); border-bottom:1px solid var(--pal-gray);">
                ← Voltar ao Login
            </a>
        </div>

    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/pages/login.js') }}"></script>
@endpush
