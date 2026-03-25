@extends('layouts.theme')

@section('title', 'Demonstração Professor - Smart Attendance')

@section('body-class', 'gradient-bg')

@section('hide-nav-defaults', true)

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/theme_professor.css') }}">
@endpush

@section('nav-right')
<div id="demo-toast" class="pal-demo-toast">
    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
    Sessão de Presença Iniciada!
</div>

<div class="pal-nav-actions" style="gap:0.5rem">
    <div class="pal-nav-user pal-info-tooltip pal-info-tooltip-down" data-tooltip="No portal docente, suas credenciais de professor são exibidas para garantir a autoria dos registros." style="margin-right:0;">
        <span class="pal-nav-user-role">Professor (Demo)</span>
        <span class="pal-nav-user-name">Dra. Maria Oliveira</span>
    </div>
    <button id="open-profile" class="pal-profile-btn pal-info-tooltip pal-info-tooltip-down" data-tooltip="Acesse seus dados de registro docente e a lista de disciplinas sob sua responsabilidade.">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
    </button>
    <a href="{{ route('professor.info') }}" class="pal-nav-btn pal-nav-btn-danger pal-info-tooltip pal-info-tooltip-down" data-tooltip="Encerrar a demonstração docente e voltar para a página de informações.">Sair</a>
</div>
@endsection

