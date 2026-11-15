@extends('layouts.theme')

@section('title', 'Justificativas de falta - Smart Attendance')
@section('body-class', 'gradient-bg relative min-h-screen flex flex-col')

@section('nav-left')
    <a href="{{ route('dashboard.professor') }}" class="pal-nav-btn pal-nav-btn-ghost">⬅ Painel</a>
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

    <main class="max-w-6xl mx-auto w-full p-6 mt-8 relative z-10 flex-grow">
        <div class="mb-8 animate-reveal">
            <p class="pal-eyebrow" style="margin-bottom:0.55rem">Frequência</p>
            <h1 class="pal-title">Justificativas de falta</h1>
            <p class="pal-subtitle">Ao aprovar, a falta é abonada e passa a contar como presença. O aluno recebe a decisão por e-mail.</p>
        </div>

        @if(session('status'))
            <div class="rc-alert rc-alert-ok" role="status">{{ session('status') }}</div>
        @endif
        @if($errors->any())
            <div class="rc-alert rc-alert-erro" role="alert">{{ $errors->first() }}</div>
        @endif

        <section class="rc-card animate-reveal pal-card-delay-1" aria-labelledby="titulo-pendentes">
            <div class="rc-card-header">
                <h2 id="titulo-pendentes" class="rc-card-title">Aguardando sua análise</h2>
                <span class="rc-chip {{ $pendentes->isEmpty() ? 'rc-chip-ok' : 'rc-chip-alerta' }}">{{ $pendentes->count() }} {{ $pendentes->count() === 1 ? 'pendente' : 'pendentes' }}</span>
            </div>

            @forelse($pendentes as $j)
                <article style="padding:1.5rem; border-bottom:1px solid color-mix(in srgb, var(--pal-white) 6%, transparent)" aria-labelledby="j-{{ $j->id }}">
                    <div class="flex flex-wrap justify-between gap-3 mb-3">
                        <div>
                            <h3 id="j-{{ $j->id }}" class="rc-strong" style="color:var(--pal-white); font-weight:800; font-size:1rem; margin:0">{{ $j->aluno->nome }}</h3>
                            <p class="rc-hint" style="margin:0.15rem 0 0">{{ $j->materia->nome }} · falta em {{ $j->data_aula->format('d/m/Y') }} · enviada {{ $j->created_at->diffForHumans() }}</p>
                        </div>
                        @if($j->anexo_path)
                            <a href="{{ route('professor.justificativas.anexo', $j) }}" class="rc-btn rc-btn-neutro rc-btn-sm">📎 Ver atestado</a>
                        @else
                            <span class="rc-chip rc-chip-neutro">Sem anexo</span>
                        @endif
                    </div>
                    <p style="color:var(--pal-light-gray); margin:0 0 1rem; white-space:pre-line">{{ $j->motivo }}</p>
                    <form method="POST" action="{{ route('professor.justificativas.decidir', $j) }}" class="flex flex-wrap gap-3 items-end">
                        @csrf
                        <div class="rc-field" style="flex:1; min-width:220px">
                            <label for="comentario-{{ $j->id }}" class="rc-hint">Comentário para o aluno (opcional)</label>
                            <input id="comentario-{{ $j->id }}" name="comentario" class="rc-input" maxlength="1000">
                        </div>
                        <button type="submit" name="decisao" value="aprovar" class="rc-btn rc-btn-confirmar">Aprovar e abonar</button>
                        <button type="submit" name="decisao" value="recusar" class="rc-btn rc-btn-perigo">Recusar</button>
                    </form>
                </article>
            @empty
                <p class="rc-hint" style="padding:1.5rem">Nenhuma justificativa aguardando análise.</p>
            @endforelse
        </section>

        @if($decididas->isNotEmpty())
            <section class="rc-card mt-8 animate-reveal pal-card-delay-2" aria-labelledby="titulo-decididas">
                <div class="rc-card-header"><h2 id="titulo-decididas" class="rc-card-title">Decididas recentemente</h2></div>
                <div class="overflow-x-auto">
                    <table class="rc-table">
                        <thead><tr><th scope="col">Aluno</th><th scope="col">Aula</th><th scope="col">Decisão</th><th scope="col">Em</th></tr></thead>
                        <tbody>
                            @foreach($decididas as $j)
                                <tr>
                                    <td class="rc-strong">{{ $j->aluno->nome }}</td>
                                    <td>{{ $j->data_aula->format('d/m/Y') }}<br><span class="rc-meta">{{ $j->materia->nome }}</span></td>
                                    <td><span class="rc-chip {{ $j->status === 'aprovada' ? 'rc-chip-ok' : 'rc-chip-erro' }}">{{ $j->rotuloStatus() }}</span></td>
                                    <td class="rc-meta">{{ $j->decidida_em?->format('d/m/Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif
    </main>
</div>
@endsection
