{{-- Selo de situação do aluno — mesma aparência do badgeSituacao() em JS --}}
@php
    [$classe, $rotulo] = match ($status) {
        'aprovado'        => ['bg-green-600 text-white', '✓ Aprovado'],
        'reprovado'       => ['bg-orange-600 text-white', 'Reprovado por nota'],
        'reprovado_falta' => ['bg-red-600 text-white', 'Reprovado por falta'],
        default           => ['bg-white/5 border border-white/10 pal-text-muted', 'Em andamento'],
    };
@endphp
<span class="px-3 py-1 rounded-sm text-xs font-bold whitespace-nowrap {{ $classe }}">{{ $rotulo }}</span>
