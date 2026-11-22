<?php

namespace App\Support;

use App\Mail\AvisoFrequenciaMail;
use App\Models\AlunoModel;
use App\Models\Materia;
use App\Models\Presenca;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Aviso por e-mail ao aluno cuja matéria entrou em Atenção por faltas ou
 * frequência (issue #10). No máximo um aviso por aluno e matéria a cada
 * 7 dias. Roda pelo cron da Vercel (/cron/avisos-frequencia) ou por
 * `php artisan frequencia:avisar`.
 *
 * O aviso só é registrado depois do envio: se o e-mail falhar, a próxima
 * rodada tenta de novo, e os outros alunos seguem sendo avisados.
 */
class AvisosFrequencia
{
    public const INTERVALO_DIAS = 7;

    /** @return int quantidade de avisos enviados */
    public static function enviar(): int
    {
        $realizadas = Presenca::select('materia_id', DB::raw('COUNT(DISTINCT codigo_aula) as total'))
            ->groupBy('materia_id')
            ->pluck('total', 'materia_id');

        $presencas = Presenca::select('materia_id', 'aluno_id', DB::raw('COUNT(*) as total'))
            ->groupBy('materia_id', 'aluno_id')
            ->get()
            ->mapWithKeys(fn ($p) => [$p->materia_id . '|' . $p->aluno_id => (int) $p->total]);

        // Matrículas de alunos e matérias não excluídos
        $matriculas = DB::table('aluno_materia')
            ->join('alunos', 'alunos.id', '=', 'aluno_materia.aluno_id')
            ->join('materias', 'materias.id', '=', 'aluno_materia.materia_id')
            ->whereNull('alunos.deleted_at')
            ->whereNull('materias.deleted_at')
            ->get(['aluno_materia.aluno_id', 'aluno_materia.materia_id', 'materias.total_aulas',
                'prova1', 'trabalho1', 'trabalho2', 'prova2']);

        $avisadosRecentemente = DB::table('avisos_frequencia')
            ->where('enviado_em', '>', now()->subDays(self::INTERVALO_DIAS))
            ->get(['aluno_id', 'materia_id'])
            ->mapWithKeys(fn ($a) => [$a->materia_id . '|' . $a->aluno_id => true]);

        $alertas = $matriculas
            ->map(fn ($m) => [$m, new SituacaoAcademica(
                aulasRealizadas: (int) ($realizadas[$m->materia_id] ?? 0),
                presencas: $presencas[$m->materia_id . '|' . $m->aluno_id] ?? 0,
                aulasPrevistas: (int) ($m->total_aulas ?? 0),
                notas: [$m->prova1, $m->trabalho1, $m->trabalho2, $m->prova2],
            )])
            ->filter(fn ($par) => $par[1]->alertaDeFrequencia()
                && ! isset($avisadosRecentemente[$par[0]->materia_id . '|' . $par[0]->aluno_id]))
            ->values();

        if ($alertas->isEmpty()) {
            return 0;
        }

        $alunos = AlunoModel::whereIn('id', $alertas->pluck('0.aluno_id')->unique())->get()->keyBy('id');
        $materias = Materia::whereIn('id', $alertas->pluck('0.materia_id')->unique())->get()->keyBy('id');
        $enviados = 0;

        foreach ($alertas as [$m, $situacao]) {
            $aluno = $alunos[$m->aluno_id] ?? null;
            $materia = $materias[$m->materia_id] ?? null;

            if (! $aluno || ! $materia || ! $aluno->email) {
                continue;
            }

            try {
                Mail::to($aluno->email)->send(new AvisoFrequenciaMail($aluno, $materia, $situacao));
            } catch (\Throwable $e) {
                // Só a classe: o texto do servidor pode trazer endereços de e-mail
                Log::warning('Falha no aviso de baixa frequência', [
                    'aluno_id'   => $aluno->id,
                    'materia_id' => $materia->id,
                    'excecao'    => $e::class,
                ]);

                continue;
            }

            DB::table('avisos_frequencia')->insert([
                'aluno_id'   => $aluno->id,
                'materia_id' => $materia->id,
                'faltas'     => $situacao->faltas,
                'frequencia' => $situacao->frequencia,
                'enviado_em' => now(),
            ]);
            $enviados++;
        }

        return $enviados;
    }
}
