@extends('layouts.theme')

@section('title', 'Gráficos - ' . $materia->nome . ' - Smart Attendance')
@section('body-class', 'gradient-bg relative min-h-screen flex flex-col')

@section('nav-left')
    <a href="{{ route('professor.gerenciar.materia', $materia->id) }}" class="pal-nav-btn pal-nav-btn-ghost">⬅ Voltar à turma</a>
@endsection

@section('nav-user')
    <div class="pal-nav-user">
        <span class="pal-nav-user-role">Professor</span>
        <span class="pal-nav-user-name">{{ $professor->nome }}</span>
    </div>
@endsection

@php
    $aulas = count($dados['frequencia_por_aula']);
    $rotulos = ['regular' => 'Em dia', 'aprovado' => 'Aprovado', 'atencao' => 'Atenção', 'reprovado' => 'Reprovado por nota', 'reprovado_falta' => 'Reprovado por falta'];
@endphp

@section('content')
<div class="flex-grow flex flex-col relative overflow-hidden">
    <div class="blob top-[-100px] left-[-100px]"></div>
    <div class="blob-2"></div>

    <main class="max-w-7xl mx-auto w-full p-6 mt-8 relative z-10 flex-grow">
        <div class="mb-8 animate-reveal">
            <p class="pal-eyebrow" style="margin-bottom:0.55rem">{{ $materia->nome }}</p>
            <h1 class="pal-title">Presença da turma</h1>
            <p class="pal-subtitle">Como a frequência evoluiu aula a aula e quem precisa de atenção agora.</p>
        </div>

        <div class="rc-kpis mb-6 animate-reveal pal-card-delay-1">
            <div class="rc-card rc-kpi">
                <p class="rc-kpi-valor" style="color:{{ $dados['frequencia_media'] >= 75 ? '#22c55e' : ($dados['frequencia_media'] >= 50 ? '#eab308' : '#ef4444') }}">{{ $dados['frequencia_media'] }}%</p>
                <p class="rc-kpi-rotulo">Frequência média</p>
            </div>
            <div class="rc-card rc-kpi"><p class="rc-kpi-valor">{{ $aulas }}<span class="rc-hint" style="font-size:1rem"> / {{ $materia->total_aulas ?? '—' }}</span></p><p class="rc-kpi-rotulo">Aulas realizadas</p></div>
            <div class="rc-card rc-kpi"><p class="rc-kpi-valor">{{ $totalAlunos }}</p><p class="rc-kpi-rotulo">Alunos</p></div>
            <div class="rc-card rc-kpi"><p class="rc-kpi-valor" style="color:{{ count($dados['em_risco']) ? '#eab308' : 'inherit' }}">{{ count($dados['em_risco']) }}</p><p class="rc-kpi-rotulo">Em risco</p></div>
        </div>

        @if($aulas === 0)
            <div class="rc-card rc-alert-info" style="padding:2rem; text-align:center">
                <p class="rc-card-title" style="margin-bottom:0.5rem">Ainda não há aulas para mostrar</p>
                <p class="rc-hint" style="margin:0">Os gráficos aparecem depois da primeira chamada por QR Code.</p>
            </div>
        @else
            <div class="grid gap-6 lg:grid-cols-3 animate-reveal pal-card-delay-2">
                <section class="rc-card lg:col-span-2" aria-labelledby="t-freq">
                    <div class="rc-card-header"><h2 id="t-freq" class="rc-card-title">Presentes por aula</h2><span class="rc-hint">% da turma</span></div>
                    <div class="rc-chart-box"><canvas id="grafico-frequencia" role="img" aria-label="Gráfico de linha da porcentagem de alunos presentes em cada aula"></canvas></div>
                    <details class="rc-hint" style="padding:0 1.5rem 1.25rem">
                        <summary style="cursor:pointer">Ver os números</summary>
                        <table class="rc-table" style="margin-top:0.5rem">
                            <thead><tr><th scope="col">Aula</th><th scope="col">Presentes</th></tr></thead>
                            <tbody>
                                @foreach($dados['frequencia_por_aula'] as $p)
                                    <tr><td>{{ \Carbon\Carbon::parse($p['data'])->format('d/m/Y') }}</td><td>{{ $p['percentual'] }}%</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    </details>
                </section>

                <section class="rc-card" aria-labelledby="t-sit">
                    <div class="rc-card-header"><h2 id="t-sit" class="rc-card-title">Situação dos alunos</h2></div>
                    <div class="rc-chart-box"><canvas id="grafico-situacoes" role="img" aria-label="Gráfico de rosca com a quantidade de alunos em cada situação"></canvas></div>
                    <ul class="rc-sr-only">
                        @foreach($dados['situacoes'] as $status => $qtd)
                            <li>{{ $rotulos[$status] }}: {{ $qtd }}</li>
                        @endforeach
                    </ul>
                </section>
            </div>
        @endif

        <section class="rc-card mt-6 animate-reveal pal-card-delay-3" aria-labelledby="t-risco">
            <div class="rc-card-header">
                <h2 id="t-risco" class="rc-card-title">Alunos em risco</h2>
                <a href="{{ route('professor.chamadas', $materia->id) }}" class="rc-btn rc-btn-neutro rc-btn-sm">Corrigir chamadas</a>
            </div>
            @if(empty($dados['em_risco']))
                <p class="rc-hint" style="padding:1.5rem">Ninguém em risco por enquanto.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="rc-table">
                        <thead><tr><th scope="col">Aluno</th><th scope="col">Faltas</th><th scope="col">Frequência</th><th scope="col">Situação</th></tr></thead>
                        <tbody>
                            @foreach($dados['em_risco'] as $a)
                                <tr>
                                    <td class="rc-strong">{{ $a['nome'] }}</td>
                                    <td>{{ $a['faltas'] }} de {{ $a['limite'] }}</td>
                                    <td style="min-width:140px">
                                        {{ $a['frequencia'] }}%
                                        <div class="rc-bar" aria-hidden="true"><span style="width:{{ $a['frequencia'] }}%; background:{{ $a['frequencia'] >= 75 ? '#22c55e' : ($a['frequencia'] >= 50 ? '#eab308' : '#ef4444') }}"></span></div>
                                    </td>
                                    <td><span class="rc-chip {{ $a['status'] === 'reprovado_falta' ? 'rc-chip-erro' : 'rc-chip-alerta' }}">{{ $a['rotulo'] }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </main>
</div>
@endsection

@push('scripts')
<script type="application/json" id="dados-graficos" @nonce>@json($dados)</script>
<script src="{{ asset('js/vendor/chart.umd.js') }}"></script>
<script src="{{ asset('js/graficos.js') }}?v={{ filemtime(public_path('js/graficos.js')) }}"></script>
@endpush
