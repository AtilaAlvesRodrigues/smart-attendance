@extends('layouts.theme')

@section('title', 'Chamadas - ' . $materia->nome . ' - Smart Attendance')
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

@section('content')
<div class="flex-grow flex flex-col relative overflow-hidden">
    <div class="blob top-[-100px] left-[-100px]"></div>
    <div class="blob-2"></div>

    <main class="max-w-7xl mx-auto w-full p-6 mt-8 relative z-10 flex-grow">
        <div class="mb-10 animate-reveal">
            <p class="pal-eyebrow" style="margin-bottom:0.55rem">{{ $materia->nome }}</p>
            <h1 class="pal-title">Chamadas realizadas</h1>
            <p class="pal-subtitle">Escolha uma aula para conferir quem estava presente e corrigir a chamada, se precisar.</p>
        </div>

        <section class="rc-card animate-reveal pal-card-delay-1" aria-labelledby="titulo-aulas">
            <div class="rc-card-header">
                <h2 id="titulo-aulas" class="rc-card-title">{{ $aulas->count() }} {{ $aulas->count() === 1 ? 'aula' : 'aulas' }}</h2>
                <span class="rc-hint">{{ $totalAlunos }} {{ $totalAlunos === 1 ? 'aluno matriculado' : 'alunos matriculados' }}</span>
            </div>

            @if($aulas->isEmpty())
                <p class="rc-hint" style="padding:2rem 1.5rem">Nenhuma chamada foi feita nesta matéria ainda. As aulas aparecem aqui depois do primeiro QR Code escaneado.</p>
            @else
                <ul class="rc-aulas" role="list">
                    @foreach($aulas as $aula)
                        @php $pct = $totalAlunos > 0 ? round($aula->presentes / $totalAlunos * 100) : 0; @endphp
                        <li>
                            <a class="rc-aula" href="{{ route('professor.chamadas.show', [$materia->id, $aula->codigo_aula]) }}">
                                <span class="rc-aula-data">{{ \Carbon\Carbon::parse($aula->data_aula)->format('d/m/Y') }}</span>
                                <span class="rc-aula-meta">
                                    {{ ['M' => 'Matutino', 'V' => 'Vespertino', 'N' => 'Noturno'][$aula->horario] ?? 'Horário não informado' }}
                                    · {{ $aula->presentes }} de {{ $totalAlunos }} presentes
                                </span>
                                <span class="rc-bar" aria-hidden="true">
                                    <span style="width:{{ $pct }}%; background:{{ $pct >= 75 ? '#22c55e' : ($pct >= 50 ? '#eab308' : '#ef4444') }}"></span>
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </main>
</div>
@endsection
