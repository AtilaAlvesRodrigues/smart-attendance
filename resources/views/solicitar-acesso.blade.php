@extends('layouts.theme')

@section('title', 'Solicitar Acesso - Smart Attendance')

@section('body-class', 'gradient-bg')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages/login.css') }}">
@endpush

@section('content')
<div class="login-container">
    <div class="login-wrapper" style="max-width: 480px;">

        <div class="login-header">
            <p class="pal-eyebrow">{{ $tipo === 'aluno' ? 'Portal do Aluno' : 'Portal do Professor' }}</p>
            <h1 class="pal-title">Smart Attendance</h1>
            <p class="pal-subtitle">Solicitar Acesso ao Sistema</p>
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

            <p class="pal-text" style="font-size: 13px; color: var(--pal-gray); margin-bottom: 4px;">
                Preencha os dados abaixo. Sua solicitação será analisada pelo administrador e você receberá um e-mail com as instruções de acesso.
            </p>

            <form method="POST" action="{{ route('solicitar-acesso.store', $tipo) }}" class="login-form" id="form-solicitacao">
                @csrf

                <div>
                    <label class="pal-eyebrow">Nome Completo</label>
                    <input type="text" name="nome" value="{{ old('nome') }}" required
                        class="login-input" placeholder="Seu nome completo"
                        pattern="[A-Za-zÀ-ÖØ-öø-ÿ\s]+" title="O nome deve conter apenas letras e espaços.">
                </div>

                <div>
                    <label class="pal-eyebrow">E-mail</label>
                    <input type="email" name="email" id="campo-email" value="{{ old('email') }}" required
                        class="login-input" placeholder="nome@email.com" autocomplete="email">
                    <div id="email-feedback" style="display:none; margin-top:6px; font-size:12px; border-radius:8px; padding:10px 14px;"></div>
                </div>

                <div>
                    <label class="pal-eyebrow">CPF</label>
                    <input type="text" name="cpf" value="{{ old('cpf') }}" required
                        class="login-input" placeholder="000.000.000-00">
                </div>

                @if ($tipo === 'aluno')
                <div>
                    <label class="pal-eyebrow">RA (Registro Acadêmico)</label>
                    <input type="text" name="ra" value="{{ old('ra') }}" required
                        class="login-input" placeholder="Número de matrícula">
                </div>
                @endif

                <input type="hidden" name="tipo" value="{{ $tipo }}">

                <button type="submit" id="btn-submit" class="login-btn" style="background: #a855f7; border-color: #a855f7;">
                    Enviar Solicitação
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
    <script>
        const emailInput  = document.getElementById('campo-email');
        const feedback    = document.getElementById('email-feedback');
        const btnSubmit   = document.getElementById('btn-submit');
        const verificarUrl = '{{ route('solicitar-acesso.verificar-email', $tipo) }}';
        const csrfToken   = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        let debounceTimer = null;

        function setFeedback(status, message) {
            feedback.style.display = 'block';

            const styles = {
                ok:       { bg: 'rgba(34,197,94,0.08)',   border: 'rgba(34,197,94,0.25)',  color: '#4ade80' },
                pendente: { bg: 'rgba(250,204,21,0.08)',  border: 'rgba(250,204,21,0.25)', color: '#facc15' },
                existe:   { bg: 'rgba(239,68,68,0.08)',   border: 'rgba(239,68,68,0.25)',  color: '#f87171' },
                checking: { bg: 'rgba(255,255,255,0.04)', border: 'rgba(255,255,255,0.1)', color: '#888'    },
            };

            const s = styles[status] || styles.checking;
            feedback.style.background   = s.bg;
            feedback.style.border       = `1px solid ${s.border}`;
            feedback.style.color        = s.color;
            feedback.textContent        = message;

            const bloqueado = status === 'pendente' || status === 'existe';
            btnSubmit.disabled = bloqueado;
            btnSubmit.style.opacity = bloqueado ? '0.4' : '1';
            btnSubmit.style.cursor  = bloqueado ? 'not-allowed' : 'pointer';
        }

        function hideFeedback() {
            feedback.style.display = 'none';
            btnSubmit.disabled = false;
            btnSubmit.style.opacity = '1';
            btnSubmit.style.cursor  = 'pointer';
        }

        async function verificarEmail(email) {
            setFeedback('checking', 'Verificando disponibilidade...');
            try {
                const url = verificarUrl + '?email=' + encodeURIComponent(email);
                const res = await fetch(url, {
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
                });
                const data = await res.json();

                if (data.status === 'ok') {
                    setFeedback('ok', 'E-mail disponível para solicitação.');
                } else {
                    setFeedback(data.status, data.message);
                }
            } catch {
                hideFeedback();
            }
        }

        emailInput.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            if (!emailInput.value || !emailInput.validity.valid) {
                hideFeedback();
                return;
            }
            debounceTimer = setTimeout(() => verificarEmail(emailInput.value), 600);
        });

        emailInput.addEventListener('blur', () => {
            clearTimeout(debounceTimer);
            if (emailInput.value && emailInput.validity.valid) {
                verificarEmail(emailInput.value);
            }
        });
    </script>
@endpush
