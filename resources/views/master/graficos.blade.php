@extends('layouts.theme')

@section('title', 'Gráficos de presença - Smart Attendance')
@section('body-class', 'gradient-bg relative min-h-screen flex flex-col')

@section('nav-user')
    <div class="pal-nav-user">
        <span class="pal-nav-user-role">Master</span>
        <span class="pal-nav-user-name">{{ $master->nome ?? 'Administrador' }}</span>
    </div>
@endsection

@php
    $comAulas = collect($turmas)->where('aulas_realizadas', '>', 0);
    $mediaGeral = $comAulas->isNotEmpty() ? (int) round($comAulas->avg('frequencia_media')) : null;
    $emRisco = collect($turmas)->sum('em_risco');
    $abaixo = $comAulas->where('frequencia_media', '<', 75)->count();
@endphp

@section('content')
<div class="flex-grow flex flex-col relative overflow-hidden">
    <div class="blob top-[-100px] left-[-100px]"></div>
    <div class="blob-2"></div>

    <main class="max-w-7xl mx-auto w-full p-6 mt-8 relative z-10 flex-grow">
        <div class="mb-8 animate-reveal">
            <p class="pal-eyebrow" style="margin-bottom:0.55rem">Visão geral</p>
            <h1 class="pal-title">Presença por turma</h1>
            <p class="pal-subtitle">Frequência média de cada matéria, considerando só as aulas já realizadas.</p>
        </div>

        <div class="rc-kpis mb-6 animate-reveal pal-card-delay-1">
            <div class="rc-card rc-kpi"><p class="rc-kpi-valor">{{ $mediaGeral !== null ? $mediaGeral . '%' : '—' }}</p><p class="rc-kpi-rotulo">Média geral</p></div>
            <div class="rc-card rc-kpi"><p class="rc-kpi-valor">{{ $comAulas->count() }}</p><p class="rc-kpi-rotulo">Turmas com aulas</p></div>
            <div class="rc-card rc-kpi"><p class="rc-kpi-valor" style="color:{{ $abaixo ? '#eab308' : 'inherit' }}">{{ $abaixo }}</p><p class="rc-kpi-rotulo">Abaixo de 75%</p></div>
            <div class="rc-card rc-kpi"><p class="rc-kpi-valor" style="color:{{ $emRisco ? '#ef4444' : 'inherit' }}">{{ $emRisco }}</p><p class="rc-kpi-rotulo">Matrículas em risco</p></div>
        </div>

        <section class="rc-card animate-reveal pal-card-delay-2" aria-labelledby="t-turmas">
            <div class="rc-card-header">
                <h2 id="t-turmas" class="rc-card-title">Frequência média por turma</h2>
                <span class="rc-hint"><span style="color:#22c55e">●</span> 75%+ &nbsp; <span style="color:#eab308">●</span> 50 a 74% &nbsp; <span style="color:#ef4444">●</span> abaixo de 50%</span>
            </div>
            @if($comAulas->isEmpty())
                <p class="rc-hint" style="padding:1.5rem">Nenhuma turma teve chamada ainda.</p>
            @else
                <div class="rc-chart-box"><canvas id="grafico-turmas" role="img" aria-label="Gráfico de barras com a frequência média de cada turma"></canvas></div>
            @endif

            <div class="overflow-x-auto">
                <table class="rc-table">
                    <thead><tr><th scope="col">Turma</th><th scope="col">Alunos</th><th scope="col">Aulas</th><th scope="col">Frequência</th><th scope="col">Em risco</th></tr></thead>
                    <tbody>
                        @foreach($turmas as $t)
                            <tr>
                                <td class="rc-strong">{{ $t['materia'] }}</td>
                                <td>{{ $t['alunos'] }}</td>
                                <td>{{ $t['aulas_realizadas'] }}</td>
                                <td>{{ $t['aulas_realizadas'] ? $t['frequencia_media'] . '%' : '—' }}</td>
                                <td>@if($t['em_risco'])<span class="rc-chip rc-chip-alerta">{{ $t['em_risco'] }}</span>@else — @endif</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>
@endsection

@push('scripts')
<script type="application/json" id="dados-graficos" @nonce>@json(['turmas' => $turmas])</script>
<script src="{{ asset('js/vendor/chart.umd.js') }}"></script>
<script src="{{ asset('js/graficos.js') }}?v={{ filemtime(public_path('js/graficos.js')) }}"></script>
@endpush
