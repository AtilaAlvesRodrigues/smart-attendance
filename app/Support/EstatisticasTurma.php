<?php

namespace App\Support;

use App\Models\Materia;
use App\Models\Presenca;
use Illuminate\Support\Facades\DB;

/**
 * Números dos gráficos de presença. Toda frequência e situação vem do
 * SituacaoAcademica, para bater com o painel do aluno e a tela de notas.
 */
class EstatisticasTurma
{
    private const STATUS = ['aprovado', 'reprovado', 'reprovado_falta', 'atencao', 'regular'];

    /**
     * @return array{
     *   frequencia_por_aula: list<array{data: string, percentual: int}>,
     *   situacoes: array<string, int>,
     *   frequencia_media: int,
     *   em_risco: list<array{nome: string, faltas: int, limite: int, frequencia: int, status: string, rotulo: string}>
     * }
     */
    public static function daMateria(Materia $materia): array
    {
        $alunos = $materia->alunos()->orderBy('nome')->get();
        $total = $alunos->count();

        $aulas = AulasDaMateria::listar($materia->id)->sortBy('data_aula')->values();
        $frequenciaPorAula = $aulas->map(fn ($aula) => [
            'data'       => $aula->data_aula,
            'percentual' => $total > 0 ? min(100, (int) round($aula->presentes / $total * 100)) : 0,
        ])->all();

        $presencas = Presenca::where('materia_id', $materia->id)
            ->select('aluno_id', DB::raw('COUNT(*) as total'))
            ->groupBy('aluno_id')
            ->pluck('total', 'aluno_id');

        $situacoes = array_fill_keys(self::STATUS, 0);
        $frequencias = [];
        $emRisco = [];

        foreach ($alunos as $aluno) {
            $s = new SituacaoAcademica(
                aulasRealizadas: $aulas->count(),
                presencas: (int) ($presencas[$aluno->id] ?? 0),
                aulasPrevistas: (int) ($materia->total_aulas ?? 0),
                notas: [$aluno->pivot->prova1, $aluno->pivot->trabalho1, $aluno->pivot->trabalho2, $aluno->pivot->prova2],
            );

            $situacoes[$s->status]++;
            $frequencias[] = $s->frequencia;

            if (in_array($s->status, ['reprovado_falta', 'atencao'], true)) {
                $emRisco[] = [
                    'nome'       => $aluno->nome,
                    'faltas'     => $s->faltas,
                    'limite'     => $s->limiteFaltas,
                    'frequencia' => $s->frequencia,
                    'status'     => $s->status,
                    'rotulo'     => $s->rotulo(),
                ];
            }
        }

        usort($emRisco, fn ($a, $b) => [$b['faltas'], $a['nome']] <=> [$a['faltas'], $b['nome']]);

        return [
            'frequencia_por_aula' => $frequenciaPorAula,
            'situacoes'           => $situacoes,
            'frequencia_media'    => $frequencias ? (int) round(array_sum($frequencias) / count($frequencias)) : 100,
            'em_risco'            => $emRisco,
        ];
    }

    /**
     * Uma linha por turma (matéria), com 3 consultas no total.
     *
     * @return list<array{materia_id: int, materia: string, alunos: int, aulas_realizadas: int, frequencia_media: int, em_risco: int, situacoes: array<string, int>}>
     */
    public static function geral(): array
    {
        $materias = Materia::orderBy('nome')->get(['id', 'nome', 'total_aulas']);

        $realizadas = Presenca::select('materia_id', DB::raw('COUNT(DISTINCT codigo_aula) as total'))
            ->groupBy('materia_id')
            ->pluck('total', 'materia_id');

        $presencas = Presenca::select('materia_id', 'aluno_id', DB::raw('COUNT(*) as total'))
            ->groupBy('materia_id', 'aluno_id')
            ->get()
            ->mapWithKeys(fn ($p) => [$p->materia_id . '|' . $p->aluno_id => (int) $p->total]);

        $matriculas = DB::table('aluno_materia')
            ->join('alunos', 'alunos.id', '=', 'aluno_materia.aluno_id')
            ->whereNull('alunos.deleted_at')
            ->get(['aluno_materia.materia_id', 'aluno_materia.aluno_id', 'prova1', 'trabalho1', 'trabalho2', 'prova2'])
            ->groupBy('materia_id');

        return $materias->map(function ($materia) use ($realizadas, $presencas, $matriculas) {
            $doMateria = $matriculas[$materia->id] ?? collect();
            $aulas = (int) ($realizadas[$materia->id] ?? 0);

            $situacoes = $doMateria->map(fn ($m) => new SituacaoAcademica(
                aulasRealizadas: $aulas,
                presencas: $presencas[$materia->id . '|' . $m->aluno_id] ?? 0,
                aulasPrevistas: (int) ($materia->total_aulas ?? 0),
                notas: [$m->prova1, $m->trabalho1, $m->trabalho2, $m->prova2],
            ));

            return [
                'materia_id'       => $materia->id,
                'materia'          => $materia->nome,
                'alunos'           => $doMateria->count(),
                'aulas_realizadas' => $aulas,
                'frequencia_media' => $situacoes->isNotEmpty() ? (int) round($situacoes->avg('frequencia')) : 100,
                'em_risco'         => $situacoes->whereIn('status', ['reprovado_falta', 'atencao'])->count(),
                'situacoes'        => array_merge(array_fill_keys(self::STATUS, 0), $situacoes->countBy('status')->all()),
            ];
        })->values()->all();
    }

    /**
     * Soma das situações de todas as matrículas (gráfico de rosca do Master).
     *
     * @param  list<array{situacoes: array<string, int>}>  $turmas  resultado de geral()
     * @return array<string, int>
     */
    public static function situacoesTotais(array $turmas): array
    {
        $total = array_fill_keys(self::STATUS, 0);

        foreach ($turmas as $turma) {
            foreach ($turma['situacoes'] as $status => $qtd) {
                $total[$status] += $qtd;
            }
        }

        return $total;
    }
}
