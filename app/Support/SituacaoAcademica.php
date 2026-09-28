<?php

namespace App\Support;

/**
 * Situação de um aluno em uma matéria: frequência, faltas, média e status.
 *
 * Fonte única da regra, usada tanto no painel do aluno quanto na tela de
 * gestão do professor, para que os dois lados sempre mostrem o mesmo número.
 *
 * REGRAS:
 * - Faltas = aulas já realizadas (chamadas feitas) − presenças do aluno.
 * - Limite de faltas = 25% das aulas previstas (frequência mínima de 75%).
 * - Reprovado por falta apenas quando as faltas ULTRAPASSAM o limite — no meio
 *   do semestre, um aluno que foi a todas as aulas não aparece como reprovado.
 * - Média = média simples das notas lançadas; aprovação com média >= 5.
 * - "Atenção" antes de reprovar: faltas perto do limite, média parcial abaixo
 *   de 5 ou frequência abaixo de 75% nas aulas já dadas.
 */
class SituacaoAcademica
{
    public const MEDIA_MINIMA = 5.0;
    public const PERCENTUAL_LIMITE_FALTAS = 0.25;

    public readonly int $faltas;
    public readonly int $limiteFaltas;
    public readonly int $faltasRestantes;
    public readonly int $frequencia;
    public readonly ?float $media;
    public readonly int $notasLancadas;
    /** aprovado | reprovado | reprovado_falta | atencao | regular */
    public readonly string $status;

    /**
     * @param  array<int, float|string|null>  $notas
     */
    public function __construct(
        public readonly int $aulasRealizadas,
        public readonly int $presencas,
        public readonly int $aulasPrevistas,
        array $notas = [],
    ) {
        $this->faltas = max(0, $aulasRealizadas - $presencas);
        $this->limiteFaltas = (int) floor($aulasPrevistas * self::PERCENTUAL_LIMITE_FALTAS);
        $this->faltasRestantes = max(0, $this->limiteFaltas - $this->faltas);
        $this->frequencia = $aulasRealizadas > 0
            ? (int) round(min($presencas, $aulasRealizadas) / $aulasRealizadas * 100)
            : 100;

        $lancadas = array_values(array_filter($notas, fn ($n) => $n !== null && $n !== ''));
        $this->notasLancadas = count($lancadas);
        $this->media = $lancadas ? round(array_sum(array_map('floatval', $lancadas)) / count($lancadas), 1) : null;

        $this->status = $this->calcularStatus(count($notas));
    }

    private function calcularStatus(int $totalNotas): string
    {
        if ($this->faltas > $this->limiteFaltas && $this->aulasPrevistas > 0) {
            return 'reprovado_falta';
        }

        $todasNotasLancadas = $totalNotas > 0 && $this->notasLancadas === $totalNotas;
        if ($todasNotasLancadas) {
            return $this->media >= self::MEDIA_MINIMA ? 'aprovado' : 'reprovado';
        }

        $pertoDoLimite = $this->limiteFaltas > 0 && $this->faltas >= ceil($this->limiteFaltas * 0.75);
        $mediaBaixa = $this->media !== null && $this->media < self::MEDIA_MINIMA;
        $frequenciaBaixa = $this->aulasRealizadas >= 3 && $this->frequencia < 75;

        return ($pertoDoLimite || $mediaBaixa || $frequenciaBaixa) ? 'atencao' : 'regular';
    }

    public function rotulo(): string
    {
        return match ($this->status) {
            'aprovado'        => 'Aprovado',
            'reprovado'       => 'Reprovado por nota',
            'reprovado_falta' => 'Reprovado por falta',
            'atencao'         => 'Atenção',
            default           => 'Em dia',
        };
    }

    /** Explicação curta, em linguagem simples, do porquê do status. */
    public function explicacao(): string
    {
        return match ($this->status) {
            'reprovado_falta' => "Você passou do limite de {$this->limiteFaltas} faltas.",
            'aprovado'        => 'Média final ' . number_format($this->media, 1, ',', '') . ' e frequência suficiente.',
            'reprovado'       => 'Média final ' . number_format($this->media, 1, ',', '') . ', abaixo de 5,0.',
            'atencao'         => $this->media !== null && $this->media < self::MEDIA_MINIMA
                ? 'Média parcial abaixo de 5,0.'
                : ($this->frequencia < 75 && $this->faltasRestantes > 1
                    ? "Frequência de {$this->frequencia}%, abaixo dos 75% exigidos. Restam {$this->faltasRestantes} faltas."
                    : ($this->faltasRestantes === 0
                    ? 'Nenhuma falta restante: a próxima reprova.'
                    : "Restam só {$this->faltasRestantes} " . ($this->faltasRestantes === 1 ? 'falta' : 'faltas') . ' até o limite.')),
            default           => $this->faltasRestantes . ' ' . ($this->faltasRestantes === 1 ? 'falta restante' : 'faltas restantes') . ' até o limite.',
        };
    }
}
