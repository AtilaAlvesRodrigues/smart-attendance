@extends('layouts.theme')

@section('title', 'Gerenciar Professores - Master')

@section('body-class', 'gradient-bg relative min-h-screen flex flex-col')


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
@section('content')
    <div class="flex-grow flex flex-col relative overflow-hidden">
        
        <!-- Background Elements -->
        <div class="blob top-[-100px] left-[-100px]"></div>
        <div class="blob-2"></div>

        <!-- Glass Navbar -->
        <nav class="glass mx-6 mt-6 p-4 rounded-sm border border-white/10 relative z-20 backdrop-blur-xl animate-reveal">
            <div class="max-w-7xl mx-auto flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <a href="{{ route('dashboard.master') }}" class="w-10 h-10 bg-white/5 border border-white/10 rounded-sm flex items-center justify-center text-white font-black text-sm hover:border-white/30 transition">
                        P
                    </a>
                    <div>
                        <h1 class="text-xl font-black tracking-tighter text-white italic leading-none">GERENCIAR PROFESSORES</h1>
                        <a href="{{ route('dashboard.master') }}" class="text-xs font-bold text-white/70 hover:text-white uppercase tracking-widest mt-1 block">🛡️ Voltar ao Dashboard</a>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <div class="hidden md:block mr-2">
                        <input type="text" id="search-professores" placeholder="Pesquisar..."
                            class="pal-filter-input w-48 text-[11px]">
                    </div>
                </div>
            </div>
        </nav>

        <main class="max-w-7xl mx-auto w-full p-6 mt-8 relative z-10 flex-grow">
            
            <div class="mb-12 animate-reveal [animation-delay:200ms]">
                <h2 class="text-4xl font-black tracking-tighter pal-title">Corpo Docente</h2>
                <p class="pal-subtitle font-medium">Controle de acessos, turmas e registros de professores.</p>
            </div>

            <!-- Professores Section -->
            <div class="glass rounded-sm border border-white/10 overflow-hidden animate-reveal [animation-delay:400ms]">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-xs font-black text-white/80 uppercase tracking-[0.2em] border-b border-white/10">
                                <th class="py-4 px-6">Professor</th>
                                <th class="py-4 px-6">Email</th>
                                <th class="py-4 px-6">CPF</th>
                                <th class="py-4 px-6 text-center">Disciplinas</th>
                                <th class="py-4 px-6 text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody id="professores-body" class="divide-y divide-white/5">
                            @foreach($professores as $prof)
                            <tr onclick='openModal("professor", @json($prof))' class="hover:bg-white/5 transition-colors group cursor-pointer">
                                <td class="py-4 px-6">
                                    <span class="block font-bold text-white tracking-tight group-hover:text-blue-300 transition-colors">{{ $prof->nome }}</span>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="text-white/60 text-sm">{{ $prof->email }}</span>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="text-[11px] font-black text-white/70 font-mono tracking-wider bg-white/5 px-2 py-1 rounded-sm">{{ $prof->cpf }}</span>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <span class="px-3 py-1 bg-blue-500/20 text-blue-400 rounded-sm text-xs font-bold border border-blue-500/20">
                                        {{ $prof->materias_count }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <button class="text-white/70 group-hover:text-white transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($professores->isEmpty())
                    <div id="professores-empty" class="py-20 text-center text-white/70 italic font-medium">Nenhum professor encontrado.</div>
                @else
                    <div id="professores-empty" class="hidden py-20 text-center text-white/70 italic font-medium">Nenhum professor encontrado.</div>
                @endif
                
                <div class="px-8 py-6 border-t border-white/5 bg-white/5">
                    {{ $professores->links() }}
                </div>
            </div>
        </main>
    </div>

    <!-- Info Modal -->
    <div id="info-modal" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center backdrop-blur-md transition-opacity duration-300 p-4">
        <div class="glass rounded-sm shadow-2xl w-full max-w-lg transform transition-all scale-95 border border-white/10" id="modal-content-container">
            <div class="p-8">
                <div class="flex justify-between items-center mb-8 pb-4 border-b border-white/10">
                    <h3 id="modal-title" class="text-xs font-black text-white/70 uppercase tracking-[0.3em]">Detalhes</h3>
                    <button onclick="closeModal()" class="text-white/70 hover:text-white transition-colors">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                
                <div id="modal-body" class="space-y-6">
                <!-- Dynamic Content -->
                </div>
            </div>
        </div>
    </div>

    <!-- User Profile Modal -->
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
@endsection

@push('scripts')
    <script src="{{ asset('js/dashboard_modal.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
             const escapeHtml = (unsafe) => {
                if (unsafe === null || unsafe === undefined) return '';
                return String(unsafe).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
            };

            const setupSearch = (inputId, url, tbodyId, emptyId, renderRow) => {
                const input = document.getElementById(inputId);
                const tbody = document.getElementById(tbodyId);
                const emptyMsg = document.getElementById(emptyId);
                const originalTbody = tbody.innerHTML;
                const paginationEl = tbody.closest('.glass, .overflow-hidden')?.querySelector('.px-8.py-6.border-t');
                const originalPagination = paginationEl ? paginationEl.innerHTML : '';
                let debounceTimer = null;

                const triggerSearch = async () => {
                    const query = input.value.trim();

                    // Se vazio, restaurar conteudo original do Blade (com paginacao)
                    if (query === '') {
                        tbody.innerHTML = originalTbody;
                        emptyMsg.classList.add('hidden');
                        if (paginationEl) {
                            paginationEl.innerHTML = originalPagination;
                            paginationEl.style.display = '';
                        }
                        return;
                    }

                    try {
                        const params = new URLSearchParams();
                        params.set('q', query);
                        const response = await fetch(`${url}?${params.toString()}`);
                        const data = await response.json();

                        tbody.innerHTML = '';
                        if (paginationEl) paginationEl.style.display = 'none';

                        if (data.length === 0) {
                            emptyMsg.classList.remove('hidden');
                        } else {
                            emptyMsg.classList.add('hidden');
                            data.forEach(item => {
                                const jsonItem = JSON.stringify(item).replace(/"/g, '&quot;').replace(/'/g, '&apos;');
                                tbody.innerHTML += renderRow(item, jsonItem, escapeHtml);
                            });
                        }
                    } catch (error) {
                        console.error('Erro na pesquisa:', error);
                    }
                };

                input.addEventListener('input', () => {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(triggerSearch, 300);
                });
            };

            setupSearch('search-professores', '{{ route("master.search.professores") }}', 'professores-body', 'professores-empty', (prof, jsonItem, esc) => `
                <tr onclick='openModal("professor", ${jsonItem})' class="hover:bg-white/5 transition-colors group cursor-pointer">
                    <td class="py-4 px-6">
                        <span class="block font-bold text-white tracking-tight group-hover:text-blue-300 transition-colors">${esc(prof.nome)}</span>
                    </td>
                    <td class="py-4 px-6">
                        <span class="text-white/60 text-sm">${esc(prof.email)}</span>
                    </td>
                    <td class="py-4 px-6">
                        <span class="text-[11px] font-black text-white/70 font-mono tracking-wider bg-white/5 px-2 py-1 rounded-sm">${esc(prof.cpf)}</span>
                    </td>
                    <td class="py-4 px-6 text-center">
                        <span class="px-3 py-1 bg-blue-500/20 text-blue-400 rounded-sm text-xs font-bold border border-blue-500/20">
                            ${prof.materias ? prof.materias.length : 0}
                        </span>
                    </td>
                    <td class="py-4 px-6 text-right">
                        <button class="text-white/70 group-hover:text-white transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </button>
                    </td>
                </tr>
            `);

            // Profile Modal Logic
            const pModal = document.getElementById('profile-modal');
            const openPBtn = document.getElementById('open-profile');
            const closePBtn = document.getElementById('close-profile');
            const pOverlay = document.getElementById('close-profile-overlay');

            function togglePModal(show) {
                pModal.classList.toggle('hidden', !show);
                if(show) {
                    pModal.style.display = 'flex';
                } else {
                    pModal.style.display = 'none';
                }
                document.body.style.overflow = show ? 'hidden' : '';
            }

            if (openPBtn) openPBtn.addEventListener('click', (e) => { e.stopPropagation(); togglePModal(true); });
            if (closePBtn) closePBtn.addEventListener('click', () => togglePModal(false));
            if (pOverlay) pOverlay.addEventListener('click', () => togglePModal(false));
            document.addEventListener('keydown', e => { if (e.key === 'Escape') togglePModal(false); });
        });
    </script>
@endpush
