@extends('layouts.theme')

@section('title', $materia->nome . ' - Gerenciar - Smart Attendance')

@section('body-class', 'gradient-bg relative min-h-screen flex flex-col')
@section('nav-left')
    <a href="{{ route('professor.gerenciar.index') }}" class="pal-nav-btn pal-nav-btn-ghost">
        ⬅ Voltar às Turmas
    </a>
@endsection

@section('nav-user')
    <div class="pal-nav-user">
        <span class="pal-nav-user-role">Professor</span>
        <span class="pal-nav-user-name">{{ $professor->nome ?? 'Docente' }}</span>
    </div>
@endsection

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
    html.light-mode .nota-input {
        background: rgba(0, 0, 0, 0.03);
        border: 1px solid rgba(0, 0, 0, 0.1);
        color: #1e293b;
    }
    .nota-input:focus {
        outline: none;
        border-color: #efefef;
        background: rgba(255, 255, 255, 0.1);
        box-shadow: 0 0 20px rgba(255, 255, 255, 0.1);
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

        <main class="max-w-7xl mx-auto w-full p-6 mt-8 relative z-10 flex-grow">
            
            <div class="mb-12 animate-reveal [animation-delay:200ms]">
                <p class="pal-eyebrow" style="margin-bottom:0.55rem">{{ $materia->nome }}</p>
                <h2 class="pal-title">Gerenciamento de Turma</h2>
                <p class="pal-subtitle font-medium italic">Sala: {{ $materia->sala }} · {{ $alunos->count() }} {{ $alunos->count() === 1 ? 'aluno matriculado' : 'alunos matriculados' }}</p>
            </div>

            {{-- Cards de resumo --}}
            <div class="grid grid-cols-2 md:grid-cols-5 gap-6 mb-12 animate-reveal [animation-delay:300ms]">
                <div class="glass p-6 rounded-sm border border-white/10 text-center">
                    <p class="text-3xl font-black tracking-tighter pal-text">{{ $alunos->count() }}</p>
                    <p class="text-[9px] uppercase font-black tracking-widest mt-2 pal-text-muted">Alunos</p>
                </div>
                <div class="glass p-6 rounded-sm border border-white/10 text-center">
                    <p class="text-3xl font-black text-blue-400 tracking-tighter">{{ $totalAulas }}</p>
                    <p class="text-[9px] uppercase font-black tracking-widest mt-2 pal-text-muted">Aulas Semestre</p>
                </div>
                <div class="glass p-6 rounded-sm border border-white/10 text-center">
                    <p class="text-3xl font-black tracking-tighter pal-text" style="opacity: 0.8">{{ $aulasRealizadas }}</p>
                    <p class="text-[9px] uppercase font-black tracking-widest mt-2 pal-text-muted">Realizadas</p>
                </div>
                <div class="glass p-6 rounded-sm border border-white/10 text-center">
                    @php
                        $mediaGeral = $alunos->map(function($a) {
                            $notas = collect([$a->pivot->prova1, $a->pivot->trabalho1, $a->pivot->trabalho2, $a->pivot->prova2])->filter(fn($n) => $n !== null);
                            return $notas->isNotEmpty() ? $notas->avg() : null;
                        })->filter(fn($m) => $m !== null);
                    @endphp
                    <p class="text-3xl font-black text-green-400 tracking-tighter">{{ $mediaGeral->isNotEmpty() ? number_format($mediaGeral->avg(), 1) : '--' }}</p>
                    <p class="text-[9px] uppercase font-black tracking-widest mt-2 pal-text-muted">Média Geral</p>
                </div>
                <div class="glass p-6 rounded-sm border border-white/10 text-center">
                    @php
                        $situacoes = $alunos->map(fn ($a) => new \App\Support\SituacaoAcademica(
                            (int) $aulasRealizadas, (int) ($presencasPorAluno[$a->id] ?? 0), (int) $totalAulas,
                            [$a->pivot->prova1, $a->pivot->trabalho1, $a->pivot->trabalho2, $a->pivot->prova2],
                        ));
                        $concluidos = $situacoes->filter(fn ($st) => in_array($st->status, ['aprovado', 'reprovado', 'reprovado_falta']));
                        $aprovados = $concluidos->where('status', 'aprovado')->count();
                    @endphp
                    <p class="text-3xl font-black text-emerald-400 tracking-tighter">{{ $concluidos->isNotEmpty() ? round($aprovados / $concluidos->count() * 100) . '%' : '--' }}</p>
                    <p class="text-xs font-bold mt-2 pal-text-muted" title="Entre os alunos com todas as notas lançadas ou já reprovados por falta">Aprovação ({{ $concluidos->count() }} com situação final)</p>
                </div>
            </div>

            {{-- Tabela Container --}}
            <div class="glass rounded-sm border border-white/10 overflow-hidden animate-reveal [animation-delay:400ms]">
                <div class="px-8 py-6 border-b border-white/10 flex justify-between items-center bg-white/5">
                    <h3 class="text-xl font-black italic tracking-tighter pal-text">📋 Alunos Matriculados</h3>
                    <div class="flex gap-2">
                        <span class="px-3 py-1 bg-white/10 rounded-sm text-xs font-black uppercase tracking-widest pal-text-muted" style="opacity: 0.6">
                            Previstas: {{ $totalAulas }}
                        </span>
                        <span class="px-3 py-1 bg-white/10 rounded-sm text-xs font-black uppercase tracking-widest border border-white/20 pal-text">
                            Realizadas: {{ $aulasRealizadas }}
                        </span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="pal-subtitle font-black uppercase tracking-[0.2em] border-b border-white/10" style="font-size:0.65rem">
                                <th class="py-3 px-3">Aluno</th>
                                <th class="py-3 px-3 text-center">Presenças</th>
                                <th class="py-3 px-3 text-center">Faltas</th>
                                <th class="py-3 px-3 text-center">1ª Prova</th>
                                <th class="py-3 px-3 text-center">1º Trab.</th>
                                <th class="py-3 px-3 text-center">2º Trab.</th>
                                <th class="py-3 px-3 text-center">2ª Prova</th>
                                <th class="py-3 px-3 text-center">Média</th>
                                <th class="py-3 px-3 text-right">Situação</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            @foreach($alunos as $aluno)
                                @php
                                    $presencas = (int) ($presencasPorAluno[$aluno->id] ?? 0);
                                    $sit = new \App\Support\SituacaoAcademica(
                                        aulasRealizadas: $aulasRealizadas,
                                        presencas: $presencas,
                                        aulasPrevistas: (int) $totalAulas,
                                        notas: [$aluno->pivot->prova1, $aluno->pivot->trabalho1, $aluno->pivot->trabalho2, $aluno->pivot->prova2],
                                    );
                                    $faltas = $sit->faltas;
                                    $percentPresenca = $sit->frequencia;
                                    $media = $sit->media;
                                                                @endphp
                                <tr class="hover:bg-white/5 transition-colors group" data-faltas="{{ $sit->faltas }}" data-limite="{{ $sit->limiteFaltas }}" id="linha-{{ $aluno->ra }}">
                                    <td class="py-3 px-3">
                                        <span class="block font-bold tracking-tight pal-text">{{ $aluno->nome }}</span>
                                        <span class="block text-xs font-mono pal-text-muted">{{ $aluno->ra }}</span>
                                    </td>
                                    
                                    <td class="py-3 px-3 text-center">
                                        <div class="flex flex-col items-center">
                                            <span class="font-black {{ $sit->status === 'reprovado_falta' ? 'text-red-400' : 'text-green-400' }} text-base tracking-tighter">
                                                {{ $presencas }}
                                            </span>
                                            <span class="text-xs font-bold pal-text-muted">{{ $percentPresenca }}%</span>
                                        </div>
                                    </td>
                                    
                                    <td class="py-3 px-3 text-center">
                                        <span class="px-3 py-1 {{ $sit->status === 'reprovado_falta' ? 'bg-red-500/20 text-red-400 border-red-500/20' : ($sit->faltasRestantes <= 1 && $faltas > 0 ? 'bg-yellow-500/20 text-yellow-400 border-yellow-500/20' : 'bg-green-500/20 text-green-400 border-green-500/20') }} rounded-sm text-xs font-black border"
                                              title="{{ $faltas }} de {{ $sit->limiteFaltas }} faltas permitidas">
                                            {{ $faltas }}<span class="font-bold opacity-70">/{{ $sit->limiteFaltas }}</span>
                                        </span>
                                    </td>

                                    <td class="py-3 px-3 text-center">
                                        <input type="number" class="nota-input" data-aluno="{{ $aluno->ra }}" data-campo="prova1"
                                               value="{{ $aluno->pivot->prova1 !== null ? number_format($aluno->pivot->prova1, 1) : '' }}" min="0" max="10" step="0.1">
                                    </td>

                                    <td class="py-3 px-3 text-center">
                                        <input type="number" class="nota-input" data-aluno="{{ $aluno->ra }}" data-campo="trabalho1"
                                               value="{{ $aluno->pivot->trabalho1 !== null ? number_format($aluno->pivot->trabalho1, 1) : '' }}" min="0" max="10" step="0.1">
                                    </td>

                                    <td class="py-3 px-3 text-center">
                                        <input type="number" class="nota-input" data-aluno="{{ $aluno->ra }}" data-campo="trabalho2"
                                               value="{{ $aluno->pivot->trabalho2 !== null ? number_format($aluno->pivot->trabalho2, 1) : '' }}" min="0" max="10" step="0.1">
                                    </td>

                                    <td class="py-3 px-3 text-center">
                                        <input type="number" class="nota-input" data-aluno="{{ $aluno->ra }}" data-campo="prova2"
                                               value="{{ $aluno->pivot->prova2 !== null ? number_format($aluno->pivot->prova2, 1) : '' }}" min="0" max="10" step="0.1">
                                    </td>

                                    <td class="py-3 px-3 text-center" id="media-{{ $aluno->ra }}">
                                        @if($media !== null)
                                            <span class="font-black text-lg tracking-tighter {{ $media >= 5 ? 'text-green-400' : 'text-red-400' }}">
                                                {{ number_format($media, 1) }}
                                            </span>
                                        @else
                                            <span class="text-xs pal-text-muted">--</span>
                                        @endif
                                    </td>

                                    <td class="py-3 px-3 text-right" id="situacao-{{ $aluno->ra }}">
                                        @include('professor.gerenciar._situacao', ['status' => $sit->status])
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
                    <span class="text-xs font-bold uppercase tracking-widest pal-text-muted">Aprovado (Média ≥ 5.0)</span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="w-3 h-3 bg-red-400 rounded-full shadow-lg shadow-red-400/50"></span>
                    <span class="text-xs font-bold uppercase tracking-widest pal-text-muted">Reprovado por Nota ou Falta</span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="w-3 h-3 bg-white/20 rounded-full border border-white/10"></span>
                    <span class="text-xs font-bold uppercase tracking-widest pal-text-muted">Pendente / Em andamento</span>
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

        // Mesma regra de App\Support\SituacaoAcademica
        function calcularStatus(alunoRa, notas) {
            const linha = document.getElementById(`linha-${alunoRa}`);
            const faltas = Number(linha?.dataset.faltas || 0);
            const limite = Number(linha?.dataset.limite || 0);
            if (limite > 0 && faltas > limite) return 'reprovado_falta';
            if (notas.length === 4) {
                const media = notas.reduce((s, n) => s + n, 0) / 4;
                return media >= 5 ? 'aprovado' : 'reprovado';
            }
            return 'andamento';
        }

        function badgeSituacao(status) {
            const estilos = {
                aprovado:        ['bg-green-600 text-white', '✓ Aprovado'],
                reprovado:       ['bg-orange-600 text-white', 'Reprovado por nota'],
                reprovado_falta: ['bg-red-600 text-white', 'Reprovado por falta'],
                andamento:       ['bg-white/5 border border-white/10 pal-text-muted', 'Em andamento'],
            };
            const [classe, rotulo] = estilos[status] || estilos.andamento;
            return `<span class="px-3 py-1 rounded-sm text-xs font-bold whitespace-nowrap ${classe}">${rotulo}</span>`;
        }

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
            } else {
                mediaCell.innerHTML = `<span class="text-xs pal-text-muted">--</span>`;
            }
            situacaoCell.innerHTML = badgeSituacao(calcularStatus(alunoRa, notas));
        }
    });
</script>
@endpush
