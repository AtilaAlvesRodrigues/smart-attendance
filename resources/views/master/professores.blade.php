@extends('layouts.main')

@section('title', 'Gerenciar Professores - Master')

@section('body-class', 'gradient-bg relative min-h-screen flex flex-col')

@section('content')
    <div class="flex-grow flex flex-col relative overflow-hidden">
        
        <!-- Background Elements -->
        <div class="blob top-[-100px] left-[-100px]"></div>
        <div class="blob-2"></div>

        <!-- Glass Navbar -->
        <nav class="glass mx-6 mt-6 p-4 rounded-2xl border border-white/10 relative z-20 backdrop-blur-xl animate-reveal">
            <div class="max-w-7xl mx-auto flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <a href="{{ route('dashboard.master') }}" class="w-10 h-10 bg-blue-500 rounded-xl flex items-center justify-center text-white font-black italic shadow-lg shadow-blue-500/50 hover:scale-110 transition-transform">
                        P
                    </a>
                    <div>
                        <h1 class="text-xl font-black tracking-tighter text-white italic leading-none">GERENCIAR PROFESSORES</h1>
                        <a href="{{ route('dashboard.master') }}" class="text-[10px] font-bold text-white/40 hover:text-white uppercase tracking-widest mt-1 block">🛡️ Voltar ao Dashboard</a>
                    </div>
                </div>

                <div class="hidden md:block">
                    <input type="text" id="search-professores" placeholder="Pesquisar por nome ou CPF..." 
                        class="px-5 py-2 bg-white/5 border border-white/10 rounded-xl text-white text-xs font-medium focus:outline-none focus:ring-2 focus:ring-blue-500/50 transition-all w-64 backdrop-blur-md">
                </div>
            </div>
        </nav>

        <main class="max-w-7xl mx-auto w-full p-6 mt-8 relative z-10 flex-grow">
            
            <div class="mb-12 animate-reveal [animation-delay:200ms]">
                <h2 class="text-4xl font-black text-white tracking-tighter mb-2">Corpo Docente</h2>
                <p class="text-white/40 font-medium">Controle de acessos, turmas e registros de professores.</p>
            </div>

            <!-- Professores Section -->
            <div class="glass rounded-3xl border border-white/10 overflow-hidden animate-reveal [animation-delay:400ms]">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-[10px] font-black text-white/30 uppercase tracking-[0.2em] border-b border-white/10">
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
                                    <span class="text-[11px] font-black text-white/40 font-mono tracking-wider bg-white/5 px-2 py-1 rounded-lg">{{ $prof->cpf }}</span>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <span class="px-3 py-1 bg-blue-500/20 text-blue-400 rounded-lg text-xs font-bold border border-blue-500/20">
                                        {{ $prof->materias_count }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <button class="text-white/20 group-hover:text-white transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($professores->isEmpty())
                    <div id="professores-empty" class="py-20 text-center text-white/20 italic font-medium">Nenhum professor encontrado.</div>
                @else
                    <div id="professores-empty" class="hidden py-20 text-center text-white/20 italic font-medium">Nenhum professor encontrado.</div>
                @endif
                
                <div class="px-8 py-6 border-t border-white/5 bg-white/5">
                    {{ $professores->links() }}
                </div>
            </div>
        </main>
    </div>

    <!-- Info Modal -->
    <div id="info-modal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center backdrop-blur-sm transition-opacity duration-300">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 transform transition-all scale-95" id="modal-content-container">
            <div class="p-6">
                <div class="flex justify-between items-start mb-6">
                    <h3 id="modal-title" class="text-2xl font-bold text-gray-800">Detalhes</h3>
                    <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 focus:outline-none transition">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                
                <div id="modal-body" class="space-y-4">
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

            setupSearch('search-professores', '{{ route("master.search.professores") }}', 'professores-body', 'professores-empty', (prof, jsonItem, esc) => `
                <tr onclick='openModal("professor", ${jsonItem})' class="hover:bg-white/5 transition-colors group cursor-pointer">
                    <td class="py-4 px-6">
                        <span class="block font-bold text-white tracking-tight group-hover:text-blue-300 transition-colors">${esc(prof.nome)}</span>
                    </td>
                    <td class="py-4 px-6">
                        <span class="text-white/60 text-sm">${esc(prof.email)}</span>
                    </td>
                    <td class="py-4 px-6">
                        <span class="text-[11px] font-black text-white/40 font-mono tracking-wider bg-white/5 px-2 py-1 rounded-lg">${esc(prof.cpf)}</span>
                    </td>
                    <td class="py-4 px-6 text-center">
                        <span class="px-3 py-1 bg-blue-500/20 text-blue-400 rounded-lg text-xs font-bold border border-blue-500/20">
                            ${prof.materias ? prof.materias.length : 0}
                        </span>
                    </td>
                    <td class="py-4 px-6 text-right">
                        <button class="text-white/20 group-hover:text-white transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </button>
                    </td>
                </tr>
            `);
        });
    </script>
@endpush
