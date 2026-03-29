@php
    $isProfessor = auth('professores')->check();
    $isMaster    = auth('masters')->check();
@endphp

@if($isProfessor || $isMaster)

@once
<style>
/* ===== Sidebar ===== */
#pal-sidebar {
    position: fixed;
    top: var(--pal-nav-h, 60px);
    left: 0;
    width: 220px;
    height: calc(100vh - var(--pal-nav-h, 60px));
    z-index: 90;
    display: flex;
    flex-direction: column;
    padding-top: 0;
    background: rgba(12, 7, 28, 0.88);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-right: 1px solid rgba(255, 255, 255, 0.07);
    overflow-y: auto;
    overflow-x: hidden;
}

.pal-sidebar-inner {
    display: flex;
    flex-direction: column;
    padding: 1.25rem 0.75rem 1.5rem;
    gap: 0.15rem;
    flex: 1;
}

.pal-sidebar-section-label {
    font-size: 0.6rem;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: rgba(255, 255, 255, 0.28);
    padding: 0.75rem 0.5rem 0.4rem;
}

.pal-sidebar-divider {
    height: 1px;
    background: rgba(255, 255, 255, 0.06);
    margin: 0.5rem 0;
}

.pal-sidebar-item {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    padding: 0.6rem 0.65rem;
    border-radius: 5px;
    text-decoration: none;
    color: rgba(255, 255, 255, 0.42);
    font-size: 0.82rem;
    font-weight: 500;
    font-family: 'Inter', sans-serif;
    transition: background 0.15s ease, color 0.15s ease, border-color 0.15s ease;
    border: 1px solid transparent;
    white-space: nowrap;
}

.pal-sidebar-item:hover {
    background: rgba(255, 255, 255, 0.05);
    color: rgba(255, 255, 255, 0.85);
    text-decoration: none;
}

.pal-sidebar-item--active {
    background: rgba(255, 255, 255, 0.07);
    color: rgba(255, 255, 255, 0.95);
    border-color: rgba(255, 255, 255, 0.1);
}

.pal-sidebar-item--active .pal-sidebar-icon svg {
    stroke: #a855f7;
}

.pal-sidebar-icon {
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 18px;
    height: 18px;
}

.pal-sidebar-label {
    overflow: hidden;
    text-overflow: ellipsis;
    flex: 1;
}

.pal-sidebar-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 18px;
    height: 18px;
    padding: 0 4px;
    border-radius: 2px;
    background: rgba(168, 85, 247, 0.18);
    border: 1px solid rgba(168, 85, 247, 0.25);
    color: #a855f7;
    font-size: 0.65rem;
    font-weight: 700;
    line-height: 1;
    flex-shrink: 0;
}

.light-mode .pal-sidebar-badge {
    background: rgba(126, 34, 206, 0.1);
    border-color: rgba(126, 34, 206, 0.2);
    color: #7e22ce;
}

html.sidebar-collapsed .pal-sidebar-badge {
    display: none;
}

/* Offset page content on desktop */
.pal-has-sidebar .pal-page {
    padding-left: 220px;
}

/* Theme toggle bump on desktop (avoids overlap with sidebar) */
.pal-has-sidebar #theme-toggle {
    right: 2rem;
}

/* Light mode */
.light-mode #pal-sidebar {
    background: rgba(248, 250, 252, 0.93);
    border-right-color: rgba(0, 0, 0, 0.07);
}

.light-mode .pal-sidebar-item {
    color: rgba(30, 41, 59, 0.48);
}

.light-mode .pal-sidebar-item:hover {
    background: rgba(0, 0, 0, 0.04);
    color: rgba(30, 41, 59, 0.85);
}

.light-mode .pal-sidebar-item--active {
    background: rgba(0, 0, 0, 0.05);
    color: rgba(30, 41, 59, 0.95);
    border-color: rgba(0, 0, 0, 0.08);
}

.light-mode .pal-sidebar-item--active .pal-sidebar-icon svg {
    stroke: #7e22ce;
}

.light-mode .pal-sidebar-section-label {
    color: rgba(30, 41, 59, 0.38);
}

.light-mode .pal-sidebar-divider {
    background: rgba(0, 0, 0, 0.07);
}

/* ===== Collapse transition ===== */
#pal-sidebar {
    transition: width 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

