@extends('layouts.theme')

@section('title', 'Painel Master - Smart Attendance')

@section('body-class', 'gradient-bg')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/theme_master.css') }}">
@endpush

@section('nav-left')
    {{-- Root dashboard --}}
@endsection

@section('nav-user')
    <div class="pal-nav-actions" style="gap:0.5rem">
        <div class="pal-nav-user">
            <span class="pal-nav-user-role">Acesso Root</span>
            <span class="pal-nav-user-name">{{ $master->nome ?? 'Administrador' }}</span>
        </div>
        <button id="open-profile" class="pal-profile-btn">
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
    <div class="border-b border-white/5 pb-8 mb-10">
        <p class="pal-eyebrow mb-2">Admin Master</p>
        <h1 class="pal-title">Visão Geral do Sistema</h1>
        <p class="pal-subtitle">Controle total de usuários, turmas e registros de presença.</p>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        @foreach([
            [$professoresCount, 'Professores'],
            [$alunosCount, 'Alunos'],
            [$materiasCount, 'Matérias'],
        ] as [$count, $label])
        <div class="pal-stat-card shadow-xl transition-all animate-fade-in">
            <p class="number mb-3">{{ $count }}</p>
            <p class="label m-0">{{ $label }}</p>
        </div>
        @endforeach
    </div>

    {{-- Action Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        @foreach([
            [route('master.professores'), 'Professores', 'Visualizar e gerenciar docentes do sistema.'],
            [route('master.alunos'), 'Alunos', 'Gerenciar matrículas e acesso dos estudantes.'],
            [route('master.materias'), 'Matérias', 'Configurar disciplinas e vínculos curriculares.'],
        ] as $index => $item)
        @php
            [$url, $title, $desc] = $item;
        @endphp
        <a href="{{ $url }}" class="group glass block p-8 rounded-sm border border-white/10 hover:border-white/20 transition-all duration-300 no-underline hover:-translate-y-1 pal-card-delay-{{ $index + 1 }} tilt-card">
            <h3 class="text-lg font-black tracking-tight pal-text mb-2">{{ $title }}</h3>
            <p class="text-sm pal-subtitle leading-relaxed mb-6">{{ $desc }}</p>
            <span class="font-mono text-[10px] font-bold tracking-[0.15em] uppercase pal-text-muted group-hover:pal-text transition-colors border-b border-white/20 pb-1">Explorar &rarr;</span>
        </a>
        @endforeach
    </div>

    {{-- Large Action --}}
    <a href="{{ route('master.presenca') }}" class="group glass block p-10 rounded-sm border border-white/10 hover:border-white/20 transition-all duration-300 no-underline">
        <div class="flex items-center justify-between gap-8 flex-wrap">
            <div>
                <p class="pal-eyebrow mb-3">Módulo</p>
                <h3 class="text-3xl font-black tracking-tighter pal-text mb-3">Central de Chamada QR Code</h3>
                <p class="text-sm pal-subtitle m-0">Monitore presenças em tempo real e acesse o log completo de atividades.</p>
            </div>
            <span class="pal-btn-primary px-8 py-4 text-sm font-bold tracking-wide rounded-sm whitespace-nowrap group-hover:bg-indigo-600 group-hover:text-white transition-colors">Acessar Central &rarr;</span>
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
