@extends('layouts.theme')

@section('title', 'Gerenciar Matérias - Master')

@section('body-class', 'gradient-bg relative min-h-screen flex flex-col')
@section('no-nav')

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
                        M
                    </a>
                    <div>
                        <h1 class="text-xl font-black tracking-tighter text-white italic leading-none">GERENCIAR DISCIPLINAS</h1>
                        <a href="{{ route('dashboard.master') }}" class="text-xs font-bold text-white/70 hover:text-white uppercase tracking-widest mt-1 block">🛡️ Voltar ao Dashboard</a>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <div class="hidden md:block mr-2">
                        <input type="text" id="search-materias" placeholder="Pesquisar..." 
                            class="px-4 py-1.5 bg-white/5 border border-white/10 rounded-sm text-white text-[11px] font-medium focus:outline-none focus:ring-1 focus:ring-white/30 transition-all w-48">
                    </div>
                    
                    <div class="flex items-center gap-3 border-l border-white/10 pl-4">
                        <div class="text-right hidden sm:block">
                            <p class="text-[10px] font-black text-white/40 uppercase tracking-widest leading-none mb-1">Master</p>
                            <p class="text-xs font-bold text-white leading-none">Admin</p>
                        </div>
                        <button id="open-profile" class="pal-profile-btn">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <main class="max-w-7xl mx-auto w-full p-6 mt-8 relative z-10 flex-grow">
            
            <div class="mb-12 animate-reveal [animation-delay:200ms]">
                <h2 class="text-4xl font-black tracking-tighter pal-title">Grade Curricular</h2>
                <p class="pal-subtitle font-medium">Configuração de horários, salas e vínculos acadêmicos.</p>
            </div>

            <!-- Matérias Section -->
            <div class="glass rounded-sm border border-white/10 overflow-hidden animate-reveal [animation-delay:400ms]">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-xs font-black text-white/80 uppercase tracking-[0.2em] border-b border-white/10">
                                <th class="py-4 px-6">Disciplina</th>
                                <th class="py-4 px-6 text-center">Matutino</th>
                                <th class="py-4 px-6 text-center">Vespertino</th>
                                <th class="py-4 px-6 text-center">Noturno</th>
                                <th class="py-4 px-6 text-center">Docentes</th>
                                <th class="py-4 px-6 text-center">Alunos</th>
                            </tr>
                        </thead>
                        <tbody id="materias-body" class="divide-y divide-white/5">
                            @foreach($materias as $materia)
                            <tr onclick='openModal("materia", @json($materia))' class="hover:bg-white/5 transition-colors group cursor-pointer">
                                <td class="py-4 px-6">
                                    <span class="block font-bold text-white tracking-tight group-hover:text-teal-300 transition-colors">{{ $materia->nome }}</span>
                                    <div class="flex gap-2 mt-1">
                                        <span class="text-[9px] text-white/80 font-black uppercase tracking-widest">{{ $materia->sala }}</span>
                                        <span class="text-[9px] text-white/70 font-black uppercase tracking-widest">• {{ $materia->carga_horaria }}h</span>
                                    </div>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <span class="w-2 h-2 rounded-full inline-block {{ $materia->horario_matutino ? 'bg-green-500 shadow-lg shadow-green-500/50 animate-pulse' : 'bg-white/10' }}"></span>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <span class="w-2 h-2 rounded-full inline-block {{ $materia->horario_vespertino ? 'bg-green-500 shadow-lg shadow-green-500/50 animate-pulse' : 'bg-white/10' }}"></span>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <span class="w-2 h-2 rounded-full inline-block {{ $materia->horario_noturno ? 'bg-green-500 shadow-lg shadow-green-500/50 animate-pulse' : 'bg-white/10' }}"></span>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <span class="px-3 py-1 bg-blue-500/20 text-blue-400 rounded-sm text-xs font-bold border border-blue-500/20">
                                        {{ $materia->professores_count }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <span class="px-3 py-1 bg-purple-500/20 text-purple-400 rounded-sm text-xs font-bold border border-purple-500/20">
                                        {{ $materia->alunos_count }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($materias->isEmpty())
                    <div id="materias-empty" class="py-20 text-center text-white/70 italic font-medium">Nenhuma disciplina encontrada.</div>
                @else
                    <div id="materias-empty" class="hidden py-20 text-center text-white/70 italic font-medium">Nenhuma disciplina encontrada.</div>
                @endif
                
                <div class="px-8 py-6 border-t border-white/5 bg-white/5">
                    {{ $materias->links() }}
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

                const triggerSearch = async () => {
                    const query = input.value;
                    try {
                        const response = await fetch(`${url}?q=${query}`);
                        const data = await response.json();

                        tbody.innerHTML = '';
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

                input.addEventListener('input', triggerSearch);
                input.addEventListener('focus', triggerSearch);
            };

            setupSearch('search-materias', '{{ route("master.search.materias") }}', 'materias-body', 'materias-empty', (materia, jsonItem, esc) => `
                <tr onclick='openModal("materia", ${jsonItem})' class="hover:bg-white/5 transition-colors group cursor-pointer">
                    <td class="py-4 px-6">
                        <span class="block font-bold text-white tracking-tight group-hover:text-teal-300 transition-colors">${esc(materia.nome)}</span>
                        <div class="flex gap-2 mt-1">
                            <span class="text-[9px] text-white/80 font-black uppercase tracking-widest">${esc(materia.sala || '')}</span>
                            <span class="text-[9px] text-white/70 font-black uppercase tracking-widest">• ${esc(materia.carga_horaria)}h</span>
                        </div>
                    </td>
                    <td class="py-4 px-6 text-center">
                        <span class="w-2 h-2 rounded-full inline-block ${materia.horario_matutino ? 'bg-green-500 shadow-lg shadow-green-500/50 animate-pulse' : 'bg-white/10'}"></span>
                    </td>
                    <td class="py-4 px-6 text-center">
                        <span class="w-2 h-2 rounded-full inline-block ${materia.horario_vespertino ? 'bg-green-500 shadow-lg shadow-green-500/50 animate-pulse' : 'bg-white/10'}"></span>
                    </td>
                    <td class="py-4 px-6 text-center">
                        <span class="w-2 h-2 rounded-full inline-block ${materia.horario_noturno ? 'bg-green-500 shadow-lg shadow-green-500/50 animate-pulse' : 'bg-white/10'}"></span>
                    </td>
                    <td class="py-4 px-6 text-center">
                        <span class="px-3 py-1 bg-blue-500/20 text-blue-400 rounded-sm text-xs font-bold border border-blue-500/20">
                            ${materia.professores_count ?? 0}
                        </span>
                    </td>
                    <td class="py-4 px-6 text-center">
                        <span class="px-3 py-1 bg-purple-500/20 text-purple-400 rounded-sm text-xs font-bold border border-purple-500/20">
                            ${materia.alunos_count ?? 0}
                        </span>
                    </td>
                </tr>
            `);

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
        });
    </script>
@endpush
