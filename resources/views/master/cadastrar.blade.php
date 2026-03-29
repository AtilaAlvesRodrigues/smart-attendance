@extends('layouts.theme')

@section('title', 'Cadastrar - Smart Attendance')

@section('body-class', 'gradient-bg')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/theme_master.css') }}">
@endpush

@section('nav-user')
    <div class="pal-nav-actions" style="gap:0.5rem">
        <div class="pal-nav-user">
            <span class="pal-nav-user-role">Acesso Root</span>
            <span class="pal-nav-user-name">{{ auth()->guard('masters')->user()->nome ?? 'Administrador' }}</span>
        </div>
        <button id="open-profile" class="pal-profile-btn">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
        </button>
    </div>
@endsection

@section('content')

{{-- Profile Modal --}}
<div id="profile-modal" class="pal-modal-overlay" style="display:none;">
    <div id="close-profile-overlay" style="position:absolute; inset:0;"></div>
    <div class="pal-modal-content">
        <div class="pal-modal-header">
            <div>
                <p class="pal-eyebrow" style="margin-bottom:0.3rem;">Painel de Controle</p>
                <h2 class="pal-always-white" style="font-size:1.4rem; font-weight:900; letter-spacing:-0.03em; margin:0;">Perfil Master</h2>
            </div>
            <button id="close-profile" class="pal-profile-btn" style="border-color:rgba(255,255,255,0.1); color:#888;">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="pal-modal-body">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:2rem;">
                <div class="pal-profile-field">
                    <p class="pal-profile-field-label">Nome de Exibição</p>
                    <p class="pal-profile-field-value">Administrador Master</p>
                </div>
                <div class="pal-profile-field">
                    <p class="pal-profile-field-label">Nível de Acesso</p>
                    <p class="pal-profile-field-value">Controle Total (Sudo)</p>
                </div>
                <div class="pal-profile-field" style="grid-column: span 2;">
                    <p class="pal-profile-field-label">E-mail do Sistema</p>
                    <p class="pal-profile-field-value">{{ auth()->guard('masters')->user()->email ?? 'master@smartattendance.com' }}</p>
                </div>
            </div>

            <hr class="pal-divider" style="margin-bottom:1.5rem;">
            <p class="pal-eyebrow" style="margin-bottom:1rem;">Segurança</p>

            <div class="pal-profile-field" style="background:rgba(34,197,94,0.05); border-color:rgba(34,197,94,0.1);">
                <div style="display:flex; align-items:center; gap:0.75rem;">
                    <span style="width:8px; height:8px; background:#22c55e; border-radius:50%; display:inline-block;" class="animate-pulse"></span>
                    <p class="pal-profile-field-value" style="color:#22c55e; font-size:11px;">Sessão Autenticada com Firewall Ativo</p>
                </div>
            </div>
        </div>
    </div>
</div>

