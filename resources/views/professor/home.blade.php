@extends('layouts.theme')

@section('title', 'Painel do Professor - Smart Attendance')

@section('body-class', 'gradient-bg')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/theme_professor.css') }}">
@endpush

@section('nav-user')
<div class="pal-nav-actions" style="gap:0.5rem">
    <div class="pal-nav-user">
        <span class="pal-nav-user-role">Professor</span>
        <span class="pal-nav-user-name">{{ $professor->nome ?? 'Docente' }}</span>
    </div>
    <button id="open-profile" class="pal-profile-btn" type="button" aria-label="Abrir meu perfil" title="Meu perfil">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
    </button>
</div>
@endsection

@section('content')
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
                <div id="event-qrcode-container" style="background:white; padding:1.5rem; border-radius:8px; display:inline-block; margin-bottom:1.5rem; box-shadow:0 10px 25px rgba(0,0,0,0.2);"></div>
                <p class="pal-subtitle" style="font-size:0.8rem;">Aponte a câmera para realizar o check-in automático.</p>
            </div>
        </div>
    </div>

    {{-- Profile Modal --}}
    <div id="profile-modal" class="pal-modal-overlay" style="display:none;">
        <div id="close-modal-overlay" style="position:absolute; inset:0;"></div>
        <div class="pal-modal-content">
            <div class="pal-modal-header">
                <div>
                    <p class="pal-eyebrow" style="margin-bottom:0.3rem;">Seus dados</p>
                    <h2 class="pal-text" style="font-size:1.4rem; font-weight:900; letter-spacing:-0.03em; margin:0;">Meu Perfil</h2>
                </div>
                <button id="close-profile" class="pal-profile-btn" type="button" aria-label="Fechar perfil">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="pal-modal-body">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:2rem;">
                    @foreach([['Nome Completo', $professor->nome], ['E-mail', $professor->email], ['CPF', $professor->cpf]] as $field)
                    <div class="pal-profile-field">
                        <p class="pal-profile-field-label">{{ $field[0] }}</p>
                        <p class="pal-profile-field-value">{{ $field[1] }}</p>
                    </div>
                    @endforeach
                </div>

                <hr class="pal-divider" style="margin-bottom:1.5rem;">
                <p class="pal-eyebrow" style="margin-bottom:1rem;">Suas Disciplinas</p>

                <div style="display:flex; flex-direction:column; gap:0.75rem;">
                    @forelse($professor->materias as $materia)
                    <div class="pal-profile-field" style="padding:1rem 1.25rem;">
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <div>
                                <p class="pal-profile-field-value" style="font-size:0.9rem;">{{ $materia->nome }}</p>
                                <p style="font-size:0.72rem; color:#999; margin:0;">{{ $materia->carga_horaria }}h · {{ $materia->turma->nome ?? 'Sem Turma' }}</p>
                            </div>
                            <span class="px-2 py-1 bg-white/5 border border-white/10 rounded-sm text-[10px] font-black uppercase tracking-wider text-white/50">Ativo</span>
                        </div>
                    </div>
                    @empty
                    <div style="text-align:center; padding:2rem; border:1px dashed rgba(255,255,255,0.08); border-radius:3px;">
                        <p style="color:#777; font-size:0.85rem; margin:0;">Nenhuma disciplina vinculada.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    @php
        $nomeProfessor = $professor->nome ?? 'Professor';
    @endphp
    <main class="pal-main" style="padding-top: 1rem;">

        {{-- Cabeçalho --}}
        <div class="pal-section-header" style="padding:1.5rem 0 1rem; border-bottom:1px solid rgba(255,255,255,0.07); margin-bottom:1.5rem;">
            <p class="pal-eyebrow">Painel do Professor</p>
            <h1 class="pal-title">Olá, {{ $nomeProfessor }}</h1>
            <p class="pal-subtitle">Faça a chamada da sua aula em segundos: escolha a matéria e projete o QR Code para a turma.</p>
        </div>

        {{-- Ação principal: chamada --}}
        <section class="pal-dashboard-banner pal-banner-primary" aria-labelledby="titulo-chamada" style="margin-bottom:1.5rem;">
            <div style="flex:1; min-width:260px;">
                @if(isset($activeCode))
                <p class="pal-eyebrow" style="color:#22c55e; margin:0 0 0.75rem; display:flex; align-items:center; gap:0.5rem;">
                    <span style="width:8px; height:8px; background:#22c55e; border-radius:50%; display:inline-block;" aria-hidden="true"></span>
                    Chamada aberta agora
                </p>
                <h2 id="titulo-chamada" class="pal-title" style="font-size:1.6rem; margin-bottom:0.5rem;">{{ $activeMateria->nome }}</h2>
                <p class="pal-subtitle" style="margin-bottom:1.25rem;">Os alunos já podem escanear o QR Code. Acompanhe quem registrou presença na tela da chamada.</p>
                <a href="{{ route('professor.presenca.gerar', $activeMateria->id) }}" class="pal-dashboard-btn pal-dashboard-btn-solid">
                    Abrir a chamada →
                </a>
                @else
                <h2 id="titulo-chamada" class="pal-title" style="font-size:1.6rem; margin-bottom:0.5rem;">Iniciar chamada</h2>
                <p class="pal-subtitle" style="margin-bottom:1.25rem;">Escolha a matéria da aula. O sistema gera um QR Code válido por 2 horas e mostra, ao vivo, quem registrou presença.</p>
                <a href="{{ route('professor.presenca.index') }}" class="pal-dashboard-btn pal-dashboard-btn-solid">
                    Iniciar chamada →
                </a>
                @endif
            </div>

            @if(isset($activeCode))
            <a href="{{ route('professor.presenca.gerar', $activeMateria->id) }}" aria-label="Abrir a chamada de {{ $activeMateria->nome }}"
                style="width:180px; height:180px; background:white; border-radius:4px; display:flex; align-items:center; justify-content:center; flex-shrink:0; text-decoration:none;">
                <div id="qrcode"></div>
            </a>
            @endif
        </section>

        {{-- Outras tarefas --}}
        <h2 class="pal-text" style="font-size:1.1rem; font-weight:800; margin:0 0 1rem;">Outras tarefas</h2>
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:1rem;">

            <div class="pal-dashboard-card pal-card-primary pal-card-delay-1">
                <div class="pal-icon-box" style="width:40px; height:40px; border:1px solid rgba(255,255,255,0.1); border-radius:3px; display:flex; align-items:center; justify-content:center; margin-bottom:1.25rem;" aria-hidden="true">
                    <svg width="18" height="18" fill="none" stroke="#888" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
                <h3 class="pal-text font-black" style="font-size:1.1rem; margin-bottom:0.4rem;">Minhas turmas e notas</h3>
                <p class="pal-subtitle" style="margin-bottom:1.25rem;">Lance notas e veja a frequência e a situação de cada aluno.</p>
                <a href="{{ route('professor.gerenciar.index') }}" class="pal-dashboard-btn pal-dashboard-btn-ghost">
                    Ver turmas
                </a>
            </div>

            <div class="pal-dashboard-card pal-card-muted pal-card-delay-2">
                <div class="pal-icon-box" style="width:40px; height:40px; border:1px solid rgba(255,255,255,0.1); border-radius:3px; display:flex; align-items:center; justify-content:center; margin-bottom:1.25rem;" aria-hidden="true">
                    <svg width="18" height="18" fill="none" stroke="#888" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
                <h3 class="pal-text font-black" style="font-size:1.1rem; margin-bottom:0.4rem;">Relatórios</h3>
                <p class="pal-subtitle" style="margin-bottom:1.25rem;">Histórico de presenças por data, aluno e matéria.</p>
                <a href="{{ route('professor.relatorios') }}" class="pal-dashboard-btn pal-dashboard-btn-ghost">
                    Ver relatórios
                </a>
            </div>

            <div class="pal-dashboard-card pal-card-muted pal-card-delay-3">
                <div class="pal-icon-box" style="width:40px; height:40px; border:1px solid rgba(255,255,255,0.1); border-radius:3px; display:flex; align-items:center; justify-content:center; margin-bottom:1.25rem;" aria-hidden="true">
                    <svg width="18" height="18" fill="none" stroke="#888" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 11-3-3 3 3 0 013 3z"/></svg>
                </div>
                <h3 class="pal-text font-black" style="font-size:1.1rem; margin-bottom:0.4rem;">Palestra ou evento</h3>
                <p class="pal-subtitle" style="margin-bottom:1.25rem;">Lista de presença para público externo, sem login: o participante informa nome e e-mail.</p>
                @if($hasActiveEvento)
                <a href="{{ route('professor.evento.presenca') }}" class="pal-dashboard-btn pal-dashboard-btn-ghost">
                    <span style="width:8px; height:8px; background:#22c55e; border-radius:50%; display:inline-block; margin-right:0.4rem;" aria-hidden="true"></span>
                    Ver evento aberto
                </a>
                @else
                <a href="{{ route('professor.evento.presenca') }}" class="pal-dashboard-btn pal-dashboard-btn-ghost">
                    Abrir check-in
                </a>
                @endif
            </div>
        </div>

    </main>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    @if(isset($activeCode))
    document.addEventListener('DOMContentLoaded', () => {
        new QRCode(document.getElementById("qrcode"), {
            text: "{{ route('presenca.confirmar', $activeCode) }}",
            width: 160, height: 160,
            colorDark: "#000000", colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });
    });
    @endif

    // Profile Modal Logic
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
