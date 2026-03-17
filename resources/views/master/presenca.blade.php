@extends('layouts.theme')

@section('title', 'Central de Frequência - Master')

@section('body-class', 'gradient-bg relative min-h-screen flex flex-col')
@section('no-nav')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/theme_master.css') }}">
    @endpush
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
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            z-index: 50;
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 1.25rem;
            margin-top: 0.5rem;
            max-height: 250px;
            overflow-y: auto;
            display: none;
            box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.5);
        }
        body.theme-light .suggestion-box {
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid rgba(0, 0, 0, 0.1);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
        .suggestion-item {
            padding: 1rem 1.25rem;
            color: white;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        body.theme-light .suggestion-item {
            color: #1e293b;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        .suggestion-item:last-child {
            border-bottom: none;
        }
        .suggestion-item:hover {
            background: rgba(255, 255, 255, 0.1);
            padding-left: 1.5rem;
        }
        body.theme-light .suggestion-item:hover {
            background: rgba(0, 0, 0, 0.03);
            color: #a855f7;
        }
        .suggestion-item .sub {
            display: block;
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.4);
            font-weight: bold;
            margin-top: 2px;
        }
        body.theme-light .suggestion-item .sub {
            color: #64748b;
        }
    </style>

    <div class="flex-grow flex flex-col relative overflow-hidden">
        
        <!-- Background Elements -->
        <div class="blob top-[-100px] left-[-100px]"></div>
        <div class="blob-2"></div>

        <!-- Glass Navbar -->
        <nav class="glass mx-6 mt-6 p-4 rounded-sm border border-white/10 relative z-20 backdrop-blur-xl animate-reveal no-print">
            <div class="max-w-7xl mx-auto flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <a href="{{ route('dashboard.master') }}" class="w-10 h-10 bg-orange-500 rounded-sm flex items-center justify-center text-white font-black italic shadow-lg shadow-orange-500/50 html-light:shadow-none hover:scale-110 transition-transform">
                        P
                    </a>
                    <div>
                        <h1 class="text-xl font-black tracking-tighter text-white italic leading-none">PAINEL DE CHAMADAS</h1>
                        <a href="{{ route('dashboard.master') }}" class="text-xs font-bold text-white/70 hover:text-white uppercase tracking-widest mt-1 block">🛡️ Voltar ao Dashboard</a>
                    </div>
                </div>

                <div class="hidden md:flex items-center gap-2">
                    <button onclick="window.print()" class="px-5 py-2 bg-blue-600 hover:bg-blue-500 rounded-sm text-white text-xs font-bold transition-all hover:scale-105 active:scale-95 shadow-lg shadow-blue-600/20 flex items-center gap-2">
                        📄 Gerar PDF
                    </button>
                </div>
            </div>
        </nav>

        <main class="max-w-7xl mx-auto w-full p-6 mt-8 relative z-10 flex-grow">
    
            <div class="mb-12 animate-reveal [animation-delay:200ms] no-print">
                <h2 class="text-4xl font-black tracking-tighter mb-2 pal-always-white">Relatório de Presenças</h2>
                <p class="text-white/70 font-medium">Acompanhamento centralizado de registros em tempo real.</p>
            </div>

            <!-- Filtros -->
            <div class="glass p-4 md:p-6 rounded-sm border border-white/10 mb-8 animate-reveal [animation-delay:300ms] no-print relative z-30">
                <form action="{{ route('master.presenca') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 items-center">
                    <div class="relative pal-filter-card pal-card-delay-1">
                        <label class="block text-xs font-black text-white/80 uppercase tracking-[0.2em] mb-2">Professor</label>
                        <input type="text" name="professor" id="input-professor" value="{{ request('professor') }}" autocomplete="off" placeholder="Nome..." 
                            class="w-full bg-white/5 border border-white/10 rounded-sm p-3 text-white text-sm focus:outline-none focus:ring-2 focus:ring-orange-500/50 transition-all">
                        <div id="suggestions-professor" class="suggestion-box"></div>
                    </div>
                    <div class="relative pal-filter-card pal-card-delay-2">
                        <label class="block text-xs font-black text-white/80 uppercase tracking-[0.2em] mb-2">Matéria</label>
                        <input type="text" name="materia" id="input-materia" value="{{ request('materia') }}" autocomplete="off" placeholder="Nome..." 
                            class="w-full bg-white/5 border border-white/10 rounded-sm p-3 text-white text-sm focus:outline-none focus:ring-2 focus:ring-orange-500/50 transition-all">
                        <div id="suggestions-materia" class="suggestion-box"></div>
                    </div>
                    <div class="relative pal-filter-card pal-card-delay-3">
                        <label class="block text-xs font-black text-white/80 uppercase tracking-[0.2em] mb-2">Aluno</label>
                        <input type="text" name="aluno" id="input-aluno" value="{{ request('aluno') }}" autocomplete="off" placeholder="Nome..." 
                            class="w-full bg-white/5 border border-white/10 rounded-sm p-3 text-white text-sm focus:outline-none focus:ring-2 focus:ring-orange-500/50 transition-all">
                        <div id="suggestions-aluno" class="suggestion-box"></div>
                    </div>
                    <div class="flex gap-2 no-print">
                        <button type="submit" class="flex-grow bg-white/10 html-light:bg-white pal-text font-black py-3 rounded-sm transition-all hover:scale-105 active:scale-95 shadow-xl border border-white/10 html-light:border-transparent">
                            Filtrar
                        </button>
                        <a href="{{ route('master.presenca') }}" class="px-4 bg-white/5 hover:bg-white/10 border border-white/10 rounded-sm flex items-center justify-center text-white transition-all">
                            ✖
                        </a>
                    </div>
                </form>
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
                        <tbody class="divide-y divide-white/5">
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
                                        ->where('aluno_ra', $p->aluno_ra)
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
                                        <span class="block text-xs text-white/80 font-mono">{{ $p->aluno_ra }}</span>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="block font-bold text-white tracking-tight">{{ $p->materia->nome ?? 'N/A' }}</span>
                                        <span class="block text-xs text-white/80 uppercase tracking-widest font-black">{{ $p->materia->sala ?? '' }}</span>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="block text-white/80 font-medium">{{ $p->professor->nome ?? 'N/A' }}</span>
                                        <span class="block text-xs text-white/80 font-mono">{{ $p->professor_cpf }}</span>
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
                                <tr>
                                    <td colspan="6" class="py-20 text-center text-white/70 italic font-medium">Nenhum registro encontrado.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="px-8 py-6 border-t border-white/5 bg-white/5 no-print">
                    {{ $presencas->withQueryString()->links() }}
                </div>
            </div>
        </main>
    </div>
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const setupAutocomplete = (inputId, suggestionId, url, renderItem) => {
            const input = document.getElementById(inputId);
            const box = document.getElementById(suggestionId);
            let timeout = null;

            const triggerSearch = async () => {
                const query = input.value;
                // Removido o limite de query.length < 2 para mostrar tudo ao clicar
                
                try {
                    const response = await fetch(`${url}?q=${query}`);
                    const data = await response.json();
                    
                    box.innerHTML = '';
                    if (data.length > 0) {
                        data.forEach(item => {
                            const div = document.createElement('div');
                            div.className = 'suggestion-item';
                            div.innerHTML = renderItem(item);
                            div.onclick = () => {
                                input.value = item.nome;
                                box.style.display = 'none';
                                // Opcional: submeter o form ao selecionar
                                // input.closest('form').submit();
                            };
                            box.appendChild(div);
                        });
                        box.style.display = 'block';
                    } else {
                        box.style.display = 'none';
                    }
                } catch (e) {
                    console.error('Search error:', e);
                }
            };

            input.addEventListener('input', () => {
                clearTimeout(timeout);
                timeout = setTimeout(triggerSearch, 300);
            });

            // Mostrar ao clicar ou focar
            input.addEventListener('focus', triggerSearch);
            input.addEventListener('click', triggerSearch);

            document.addEventListener('click', (e) => {
                if (!input.contains(e.target) && !box.contains(e.target)) {
                    box.style.display = 'none';
                }
            });
        };

        setupAutocomplete('input-professor', 'suggestions-professor', '{{ route("master.search.professores") }}', (prof) => `
            <span class="block font-bold">👨‍🏫 ${prof.nome}</span>
            <span class="sub">${prof.cpf}</span>
        `);

        setupAutocomplete('input-materia', 'suggestions-materia', '{{ route("master.search.materias") }}', (mat) => `
            <span class="block font-bold">📚 ${mat.nome}</span>
            <span class="sub">SALA: ${mat.sala || 'N/A'}</span>
        `);

        setupAutocomplete('input-aluno', 'suggestions-aluno', '{{ route("master.search.alunos") }}', (aluno) => `
            <span class="block font-bold">🧑‍🎓 ${aluno.nome}</span>
            <span class="sub">RA: ${aluno.ra}</span>
        `);
    });
</script>
@endpush
@endsection
