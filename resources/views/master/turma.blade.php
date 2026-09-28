@extends('layouts.theme')

@section('title', 'Turma de ' . $materia->nome . ' - Master')

@section('body-class', 'gradient-bg relative min-h-screen flex flex-col')

@section('nav-left')
    <a href="{{ route('master.materias') }}" class="pal-nav-btn pal-nav-btn-ghost hidden sm:inline-flex">← Matérias</a>
@endsection

@section('nav-user')
<div class="pal-nav-actions" style="gap:0.5rem">
    <div class="pal-nav-user">
        <span class="pal-nav-user-role">Administrador</span>
        <span class="pal-nav-user-name">{{ auth()->guard('masters')->user()->nome ?? 'Administrador' }}</span>
    </div>
</div>
@endsection

@section('content')
<main class="pal-main" style="max-width:1100px; margin:0 auto; width:100%;">

    <div class="border-b border-white/5 pb-6 mb-8">
        <p class="pal-eyebrow mb-2">Turma</p>
        <h1 class="pal-title">{{ $materia->nome }}</h1>
        <p class="pal-subtitle">
            {{ $materia->sala ?? 'Sala a definir' }} · {{ $materia->total_aulas ?? 0 }} aulas no semestre.
            Escolha quem dá esta matéria e quem está matriculado. Só alunos matriculados conseguem registrar presença pelo QR Code.
        </p>
    </div>

    @if(session('success'))
        <div class="glass p-4 rounded-sm mb-6 pal-text font-bold" role="status" style="border:1px solid rgba(34,197,94,0.5); background:rgba(34,197,94,0.1);">
            ✓ {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="glass p-4 rounded-sm mb-6 pal-text" role="alert" style="border:1px solid rgba(239,68,68,0.5); background:rgba(239,68,68,0.1);">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('master.turma.update', $materia) }}" id="form-turma">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

            {{-- Professores --}}
            <fieldset class="glass p-6 rounded-sm border border-white/10 lg:col-span-2" data-lista="professores">
                <legend class="sr-only">Professores responsáveis</legend>
                <div class="flex items-baseline justify-between gap-3 mb-1">
                    <h2 class="pal-text font-black" style="font-size:1.15rem;">Professores</h2>
                    <span class="pal-subtitle" style="margin:0;" data-contador>0 selecionados</span>
                </div>
                <p class="pal-subtitle mb-4" style="margin-top:0;">Quem pode abrir a chamada e lançar notas.</p>
                <input type="search" class="pal-filter-input w-full mb-3" placeholder="Buscar professor pelo nome" aria-label="Buscar professor" data-busca>
                <ul class="flex flex-col gap-1 overflow-y-auto" style="max-height:420px;">
                    @forelse($professores as $professor)
                    <li data-item data-texto="{{ \Illuminate\Support\Str::lower($professor->nome) }}">
                        <label class="flex items-center gap-3 p-3 rounded-sm cursor-pointer hover:bg-white/5">
                            <input type="checkbox" name="professores[]" value="{{ $professor->id }}" class="w-5 h-5 accent-violet-500"
                                   @checked(in_array($professor->id, old('professores', $professoresIds)))>
                            <span class="pal-text font-bold">{{ $professor->nome }}</span>
                        </label>
                    </li>
                    @empty
                    <li class="pal-subtitle">Nenhum professor cadastrado. <a class="pal-text underline" href="{{ route('master.cadastrar') }}">Cadastrar professor</a></li>
                    @endforelse
                </ul>
            </fieldset>

            {{-- Alunos --}}
            <fieldset class="glass p-6 rounded-sm border border-white/10 lg:col-span-3" data-lista="alunos">
                <legend class="sr-only">Alunos matriculados</legend>
                <div class="flex items-baseline justify-between gap-3 mb-1">
                    <h2 class="pal-text font-black" style="font-size:1.15rem;">Alunos matriculados</h2>
                    <span class="pal-subtitle" style="margin:0;" data-contador>0 selecionados</span>
                </div>
                <p class="pal-subtitle mb-4" style="margin-top:0;">Desmarcar um aluno remove a matrícula e as notas dele nesta matéria.</p>
                <div class="flex gap-2 mb-3 flex-wrap">
                    <input type="search" class="pal-filter-input flex-grow" style="min-width:200px;" placeholder="Buscar por nome ou RA" aria-label="Buscar aluno" data-busca>
                    <button type="button" class="pal-nav-btn pal-nav-btn-ghost" data-marcar="1">Marcar visíveis</button>
                    <button type="button" class="pal-nav-btn pal-nav-btn-ghost" data-marcar="0">Desmarcar visíveis</button>
                </div>
                <ul class="flex flex-col gap-1 overflow-y-auto" style="max-height:420px;">
                    @forelse($alunos as $aluno)
                    <li data-item data-texto="{{ \Illuminate\Support\Str::lower($aluno->nome . ' ' . $aluno->ra) }}">
                        <label class="flex items-center gap-3 p-3 rounded-sm cursor-pointer hover:bg-white/5">
                            <input type="checkbox" name="alunos[]" value="{{ $aluno->id }}" class="w-5 h-5 accent-violet-500"
                                   @checked(in_array($aluno->id, old('alunos', $alunosIds)))>
                            <span class="flex flex-col">
                                <span class="pal-text font-bold">{{ $aluno->nome }}</span>
                                <span class="pal-subtitle font-mono" style="margin:0; font-size:0.85rem;">RA {{ $aluno->ra }}</span>
                            </span>
                        </label>
                    </li>
                    @empty
                    <li class="pal-subtitle">Nenhum aluno cadastrado. <a class="pal-text underline" href="{{ route('master.cadastrar') }}">Cadastrar aluno</a></li>
                    @endforelse
                </ul>
                <p class="pal-subtitle hidden" data-sem-resultado>Nenhum aluno encontrado com essa busca.</p>
            </fieldset>
        </div>

        <div class="flex gap-3 justify-end mt-6 flex-wrap">
            <a href="{{ route('master.materias') }}" class="pal-btn-outline px-6 py-3 rounded-sm">Cancelar</a>
            <button type="submit" class="px-6 py-3 rounded-sm font-bold" style="background:#16a34a; color:#fff; min-height:44px;">
                Salvar turma
            </button>
        </div>
    </form>
