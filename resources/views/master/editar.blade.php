@extends('layouts.theme')

@php
    $rotulo = ['aluno' => 'aluno', 'professor' => 'professor', 'materia' => 'matéria'][$tipo];
    $campos = match ($tipo) {
        'aluno' => [
            ['nome', 'Nome completo', 'text', 'name'],
            ['email', 'E-mail', 'email', 'email'],
            ['cpf', 'CPF', 'text', 'off'],
            ['ra', 'RA (matrícula)', 'text', 'off'],
        ],
        'professor' => [
            ['nome', 'Nome completo', 'text', 'name'],
            ['email', 'E-mail', 'email', 'email'],
            ['cpf', 'CPF', 'text', 'off'],
        ],
        'materia' => [
            ['nome', 'Nome da matéria', 'text', 'off'],
            ['sala', 'Sala', 'text', 'off'],
            ['carga_horaria', 'Carga horária (horas)', 'number', 'off'],
            ['total_aulas', 'Aulas previstas no semestre', 'number', 'off'],
        ],
    };
    $temConta = $tipo !== 'materia';
@endphp

@section('title', 'Editar ' . $rotulo . ' - Smart Attendance')
@section('body-class', 'gradient-bg relative min-h-screen flex flex-col')

@section('nav-left')
    <a href="{{ $voltar }}" class="pal-nav-btn pal-nav-btn-ghost">⬅ Voltar à lista</a>
@endsection

@section('nav-user')
    <div class="pal-nav-user">
        <span class="pal-nav-user-role">Master</span>
        <span class="pal-nav-user-name">{{ $master->nome ?? 'Administrador' }}</span>
    </div>
@endsection

@section('content')
<div class="flex-grow flex flex-col relative overflow-hidden">
    <div class="blob top-[-100px] left-[-100px]"></div>
    <div class="blob-2"></div>

    <main class="max-w-3xl mx-auto w-full p-6 mt-8 relative z-10 flex-grow">
        <div class="mb-8 animate-reveal">
            <p class="pal-eyebrow" style="margin-bottom:0.55rem">Editar {{ $rotulo }}</p>
            <h1 class="pal-title">{{ $registro->nome }}</h1>
            <p class="pal-subtitle">Cadastrado em {{ $registro->created_at?->format('d/m/Y') ?? '—' }}. As alterações valem na hora.</p>
        </div>

        @if(session('status'))
            <div class="rc-alert rc-alert-ok" role="status">{{ session('status') }}</div>
        @endif

        <section class="rc-card animate-reveal pal-card-delay-1" aria-labelledby="t-dados">
            <div class="rc-card-header"><h2 id="t-dados" class="rc-card-title">Dados</h2></div>
            <form method="POST" action="{{ route('master.atualizar', [$tipo, $registro->id]) }}" class="grid gap-5 sm:grid-cols-2" style="padding:1.5rem" novalidate>
                @csrf
                @method('PUT')
                @foreach($campos as [$campo, $label, $tipoInput, $auto])
                    <div class="rc-field {{ $campo === 'nome' ? 'sm:col-span-2' : '' }}">
                        <label for="{{ $campo }}" class="login-label">{{ $label }}</label>
                        <input id="{{ $campo }}" name="{{ $campo }}" type="{{ $tipoInput }}" class="rc-input" autocomplete="{{ $auto }}"
                            value="{{ old($campo, $registro->{$campo}) }}" @if($tipoInput === 'number') min="1" @endif
                            @error($campo) aria-invalid="true" aria-describedby="erro-{{ $campo }}" @enderror>
                        @error($campo)<span id="erro-{{ $campo }}" class="login-error" style="margin-top:0.25rem">{{ $message }}</span>@enderror
                    </div>
                @endforeach
                <div class="sm:col-span-2 flex flex-wrap gap-3">
                    <button type="submit" class="rc-btn rc-btn-confirmar">Salvar alterações</button>
                    <a href="{{ $voltar }}" class="rc-btn rc-btn-neutro">Cancelar</a>
                </div>
            </form>
        </section>

        @if($temConta)
            <section class="rc-card mt-6 animate-reveal pal-card-delay-2" aria-labelledby="t-2fa">
                <div class="rc-card-header">
                    <div>
                        <h2 id="t-2fa" class="rc-card-title">Verificação em duas etapas</h2>
                        <p class="rc-hint" style="margin:0.25rem 0 0">
                            @if($registro->temDoisFatores())
                                Ativada. Se a pessoa perdeu o celular e os códigos de recuperação, remova a verificação para ela entrar só com a senha e configurar de novo.
                            @else
                                Não ativada.
                            @endif
                        </p>
                    </div>
                    @if($registro->temDoisFatores())
                        <form method="POST" action="{{ route('master.resetar-2fa', [$tipo, $registro->id]) }}">
                            @csrf
                            <button type="submit" class="rc-btn rc-btn-neutro rc-btn-sm">Remover verificação</button>
                        </form>
                    @endif
                </div>
            </section>
        @endif

        <section class="rc-card mt-6 animate-reveal pal-card-delay-3" aria-labelledby="t-excluir" style="border-color:rgba(239,68,68,0.35)">
            <div class="rc-card-header">
                <div>
                    <h2 id="t-excluir" class="rc-card-title">Excluir {{ $rotulo }}</h2>
                    <p class="rc-hint" style="margin:0.25rem 0 0">
                        @if($tipo === 'materia')
                            A matéria some das telas de alunos e professores. O histórico de presenças e notas fica guardado.
                        @else
                            A pessoa perde o acesso ao sistema. O histórico de presenças e notas fica guardado.
                        @endif
                    </p>
                </div>
                <button type="button" class="rc-btn rc-btn-perigo" data-abrir-dialogo="dialogo-excluir">Excluir</button>
            </div>
        </section>

        <dialog id="dialogo-excluir" class="rc-dialog" aria-labelledby="dialogo-titulo">
            <h2 id="dialogo-titulo" class="rc-card-title" style="margin-bottom:0.75rem">Excluir {{ $registro->nome }}?</h2>
            <p class="rc-hint" style="margin:0 0 1.5rem">Esta ação tira {{ $tipo === 'materia' ? 'a matéria' : 'o acesso' }} do sistema. Para desfazer, será preciso falar com o suporte técnico.</p>
            <form method="POST" action="{{ route('master.excluir', [$tipo, $registro->id]) }}" class="flex flex-wrap gap-3 justify-end">
                @csrf
                @method('DELETE')
                <button type="button" class="rc-btn rc-btn-neutro" data-fechar-dialogo autofocus>Cancelar</button>
                <button type="submit" class="rc-btn rc-btn-perigo">Sim, excluir</button>
            </form>
        </dialog>
    </main>
</div>
@endsection

@push('scripts')
<script @nonce>
    document.querySelectorAll('[data-abrir-dialogo]').forEach(function (botao) {
        var dialogo = document.getElementById(botao.dataset.abrirDialogo);
        botao.addEventListener('click', function () { dialogo.showModal(); });
        dialogo.querySelectorAll('[data-fechar-dialogo]').forEach(function (fechar) {
            fechar.addEventListener('click', function () { dialogo.close(); });
        });
    });
</script>
@endpush
