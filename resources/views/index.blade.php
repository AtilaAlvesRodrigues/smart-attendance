@extends('layouts.theme')

@section('title', 'Smart Attendance — Presença Inteligente')

@section('content')

{{-- ===== HERO SECTION ===== --}}
<section id="hero" class="pal-hero">
    <div class="pal-container">
        <div class="pal-hero-content">
            <p class="pal-overline">Sistema de Presença Inteligente</p>
            <h1 class="pal-hero-title">
                Controle de<br>
                Presença<br>
                <em>para Todos.</em>
            </h1>
            <p class="pal-hero-sub">
                Automatize o registro de frequência com QR Code dinâmico. Simples para o aluno. Poderoso para o docente.
            </p>
            <div class="pal-hero-actions">
                <a href="{{ route('login.aluno.form') }}" class="pal-btn-primary">Entrar como Aluno</a>
                <a href="{{ route('login.professor.form') }}" class="pal-btn-outline">Entrar como Professor</a>
            </div>
        </div>
        <div class="pal-hero-visual">
            @if(isset($activeCodes) && count($activeCodes) > 0)
                <div style="display: flex; gap: 2rem; flex-wrap: wrap; justify-content: center;">
                @foreach($activeCodes as $index => $code)
                    <div style="display: flex; flex-direction: column; align-items: center; gap: 1rem;">
                        <a href="{{ route('presenca.confirmar', $code['codigo']) }}" 
                           style="width:220px; height:220px; background:white; border-radius:4px; display:flex; align-items:center; justify-content:center; flex-shrink:0; text-decoration:none; padding: 10px;"
                           id="qrcode-{{$index}}" data-code="{{ route('presenca.confirmar', $code['codigo']) }}">
                        </a>
                        <div style="text-align: center;">
                            <span style="font-family:'Space Grotesk',monospace; font-size:0.75rem; font-weight:700; letter-spacing:0.2em; text-transform:uppercase; color:#22c55e; display:block; margin-bottom: 0.2rem;">Sessão Ativa</span>
                            <strong style="color: white; font-size: 1.1rem;">{{ $code['materia_nome'] }}</strong>
                            <span style="color: #888; display: block; font-size: 0.8rem; margin-top: 0.2rem;">Sala: {{ $code['sala'] }}</span>
                        </div>
                    </div>
                @endforeach
                </div>
            @else
            <div class="pal-qr-mock">
                <!-- Data dots inner visual -->
                <div class="pal-qr-inner">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" fill="none" width="120" height="120">
                        <!-- QR Code mock pattern -->
                        <rect x="10" y="10" width="30" height="30" rx="2" fill="white"/>
                        <rect x="15" y="15" width="20" height="20" rx="1" fill="#0d0d0d"/>
                        <rect x="18" y="18" width="14" height="14" rx="1" fill="white"/>
                        <rect x="60" y="10" width="30" height="30" rx="2" fill="white"/>
                        <rect x="65" y="15" width="20" height="20" rx="1" fill="#0d0d0d"/>
                        <rect x="68" y="18" width="14" height="14" rx="1" fill="white"/>
                        <rect x="10" y="60" width="30" height="30" rx="2" fill="white"/>
                        <rect x="15" y="65" width="20" height="20" rx="1" fill="#0d0d0d"/>
                        <rect x="18" y="68" width="14" height="14" rx="1" fill="white"/>
                        <!-- Data dots -->
                        <rect x="48" y="10" width="6" height="6" fill="white"/>
                        <rect x="56" y="10" width="6" height="6" fill="white"/>
                        <rect x="44" y="18" width="6" height="6" fill="white"/>
                        <rect x="52" y="18" width="6" height="6" fill="white"/>
                        <rect x="44" y="26" width="6" height="6" fill="white"/>
                        <rect x="56" y="26" width="6" height="6" fill="white"/>
                        <rect x="48" y="34" width="6" height="6" fill="white"/>
                        <rect x="10" y="44" width="6" height="6" fill="white"/>
                        <rect x="18" y="44" width="6" height="6" fill="white"/>
                        <rect x="26" y="44" width="6" height="6" fill="white"/>
                        <rect x="34" y="44" width="6" height="6" fill="white"/>
                        <rect x="42" y="44" width="6" height="6" fill="white"/>
                        <rect x="50" y="44" width="6" height="6" fill="white"/>
                        <rect x="58" y="44" width="6" height="6" fill="white"/>
                        <rect x="66" y="44" width="6" height="6" fill="white"/>
                        <rect x="74" y="44" width="6" height="6" fill="white"/>
                        <rect x="82" y="44" width="6" height="6" fill="white"/>
                        <rect x="44" y="52" width="6" height="6" fill="white"/>
                        <rect x="52" y="52" width="6" height="6" fill="white"/>
                        <rect x="60" y="52" width="6" height="6" fill="white"/>
                        <rect x="68" y="52" width="6" height="6" fill="white"/>
                        <rect x="76" y="52" width="6" height="6" fill="white"/>
                        <rect x="44" y="60" width="6" height="6" fill="white"/>
                        <rect x="60" y="60" width="6" height="6" fill="white"/>
                        <rect x="76" y="60" width="6" height="6" fill="white"/>
                        <rect x="52" y="68" width="6" height="6" fill="white"/>
                        <rect x="60" y="68" width="6" height="6" fill="white"/>
                        <rect x="68" y="68" width="6" height="6" fill="white"/>
                        <rect x="44" y="76" width="6" height="6" fill="white"/>
                        <rect x="52" y="76" width="6" height="6" fill="white"/>
                        <rect x="68" y="76" width="6" height="6" fill="white"/>
                        <rect x="76" y="76" width="6" height="6" fill="white"/>
                        <rect x="60" y="84" width="6" height="6" fill="white"/>
                        <rect x="76" y="84" width="6" height="6" fill="white"/>
                    </svg>
                    <p class="pal-qr-label">QR CODE DINÂMICO</p>
                    <p class="pal-qr-sub">Escaneie para confirmar presença</p>
                </div>
            </div>
            @endif
        </div>
    </div>
    <div class="pal-hero-scroll-hint">
        <span>↓</span>
        <span>Como funciona</span>
    </div>
