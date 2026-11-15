@extends('layouts.theme')

@section('title', 'Verificação em duas etapas - Smart Attendance')
@section('body-class', 'gradient-bg')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/login.css') }}">
@endpush

@section('content')
<div class="login-container">
    <div class="login-wrapper">
        <div class="login-header">
            <p class="pal-eyebrow">Verificação em duas etapas</p>
            <h1 class="pal-title">Confirme que é você</h1>
            <p class="pal-subtitle">Abra o app autenticador no celular e digite o código de 6 números do Smart Attendance.</p>
        </div>

        <div class="glass login-card">
            <form method="POST" action="{{ route('dois-fatores.verificar') }}" class="login-form" id="form-2fa">
                @csrf

                @error('codigo')
                    <div class="login-error" role="alert">{{ $message }}</div>
                @enderror

                <div id="campo-totp">
                    <label for="codigo" class="pal-eyebrow">Código do app</label>
                    <input id="codigo" name="codigo" class="login-input" inputmode="numeric" autocomplete="one-time-code"
                        pattern="[0-9 ]{6,7}" maxlength="7" required autofocus placeholder="000 000"
                        style="font-size:1.6rem; letter-spacing:0.35em; text-align:center; font-weight:800">
                </div>

                <button type="submit" class="login-btn">Verificar e entrar</button>

                <details class="rc-hint" id="recuperacao">
                    <summary style="cursor:pointer">Perdeu o celular? Use um código de recuperação</summary>
                    <p style="margin:0.75rem 0 0.5rem">Digite um dos códigos que você guardou ao ativar a verificação (formato XXXXX-XXXXX). Cada código funciona uma vez só.</p>
                    <button type="button" class="rc-btn rc-btn-neutro rc-btn-sm" id="usar-recuperacao">Usar código de recuperação</button>
                </details>
            </form>
        </div>

        <div class="login-back">
            <a href="{{ route('login_form') }}" class="pal-product-link" style="color:var(--pal-gray); border-bottom:1px solid var(--pal-gray);">← Voltar ao login</a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script @nonce>
    (function () {
        var botao = document.getElementById('usar-recuperacao');
        var campo = document.getElementById('codigo');
        if (!botao || !campo) return;
        botao.addEventListener('click', function () {
            campo.removeAttribute('pattern');
            campo.setAttribute('maxlength', '11');
            campo.setAttribute('inputmode', 'text');
            campo.setAttribute('autocomplete', 'off');
            campo.placeholder = 'XXXXX-XXXXX';
            campo.style.letterSpacing = '0.12em';
            campo.value = '';
            document.querySelector('label[for="codigo"]').textContent = 'Código de recuperação';
            campo.focus();
        });
    })();
</script>
@endpush
