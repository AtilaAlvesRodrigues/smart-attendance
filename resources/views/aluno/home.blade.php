@extends('layouts.theme')

@section('title', 'Painel do Aluno - Smart Attendance')

@section('body-class', 'gradient-bg')
@section('nav-user')
<div class="pal-nav-actions" style="gap:0.5rem">
    <div class="pal-nav-user" style="margin-right:0;">
        <span class="pal-nav-user-role">Aluno</span>
        <span class="pal-nav-user-name">{{ $aluno->nome ?? 'Estudante' }}</span>
    </div>
    <button id="open-profile" class="pal-profile-btn" type="button" aria-label="Abrir meu perfil" title="Meu perfil">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
    </button>
</div>
@endsection
@push('styles')
<link rel="stylesheet" href="{{ asset('css/theme_aluno.css') }}">
@endpush

@section('content')

    {{-- Profile Modal --}}
    <div id="profile-modal" class="pal-modal-overlay" style="display:none;">
        <div id="close-modal-overlay" style="position:absolute; inset:0;"></div>
        <div class="pal-modal-content">
            <div class="pal-modal-header">
                <div>
                    <p class="pal-eyebrow" style="margin-bottom:0.3rem;">Seus dados</p>
                    <h2 class="pal-text" style="font-size:1.4rem; font-weight:900; letter-spacing:-0.03em; margin:0;">Meu Perfil</h2>
                </div>
                <button id="close-profile" class="pal-profile-btn" type="button" aria-label="Fechar perfil">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="pal-modal-body">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:2rem;">
                    @foreach([['Nome Completo', $aluno->nome], ['E-mail', $aluno->email], ['Matrícula (RA)', $aluno->ra], ['CPF', $aluno->cpf]] as $field)
                    <div class="pal-profile-field">
                        <p class="pal-profile-field-label">{{ $field[0] }}</p>
                        <p class="pal-profile-field-value">{{ $field[1] }}</p>
                    </div>
                    @endforeach
                </div>

                <hr class="pal-divider" style="margin-bottom:1.5rem;">
                <p class="pal-eyebrow" style="margin-bottom:1rem;">Matérias e Frequência</p>

                <div style="display:flex; flex-direction:column; gap:0.75rem;">
                    @forelse($aluno->materias as $materia)
                    @php
                        $percent = $materia->limite_faltas > 0 ? ($materia->faltas / $materia->limite_faltas) * 100 : 0;
                        $percent = min(100, $percent);
                        $barColor = $percent > 80 ? '#ef4444' : ($percent > 50 ? '#eab308' : '#22c55e');
                    @endphp
                    <div class="pal-profile-field" style="padding:1.25rem;">
                        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:0.75rem;">
                            <div>
                                <p class="pal-profile-field-value" style="font-size:0.9rem;">{{ $materia->nome }}</p>
                                <p style="font-size:0.72rem; color:#999; margin:0;">{{ $materia->carga_horaria }}h · {{ $materia->total_aulas }} aulas</p>
                            </div>
                            <div style="text-align:right;">
                                <span style="font-size:1.4rem; font-weight:900; color:{{ $materia->faltas >= $materia->limite_faltas ? '#ef4444' : 'inherit' }};" class="pal-profile-field-value">{{ $materia->faltas }}</span>
                                <span style="font-size:0.72rem; color:#999;"> / {{ $materia->limite_faltas }} faltas</span>
                            </div>
                        </div>
                        <div style="height:3px; background:rgba(255,255,255,0.05); border-radius:2px; overflow:hidden;">
                            <div style="height:100%; width:{{ $percent }}%; background:{{ $barColor }}; transition:width 1s ease;"></div>
                        </div>
                        @if($materia->faltas >= $materia->limite_faltas)
                        <p style="font-family:'Space Grotesk',monospace; font-size:0.75rem; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; color:#ef4444; margin:0.5rem 0 0;">⚠ Limite de faltas atingido</p>
                        @endif
                    </div>
                    @empty
                    <div style="text-align:center; padding:2rem; border:1px dashed rgba(255,255,255,0.08); border-radius:3px;">
                        <p style="color:#777; font-size:0.85rem; margin:0;">Nenhuma matéria vinculada.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    @php
        $primeiroNome = \Illuminate\Support\Str::of($aluno->nome ?? 'Estudante')->explode(' ')->first();
        $emAtencao = $aluno->materias->filter(fn ($m) => in_array($m->situacao->status, ['atencao', 'reprovado', 'reprovado_falta']))->count();
    @endphp
    <main class="pal-main sa-aluno">

        <header class="sa-aluno-header">
            <div>
                <p class="pal-eyebrow">Painel do Aluno</p>
                <h1 class="pal-title">Olá, {{ $primeiroNome }}</h1>
                <p class="pal-subtitle">
                    Sua frequência e suas notas neste semestre.
                    @if($aluno->materias->isNotEmpty())
                        @if($emAtencao > 0)
                            <strong class="sa-alerta-texto">{{ $emAtencao }} {{ $emAtencao === 1 ? 'matéria precisa' : 'matérias precisam' }} de atenção.</strong>
                        @else
                            <strong class="sa-ok-texto">Tudo em dia.</strong>
                        @endif
                    @endif
                </p>
            </div>
            <div class="sa-timer">
                <div id="aluno-timer-slot"></div>
                <span class="pal-timer-caption">tempo até a sessão expirar</span>
            </div>
        </header>

        {{-- Presença pendente (aluno escaneou o QR antes de fazer login) --}}
        @if(session('pending_attendance_code'))
        <div class="sa-pendente" role="alert">
            <div>
                <p class="sa-pendente-titulo">Você tem uma presença para confirmar</p>
                <p class="sa-pendente-texto">Você escaneou o QR Code da aula antes de entrar. Falta só um toque.</p>
            </div>
            <a href="{{ route('presenca.confirmar', session('pending_attendance_code')) }}" class="sa-btn-confirmar">
                Confirmar presença
            </a>
        </div>
        @endif

        {{-- Matérias --}}
        <section aria-labelledby="titulo-materias">
            <h2 id="titulo-materias" class="sa-secao-titulo">Minhas matérias</h2>

            @if($aluno->materias->isEmpty())
                <div class="sa-vazio">
                    <p><strong>Você ainda não está matriculado em nenhuma matéria.</strong></p>
                    <p>Assim que a coordenação fizer sua matrícula, suas matérias aparecem aqui.</p>
                </div>
            @else
            <div class="sa-materias">
                @foreach($aluno->materias as $materia)
                    @php
                        $sit = $materia->situacao;
                        $usoLimite = $sit->limiteFaltas > 0 ? min(100, round($sit->faltas / $sit->limiteFaltas * 100)) : 0;
                        $icone = ['aprovado' => '✓', 'regular' => '✓', 'atencao' => '!', 'reprovado' => '✕', 'reprovado_falta' => '✕'][$sit->status];
                        $notas = [
                            'P1' => $materia->pivot->prova1,
                            'T1' => $materia->pivot->trabalho1,
                            'T2' => $materia->pivot->trabalho2,
                            'P2' => $materia->pivot->prova2,
                        ];
                    @endphp
                    <article class="sa-card sa-status-{{ $sit->status }}" aria-labelledby="materia-{{ $materia->id }}">
                        <div class="sa-card-topo">
                            <div>
                                <h3 id="materia-{{ $materia->id }}" class="sa-card-nome">{{ $materia->nome }}</h3>
                                <p class="sa-card-meta">{{ $materia->sala ?? 'Sala a definir' }} · {{ $materia->total_aulas ?? 0 }} aulas no semestre</p>
                            </div>
                            <span class="sa-badge"><span aria-hidden="true">{{ $icone }}</span> {{ $sit->rotulo() }}</span>
                        </div>

                        <div class="sa-card-numeros">
                            <div>
                                <p class="sa-numero">{{ $sit->frequencia }}%</p>
                                <p class="sa-numero-rotulo">frequência</p>
                            </div>
                            <div>
                                <p class="sa-numero">{{ $sit->faltas }}<span class="sa-numero-de">/{{ $sit->limiteFaltas }}</span></p>
                                <p class="sa-numero-rotulo">faltas usadas</p>
                            </div>
                            <div>
                                <p class="sa-numero">{{ $sit->media !== null ? number_format($sit->media, 1, ',', '') : '—' }}</p>
                                <p class="sa-numero-rotulo">{{ $sit->notasLancadas === 4 ? 'média final' : 'média parcial' }}</p>
                            </div>
                        </div>

                        <div class="sa-barra" role="progressbar" aria-valuemin="0" aria-valuemax="{{ $sit->limiteFaltas }}" aria-valuenow="{{ min($sit->faltas, $sit->limiteFaltas) }}"
                             aria-label="{{ $sit->faltas }} de {{ $sit->limiteFaltas }} faltas permitidas">
                            <div class="sa-barra-preenchida" style="width:{{ $usoLimite }}%"></div>
                        </div>
                        <p class="sa-explicacao">{{ $sit->explicacao() }}</p>

                        <dl class="sa-notas">
                            @foreach($notas as $rotulo => $valor)
                            <div>
                                <dt>{{ $rotulo }}</dt>
                                <dd>{{ $valor !== null ? number_format($valor, 1, ',', '') : '—' }}</dd>
                            </div>
                            @endforeach
                        </dl>

                        @if($materia->historico->isNotEmpty())
                        <details class="sa-historico">
                            <summary>Ver histórico de aulas ({{ $materia->historico->count() }})</summary>
                            <ul>
                                @foreach($materia->historico as $aula)
                                <li class="{{ $aula['presente'] ? 'sa-hist-presente' : 'sa-hist-falta' }}">
                                    <span>{{ $aula['data']->format('d/m') }} · {{ ucfirst($aula['data']->locale('pt_BR')->translatedFormat('D')) }}</span>
                                    <strong>{{ $aula['presente'] ? '✓ Presente' : '✕ Falta' }}</strong>
                                </li>
                                @endforeach
                            </ul>
                        </details>
                        @endif
                    </article>
                @endforeach
            </div>
            <p class="sa-legenda">P = prova · T = trabalho · “—” = nota ainda não lançada. Aprovação com média 5,0 e no máximo 25% de faltas.</p>
            @endif
        </section>

        {{-- Como registrar presença --}}
        <section class="sa-guia" aria-labelledby="titulo-guia">
            <h2 id="titulo-guia" class="sa-secao-titulo">Como registrar sua presença</h2>
            <ol class="sa-passos">
                <li><span class="sa-passo-num" aria-hidden="true">1</span><div><strong>Abra a câmera do celular</strong><p>Na sala, aponte para o QR Code que o professor projetar.</p></div></li>
                <li><span class="sa-passo-num" aria-hidden="true">2</span><div><strong>Toque no link que aparecer</strong><p>Se pedir, entre com seu RA, e-mail ou CPF.</p></div></li>
                <li><span class="sa-passo-num" aria-hidden="true">3</span><div><strong>Pronto!</strong><p>A confirmação aparece na tela e sua frequência é atualizada aqui.</p></div></li>
            </ol>
        </section>

    </main>
@endsection

@push('scripts')
<script>
    const modal = document.getElementById('profile-modal');
    const openBtn = document.getElementById('open-profile');
    const closeBtn = document.getElementById('close-profile');
    const overlay = document.getElementById('close-modal-overlay');
    function toggleModal(show) {
        modal.style.display = show ? 'flex' : 'none';
        document.body.style.overflow = show ? 'hidden' : '';
    }
    openBtn.addEventListener('click', () => toggleModal(true));
    closeBtn.addEventListener('click', () => toggleModal(false));
    overlay.addEventListener('click', () => toggleModal(false));
    document.addEventListener('keydown', e => { if (e.key === 'Escape') toggleModal(false); });

</script>
@endpush