<main class="pal-main animate-reveal">

    {{-- Header --}}
    <div class="border-b border-white/5 pb-8 mb-10">
        <a href="{{ route('dashboard.master') }}" class="no-underline" style="display:inline-flex; align-items:center; gap:0.4rem; font-size:0.75rem; font-weight:700; color:#888; text-transform:uppercase; letter-spacing:0.1em; margin-bottom:1.25rem; transition:color 0.2s;"
            onmouseover="this.style.color='#efefef'"
            onmouseout="this.style.color='#888'">
            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
            Voltar ao Painel
        </a>
        <p class="pal-eyebrow mb-2" style="color:#a855f7;">Admin Master</p>
        <h1 class="pal-title">Cadastrar</h1>
        <p class="pal-subtitle">Adicione professores, alunos ou matérias diretamente no sistema.</p>
    </div>

    {{-- Cards Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        {{-- Card Professor --}}
        <div class="glass pal-card-delay-1" style="border-radius:8px; border:1px solid rgba(168,85,247,0.2); background:rgba(168,85,247,0.04); overflow:hidden;">
            <div style="height:3px; background:linear-gradient(90deg,#a855f7,#7c3aed);"></div>
            <div style="padding:2rem;">
                <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:0.75rem;">
                    <div style="width:38px; height:38px; border-radius:8px; background:rgba(168,85,247,0.15); border:1px solid rgba(168,85,247,0.25); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <svg width="18" height="18" fill="none" stroke="#a855f7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <div>
                        <p class="pal-eyebrow" style="color:#a855f7; margin:0 0 0.1rem; font-size:0.65rem;">Novo Registro</p>
                        <h2 class="pal-text" style="font-size:1.15rem; font-weight:900; letter-spacing:-0.02em; margin:0;">Professor</h2>
                    </div>
                </div>
                <p class="pal-subtitle" style="font-size:0.8rem; margin:0 0 1.25rem;">Cadastra o docente e envia e-mail de primeiro acesso.</p>

                <hr style="border:none; border-top:1px solid rgba(255,255,255,0.06); margin:0 0 1.25rem;">

                <div id="feedback-professor" style="display:none; margin-bottom:1rem; padding:0.75rem 1rem; border-radius:6px; font-size:0.82rem; font-weight:600;"></div>

                <form id="form-professor" data-url="{{ route('master.cadastrar.professor') }}">
                    @csrf
                    <div style="display:flex; flex-direction:column; gap:1rem;">
                        <div>
                            <label class="pal-eyebrow" style="display:block; margin-bottom:0.4rem; font-size:0.7rem;">Nome Completo</label>
                            <input type="text" name="nome" class="login-input" style="width:100%; box-sizing:border-box;" placeholder="Ex: Prof. João Silva" required>
                            <span class="field-error" data-field="nome" style="display:none; color:#ef4444; font-size:0.75rem; margin-top:0.25rem;"></span>
                        </div>
                        <div>
                            <label class="pal-eyebrow" style="display:block; margin-bottom:0.4rem; font-size:0.7rem;">E-mail</label>
                            <input type="email" name="email" class="login-input" style="width:100%; box-sizing:border-box;" placeholder="professor@email.com" required>
                            <span class="field-error" data-field="email" style="display:none; color:#ef4444; font-size:0.75rem; margin-top:0.25rem;"></span>
                        </div>
                        <div>
                            <label class="pal-eyebrow" style="display:block; margin-bottom:0.4rem; font-size:0.7rem;">CPF</label>
                            <input type="text" name="cpf" class="login-input" style="width:100%; box-sizing:border-box;" placeholder="000.000.000-00" required>
                            <span class="field-error" data-field="cpf" style="display:none; color:#ef4444; font-size:0.75rem; margin-top:0.25rem;"></span>
                        </div>
                        <button type="submit" style="width:100%; margin-top:0.5rem; padding:0.75rem 1rem; border-radius:6px; border:1px solid rgba(168,85,247,0.4); background:rgba(168,85,247,0.15); color:#c084fc; font-weight:700; font-size:0.9rem; cursor:pointer; transition:background 0.2s;"
                            onmouseover="this.style.background='rgba(168,85,247,0.25)'" onmouseout="this.style.background='rgba(168,85,247,0.15)'">
                            Cadastrar e Enviar E-mail
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Card Aluno --}}
        <div class="glass pal-card-delay-2" style="border-radius:8px; border:1px solid rgba(34,197,94,0.2); background:rgba(34,197,94,0.03); overflow:hidden;">
            <div style="height:3px; background:linear-gradient(90deg,#22c55e,#16a34a);"></div>
            <div style="padding:2rem;">
                <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:0.75rem;">
                    <div style="width:38px; height:38px; border-radius:8px; background:rgba(34,197,94,0.12); border:1px solid rgba(34,197,94,0.22); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <svg width="18" height="18" fill="none" stroke="#22c55e" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <div>
                        <p class="pal-eyebrow" style="color:#22c55e; margin:0 0 0.1rem; font-size:0.65rem;">Novo Registro</p>
                        <h2 class="pal-text" style="font-size:1.15rem; font-weight:900; letter-spacing:-0.02em; margin:0;">Aluno</h2>
                    </div>
                </div>
                <p class="pal-subtitle" style="font-size:0.8rem; margin:0 0 1.25rem;">Matricula o estudante e envia e-mail de primeiro acesso.</p>

                <hr style="border:none; border-top:1px solid rgba(255,255,255,0.06); margin:0 0 1.25rem;">

                <div id="feedback-aluno" style="display:none; margin-bottom:1rem; padding:0.75rem 1rem; border-radius:6px; font-size:0.82rem; font-weight:600;"></div>

                <form id="form-aluno" data-url="{{ route('master.cadastrar.aluno') }}">
                    @csrf
                    <div style="display:flex; flex-direction:column; gap:1rem;">
                        <div>
                            <label class="pal-eyebrow" style="display:block; margin-bottom:0.4rem; font-size:0.7rem;">Nome Completo</label>
                            <input type="text" name="nome" class="login-input" style="width:100%; box-sizing:border-box;" placeholder="Ex: Maria Souza" required>
                            <span class="field-error" data-field="nome" style="display:none; color:#ef4444; font-size:0.75rem; margin-top:0.25rem;"></span>
                        </div>
                        <div>
                            <label class="pal-eyebrow" style="display:block; margin-bottom:0.4rem; font-size:0.7rem;">E-mail</label>
                            <input type="email" name="email" class="login-input" style="width:100%; box-sizing:border-box;" placeholder="aluno@email.com" required>
                            <span class="field-error" data-field="email" style="display:none; color:#ef4444; font-size:0.75rem; margin-top:0.25rem;"></span>
                        </div>
                        <div>
                            <label class="pal-eyebrow" style="display:block; margin-bottom:0.4rem; font-size:0.7rem;">CPF</label>
                            <input type="text" name="cpf" class="login-input" style="width:100%; box-sizing:border-box;" placeholder="000.000.000-00" required>
                            <span class="field-error" data-field="cpf" style="display:none; color:#ef4444; font-size:0.75rem; margin-top:0.25rem;"></span>
                        </div>
                        <div>
                            <label class="pal-eyebrow" style="display:block; margin-bottom:0.4rem; font-size:0.7rem;">RA / Matrícula</label>
                            <input type="text" name="ra" class="login-input" style="width:100%; box-sizing:border-box;" placeholder="Ex: 100000001" required>
                            <span class="field-error" data-field="ra" style="display:none; color:#ef4444; font-size:0.75rem; margin-top:0.25rem;"></span>
                        </div>
                        <button type="submit" style="width:100%; margin-top:0.5rem; padding:0.75rem 1rem; border-radius:6px; border:1px solid rgba(34,197,94,0.35); background:rgba(34,197,94,0.12); color:#4ade80; font-weight:700; font-size:0.9rem; cursor:pointer; transition:background 0.2s;"
                            onmouseover="this.style.background='rgba(34,197,94,0.22)'" onmouseout="this.style.background='rgba(34,197,94,0.12)'">
                            Cadastrar e Enviar E-mail
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Card Matéria --}}
        <div class="glass pal-card-delay-3" style="border-radius:8px; border:1px solid rgba(251,191,36,0.2); background:rgba(251,191,36,0.03); overflow:hidden;">
            <div style="height:3px; background:linear-gradient(90deg,#fbbf24,#d97706);"></div>
            <div style="padding:2rem;">
                <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:0.75rem;">
                    <div style="width:38px; height:38px; border-radius:8px; background:rgba(251,191,36,0.12); border:1px solid rgba(251,191,36,0.22); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <svg width="18" height="18" fill="none" stroke="#fbbf24" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    </div>
                    <div>
                        <p class="pal-eyebrow" style="color:#fbbf24; margin:0 0 0.1rem; font-size:0.65rem;">Novo Registro</p>
                        <h2 class="pal-text" style="font-size:1.15rem; font-weight:900; letter-spacing:-0.02em; margin:0;">Matéria</h2>
                    </div>
                </div>
                <p class="pal-subtitle" style="font-size:0.8rem; margin:0 0 1.25rem;">Cria uma disciplina e configura sala e carga horária.</p>

                <hr style="border:none; border-top:1px solid rgba(255,255,255,0.06); margin:0 0 1.25rem;">

                <div id="feedback-materia" style="display:none; margin-bottom:1rem; padding:0.75rem 1rem; border-radius:6px; font-size:0.82rem; font-weight:600;"></div>

                <form id="form-materia" data-url="{{ route('master.cadastrar.materia') }}">
                    @csrf
                    <div style="display:flex; flex-direction:column; gap:1rem;">
                        <div>
                            <label class="pal-eyebrow" style="display:block; margin-bottom:0.4rem; font-size:0.7rem;">Nome da Matéria</label>
                            <input type="text" name="nome" class="login-input" style="width:100%; box-sizing:border-box;" placeholder="Ex: Matemática Aplicada" required>
                            <span class="field-error" data-field="nome" style="display:none; color:#ef4444; font-size:0.75rem; margin-top:0.25rem;"></span>
                        </div>
                        <div>
                            <label class="pal-eyebrow" style="display:block; margin-bottom:0.4rem; font-size:0.7rem;">Sala</label>
                            <input type="text" name="sala" class="login-input" style="width:100%; box-sizing:border-box;" placeholder="Ex: Sala 101">
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                            <div>
                                <label class="pal-eyebrow" style="display:block; margin-bottom:0.4rem; font-size:0.7rem;">Carga Horária (h)</label>
                                <input type="number" name="carga_horaria" class="login-input" style="width:100%; box-sizing:border-box;" placeholder="Ex: 80" min="1">
                            </div>
                            <div>
                                <label class="pal-eyebrow" style="display:block; margin-bottom:0.4rem; font-size:0.7rem;">Total de Aulas</label>
                                <input type="number" name="total_aulas" class="login-input" style="width:100%; box-sizing:border-box;" placeholder="Ex: 40" min="1">
                            </div>
                        </div>
                        <button type="submit" style="width:100%; margin-top:0.5rem; padding:0.75rem 1rem; border-radius:6px; border:1px solid rgba(251,191,36,0.35); background:rgba(251,191,36,0.12); color:#fbbf24; font-weight:700; font-size:0.9rem; cursor:pointer; transition:background 0.2s;"
                            onmouseover="this.style.background='rgba(251,191,36,0.22)'" onmouseout="this.style.background='rgba(251,191,36,0.12)'">
                            Cadastrar Matéria
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

