<?php

namespace App\Support;

use App\Models\Materia;
use App\Models\Presenca;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Options;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Planilha .xlsx das presenças, gerada em fluxo (pouca memória).
 *
 * Aba "Presenças": um registro por linha, nos filtros da tela.
 * Aba "Resumo": situação de cada aluno nas matérias que aparecem no resultado,
 * calculada pelo SituacaoAcademica.
 *
 * Privacidade: só nome e RA. CPF e e-mail nunca entram na planilha.
 */
class ExportadorPresencas
{
    public const CONTENT_TYPE = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

    private const ORIGEM = [
        Presenca::ORIGEM_QR            => 'QR Code',
        Presenca::ORIGEM_CORRECAO      => 'Correção do professor',
        Presenca::ORIGEM_JUSTIFICATIVA => 'Falta abonada',
    ];

    private const HORARIO = ['M' => 'Matutino', 'V' => 'Vespertino', 'N' => 'Noturno'];

    public static function baixar(Builder $presencas, string $prefixo = 'presencas'): StreamedResponse
    {
        $nome = $prefixo . '-' . now()->format('Y-m-d') . '.xlsx';
        $materiaIds = (clone $presencas)->reorder()->distinct()->pluck('materia_id');

        return response()->streamDownload(function () use ($presencas, $materiaIds) {
            $opcoes = new Options();
            foreach ([1 => 32, 2 => 14, 3 => 30, 4 => 14, 5 => 13, 6 => 22, 7 => 18, 8 => 20] as $coluna => $largura) {
                $opcoes->setColumnWidth($largura, $coluna);
            }

            $writer = new Writer($opcoes);
            $writer->openToFile('php://output');
            $cabecalho = (new Style())->setFontBold();

            $writer->getCurrentSheet()->setName('Presenças');
            $writer->addRow(Row::fromValues(['Aluno', 'RA', 'Matéria', 'Data da aula', 'Horário', 'Origem', 'Registrado em'], $cabecalho));

            (clone $presencas)->with(['aluno', 'materia'])->chunk(500, function ($lote) use ($writer) {
                foreach ($lote as $p) {
                    $writer->addRow(Row::fromValues([
                        $p->aluno?->nome ?? 'Aluno removido',
                        (string) ($p->aluno?->ra ?? ''),
                        $p->materia?->nome ?? '',
                        \Carbon\Carbon::parse($p->data_aula)->format('d/m/Y'),
                        self::HORARIO[$p->horario] ?? '',
                        self::ORIGEM[$p->origem] ?? $p->origem,
                        $p->created_at?->format('d/m/Y H:i') ?? '',
                    ]));
                }
            });

            $writer->addNewSheetAndMakeItCurrent()->setName('Resumo');
            $writer->addRow(Row::fromValues(['Aluno', 'RA', 'Matéria', 'Presenças', 'Faltas', 'Limite de faltas', 'Frequência (%)', 'Situação'], $cabecalho));

            foreach (self::resumo($materiaIds->all()) as $linha) {
                $writer->addRow(Row::fromValues($linha));
            }

            $writer->close();
        }, $nome, ['Content-Type' => self::CONTENT_TYPE]);
    }

    /** @return iterable<list<mixed>> */
    private static function resumo(array $materiaIds): iterable
    {
        if (! $materiaIds) {
            return;
        }

        $realizadas = Presenca::whereIn('materia_id', $materiaIds)
            ->select('materia_id', DB::raw('COUNT(DISTINCT codigo_aula) as total'))
            ->groupBy('materia_id')
            ->pluck('total', 'materia_id');

        $porAluno = Presenca::whereIn('materia_id', $materiaIds)
            ->select('materia_id', 'aluno_id', DB::raw('COUNT(*) as total'))
            ->groupBy('materia_id', 'aluno_id')
            ->get()
            ->mapWithKeys(fn ($p) => [$p->materia_id . '|' . $p->aluno_id => (int) $p->total]);

        $materias = Materia::with(['alunos' => fn ($q) => $q->orderBy('nome')])
            ->whereIn('id', $materiaIds)
            ->orderBy('nome')
            ->get();

        foreach ($materias as $materia) {
            foreach ($materia->alunos as $aluno) {
                $s = new SituacaoAcademica(
                    aulasRealizadas: (int) ($realizadas[$materia->id] ?? 0),
                    presencas: $porAluno[$materia->id . '|' . $aluno->id] ?? 0,
                    aulasPrevistas: (int) ($materia->total_aulas ?? 0),
                    notas: [$aluno->pivot->prova1, $aluno->pivot->trabalho1, $aluno->pivot->trabalho2, $aluno->pivot->prova2],
                );

                yield [$aluno->nome, (string) $aluno->ra, $materia->nome, $s->presencas, $s->faltas, $s->limiteFaltas, $s->frequencia, $s->rotulo()];
            }
        }
    }
}
