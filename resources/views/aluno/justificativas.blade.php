@extends('layouts.theme')

@section('title', 'Justificar faltas - Smart Attendance')
@section('body-class', 'gradient-bg relative min-h-screen flex flex-col')

@section('nav-left')
    <a href="{{ route('dashboard.aluno') }}" class="pal-nav-btn pal-nav-btn-ghost">⬅ Meu painel</a>
@endsection

@section('nav-user')
    <div class="pal-nav-user">
        <span class="pal-nav-user-role">Aluno</span>
        <span class="pal-nav-user-name">{{ $aluno->nome }}</span>
    </div>
@endsection

@section('content')
<div class="flex-grow flex flex-col relative overflow-hidden">
    <div class="blob top-[-100px] left-[-100px]"></div>
    <div class="blob-2"></div>

    <main class="max-w-5xl mx-auto w-full p-6 mt-8 relative z-10 flex-grow">
        <div class="mb-8 animate-reveal">
            <p class="pal-eyebrow" style="margin-bottom:0.55rem">Frequência</p>
            <h1 class="pal-title">Justificar faltas</h1>
            <p class="pal-subtitle">Faltou por um motivo justo? Escolha a aula, explique o que houve e anexe o atestado. Se o professor aprovar, a falta deixa de contar.</p>
        </div>

        @if(session('status'))
            <div class="rc-alert rc-alert-ok" role="status">{{ session('status') }}</div>
        @endif
        @if($errors->any())
            <div class="rc-alert rc-alert-erro" role="alert">
                <strong>Não foi possível enviar:</strong> {{ $errors->first() }}
            </div>
        @endif

        <section class="rc-card animate-reveal pal-card-delay-1" aria-labelledby="titulo-nova">
            <div class="rc-card-header"><h2 id="titulo-nova" class="rc-card-title">Nova justificativa</h2></div>

            @if($faltasPorMateria->isEmpty())
                <p class="rc-hint" style="padding:1.5rem">Você não tem faltas para justificar. 🎉</p>
            @else
                <form method="POST" action="{{ route('aluno.justificativas.store') }}" enctype="multipart/form-data" class="grid gap-5" style="padding:1.5rem" id="form-justificativa">
                    @csrf
                    <input type="hidden" name="materia_id" id="materia_id" value="{{ old('materia_id') }}">

                    <div class="rc-field">
                        <label for="codigo_aula" class="login-label">Aula em que você faltou</label>
                        <select id="codigo_aula" name="codigo_aula" class="rc-select" required>
                            <option value="">Escolha a aula</option>
                            @foreach($faltasPorMateria as $grupo)
                                <optgroup label="{{ $grupo['materia']->nome }}">
                                    @foreach($grupo['faltas'] as $falta)
                                        <option value="{{ $falta->codigo_aula }}" data-materia="{{ $grupo['materia']->id }}" @selected(old('codigo_aula') === $falta->codigo_aula)>
                                            {{ \Carbon\Carbon::parse($falta->data_aula)->format('d/m/Y') }} · {{ $grupo['materia']->nome }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>

                    <div class="rc-field">
                        <label for="motivo" class="login-label">Motivo</label>
                        <textarea id="motivo" name="motivo" class="rc-textarea" minlength="10" maxlength="2000" required aria-describedby="motivo-dica">{{ old('motivo') }}</textarea>
                        <span id="motivo-dica" class="rc-hint">Ex.: consulta médica, doença, luto, convocação oficial.</span>
                    </div>

                    @if($anexoHabilitado)
                        <div class="rc-field">
                            <label for="anexo" class="login-label">Atestado ou comprovante (opcional)</label>
                            <input id="anexo" name="anexo" type="file" class="rc-input" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" aria-describedby="anexo-dica">
                            <span id="anexo-dica" class="rc-hint">PDF, JPG ou PNG, até 5 MB. Só você e o professor da matéria podem ver o arquivo.</span>
                        </div>
                    @else
                        <div class="rc-alert rc-alert-info" style="margin:0">O envio de anexos está temporariamente indisponível. Envie a justificativa com o texto e entregue o atestado ao professor.</div>
                    @endif

                    <div><button type="submit" class="rc-btn rc-btn-confirmar">Enviar justificativa</button></div>
                </form>
            @endif
        </section>

        <section class="rc-card mt-8 animate-reveal pal-card-delay-2" aria-labelledby="titulo-enviadas">
            <div class="rc-card-header"><h2 id="titulo-enviadas" class="rc-card-title">Enviadas</h2></div>
            @if($justificativas->isEmpty())
                <p class="rc-hint" style="padding:1.5rem">Nenhuma justificativa enviada ainda.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="rc-table">
                        <thead><tr><th scope="col">Aula</th><th scope="col">Situação</th><th scope="col">Resposta do professor</th></tr></thead>
                        <tbody>
                            @foreach($justificativas as $j)
                                <tr>
                                    <td>
                                        <span class="rc-strong">{{ $j->data_aula->format('d/m/Y') }}</span><br>
                                        <span class="rc-meta">{{ $j->materia->nome }}</span>
                                        @if($j->anexo_path)
                                            <br><a href="{{ route('aluno.justificativas.anexo', $j) }}" class="rc-hint" style="text-decoration:underline">Ver anexo</a>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="rc-chip {{ ['aprovada' => 'rc-chip-ok', 'recusada' => 'rc-chip-erro'][$j->status] ?? 'rc-chip-alerta' }}">{{ $j->rotuloStatus() }}</span>
                                    </td>
                                    <td>{{ $j->comentario_professor ?: '—' }}</td>
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
<script @nonce>
    // A matéria acompanha a aula escolhida (cada opção sabe a sua matéria)
    (function () {
        var select = document.getElementById('codigo_aula');
        var materia = document.getElementById('materia_id');
        if (!select || !materia) return;
        function sync() {
            var opt = select.options[select.selectedIndex];
            materia.value = opt && opt.dataset.materia ? opt.dataset.materia : '';
        }
        select.addEventListener('change', sync);
        sync();
    })();
</script>
@endpush