</main>

@push('scripts')
<script>
(function () {
    'use strict';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    // ── helpers ──────────────────────────────────────────────────────────────

    function getFeedback(formId) {
        return document.getElementById('feedback-' + formId);
    }

    function showFeedback(el, msg, success) {
        el.textContent = msg;
        el.style.display = 'block';
        el.style.background  = success ? 'rgba(34,197,94,0.08)'  : 'rgba(239,68,68,0.08)';
        el.style.border      = success ? '1px solid rgba(34,197,94,0.25)'  : '1px solid rgba(239,68,68,0.25)';
        el.style.color       = success ? '#22c55e' : '#ef4444';
    }

    function clearFeedback(el) {
        el.style.display = 'none';
        el.textContent = '';
    }

    function clearFieldErrors(form) {
        form.querySelectorAll('.field-error').forEach(function (el) {
            el.style.display = 'none';
            el.textContent = '';
        });
    }

    function showFieldErrors(form, errors) {
        Object.entries(errors).forEach(function ([field, msgs]) {
            var el = form.querySelector('.field-error[data-field="' + field + '"]');
            if (el) {
                el.textContent = msgs[0];
                el.style.display = 'block';
            }
        });
    }

    // ── submit handler ────────────────────────────────────────────────────────

    ['professor', 'aluno', 'materia'].forEach(function (tipo) {
        var form     = document.getElementById('form-' + tipo);
        var feedback = getFeedback(tipo);
        if (!form || !feedback) return;

        var defaultLabel = tipo === 'materia' ? 'Cadastrar Materia' : 'Cadastrar e Enviar E-mail';

        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            clearFeedback(feedback);
            clearFieldErrors(form);

            var btn = form.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.textContent = 'Aguarde...';

            try {
                var res = await fetch(form.dataset.url, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: new FormData(form),
                });

                var data = await res.json();

                if (res.ok && data.success) {
                    showFeedback(feedback, data.message, true);
                    form.reset();
                } else if (res.status === 419) {
                    showFeedback(feedback, 'Sessao expirada. Recarregue a pagina e tente novamente.', false);
                } else if (res.status === 422 && data.errors) {
                    var firstMsg = Object.values(data.errors)[0]?.[0] || 'Verifique os campos preenchidos.';
                    showFeedback(feedback, firstMsg, false);
                    showFieldErrors(form, data.errors);
                } else {
                    showFeedback(feedback, data.message || 'Erro ao cadastrar. Tente novamente.', false);
                }
            } catch (_err) {
                showFeedback(feedback, 'Erro de conexao. Tente novamente.', false);
            } finally {
                btn.disabled = false;
                btn.textContent = defaultLabel;
            }
        });
    });

    // ── Profile Modal ─────────────────────────────────────────────────────────

    var modal   = document.getElementById('profile-modal');
    var openBtn = document.getElementById('open-profile');
    var closeBtn= document.getElementById('close-profile');
    var overlay = document.getElementById('close-profile-overlay');

    function toggleModal(show) {
        modal.style.display = show ? 'flex' : 'none';
        document.body.style.overflow = show ? 'hidden' : '';
    }

    if (openBtn)  openBtn.addEventListener('click',  function () { toggleModal(true);  });
    if (closeBtn) closeBtn.addEventListener('click', function () { toggleModal(false); });
    if (overlay)  overlay.addEventListener('click',  function () { toggleModal(false); });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') toggleModal(false);
    });

})();
</script>
@endpush

@endsection
