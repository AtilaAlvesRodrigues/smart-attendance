@extends('layouts.main')

@section('title', $materia->nome . ' - Gerenciar - Smart Attendance')

@section('body-class', 'gradient-bg relative min-h-screen flex flex-col')

@push('styles')
<style>
    /* Input de nota estilizado para Glassmorphism */
    .nota-input {
        width: 70px;
        text-align: center;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 0.75rem;
        padding: 0.5rem 0.25rem;
        font-size: 0.875rem;
        font-weight: 800;
        color: white;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    body.theme-light .nota-input {
        background: rgba(0, 0, 0, 0.03);
        border: 1px solid rgba(0, 0, 0, 0.1);
        color: #1e293b;
    }
    .nota-input:focus {
        outline: none;
        border-color: #a855f7;
        background: rgba(168, 85, 247, 0.1);
        box-shadow: 0 0 20px rgba(168, 85, 247, 0.2);
    }
    .nota-input.saving {
        border-color: #f59e0b;
        background: rgba(245, 158, 11, 0.1);
    }
    .nota-input.saved {
        border-color: #10b981;
        background: rgba(16, 185, 129, 0.1);
    }
    .nota-input.error {
        border-color: #ef4444;
        background: rgba(239, 68, 68, 0.1);
    }

    /* Ocultar setas do input number */
    .nota-input::-webkit-inner-spin-button,
    .nota-input::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
</style>
@endpush

@section('content')
    <div class="flex-grow flex flex-col relative overflow-hidden">
        
        <!-- Background Elements -->
        <div class="blob top-[-100px] left-[-100px]"></div>
        <div class="blob-2"></div>

        <!-- Glass Navbar -->
        <nav class="glass mx-6 mt-6 p-4 rounded-2xl border border-white/10 relative z-20 backdrop-blur-xl animate-reveal">
            <div class="max-w-7xl mx-auto flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <a href="{{ route('professor.gerenciar.index') }}" class="w-10 h-10 bg-indigo-500 rounded-xl flex items-center justify-center text-white font-black italic shadow-lg shadow-indigo-500/50 hover:scale-110 transition-transform">
                        M
                    </a>
                    <div>
                        <h1 class="text-xl font-black tracking-tighter text-white italic leading-none truncate max-w-[200px] md:max-w-none">{{ $materia->nome }}</h1>
                        <a href="{{ route('professor.gerenciar.index') }}" class="text-[10px] font-bold text-white/40 hover:text-white uppercase tracking-widest mt-1 block">🛡️ Voltar às Matérias</a>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('dashboard.professor') }}" 
                       class="px-5 py-2 bg-white/5 hover:bg-white/10 border border-white/10 rounded-xl text-white text-xs font-bold transition-all hover:scale-105 active:scale-95">
                        🏠 Dashboard
                    </a>
                </div>
            </div>
        </nav>

        <main class="max-w-7xl mx-auto w-full p-6 mt-8 relative z-10 flex-grow">
            
            <div class="mb-12 animate-reveal [animation-delay:200ms]">
                <h2 class="text-4xl font-black text-white tracking-tighter mb-2">Gerenciamento de Turma</h2>
                <p class="text-white/40 font-medium italic">Sala: {{ $materia->sala }} · {{ $alunos->count() }} alunos matriculados</p>
            </div>

            {{-- Cards de resumo --}}
            <div class="grid grid-cols-2 md:grid-cols-5 gap-6 mb-12 animate-reveal [animation-delay:300ms]">
                <div class="glass p-6 rounded-3xl border border-white/10 text-center">
                    <p class="text-3xl font-black text-purple-400 tracking-tighter">{{ $alunos->count() }}</p>
                    <p class="text-[9px] text-white/20 uppercase font-black tracking-widest mt-2">Alunos</p>
                </div>
                <div class="glass p-6 rounded-3xl border border-white/10 text-center">
                    <p class="text-3xl font-black text-blue-400 tracking-tighter">{{ $totalAulas }}</p>
                    <p class="text-[9px] text-white/20 uppercase font-black tracking-widest mt-2">Aulas Semestre</p>
                </div>
                <div class="glass p-6 rounded-3xl border border-white/10 text-center">
                    <p class="text-3xl font-black text-indigo-400 tracking-tighter">{{ $aulasRealizadas }}</p>
                    <p class="text-[9px] text-white/20 uppercase font-black tracking-widest mt-2">Realizadas</p>
                </div>
                <div class="glass p-6 rounded-3xl border border-white/10 text-center">
                    @php
                        $mediaGeral = $alunos->map(function($a) {
                            $notas = collect([$a->pivot->prova1, $a->pivot->trabalho1, $a->pivot->trabalho2, $a->pivot->prova2])->filter(fn($n) => $n !== null);
                            return $notas->isNotEmpty() ? $notas->avg() : null;
                        })->filter(fn($m) => $m !== null);
                    @endphp
                    <p class="text-3xl font-black text-green-400 tracking-tighter">{{ $mediaGeral->isNotEmpty() ? number_format($mediaGeral->avg(), 1) : '--' }}</p>
                    <p class="text-[9px] text-white/20 uppercase font-black tracking-widest mt-2">Média Geral</p>
                </div>
                <div class="glass p-6 rounded-3xl border border-white/10 text-center">
                    @php
                        $aprovados = $mediaGeral->filter(fn($m) => $m >= 5)->count();
                    @endphp
                    <p class="text-3xl font-black text-emerald-400 tracking-tighter">{{ $mediaGeral->isNotEmpty() ? round(($aprovados / $mediaGeral->count()) * 100) . '%' : '--' }}</p>
                    <p class="text-[9px] text-white/20 uppercase font-black tracking-widest mt-2">Taxa Aprovação</p>
                </div>
            </div>

            {{-- Tabela Container --}}
            <div class="glass rounded-3xl border border-white/10 overflow-hidden animate-reveal [animation-delay:400ms]">
                <div class="px-8 py-6 border-b border-white/10 flex justify-between items-center bg-white/5">
                    <h3 class="text-xl font-black text-white italic tracking-tighter">📋 Alunos Matriculados</h3>
                    <div class="flex gap-2">
                        <span class="px-3 py-1 bg-white/10 rounded-lg text-[10px] font-black text-white/60 uppercase tracking-widest">
                            Previstas: {{ $totalAulas }}
                        </span>
                        <span class="px-3 py-1 bg-indigo-500/20 rounded-lg text-[10px] font-black text-indigo-400 uppercase tracking-widest border border-indigo-500/20">
                            Realizadas: {{ $aulasRealizadas }}
                        </span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-[10px] font-black text-white/30 uppercase tracking-[0.2em] border-b border-white/10">
                                <th class="py-4 px-6">Aluno</th>
                                <th class="py-4 px-6 text-center">Presenças</th>
                                <th class="py-4 px-6 text-center">Faltas</th>
                                <th class="py-4 px-6 text-center">1ª Prova</th>
                                <th class="py-4 px-6 text-center">1º Trab.</th>
                                <th class="py-4 px-6 text-center">2º Trab.</th>
                                <th class="py-4 px-6 text-center">2ª Prova</th>
                                <th class="py-4 px-6 text-center">Média</th>
                                <th class="py-4 px-6 text-right">Situação</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            @foreach($alunos as $aluno)
                                @php
                                    $presencas = $presencasPorAluno[$aluno->ra] ?? 0;
                                    $faltas = max(0, $totalAulas - $presencas);
                                    $percentPresenca = $totalAulas > 0 ? round(($presencas / $totalAulas) * 100) : 100;
                                    
                                    $notasArr = collect([$aluno->pivot->prova1, $aluno->pivot->trabalho1, $aluno->pivot->trabalho2, $aluno->pivot->prova2])->filter(fn($n) => $n !== null);
                                    $media = $notasArr->isNotEmpty() ? round($notasArr->avg(), 2) : null;
                                    
                                    $reprovadoFalta = $totalAulas > 0 && $percentPresenca < 75;
                                    $aprovado = $media !== null && $media >= 5 && !$reprovadoFalta;
                                @endphp
                                <tr class="hover:bg-white/5 transition-colors group">
                                    <td class="py-4 px-6">
                                        <span class="block font-bold text-white tracking-tight">{{ $aluno->nome }}</span>
                                        <span class="block text-[10px] text-white/30 font-mono">{{ $aluno->ra }}</span>
                                    </td>
                                    
                                    <td class="py-4 px-6 text-center">
                                        <div class="flex flex-col items-center">
                                            <span class="font-black {{ $percentPresenca >= 75 ? 'text-green-400' : 'text-red-400' }} text-base tracking-tighter">
                                                {{ $presencas }}
                                            </span>
                                            <span class="text-[9px] text-white/20 uppercase font-bold">{{ $percentPresenca }}%</span>
                                        </div>
                                    </td>
                                    
                                    <td class="py-4 px-6 text-center">
                                        <span class="px-3 py-1 {{ $faltas > 0 ? 'bg-red-500/20 text-red-400 border-red-500/20' : 'bg-green-500/20 text-green-400 border-green-500/20' }} rounded-lg text-xs font-black border">
                                            {{ $faltas }}
                                        </span>
                                    </td>

                                    <td class="py-4 px-6 text-center">
                                        <input type="number" class="nota-input" data-aluno="{{ $aluno->ra }}" data-campo="prova1"
                                               value="{{ $aluno->pivot->prova1 !== null ? number_format($aluno->pivot->prova1, 1) : '' }}" min="0" max="10" step="0.1">
                                    </td>

                                    <td class="py-4 px-6 text-center">
                                        <input type="number" class="nota-input" data-aluno="{{ $aluno->ra }}" data-campo="trabalho1"
                                               value="{{ $aluno->pivot->trabalho1 !== null ? number_format($aluno->pivot->trabalho1, 1) : '' }}" min="0" max="10" step="0.1">
                                    </td>

                                    <td class="py-4 px-6 text-center">
                                        <input type="number" class="nota-input" data-aluno="{{ $aluno->ra }}" data-campo="trabalho2"
                                               value="{{ $aluno->pivot->trabalho2 !== null ? number_format($aluno->pivot->trabalho2, 1) : '' }}" min="0" max="10" step="0.1">
                                    </td>

                                    <td class="py-4 px-6 text-center">
                                        <input type="number" class="nota-input" data-aluno="{{ $aluno->ra }}" data-campo="prova2"
                                               value="{{ $aluno->pivot->prova2 !== null ? number_format($aluno->pivot->prova2, 1) : '' }}" min="0" max="10" step="0.1">
                                    </td>

                                    <td class="py-4 px-6 text-center" id="media-{{ $aluno->ra }}">
                                        @if($media !== null)
                                            <span class="font-black text-lg tracking-tighter {{ $media >= 5 ? 'text-green-400' : 'text-red-400' }}">
                                                {{ number_format($media, 1) }}
                                            </span>
                                        @else
                                            <span class="text-white/20 text-xs">--</span>
                                        @endif
                                    </td>

                                    <td class="py-4 px-6 text-right" id="situacao-{{ $aluno->ra }}">
                                        @if($reprovadoFalta)
                                            <span class="px-3 py-1 bg-red-500 text-white rounded-lg text-[9px] font-black uppercase tracking-widest shadow-lg shadow-red-500/30">Rep. Falta</span>
                                        @elseif($media !== null && $aprovado)
                                            <span class="px-3 py-1 bg-green-500 text-white rounded-lg text-[9px] font-black uppercase tracking-widest shadow-lg shadow-green-500/30">Aprovado</span>
                                        @elseif($media !== null && !$aprovado)
                                            <span class="px-3 py-1 bg-orange-500 text-white rounded-lg text-[9px] font-black uppercase tracking-widest shadow-lg shadow-orange-500/30">Reprovado</span>
                                        @else
                                            <span class="px-3 py-1 bg-white/5 text-white/40 rounded-lg text-[9px] font-black uppercase tracking-widest border border-white/5">Pendente</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Legenda --}}
            <div class="mt-8 flex flex-wrap gap-8 animate-reveal [animation-delay:500ms]">
                <div class="flex items-center gap-3">
                    <span class="w-3 h-3 bg-green-400 rounded-full shadow-lg shadow-green-400/50"></span>
                    <span class="text-[10px] font-bold text-white/40 uppercase tracking-widest">Aprovado (Média ≥ 5.0)</span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="w-3 h-3 bg-red-400 rounded-full shadow-lg shadow-red-400/50"></span>
                    <span class="text-[10px] font-bold text-white/40 uppercase tracking-widest">Reprovado por Nota ou Falta</span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="w-3 h-3 bg-white/20 rounded-full border border-white/10"></span>
                    <span class="text-[10px] font-bold text-white/40 uppercase tracking-widest">Pendente / Em andamento</span>
                </div>
            </div>
        </main>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const saveUrl = "{{ route('professor.gerenciar.salvar_notas', $materia->id) }}";

        document.querySelectorAll('.nota-input').forEach(input => {
            let valorOriginal = input.value;

            input.addEventListener('focus', function() {
                valorOriginal = this.value;
            });

            input.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.blur();
                }
            });

            input.addEventListener('blur', function() {
                const novoValor = this.value;
                if (novoValor === valorOriginal) return;

                const num = parseFloat(novoValor);
                if (novoValor !== '' && (isNaN(num) || num < 0 || num > 10)) {
                    this.classList.add('error');
                    setTimeout(() => {
                        this.classList.remove('error');
                        this.value = valorOriginal;
                    }, 1000);
                    return;
                }

                const alunoRa = this.dataset.aluno;
                const campo = this.dataset.campo;
                const valor = novoValor === '' ? null : num;

                this.classList.add('saving');

                fetch(saveUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body: JSON.stringify({ aluno_ra: alunoRa, campo: campo, valor: valor })
                })
                .then(response => {
                    if (!response.ok) throw new Error('Erro');
                    return response.json();
                })
                .then(data => {
                    this.classList.remove('saving');
                    this.classList.add('saved');
                    valorOriginal = novoValor;
                    atualizarMedia(alunoRa);
                    setTimeout(() => this.classList.remove('saved'), 1500);
                })
                .catch(() => {
                    this.classList.remove('saving');
                    this.classList.add('error');
                    setTimeout(() => {
                        this.classList.remove('error');
                        this.value = valorOriginal;
                    }, 1500);
                });
            });
        });

        function atualizarMedia(alunoRa) {
            const inputs = document.querySelectorAll(`.nota-input[data-aluno="${alunoRa}"]`);
            const notas = [];
            inputs.forEach(input => {
                const val = parseFloat(input.value);
                if (!isNaN(val)) notas.push(val);
            });

            const mediaCell = document.getElementById(`media-${alunoRa}`);
            const situacaoCell = document.getElementById(`situacao-${alunoRa}`);

            if (notas.length > 0) {
                const media = notas.reduce((sum, n) => sum + n, 0) / notas.length;
                const mF = media.toFixed(1);
                const cor = media >= 5 ? 'text-green-400' : 'text-red-400';
                mediaCell.innerHTML = `<span class="font-black text-lg tracking-tighter ${cor}">${mF}</span>`;

                if (media >= 5) {
                    situacaoCell.innerHTML = `<span class="px-3 py-1 bg-green-500 text-white rounded-lg text-[9px] font-black uppercase tracking-widest shadow-lg shadow-green-500/30">Aprovado</span>`;
                } else {
                    situacaoCell.innerHTML = `<span class="px-3 py-1 bg-orange-500 text-white rounded-lg text-[9px] font-black uppercase tracking-widest shadow-lg shadow-orange-500/30">Reprovado</span>`;
                }
            } else {
                mediaCell.innerHTML = `<span class="text-white/20 text-xs">--</span>`;
                situacaoCell.innerHTML = `<span class="px-3 py-1 bg-white/5 text-white/40 rounded-lg text-[9px] font-black uppercase tracking-widest border border-white/5">Pendente</span>`;
            }
        }
    });
</script>
@endpush
