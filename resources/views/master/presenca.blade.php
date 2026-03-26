@extends('layouts.theme')

@section('title', 'Central de Frequência - Master')

@section('body-class', 'gradient-bg relative min-h-screen flex flex-col')

@section('nav-left')
    <a href="{{ route('dashboard.master') }}" class="pal-nav-btn pal-nav-btn-ghost">
        ← Dashboard
    </a>
@endsection

@section('nav-user')
<div class="pal-nav-actions" style="gap:0.5rem">
    <div class="pal-nav-user">
        <span class="pal-nav-user-role">Acesso Root</span>
        <span class="pal-nav-user-name">Admin</span>
    </div>
    <button id="open-profile" type="button" class="pal-profile-btn">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
    </button>
</div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/theme_master.css') }}">
    <style>
        @media print {
            body { background: white !important; color: black !important; }
            .no-print, nav, .blob, .blob-2, footer, #cursor-glow, #theme-toggle { display: none !important; }
            .glass { background: white !important; border: 1px solid #eee !important; box-shadow: none !important; backdrop-filter: none !important; }
            main { padding: 0 !important; margin: 0 !important; max-width: 100% !important; }
            #printable-content { border: none !important; }
            table { width: 100% !important; border-collapse: collapse !important; }
            th, td { color: black !important; border-bottom: 1px solid #eee !important; }
            .text-white, .text-white\/30, .text-white\/40 { color: black !important; }
        }
        .suggestion-box {
            position: absolute; top: 100%; left: 0; right: 0; z-index: 50;
            background: rgba(255,255,255,0.08); backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.1); border-radius: 1.25rem;
            margin-top: 0.5rem; max-height: 250px; overflow-y: auto;
            display: none; box-shadow: 0 20px 40px -10px rgba(0,0,0,0.5);
        }
        html.light-mode .suggestion-box { background: rgba(255,255,255,0.95); border-color: rgba(0,0,0,0.1); box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1); }
        .suggestion-item { padding: 1rem 1.25rem; color: white; font-size: 0.875rem; cursor: pointer; transition: all 0.2s; border-bottom: 1px solid rgba(255,255,255,0.05); }
        html.light-mode .suggestion-item { color: #1e293b; border-bottom-color: rgba(0,0,0,0.05); }
        .suggestion-item:last-child { border-bottom: none; }
        .suggestion-item:hover { background: rgba(255,255,255,0.1); padding-left: 1.5rem; }
        html.light-mode .suggestion-item:hover { background: rgba(0,0,0,0.03); color: #7c3aed; }
        .suggestion-item .sub { display: block; font-size: 0.75rem; color: rgba(255,255,255,0.4); font-weight: bold; margin-top: 2px; }
        html.light-mode .suggestion-item .sub { color: #64748b; }
    </style>
@endpush

@section('content')
    <div class="flex-grow flex flex-col relative overflow-hidden">
        
        <!-- Background Elements -->
        <div class="blob top-[-100px] left-[-100px]"></div>
        <div class="blob-2"></div>

        <!-- Sub-header: Painel de Chamadas + PDF -->
        <div class="no-print" style="padding: 1.5rem 2rem 0;">
            <div class="glass max-w-7xl mx-auto flex justify-between items-center p-4 rounded-sm border border-white/10">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-sm flex items-center justify-center shadow-lg transition-transform hover:scale-105" style="background:var(--pal-primary); box-shadow:0 10px 20px rgba(255,165,60,0.3); color:#fff;">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    </div>
                    <div>
                        <p class="text-[1.1rem] font-black tracking-tighter pal-title italic leading-none" style="margin:0;">PAINEL DE CHAMADAS</p>
                        <p class="pal-subtitle" style="margin:2px 0 0; font-size:0.65rem; text-transform:uppercase; letter-spacing:0.1em; font-weight:800;">Central de Frequência</p>
                    </div>
                </div>
                <button onclick="window.print()" class="px-5 py-2 bg-blue-600 hover:bg-blue-500 rounded-sm text-white text-xs font-bold transition-all hover:scale-105 active:scale-95 shadow-lg shadow-blue-600/20 flex items-center gap-2">
                    📄 Gerar PDF
                </button>
            </div>
        </div>

        {{-- User Profile Modal --}}
        <div id="profile-modal" class="pal-modal-overlay" style="display:none; z-index:100;">
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

        <main class="max-w-7xl mx-auto w-full p-6 mt-8 relative z-10 flex-grow">
    
            <div class="mb-12 animate-reveal [animation-delay:200ms] no-print">
                <h2 class="pal-title" style="font-size:2.5rem; margin-bottom:0.5rem;">Relatório de Presenças</h2>
                <p class="pal-subtitle font-medium">Acompanhamento centralizado de registros em tempo real.</p>
            </div>

            <!-- Filtros 3 Inputs Assíncronos -->
            <div class="glass p-4 md:p-6 rounded-sm border border-white/10 mb-8 animate-reveal [animation-delay:300ms] no-print relative z-30">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 items-end">
                    <div class="relative">
                        <label class="pal-eyebrow" style="margin-bottom:0.5rem;">Professor</label>
                        <input type="text" id="filter-professor" autocomplete="off" placeholder="Nome ou CPF..." class="pal-filter-input w-full">
                    </div>
                    <div class="relative">
                        <label class="pal-eyebrow" style="margin-bottom:0.5rem;">Matéria</label>
                        <input type="text" id="filter-materia" autocomplete="off" placeholder="Nome ou Sala..." class="pal-filter-input w-full">
                    </div>
                    <div class="relative">
                        <label class="pal-eyebrow" style="margin-bottom:0.5rem;">Aluno</label>
                        <input type="text" id="filter-aluno" autocomplete="off" placeholder="Nome ou RA..." class="pal-filter-input w-full">
                    </div>
                    <div class="flex gap-2 no-print">
                        <button type="button" id="btn-clear-filters" class="px-4 py-2 flex items-center justify-center rounded-sm border transition-all pal-btn-action flex-grow w-full"
                           style="background:rgba(255,255,255,0.05); border-color:rgba(255,255,255,0.1); color:var(--pal-gray);"
                           onmouseover="this.style.background='rgba(255,255,255,0.1)'"
                           onmouseout="this.style.background='rgba(255,255,255,0.05)'">
                            Limpar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tabela Container -->
            <div id="printable-content" class="glass rounded-sm border border-white/10 overflow-hidden animate-reveal [animation-delay:400ms] relative z-10">
                <div class="px-8 py-6 border-b border-white/10 flex justify-between items-center no-print bg-white/5">
                    <h3 class="text-xl font-black text-white italic tracking-tighter">Registros de Frequência</h3>
                    <span class="px-3 py-1 bg-white/10 rounded-sm text-xs font-black text-white/60 uppercase tracking-widest">
                        Total: {{ $presencas->total() }} de registros
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-xs font-black text-white/80 uppercase tracking-[0.2em] border-b border-white/10">
                                <th class="py-4 px-6">Data / Hora</th>
                                <th class="py-4 px-6">Aluno (RA)</th>
                                <th class="py-4 px-6">Disciplina</th>
                                <th class="py-4 px-6">Professor (CPF)</th>
                                <th class="py-4 px-6 text-center">Faltas</th>
                                <th class="py-4 px-6 text-center">Média</th>
                            </tr>
                        </thead>
                        <tbody id="presencas-body" class="divide-y divide-white/5">
                            @forelse($presencas as $p)
                                @php
                                    $materiaPivot = $p->aluno ? $p->aluno->materias->where('id', $p->materia_id)->first()->pivot ?? null : null;
                                    $media = null;
                                    if($materiaPivot) {
                                        $notas = collect([$materiaPivot->prova1, $materiaPivot->prova2, $materiaPivot->trabalho1, $materiaPivot->trabalho2])
                                            ->filter(fn($n) => !is_null($n));
                                        if($notas->isNotEmpty()) {
                                            $media = round($notas->avg(), 1);
                                        }
                                    }

                                    $totalAulas = $p->materia->total_aulas ?? 0;
                                    $presencasAluno = \App\Models\Presenca::where('materia_id', $p->materia_id)
                                        ->where('aluno_id', $p->aluno_id)
                                        ->count();
                                    $faltas = max(0, $totalAulas - $presencasAluno);
                                    
                                    $corMedia = 'text-white/70';
                                    if($media !== null) {
                                        $corMedia = $media >= 6 ? 'text-green-400 font-bold' : 'text-red-400 font-bold';
                                    }
                                @endphp

                                <tr class="hover:bg-white/5 transition-colors group">
                                    <td class="py-4 px-6 whitespace-nowrap">
                                        <span class="block font-bold text-white tracking-tight">{{ \Carbon\Carbon::parse($p->data_aula)->format('d/m/Y') }}</span>
                                        <span class="block text-xs text-white/80 font-mono">{{ $p->created_at->format('H:i') }}</span>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="block font-bold text-white tracking-tight">{{ $p->aluno->nome ?? 'N/A' }}</span>
                                        <span class="block text-xs text-white/80 font-mono">{{ $p->aluno->ra ?? 'N/A' }}</span>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="block font-bold text-white tracking-tight">{{ $p->materia->nome ?? 'N/A' }}</span>
                                        <span class="block text-xs text-white/80 uppercase tracking-widest font-black">{{ $p->materia->sala ?? '' }}</span>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="block text-white/80 font-medium">{{ $p->professor->nome ?? 'N/A' }}</span>
                                        <span class="block text-xs text-white/80 font-mono">{{ $p->professor->cpf ?? 'N/A' }}</span>
                                    </td>
                                    
                                    <td class="py-4 px-6 text-center">
                                        <div class="flex flex-col items-center">
                                            <span class="{{ $faltas > 0 ? 'text-red-400 font-black' : 'text-green-400' }} text-lg tracking-tighter">
                                                {{ $faltas }}
                                            </span>
                                            <span class="text-[9px] text-white/70 uppercase font-bold">de {{ $totalAulas }}</span>
                                        </div>
                                    </td>

                                    <td class="py-4 px-6 text-center">
                                        <span class="{{ $corMedia }} text-lg block tracking-tighter">
                                            {{ $media !== null ? number_format($media, 1) : '--' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($presencas->isEmpty())
                    <div id="presencas-empty" class="py-20 text-center text-white/70 italic font-medium">Nenhum registro encontrado.</div>
                @else
                    <div id="presencas-empty" class="hidden py-20 text-center text-white/70 italic font-medium">Nenhum registro encontrado.</div>
                @endif
                
                <div id="pagination-links" class="px-8 py-6 border-t border-white/5 bg-white/5 no-print">
                    {{ $presencas->withQueryString()->links() }}
                </div>
            </div>
        </main>
    </div>
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Profile Modal Logic
        const pModal = document.getElementById('profile-modal');
        const openPBtn = document.getElementById('open-profile');
        const closePBtn = document.getElementById('close-profile');
        const pOverlay = document.getElementById('close-profile-overlay');

        function togglePModal(show) {
            pModal.style.display = show ? 'flex' : 'none';
            document.body.style.overflow = show ? 'hidden' : '';
        }

        if (openPBtn) openPBtn.addEventListener('click', () => togglePModal(true));
        if (closePBtn) closePBtn.addEventListener('click', () => togglePModal(false));
        if (pOverlay) pOverlay.addEventListener('click', () => togglePModal(false));
        document.addEventListener('keydown', e => { if (e.key === 'Escape') togglePModal(false); });

        const escapeHtml = (unsafe) => {
            if (unsafe === null || unsafe === undefined) return '';
            return String(unsafe).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
        };

        const setupMultiSearch = (inputIds, url, tbodyId, emptyId, paginationId, renderRow) => {
            const inputs = inputIds.map(id => document.getElementById(id));
            const tbody = document.getElementById(tbodyId);
            const emptyMsg = document.getElementById(emptyId);
            const pagination = document.getElementById(paginationId);
            const clearBtn = document.getElementById('btn-clear-filters');
            const originalTbody = tbody ? tbody.innerHTML : '';
            const originalPagination = pagination ? pagination.innerHTML : '';
            let debounceTimer = null;

            if (!tbody) return;

            const triggerSearch = async () => {
                const queryParams = new URLSearchParams();
                let hasQuery = false;

                inputs.forEach(input => {
                    if (input && input.value.trim() !== '') {
                        const paramName = input.id.replace('filter-', '');
                        queryParams.set(paramName, input.value.trim());
                        hasQuery = true;
                    }
                });

                // Se nenhum filtro preenchido, restaurar conteudo original do Blade
                if (!hasQuery) {
                    tbody.innerHTML = originalTbody;
                    emptyMsg.classList.add('hidden');
                    if (pagination) {
                        pagination.innerHTML = originalPagination;
                        pagination.style.display = '';
                    }
                    return;
                }

                try {
                    const response = await fetch(`${url}?${queryParams.toString()}`);
                    const data = await response.json();

                    tbody.innerHTML = '';
                    if (pagination) pagination.style.display = 'none';

                    if (data.length === 0) {
                        emptyMsg.classList.remove('hidden');
                    } else {
                        emptyMsg.classList.add('hidden');
                        data.forEach(item => {
                            tbody.innerHTML += renderRow(item, escapeHtml);
                        });
                    }
                } catch (error) {
                    console.error('Erro na pesquisa:', error);
                }
            };

            inputs.forEach(input => {
                if(input) input.addEventListener('input', () => {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(triggerSearch, 300);
                });
            });

            if(clearBtn) {
                clearBtn.addEventListener('click', () => {
                    inputs.forEach(input => { if(input) input.value = ''; });
                    clearTimeout(debounceTimer);
                    triggerSearch();
                });
            }
        };

        setupMultiSearch(['filter-professor', 'filter-materia', 'filter-aluno'], '{{ route("master.search.presencas") }}', 'presencas-body', 'presencas-empty', 'pagination-links', (p, esc) => {
            let corMedia = 'text-white/70';
            if (p.media !== null) {
                corMedia = p.media >= 6 ? 'text-green-400 font-bold' : 'text-red-400 font-bold';
            }
            const faltasClass = p.faltas > 0 ? 'text-red-400 font-black' : 'text-green-400';

            return `
                <tr class="hover:bg-white/5 transition-colors group">
                    <td class="py-4 px-6 whitespace-nowrap">
                        <span class="block font-bold text-white tracking-tight">${esc(p.data_aula_formatted)}</span>
                        <span class="block text-xs text-white/80 font-mono">${esc(p.created_time)}</span>
                    </td>
                    <td class="py-4 px-6">
                        <span class="block font-bold text-white tracking-tight">${esc(p.aluno_nome)}</span>
                        <span class="block text-xs text-white/80 font-mono">${esc(p.aluno_ra)}</span>
                    </td>
                    <td class="py-4 px-6">
                        <span class="block font-bold text-white tracking-tight">${esc(p.materia_nome)}</span>
                        <span class="block text-xs text-white/80 uppercase tracking-widest font-black">${esc(p.materia_sala)}</span>
                    </td>
                    <td class="py-4 px-6">
                        <span class="block text-white/80 font-medium">${esc(p.professor_nome)}</span>
                        <span class="block text-xs text-white/80 font-mono">${esc(p.professor_cpf)}</span>
                    </td>
                    <td class="py-4 px-6 text-center">
                        <div class="flex flex-col items-center">
                            <span class="${faltasClass} text-lg tracking-tighter">
                                ${p.faltas}
                            </span>
                            <span class="text-[9px] text-white/70 uppercase font-bold">de ${p.total_aulas}</span>
                        </div>
                    </td>
                    <td class="py-4 px-6 text-center">
                        <span class="${corMedia} text-lg block tracking-tighter">
                            ${p.media !== null ? Number(p.media).toFixed(1) : '--'}
                        </span>
                    </td>
                </tr>
            `;
        });
    });
</script>
@endpush
@endsection