</main>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('[data-lista]').forEach(lista => {
        const itens = [...lista.querySelectorAll('[data-item]')];
        const contador = lista.querySelector('[data-contador]');
        const busca = lista.querySelector('[data-busca]');
        const semResultado = lista.querySelector('[data-sem-resultado]');

        const atualizarContador = () => {
            const n = lista.querySelectorAll('input[type=checkbox]:checked').length;
            contador.textContent = `${n} ${n === 1 ? 'selecionado' : 'selecionados'}`;
        };

        busca?.addEventListener('input', () => {
            const termo = busca.value.trim().toLowerCase();
            let visiveis = 0;
            itens.forEach(li => {
                const mostra = !termo || li.dataset.texto.includes(termo);
                li.hidden = !mostra;
                if (mostra) visiveis++;
            });
            if (semResultado) semResultado.classList.toggle('hidden', visiveis > 0);
        });
        // Enter na busca não envia o formulário
        busca?.addEventListener('keydown', e => { if (e.key === 'Enter') e.preventDefault(); });

        lista.querySelectorAll('[data-marcar]').forEach(botao => botao.addEventListener('click', () => {
            itens.filter(li => !li.hidden).forEach(li => { li.querySelector('input').checked = botao.dataset.marcar === '1'; });
            atualizarContador();
        }));

        lista.addEventListener('change', atualizarContador);
        atualizarContador();
    });
</script>
@endpush