.pal-has-sidebar .pal-page {
    transition: padding-left 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Toggle button */
.pal-sidebar-top-bar {
    display: flex;
    justify-content: flex-end;
    padding: 0.6rem 0.6rem 0.1rem;
    flex-shrink: 0;
}

#pal-sidebar-toggle {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 26px;
    height: 26px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: rgba(255, 255, 255, 0.38);
    cursor: pointer;
    transition: background 0.15s ease, color 0.15s ease;
    flex-shrink: 0;
}

#pal-sidebar-toggle:hover {
    background: rgba(255, 255, 255, 0.1);
    color: rgba(255, 255, 255, 0.8);
}

#pal-sidebar-toggle svg {
    transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    flex-shrink: 0;
}

.light-mode #pal-sidebar-toggle {
    background: rgba(0, 0, 0, 0.04);
    border-color: rgba(0, 0, 0, 0.08);
    color: rgba(30, 41, 59, 0.38);
}

.light-mode #pal-sidebar-toggle:hover {
    background: rgba(0, 0, 0, 0.09);
    color: rgba(30, 41, 59, 0.8);
}

/* ===== Collapsed state ===== */
html.sidebar-collapsed #pal-sidebar {
    width: 52px;
}

html.sidebar-collapsed .pal-has-sidebar .pal-page {
    padding-left: 52px;
}

html.sidebar-collapsed .pal-sidebar-label,
html.sidebar-collapsed .pal-sidebar-section-label,
html.sidebar-collapsed .pal-sidebar-divider {
    display: none;
}

html.sidebar-collapsed .pal-sidebar-item {
    justify-content: center;
    padding: 0.65rem 0;
    gap: 0;
}

html.sidebar-collapsed .pal-sidebar-top-bar {
    justify-content: center;
}

html.sidebar-collapsed #pal-sidebar-toggle svg {
    transform: rotate(180deg);
}

/* Show all group items flat (no trigger) when collapsed */
html.sidebar-collapsed .pal-sidebar-group-trigger {
    display: none;
}

html.sidebar-collapsed .pal-sidebar-group-content {
    max-height: 400px !important;
    overflow: visible;
}

/* ===== Mobile — bottom bar ===== */
@media (max-width: 767px) {
    #pal-sidebar {
        top: auto;
        bottom: 0;
        left: 0;
        right: 0;
        width: 100%;
        height: 60px;
        padding-top: 0;
        flex-direction: row;
        border-right: none;
        border-top: 1px solid rgba(255, 255, 255, 0.07);
        background: rgba(8, 4, 22, 0.92);
    }

    .light-mode #pal-sidebar {
        background: rgba(248, 250, 252, 0.97);
        border-top-color: rgba(0, 0, 0, 0.07);
    }

    .pal-sidebar-inner {
        flex-direction: row;
        padding: 0 0.5rem;
        gap: 0;
        align-items: center;
        justify-content: space-around;
        width: 100%;
        overflow-x: auto;
    }

    .pal-sidebar-top-bar,
    .pal-sidebar-section-label,
    .pal-sidebar-divider,
    .pal-sidebar-badge {
        display: none;
    }

    /* Mobile: reset any collapsed padding override */
    html.sidebar-collapsed .pal-has-sidebar .pal-page {
        padding-left: 0;
    }

    .pal-sidebar-item {
        flex-direction: column;
        gap: 0.2rem;
        padding: 0.4rem 0.75rem;
        font-size: 0.58rem;
        border: none;
        border-radius: 6px;
        min-width: 48px;
        align-items: center;
        justify-content: center;
    }

    .pal-sidebar-icon {
        width: 20px;
        height: 20px;
    }

    .pal-sidebar-label {
        font-size: 0.58rem;
        font-weight: 600;
        letter-spacing: 0.02em;
    }

    /* Remove desktop offset, add bottom padding instead */
    .pal-has-sidebar .pal-page {
        padding-left: 0;
        padding-bottom: 60px;
    }

    /* Theme toggle clears the bottom bar */
    .pal-has-sidebar #theme-toggle {
        bottom: calc(60px + 1rem) !important;
    }
}
</style>
<script>
function palToggleSidebar() {
    var isCollapsed = document.documentElement.classList.toggle('sidebar-collapsed');
    localStorage.setItem('pal_sidebar_collapsed', isCollapsed ? '1' : '0');
    var btn = document.getElementById('pal-sidebar-toggle');
    if (btn) {
        btn.title = isCollapsed ? 'Expandir menu' : 'Recolher menu';
        btn.setAttribute('aria-label', btn.title);
    }
}
</script>
@endonce