</section>

{{-- ===== MARQUEE STRIP ===== --}}
<div class="pal-marquee-strip">
    <div class="pal-marquee-track">
        <span>SMART ATTENDANCE</span>
        <span class="pal-sep">✦</span>
        <span>QR CODE</span>
        <span class="pal-sep">✦</span>
        <span>FREQUÊNCIA INTELIGENTE</span>
        <span class="pal-sep">✦</span>
        <span>CEUB</span>
        <span class="pal-sep">✦</span>
        <span>SMART ATTENDANCE</span>
        <span class="pal-sep">✦</span>
        <span>QR CODE</span>
        <span class="pal-sep">✦</span>
        <span>FREQUÊNCIA INTELIGENTE</span>
        <span class="pal-sep">✦</span>
        <span>CEUB</span>
        <span class="pal-sep">✦</span>
        <span>SMART ATTENDANCE</span>
        <span class="pal-sep">✦</span>
        <span>QR CODE</span>
        <span class="pal-sep">✦</span>
        <span>FREQUÊNCIA INTELIGENTE</span>
        <span class="pal-sep">✦</span>
        <span>CEUB</span>
        <span class="pal-sep">✦</span>
    </div>
</div>

{{-- ===== STATEMENT SECTION ===== --}}
<section id="statement" class="pal-statement-section">
    <div class="pal-container">
        <p class="pal-statement-text">
            Nosso sistema registra presença em tempo real, reduz fraudes e mantém os professores focados no que importa — ensinar.
        </p>
    </div>
</section>

