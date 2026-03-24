@extends('layouts.theme')

@section('title', 'Demonstração Aluno - Smart Attendance')

@section('body-class', 'gradient-bg')

@section('hide-nav-defaults', true)

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/theme_aluno.css') }}">
    <style>
        /* Force display of some elements for demo */
        .pal-modal-overlay { z-index: 3000; }
    </style>
@endpush

@section('nav-right')
<div id="demo-toast" class="pal-demo-toast">
    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
    Chamada Confirmada com Sucesso! 
</div>

<div class="pal-nav-actions" style="gap:0.5rem">
    <div class="pal-nav-user pal-info-tooltip pal-info-tooltip-down" data-tooltip="No portal, você sempre verá seu nome e perfil de acesso atual." style="margin-right:0;">
        <span class="pal-nav-user-role">Aluno (Demo)</span>
        <span class="pal-nav-user-name">João da Silva</span>
    </div>
    <button id="open-profile" class="pal-profile-btn pal-info-tooltip pal-info-tooltip-down" data-tooltip="O botão de perfil abre seu RA digital e o resumo de faltas por disciplina.">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
    </button>
    <a href="{{ route('aluno.info') }}" class="pal-nav-btn pal-nav-btn-danger pal-info-tooltip pal-info-tooltip-down" data-tooltip="Encerrar a demonstração e voltar para a página de informações.">Sair</a>
</div>
@endsection

