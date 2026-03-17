@extends('layouts.theme')

@section('title', 'Gerenciar Alunos - Master')

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
                        A
                    </a>
                    <div>
                        <h1 class="text-xl font-black tracking-tighter text-white italic leading-none">GERENCIAR ALUNOS</h1>
                        <a href="{{ route('dashboard.master') }}" class="text-xs font-bold text-white/70 hover:text-white uppercase tracking-widest mt-1 block">🛡️ Voltar ao Dashboard</a>
                    </div>
                </div>

                <div class="hidden md:block">
                    <input type="text" id="search-alunos" placeholder="Pesquisar por nome ou RA..." 
                        class="px-5 py-2 bg-white/5 border border-white/10 rounded-sm text-white text-xs font-medium focus:outline-none focus:ring-1 focus:ring-white/30 transition-all w-64">
                </div>
            </div>
        </nav>

        <main class="max-w-7xl mx-auto w-full p-6 mt-8 relative z-10 flex-grow">
            
            <div class="mb-12 animate-reveal [animation-delay:200ms]">
                <h2 class="text-4xl font-black tracking-tighter pal-always-white">Base de Discentes</h2>
                <p class="text-white/70 font-medium">Gerenciamento centralizado de matrículas e dados acadêmicos.</p>
            </div>

            <!-- Alunos Section -->
            <div class="glass rounded-sm border border-white/10 overflow-hidden animate-reveal [animation-delay:400ms]">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-xs font-black text-white/80 uppercase tracking-[0.2em] border-b border-white/10">
                                <th class="py-4 px-6">Nome</th>
                                <th class="py-4 px-6">Email</th>
                                <th class="py-4 px-6">RA</th>
                                <th class="py-4 px-6 text-center">Disciplinas</th>
                                <th class="py-4 px-6 text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody id="alunos-body" class="divide-y divide-white/5">
                            @foreach($alunos as $aluno)
                            <tr onclick='openModal("aluno", @json($aluno))' class="hover:bg-white/5 transition-colors group cursor-pointer">
                                <td class="py-4 px-6">
                                    <span class="block font-bold text-white tracking-tight group-hover:text-purple-300 transition-colors">{{ $aluno->nome }}</span>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="text-white/60 text-sm">{{ $aluno->email }}</span>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="text-[11px] font-black text-white/70 font-mono tracking-wider bg-white/5 px-2 py-1 rounded-sm">{{ $aluno->ra }}</span>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <span class="px-3 py-1 bg-purple-500/20 text-purple-400 rounded-sm text-xs font-bold border border-purple-500/20">
                                        {{ $aluno->materias_count }}
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

                @if($alunos->isEmpty())
                    <div id="alunos-empty" class="py-20 text-center text-white/70 italic font-medium">Nenhum aluno encontrado.</div>
                @else
                    <div id="alunos-empty" class="hidden py-20 text-center text-white/70 italic font-medium">Nenhum aluno encontrado.</div>
                @endif
                
                <div class="px-8 py-6 border-t border-white/5 bg-white/5">
                    {{ $alunos->links() }}
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

            setupSearch('search-alunos', '{{ route("master.search.alunos") }}', 'alunos-body', 'alunos-empty', (aluno, jsonItem, esc) => `
                <tr onclick='openModal("aluno", ${jsonItem})' class="hover:bg-white/5 transition-colors group cursor-pointer">
                    <td class="py-4 px-6">
                        <span class="block font-bold text-white tracking-tight group-hover:text-purple-300 transition-colors">${esc(aluno.nome)}</span>
                    </td>
                    <td class="py-4 px-6">
                        <span class="text-white/60 text-sm">${esc(aluno.email)}</span>
                    </td>
                    <td class="py-4 px-6">
                        <span class="text-[11px] font-black text-white/70 font-mono tracking-wider bg-white/5 px-2 py-1 rounded-sm">${esc(aluno.ra)}</span>
                    </td>
                    <td class="py-4 px-6 text-center">
                        <span class="px-3 py-1 bg-purple-500/20 text-purple-400 rounded-sm text-xs font-bold border border-purple-500/20">
                            ${aluno.materias ? aluno.materias.length : 0}
                        </span>
                    </td>
                    <td class="py-4 px-6 text-right">
                        <button class="text-white/70 group-hover:text-white transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </button>
                    </td>
                </tr>
            `);
        });
    </script>
@endpush