<aside id="pal-sidebar" aria-label="Navegação principal">

    {{-- Toggle button —— visible only on desktop --}}
    <div class="pal-sidebar-top-bar">
        <button id="pal-sidebar-toggle"
                onclick="palToggleSidebar()"
                title="Recolher menu"
                aria-label="Recolher menu">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
            </svg>
        </button>
    </div>

    <div class="pal-sidebar-inner">

        {{-- ===== PROFESSOR ===== --}}
        @if($isProfessor)
            <span class="pal-sidebar-section-label">Docente</span>

            <x-ui.sidebar-item
                href="{{ route('dashboard.professor') }}"
                label="Início"
                :active="request()->routeIs('dashboard.professor')">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 9.75L12 3l9 6.75V21a1 1 0 01-1 1H4a1 1 0 01-1-1V9.75z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 22V12h6v10"/>
                </svg>
            </x-ui.sidebar-item>

            <x-ui.sidebar-item
                href="{{ route('professor.presenca.index') }}"
                label="Presença / QR"
                :active="request()->routeIs('professor.presenca.*')">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01
                             M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z
                             m12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1z
                             M5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                </svg>
            </x-ui.sidebar-item>

            <x-ui.sidebar-item
                href="{{ route('professor.gerenciar.index') }}"
                label="Minhas Turmas"
                :active="request()->routeIs('professor.gerenciar.*')"
                :badge="$sidebarCounts['minhas_materias'] ?? null">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13
                             C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13
                             C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13
                             C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </x-ui.sidebar-item>

            <x-ui.sidebar-item
                href="{{ route('professor.relatorios') }}"
                label="Relatórios"
                :active="request()->routeIs('professor.relatorios')">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2z
                             m0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2
                             m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </x-ui.sidebar-item>

            <div class="pal-sidebar-divider"></div>

            <x-ui.sidebar-item
                href="{{ route('professor.evento.presenca') }}"
                label="Evento / Palestra"
                :active="request()->routeIs('professor.evento.*')">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3"/>
                </svg>
            </x-ui.sidebar-item>
        @endif

        {{-- ===== MASTER ===== --}}
        @if($isMaster)
            <span class="pal-sidebar-section-label">Administração</span>

            <x-ui.sidebar-item
                href="{{ route('dashboard.master') }}"
                label="Visão Geral"
                :active="request()->routeIs('dashboard.master')">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6z
                             m10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6z
                             M4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2z
                             m10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                </svg>
            </x-ui.sidebar-item>

            <div class="pal-sidebar-divider"></div>

            <x-ui.sidebar-item
                href="{{ route('master.cadastrar') }}"
                label="Cadastrar"
                :active="request()->routeIs('master.cadastrar')">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 4v16m8-8H4"/>
                </svg>
            </x-ui.sidebar-item>

            <x-ui.sidebar-group
                label="Usuários"
                :open="request()->routeIs('master.professores') || request()->routeIs('master.alunos')">

                <x-ui.sidebar-item
                    href="{{ route('master.professores') }}"
                    label="Professores"
                    :active="request()->routeIs('master.professores')"
                    :badge="$sidebarCounts['total_professores'] ?? null">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </x-ui.sidebar-item>

                <x-ui.sidebar-item
                    href="{{ route('master.alunos') }}"
                    label="Alunos"
                    :active="request()->routeIs('master.alunos')"
                    :badge="$sidebarCounts['total_alunos'] ?? null">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857
                                 M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857
                                 m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </x-ui.sidebar-item>

            </x-ui.sidebar-group>

            <x-ui.sidebar-group
                label="Conteúdo"
                :open="request()->routeIs('master.materias') || request()->routeIs('master.presenca')">

                <x-ui.sidebar-item
                    href="{{ route('master.materias') }}"
                    label="Matérias"
                    :active="request()->routeIs('master.materias')"
                    :badge="$sidebarCounts['total_materias'] ?? null">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13
                                 C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13
                                 C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13
                                 C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </x-ui.sidebar-item>

                <x-ui.sidebar-item
                    href="{{ route('master.presenca') }}"
                    label="Central de Presença"
                    :active="request()->routeIs('master.presenca')">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2
                                 M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2
                                 m-6 9l2 2 4-4"/>
                    </svg>
                </x-ui.sidebar-item>

            </x-ui.sidebar-group>
        @endif

    </div>
</aside>

@endif
