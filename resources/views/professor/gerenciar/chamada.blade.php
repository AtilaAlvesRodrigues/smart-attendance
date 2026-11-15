@extends('layouts.theme')

@php $dataAula = \Carbon\Carbon::parse($aula->data_aula)->format('d/m/Y'); @endphp

@section('title', 'Chamada de ' . $dataAula . ' - ' . $materia->nome . ' - Smart Attendance')
@section('body-class', 'gradient-bg relative min-h-screen flex flex-col')

@section('nav-left')
    <a href="{{ route('professor.chamadas', $materia->id) }}" class="pal-nav-btn pal-nav-btn-ghost">⬅ Todas as chamadas</a>
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

    <main class="max-w-5xl mx-auto w-full p-6 mt-8 relative z-10 flex-grow">
        <div class="mb-8 animate-reveal">
            <p class="pal-eyebrow" style="margin-bottom:0.55rem">{{ $materia->nome }}</p>
            <h1 class="pal-title">Chamada de {{ $dataAula }}</h1>
            <p class="pal-subtitle">{{ $presencas->count() }} de {{ $alunos->count() }} presentes. Para corrigir, mude a chave, escreva o motivo e salve. Toda correção fica registrada.</p>
        </div>

        @if(session('status'))
            <div class="rc-alert rc-alert-ok" role="status">{{ session('status') }}</div>
        @endif
        @if($errors->any())
            <div class="rc-alert rc-alert-erro" role="alert">{{ $errors->first() }}</div>
        @endif

        <section class="rc-card animate-reveal pal-card-delay-1" aria-label="Alunos da chamada">
            <div class="overflow-x-auto">
                <table class="rc-table">
                    <thead>
                        <tr>
                            <th scope="col">Aluno</th>
                            <th scope="col">Situação</th>
                            <th scope="col">Corrigir</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($alunos as $aluno)
                            @php $p = $presencas->get($aluno->id); @endphp
                            <tr>
                                <td>
                                    <span class="rc-strong">{{ $aluno->nome }}</span><br>
                                    <span class="rc-meta">RA {{ $aluno->ra }}</span>
                                </td>
                                <td>
                                    @if(! $p)
                                        <span class="rc-chip rc-chip-erro">Falta</span>
                                    @elseif($p->origem === \App\Models\Presenca::ORIGEM_JUSTIFICATIVA)
                                        <span class="rc-chip rc-chip-neutro">Falta abonada</span>
                                    @elseif($p->origem === \App\Models\Presenca::ORIGEM_CORRECAO)
                                        <span class="rc-chip rc-chip-alerta">Presente (corrigido)</span>
                                    @else
                                        <span class="rc-chip rc-chip-ok">Presente</span>
                                    @endif
                                </td>
                                <td style="min-width:320px">
                                    <form method="POST" action="{{ route('professor.chamadas.presenca', [$materia->id, $aula->codigo_aula]) }}" class="flex flex-wrap items-center gap-3">
                                        @csrf
                                        <input type="hidden" name="aluno_id" value="{{ $aluno->id }}">
                                        <input type="hidden" name="presente" value="0">
                                        <label class="rc-switch">
                                            <input type="checkbox" name="presente" value="1" @checked($p)>
                                            <span class="rc-switch-track" aria-hidden="true"></span>
                                            <span class="rc-sr-only">Presente: {{ $aluno->nome }}</span>
                                        </label>
                                        <label class="rc-sr-only" for="motivo-{{ $aluno->id }}">Motivo da correção para {{ $aluno->nome }}</label>
                                        <input id="motivo-{{ $aluno->id }}" name="motivo" class="rc-input" style="flex:1; min-width:150px; padding:0.45rem 0.7rem" placeholder="Motivo" maxlength="500" required>
                                        <button type="submit" class="rc-btn rc-btn-confirmar rc-btn-sm">Salvar</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        @if($ajustes->isNotEmpty())
            <section class="rc-card mt-8 animate-reveal pal-card-delay-2" aria-labelledby="titulo-historico">
                <div class="rc-card-header"><h2 id="titulo-historico" class="rc-card-title">Histórico de correções</h2></div>
                <ul role="list" style="padding:0.5rem 1.5rem 1.25rem">
                    @foreach($ajustes as $ajuste)
                        <li class="rc-hint" style="padding:0.6rem 0; border-bottom:1px solid color-mix(in srgb, var(--pal-white) 6%, transparent)">
                            <strong style="color:var(--pal-white)">{{ $ajuste->aluno?->nome ?? 'Aluno removido' }}</strong>:
                            {{ ['marcou' => 'presença marcada', 'desmarcou' => 'presença removida', 'abonou' => 'falta abonada'][$ajuste->acao] ?? $ajuste->acao }}
                            em {{ $ajuste->created_at->format('d/m/Y H:i') }}. Motivo: “{{ $ajuste->motivo }}”
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif
    </main>
</div>
@endsection