@section('content')

    {{-- Profile Modal Demo --}}
    <div id="profile-modal" class="pal-modal-overlay" style="display:none;">
        <div id="close-modal-overlay" style="position:absolute; inset:0;"></div>
        <div class="pal-modal-content">
            <div class="pal-modal-header">
                <div class="pal-info-tooltip pal-info-tooltip-down" data-tooltip="Seu Perfil contém seus dados acadêmicos oficiais e resumos de frequência.">
                    <p class="pal-eyebrow" style="margin-bottom:0.3rem;">Registro Acadêmico</p>
                    <h2 class="pal-always-white" style="font-size:1.4rem; font-weight:900; letter-spacing:-0.03em; margin:0;">Meu Perfil (Demo)</h2>
                </div>
                <button id="close-profile" class="pal-profile-btn" style="border-color:rgba(255,255,255,0.1); color:#888;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="pal-modal-body">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:2rem;">
                    @foreach([['Nome Completo', 'João da Silva'], ['E-mail', 'joao.silva@instituicao.edu.br'], ['Matrícula (RA)', '21001234'], ['CPF', '123.***.***-00']] as $field)
                    <div class="pal-profile-field pal-info-tooltip pal-info-tooltip-down" data-tooltip="Informação protegida e sincronizada com o sistema acadêmico.">
                        <p class="pal-profile-field-label">{{ $field[0] }}</p>
                        <p class="pal-profile-field-value">{{ $field[1] }}</p>
                    </div>
                    @endforeach
                </div>

                <hr class="pal-divider" style="margin-bottom:1.5rem;">
                <p class="pal-eyebrow" style="margin-bottom:1rem;">Matérias e Frequência</p>

                <div style="display:flex; flex-direction:column; gap:0.75rem;">
                    <div class="pal-profile-field pal-info-tooltip pal-info-tooltip-down" data-tooltip="Resumo visual do seu progresso de presença nesta disciplina." style="padding:1.25rem;">
                        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:0.75rem;">
                            <div>
                                <p class="pal-profile-field-value" style="font-size:0.9rem;">Cálculo Diferencial I</p>
                                <p style="font-size:0.72rem; color:#999; margin:0;">60h · 40 aulas</p>
                            </div>
                            <div style="text-align:right;">
                                <span style="font-size:1.4rem; font-weight:900;" class="pal-profile-field-value">8</span>
                                <span style="font-size:0.72rem; color:#999;"> / 12 faltas</span>
                            </div>
                        </div>
                        <div style="height:3px; background:rgba(255,255,255,0.05); border-radius:2px; overflow:hidden;">
                            <div style="height:100%; width:66%; background:#eab308; transition:width 1s ease;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Dashboard Demo --}}
    <main class="pal-main" style="display:flex; flex-direction:column; align-items:center; padding-bottom: 5rem;">

        {{-- Pending attendance banner --}}
        <div class="pal-dashboard-banner pal-info-tooltip" data-tooltip="Quando o professor gera um QR Code, este banner aparece automaticamente para você confirmar sua presença." 
             style="width:100%; max-width:900px; margin-top:2.5rem; padding:1.5rem 2rem;">
            <div>
                <p style="font-family:'Space Grotesk',monospace; font-size:0.75rem; font-weight:700; letter-spacing:0.2em; text-transform:uppercase; color:#22c55e; margin:0 0 0.3rem;">Presença Pendente</p>
                <p style="font-size:1rem; font-weight:700; color:#efefef; margin:0;">Cálculo Diferencial e Integral I — Aguardando Confirmação</p>
            </div>
            <button type="button" onclick="confirmDemoPresence()" class="pal-dashboard-btn pal-dashboard-btn-solid pal-info-tooltip" data-tooltip="Clicar aqui abre o scanner de QR Code da sua câmera." style="padding:0.65rem 1.5rem; white-space:nowrap;">
                Confirmar Agora →
            </button>
        </div>

        {{-- Hero text --}}
        <div class="pal-content-container animate-reveal" style="margin-top:4rem; margin-bottom:3rem; border-top:1px solid rgba(255,255,255,0.07); padding-top:3rem; text-align:center;">
            <p class="pal-eyebrow">Interface de Usuário</p>
            <h1 class="pal-title">Painel do Aluno</h1>
            <p class="pal-subtitle">Esta é uma demonstração interativa das funcionalidades do seu dashboard.</p>
        </div>

        <div class="pal-content-container">
            <div class="pal-how-it-works-grid" style="margin-bottom:3rem;">
                <div class="pal-dashboard-card tilt-card pal-card-delay-1 pal-info-tooltip" data-tooltip="Acesse com segurança usando seu RA ou E-mail.">
                    <p style="font-family:'Space Grotesk',monospace; font-size:0.75rem; font-weight:700; letter-spacing:0.15em; color:#777; margin:0 0 1rem;">/01</p>
                    <h3 style="font-size:1rem; font-weight:800; color:#efefef; margin:0 0 0.5rem;" class="pal-text">Acesso Rápido</h3>
                    <p style="font-size:0.82rem; color:#bbb; line-height:1.6; margin:0;" class="pal-subtitle">Utilize suas credenciais acadêmicas oficiais para gerenciar sua frequência.</p>
                </div>
                
                <div class="pal-dashboard-card tilt-card pal-card-delay-2 pal-info-tooltip pal-info-tooltip-qr" data-tooltip="O professor projetará o código na sala de aula para que você possa escanear.">
                    <p style="font-family:'Space Grotesk',monospace; font-size:0.75rem; font-weight:700; letter-spacing:0.15em; color:#777; margin:0 0 1rem;">/02</p>
                    <h3 style="font-size:1rem; font-weight:800; color:#efefef; margin:0 0 0.5rem;" class="pal-text">QR Code Dinâmico</h3>
                    <p style="font-size:0.82rem; color:#bbb; line-height:1.6; margin:0;" class="pal-subtitle">Cada aula possui um código único, garantindo segurança e autenticidade.</p>
                </div>

                <div class="pal-dashboard-card tilt-card pal-card-delay-3 pal-info-tooltip" onclick="confirmDemoPresence()" style="cursor:pointer;" data-tooltip="Clique aqui para simular a confirmação instantânea de presença.">
                    <p style="font-family:'Space Grotesk',monospace; font-size:0.75rem; font-weight:700; letter-spacing:0.15em; color:#777; margin:0 0 1rem;">/03</p>
                    <h3 style="font-size:1rem; font-weight:800; color:#efefef; margin:0 0 0.5rem;" class="pal-text">Confirmação</h3>
                    <p style="font-size:0.82rem; color:#bbb; line-height:1.6; margin:0;" class="pal-subtitle">Assim que escaneado, sua falta é removida do sistema em tempo real.</p>
                </div>
            </div>
        </div>

        <div class="pal-content-container" style="display:flex; flex-direction:column; align-items:center; gap:1.5rem;">
            <a href="{{ route('aluno.info') }}" class="pal-product-link pal-info-tooltip" data-tooltip="Retorne à página anterior para rever os detalhes do login.">
                ← Voltar para Informações
            </a>
            <p class="pal-eyebrow" style="opacity: 0.5;">FIM DA DEMONSTRAÇÃO</p>
        </div>

    </main>
@endsection

@push('scripts')
<script>
    function confirmDemoPresence() {
        const toast = document.getElementById('demo-toast');
        toast.classList.add('active');
        setTimeout(() => toast.classList.remove('active'), 3000);
    }

    const modal = document.getElementById('profile-modal');
    const openBtn = document.getElementById('open-profile');
    const closeBtn = document.getElementById('close-profile');
    const overlay = document.getElementById('close-modal-overlay');

    function toggleModal(show) {
        modal.style.display = show ? 'flex' : 'none';
        document.body.style.overflow = show ? 'hidden' : '';
    }

    if (openBtn) openBtn.addEventListener('click', () => toggleModal(true));
    if (closeBtn) closeBtn.addEventListener('click', () => toggleModal(false));
    if (overlay) overlay.addEventListener('click', () => toggleModal(false));
    document.addEventListener('keydown', e => { if (e.key === 'Escape') toggleModal(false); });

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
