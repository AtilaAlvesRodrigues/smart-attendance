@extends('layouts.theme')

@section('title', 'Painel Master - Smart Attendance')

@section('body-class', 'gradient-bg')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/theme_master.css') }}">
@endpush


@section('nav-user')
    <div class="pal-nav-actions" style="gap:0.5rem">
        <a href="{{ route('master.cadastrar') }}" class="pal-nav-btn no-underline" style="background: rgba(168,85,247,0.15); border: 1px solid rgba(168,85,247,0.4); color: #c084fc; font-weight: 600;">+ Cadastrar</a>
        <div class="pal-nav-user">
            <span class="pal-nav-user-role">Administrador</span>
            <span class="pal-nav-user-name">{{ $master->nome ?? 'Administrador' }}</span>
        </div>
        <button id="open-profile" class="pal-profile-btn" type="button" aria-label="Abrir meu perfil" title="Meu perfil">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
        </button>
    </div>
@endsection

@section('content')
{{-- User Profile Modal --}}
<div id="profile-modal" class="pal-modal-overlay" style="display:none;">
    <div id="close-profile-overlay" style="position:absolute; inset:0;"></div>
    <div class="pal-modal-content">
        <div class="pal-modal-header">
            <div>
                <p class="pal-eyebrow" style="margin-bottom:0.3rem;">Painel de Controle</p>
                <h2 class="pal-always-white" style="font-size:1.4rem; font-weight:900; letter-spacing:-0.03em; margin:0;">Perfil Master</h2>
            </div>
            <button id="close-profile" class="pal-profile-btn" type="button" aria-label="Fechar perfil" style="border-color:rgba(255,255,255,0.1); color:#888;">
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
                    <p class="pal-profile-field-value">{{ auth()->user()->email ?? 'master@smartattendance.com' }}</p>
                </div>
            </div>

            <hr class="pal-divider" style="margin-bottom:1.5rem;">
            <p class="pal-eyebrow" style="margin-bottom:1rem;">Segurança</p>

            <div class="pal-profile-field" style="background:rgba(34, 197, 94, 0.05); border-color:rgba(34, 197, 94, 0.1);">
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
    <div class="border-b border-white/5 pb-8 mb-8">
        <p class="pal-eyebrow mb-2">Administração</p>
        <h1 class="pal-title">Visão geral</h1>
        <p class="pal-subtitle">Cadastre pessoas e matérias, aprove pedidos de acesso e acompanhe as presenças da instituição.</p>
    </div>

    {{-- Pendências --}}
    @if($solicitacoesPendentes > 0)
    <a href="{{ route('master.solicitacoes') }}" class="glass flex items-center justify-between gap-4 flex-wrap p-6 rounded-sm no-underline mb-8"
       style="border:1px solid rgba(245,158,11,0.5); background:rgba(245,158,11,0.08);">
        <div>
            <p class="pal-text font-black m-0" style="font-size:1.1rem;">
                {{ $solicitacoesPendentes }} {{ $solicitacoesPendentes === 1 ? 'pedido de acesso aguardando' : 'pedidos de acesso aguardando' }} sua análise
            </p>
            <p class="pal-subtitle m-0">Alunos e professores que pediram cadastro pela página pública.</p>
        </div>
        <span class="pal-btn-primary px-6 py-3 text-sm font-bold rounded-sm whitespace-nowrap">Analisar pedidos &rarr;</span>
    </a>
    @endif

    {{-- Totais + atalhos --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        @foreach([
            [route('master.professores'), $professoresCount, 'Professores', 'Ver e editar docentes e as matérias de cada um.', 'Ver professores'],
            [route('master.alunos'), $alunosCount, 'Alunos', 'Ver matrículas, dados de acesso e frequência.', 'Ver alunos'],
            [route('master.materias'), $materiasCount, 'Matérias', 'Ver disciplinas, salas e professores vinculados.', 'Ver matérias'],
        ] as $index => [$url, $count, $title, $desc, $acao])
        <a href="{{ $url }}" class="group glass block p-8 rounded-sm border border-white/10 hover:border-white/20 transition-all duration-300 no-underline pal-card-delay-{{ $index + 1 }}">
            <p class="pal-text font-black m-0" style="font-size:2.5rem; letter-spacing:-0.04em; line-height:1;">{{ $count }}</p>
            <h2 class="text-lg font-black tracking-tight pal-text mt-2 mb-2">{{ $title }}</h2>
            <p class="text-sm pal-subtitle leading-relaxed mb-6">{{ $desc }}</p>
            <span class="text-sm font-bold pal-text border-b border-white/20 pb-1">{{ $acao }} &rarr;</span>
        </a>
        @endforeach
    </div>

    {{-- Presenças --}}
    <a href="{{ route('master.presenca') }}" class="group glass block p-10 rounded-sm border border-white/10 hover:border-white/20 transition-all duration-300 no-underline">
        <div class="flex items-center justify-between gap-8 flex-wrap">
            <div>
                <h2 class="text-3xl font-black tracking-tighter pal-text mb-3">Presenças</h2>
                <p class="text-sm pal-subtitle m-0">Todas as presenças registradas, com filtros por professor, matéria e aluno.</p>
            </div>
            <span class="pal-btn-primary px-8 py-4 text-sm font-bold tracking-wide rounded-sm whitespace-nowrap">Ver presenças &rarr;</span>
        </div>
    </a>

</main>

@push('scripts')
<script>
    // Profile Modal Logic
    const modal = document.getElementById('profile-modal');
    const openBtn = document.getElementById('open-profile');
    const closeBtn = document.getElementById('close-profile');
    const overlay = document.getElementById('close-profile-overlay');

    function toggleModal(show) {
        modal.style.display = show ? 'flex' : 'none';
        document.body.style.overflow = show ? 'hidden' : '';
    }

    if (openBtn) openBtn.addEventListener('click', () => toggleModal(true));
    if (closeBtn) closeBtn.addEventListener('click', () => toggleModal(false));
    if (overlay) overlay.addEventListener('click', () => toggleModal(false));
    document.addEventListener('keydown', e => { if (e.key === 'Escape') toggleModal(false); });

    // Tilt effect
    document.addEventListener('DOMContentLoaded', () => {
        const cards = document.querySelectorAll('.tilt-card');
        cards.forEach(card => {
            card.addEventListener('mousemove', e => {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                const rotateX = (y - rect.height/2) / 15;
                const rotateY = (rect.width/2 - x) / 15;
                card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale3d(1.02, 1.02, 1.02)`;
            });
            card.addEventListener('mouseleave', () => {
                card.style.transform = `perspective(1000px) rotateX(0deg) rotateY(0deg) scale3d(1, 1, 1)`;
            });
        });
    });
</script>
@endpush
@endsection