@section('content')
    {{-- Profile Modal Demo --}}
    <div id="profile-modal" class="pal-modal-overlay" style="display:none;">
        <div id="close-modal-overlay" style="position:absolute; inset:0;"></div>
        <div class="pal-modal-content">
            <div class="pal-modal-header">
                <div class="pal-info-tooltip pal-info-tooltip-down" data-tooltip="Suas credenciais docentes são usadas para assinar digitalmente os registros de presença.">
                    <p class="pal-eyebrow" style="margin-bottom:0.3rem;">Credenciais Docentes</p>
                    <h2 class="pal-text" style="font-size:1.4rem; font-weight:900; letter-spacing:-0.03em; margin:0;">Meu Perfil (Demo)</h2>
                </div>
                <button id="close-profile" class="pal-profile-btn">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="pal-modal-body">
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:1rem; margin-bottom:2rem;">
                    @foreach([['Nome Completo', 'Dra. Maria Oliveira'], ['E-mail', 'maria.oliveira@instituicao.edu.br'], ['CPF', '987.***.***-11'], ['Departamento', 'Ciências Exatas']] as $field)
                    <div class="pal-profile-field pal-info-tooltip pal-info-tooltip-down" data-tooltip="Dado institucional vinculado ao seu contrato docente.">
                        <p class="pal-profile-field-label">{{ $field[0] }}</p>
                        <p class="pal-profile-field-value">{{ $field[1] }}</p>
                    </div>
                    @endforeach
                </div>

                <hr class="pal-divider" style="margin-bottom:1.5rem;">
                <p class="pal-eyebrow" style="margin-bottom:1rem;">Suas Disciplinas</p>

                <div style="display:flex; flex-direction:column; gap:0.75rem;">
                    <div class="pal-profile-field pal-info-tooltip pal-info-tooltip-down" data-tooltip="Lista de turmas ativas sob sua coordenação neste semestre." style="padding:1rem 1.25rem;">
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <div>
                                <p class="pal-profile-field-value" style="font-size:0.9rem;">Cálculo Diferencial I</p>
                                <p style="font-size:0.72rem; color:#999; margin:0;">60h · Turma A</p>
                            </div>
                            <span class="px-2 py-1 bg-white/5 border border-white/10 rounded-sm text-[10px] font-black uppercase tracking-wider text-white/50">Ativo</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Event QR Modal --}}
    <div id="event-qr-modal" class="pal-modal-overlay" style="display:none;">
        <div id="close-event-overlay" style="position:absolute; inset:0;"></div>
        <div class="pal-modal-content" style="max-width:400px; text-align:center;">
            <div class="pal-modal-header">
                <div>
                    <p class="pal-eyebrow" style="margin-bottom:0.3rem;">Check-in Externo</p>
                    <h2 class="pal-text" style="font-size:1.4rem;">QR Code do Evento</h2>
                </div>
                <button id="close-event-qr" class="pal-profile-btn">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="pal-modal-body" style="padding:2.5rem;">
                <div id="event-qrcode-container" style="background:white; padding:1.5rem; border-radius:8px; display:inline-block; margin-bottom:1.5rem; box-shadow:0 10px 25px rgba(0,0,0,0.2);">
                    {{-- QR Code will be injected here --}}
                </div>
                <p class="pal-subtitle" style="font-size:0.8rem;">Aponte a câmera para realizar o check-in automático.</p>
            </div>
        </div>
    </div>

    <main class="pal-main" style="padding-top: 1rem; padding-bottom: 5rem;">

        {{-- Header --}}
        <div class="pal-section-header" style="padding:1.5rem 0 0.5rem; border-bottom:1px solid rgba(255,255,255,0.07); margin-bottom:1.5rem;">
            <p class="pal-eyebrow">Interface Docente</p>
            <h1 class="pal-title">Painel de Controle</h1>
            <p class="pal-subtitle">Gerencie suas aulas e registros de presença em tempo real.</p>
        </div>

        {{-- Banner stackable --}}
        <div class="pal-dashboard-banner pal-banner-primary pal-info-tooltip" data-tooltip="Inicie o processo de escolha da matéria e geração do QR Code dinâmico para os alunos." 
             style="width:100%; max-width:9000px; margin-bottom:2rem; padding:1.5rem 2rem; border: 1px solid rgba(255,255,255,0.1); display:flex; flex-wrap:wrap; gap:1.5rem; justify-content:space-between; align-items:center;">
            <div style="flex:1; min-width:280px;">
                <p style="font-family:'Space Grotesk',monospace; font-size:0.75rem; font-weight:700; letter-spacing:0.2em; text-transform:uppercase; color:#3b82f6; margin:0 0 0.3rem;">Próxima Aula</p>
                <p style="font-size:1rem; font-weight:700; color:#efefef; margin:0;">Cálculo Diferencial I — Pronto para Iniciar Chamada</p>
            </div>
            <button type="button" onclick="confirmDemoPresence()" class="pal-dashboard-btn pal-dashboard-btn-solid pal-info-tooltip" data-tooltip="Confirmar e abrir o painel de projeção para os alunos." style="padding:0.65rem 1.5rem; white-space:nowrap;">
                Iniciar Agora →
            </button>
        </div>

        {{-- NEW PROMINENT BANNER: Eventos & Palestras --}}
        <div class="pal-dashboard-banner pal-card-delay-1 pal-info-tooltip" data-tooltip="Crie sessões de presença para palestras, workshops ou apresentações abertas ao público." 
             style="width:100%; max-width:9000px; margin-bottom:1.5rem; padding:1.25rem 2rem; border: 1px solid rgba(59, 130, 246, 0.2); background: rgba(59, 130, 246, 0.03); display:flex; flex-wrap:wrap; gap:1.5rem; justify-content:space-between; align-items:center;">
            <div style="flex:1; min-width:280px; display:flex; align-items:center; gap:1.5rem;">
                <div class="pal-icon-box" style="width:40px; height:40px; border:1px solid rgba(59, 130, 246, 0.2); border-radius:4px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <svg width="20" height="20" fill="none" stroke="#3b82f6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 11-3-3 3 3 0 013 3z"/></svg>
                </div>
                <div>
                    <h3 class="pal-text font-black" style="font-size:1.1rem; margin:0 0 0.2rem;">Palestra / Apresentação</h3>
                    <p class="pal-subtitle" style="margin:0;">Check-in externo via QR Code para sessões de curta duração.</p>
                </div>
            </div>
            <a href="{{ route('professor.evento.presenca') }}" class="pal-dashboard-btn pal-dashboard-btn-ghost pal-info-tooltip" data-tooltip="Gerar código único para o evento." style="border-color:rgba(59, 130, 246, 0.4); color:#3b82f6; padding:0.6rem 1.25rem;">
                Gerar QR Evento →
            </a>
        </div>

        {{-- Grid responsive --}}
        <div class="pal-dashboard-grid-responsive" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:1rem; margin-bottom:1.5rem;">
            <div class="pal-dashboard-card pal-card-muted pal-card-delay-1 pal-info-tooltip" data-tooltip="Acesse e valide justificativas de ausências enviadas pelos alunos online.">
                <div class="pal-icon-box" style="width:40px; height:40px; border:1px solid rgba(255,255,255,0.1); border-radius:3px; display:flex; align-items:center; justify-content:center; margin-bottom:1.25rem;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="stroke:#888;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <h3 class="pal-text font-black" style="font-size:1.1rem; margin-bottom:0.4rem;">Justificativas</h3>
                <p class="pal-subtitle" style="margin-bottom:1.25rem;">Validar faltas justificadas.</p>
                <button type="button" class="pal-dashboard-btn pal-dashboard-btn-ghost">Analisar</button>
            </div>

            <div class="pal-dashboard-card pal-card-muted pal-card-delay-2 pal-info-tooltip" data-tooltip="Visualize e extraia relatórios de presença por turma, aluno ou período letivo.">
                <div class="pal-icon-box" style="width:40px; height:40px; border:1px solid rgba(255,255,255,0.1); border-radius:3px; display:flex; align-items:center; justify-content:center; margin-bottom:1.25rem;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="stroke:#888;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
                <h3 class="pal-text font-black" style="font-size:1.1rem; margin-bottom:0.4rem;">Relatórios</h3>
                <p class="pal-subtitle" style="margin-bottom:1.25rem;">Histórico de frequências e exportação.</p>
                <button type="button" class="pal-dashboard-btn pal-dashboard-btn-ghost">Ver Histórico</button>
            </div>

            <div class="pal-dashboard-card pal-card-muted pal-card-delay-3 pal-info-tooltip" data-tooltip="Configure os parâmetros de cada disciplina e gerencie a lista de alunos matriculados.">
                <div class="pal-icon-box" style="width:40px; height:40px; border:1px solid rgba(255,255,255,0.1); border-radius:3px; display:flex; align-items:center; justify-content:center; margin-bottom:1.25rem;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="stroke:#888;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
                <h3 class="pal-text font-black" style="font-size:1.1rem; margin-bottom:0.4rem;">Minhas Turmas</h3>
                <p class="pal-subtitle" style="margin-bottom:1.25rem;">Gestão de disciplinas e alunos.</p>
                <button type="button" class="pal-dashboard-btn pal-dashboard-btn-ghost">Configurar</button>
            </div>
        </div>

        {{-- Active session banner --}}
        <div class="pal-dashboard-banner pal-banner-primary pal-info-tooltip pal-info-tooltip-qr" data-tooltip="O QR Code projetado muda para cada aula, garantindo que apenas presentes registrem frequência.">
            <div style="flex:1; min-width:260px;">
                <div style="display:flex; align-items:center; gap:0.5rem; margin-bottom:0.75rem;">
                    <span style="width:6px; height:6px; background:#22c55e; border-radius:50%; display:inline-block;"></span>
                    <p class="pal-eyebrow" style="color:#22c55e; margin:0;">Chamada Ativa (Exemplo)</p>
                </div>
                <h2 class="pal-title" style="font-size:1.4rem; margin-bottom:0.5rem;">Cálculo Diferencial I</h2>
                <p class="pal-subtitle">Alunos conectados agora: <strong class="pal-text">32 / 40</strong></p>
            </div>
            <div style="width:140px; height:140px; background:white; border-radius:4px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <img src="/img/qr-code.png" alt="QR Demo" style="width:120px; height:120px;">
            </div>
        </div>

        <div class="pal-content-container animate-reveal" style="margin-top:4rem; display:flex; flex-direction:column; align-items:center; gap:1.5rem;">
            <a href="{{ route('professor.info') }}" class="pal-product-link pal-info-tooltip" data-tooltip="Retorne à página anterior para rever os detalhes do login docente.">
                ← Voltar para Informações
            </a>
            <p class="pal-eyebrow" style="opacity: 0.5;">FIM DA DEMONSTRAÇÃO DO PROFESSOR</p>
        </div>

    </main>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    function confirmDemoPresence() {
        const toast = document.getElementById('demo-toast');
        toast.classList.add('active');
        setTimeout(() => toast.classList.remove('active'), 3000);
    }

    // Modal Logic
    const modal = document.getElementById('profile-modal');
    const openBtn = document.getElementById('open-profile');
    const closeBtn = document.getElementById('close-profile');
    const overlay = document.getElementById('close-modal-overlay');

    const eventModal = document.getElementById('event-qr-modal');
    const closeEventBtn = document.getElementById('close-event-qr');
    const eventOverlay = document.getElementById('close-event-overlay');
    let eventQrGenerated = false;

    function toggleModal(m, show) {
        m.style.display = show ? 'flex' : 'none';
        document.body.style.overflow = show ? 'hidden' : '';
    }

    function showEventQR() {
        toggleModal(eventModal, true);
        if (!eventQrGenerated) {
            const container = document.getElementById('event-qrcode-container');
            container.innerHTML = '';
            new QRCode(container, {
                text: "{{ route('evento.checkin') }}",
                width: 200,
                height: 200,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });
            eventQrGenerated = true;
        }
    }

    if (openBtn) openBtn.addEventListener('click', () => toggleModal(modal, true));
    if (closeBtn) closeBtn.addEventListener('click', () => toggleModal(modal, false));
    if (overlay) overlay.addEventListener('click', () => toggleModal(modal, false));

    if (closeEventBtn) closeEventBtn.addEventListener('click', () => toggleModal(eventModal, false));
    if (eventOverlay) eventOverlay.addEventListener('click', () => toggleModal(eventModal, false));

    document.addEventListener('keydown', e => { 
        if (e.key === 'Escape') {
            toggleModal(modal, false);
            toggleModal(eventModal, false);
        }
    });
</script>
@endpush