{{-- ===== HOW IT WORKS (Palantir product listing style) ===== --}}
<section id="how-it-works" class="pal-products-section">
    <div class="pal-container">
        <div class="pal-products-header">
            <p class="pal-tag">COMO FUNCIONA</p>
        </div>

        <div class="pal-product-item">
            <div class="pal-product-number">/0.1</div>
            <div class="pal-product-body">
                <h2 class="pal-product-title">Acesso Rápido</h2>
                <p class="pal-product-desc">O aluno acessa o Smart Attendance utilizando seu <strong>RA, e-mail institucional ou CPF</strong> de forma segura, sem necessidade de senha complexa.</p>
                <a href="{{ route('login.aluno.form') }}" class="pal-product-link">Entrar agora →</a>
            </div>
        </div>

        <div class="pal-product-item">
            <div class="pal-product-number">/0.2</div>
            <div class="pal-product-body">
                <h2 class="pal-product-title">QR Code Dinâmico</h2>
                <p class="pal-product-desc">O professor gera um <strong>QR Code exclusivo</strong> por aula, com validade configurável. O código muda a cada sessão para evitar compartilhamento.</p>
                <a href="{{ route('login.professor.form') }}" class="pal-product-link">Painel do professor →</a>
            </div>
        </div>

        <div class="pal-product-item">
            <div class="pal-product-number">/0.3</div>
            <div class="pal-product-body">
                <h2 class="pal-product-title">Confirmação Instantânea</h2>
                <p class="pal-product-desc">O aluno escaneia o QR Code com a câmera do celular e recebe a <strong>confirmação instantânea de presença</strong>. Sem formulários, sem burocracia.</p>
            </div>
        </div>

        <div class="pal-product-item">
            <div class="pal-product-number">/0.4</div>
            <div class="pal-product-body">
                <h2 class="pal-product-title">Relatórios em Tempo Real</h2>
                <p class="pal-product-desc">Professores e administradores visualizam <strong>relatórios completos de frequência</strong> com filtros por aluno, matéria e período, com alertas de limite de faltas.</p>
            </div>
        </div>
    </div>
</section>

{{-- ===== PROFILE SELECTION (Light section) ===== --}}
<section id="perfis" class="pal-light-section">
    <div class="pal-container">
        <div class="pal-section-top">
            <p class="pal-tag pal-tag-dark">SELECIONE SEU PERFIL</p>
            <h2 class="pal-section-title-dark">Quem é você?</h2>
        </div>
        <div class="pal-profiles-grid">
            <a href="{{ route('login.aluno.form') }}" class="pal-profile-card">
                <div class="pal-profile-number">01</div>
                <div class="pal-profile-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 14l9-5-9-5-9 5 9 5z"/>
                        <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                    </svg>
                </div>
                <h3 class="pal-profile-title">ALUNO</h3>
                <p class="pal-profile-sub">Confirme sua presença via QR Code. Acompanhe suas faltas em tempo real.</p>
                <span class="pal-profile-cta">Acessar →</span>
            </a>
            <a href="{{ route('login.professor.form') }}" class="pal-profile-card">
                <div class="pal-profile-number">02</div>
                <div class="pal-profile-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="18" height="18" rx="2"/>
                        <path d="M3 9h18M9 21V9"/>
                    </svg>
                </div>
                <h3 class="pal-profile-title">PROFESSOR</h3>
                <p class="pal-profile-sub">Gere QR Codes, gerencie chamadas e visualize relatórios de frequência.</p>
                <span class="pal-profile-cta">Acessar →</span>
            </a>
        </div>
    </div>
</section>

{{-- ===== STATS SECTION ===== --}}
<section class="pal-stats-section">
    <div class="pal-container">
        <div class="pal-stats-grid">
            <div class="pal-stat">
                <span class="pal-stat-number">3s</span>
                <span class="pal-stat-label">Para confirmar presença</span>
            </div>
            <div class="pal-stat">
                <span class="pal-stat-number">100%</span>
                <span class="pal-stat-label">Sem papel, sem caneta</span>
            </div>
            <div class="pal-stat">
                <span class="pal-stat-number">Real-time</span>
                <span class="pal-stat-label">Relatórios instantâneos</span>
            </div>
        </div>
    </div>
</section>

{{-- ===== FOOTER CTA ===== --}}
<section class="pal-footer-cta">
    <div class="pal-container">
        <h2 class="pal-cta-title">Pronto para começar?</h2>
        <div class="pal-cta-actions">
            <a href="{{ route('login.aluno.form') }}" class="pal-btn-primary">Sou Aluno</a>
            <a href="{{ route('login.professor.form') }}" class="pal-btn-outline">Sou Professor</a>
        </div>
    </div>
</section>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script src="{{ asset('js/pages/index.js') }}"></script>
@endpush

@endsection