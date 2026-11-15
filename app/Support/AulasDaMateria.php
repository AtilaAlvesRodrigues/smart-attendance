<?php

namespace App\Support;

use App\Models\Presenca;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Aulas (chamadas) já realizadas em uma matéria.
 *
 * Uma aula é identificada pelo codigo_aula do QR Code e só existe a partir da
 * primeira presença registrada com aquele código — a mesma definição de
 * "aula realizada" usada pelo SituacaoAcademica.
 */
class AulasDaMateria
{
    /**
     * @return Collection<int, object{codigo_aula: string, data_aula: string, horario: ?string, semestre: ?string, professor_id: int, presentes: int}>
     */
    public static function listar(int $materiaId): Collection
    {
        return self::consulta($materiaId)
            ->addSelect(DB::raw('COUNT(*) as presentes'))
            ->orderByDesc(DB::raw('MIN(data_aula)'))
            ->orderByDesc(DB::raw('MIN(created_at)'))
            ->get()
            ->map(fn ($aula) => self::normalizar($aula));
    }

    /**
     * Aulas da matéria em que o aluno não tem presença.
     *
     * @return Collection<int, object>
     */
    public static function faltasDoAluno(int $materiaId, int $alunoId): Collection
    {
        $presentes = Presenca::where('materia_id', $materiaId)
            ->where('aluno_id', $alunoId)
            ->pluck('codigo_aula');

        return self::consulta($materiaId)
            ->whereNotIn('codigo_aula', $presentes)
            ->orderByDesc(DB::raw('MIN(data_aula)'))
            ->get()
            ->map(fn ($aula) => self::normalizar($aula))
            ->values();
    }

    public static function aula(int $materiaId, string $codigo): ?object
    {
        $aula = self::consulta($materiaId)->where('codigo_aula', $codigo)->first();

        return $aula ? self::normalizar($aula) : null;
    }

    private static function consulta(int $materiaId)
    {
        return Presenca::query()
            ->where('materia_id', $materiaId)
            ->select(
                'codigo_aula',
                DB::raw('MIN(data_aula) as data_aula'),
                DB::raw('MIN(horario) as horario'),
                DB::raw('MIN(semestre) as semestre'),
                DB::raw('MIN(professor_id) as professor_id'),
            )
            ->groupBy('codigo_aula');
    }

    private static function normalizar($aula): object
    {
        return (object) [
            'codigo_aula'  => $aula->codigo_aula,
            'data_aula'    => \Carbon\Carbon::parse($aula->data_aula)->toDateString(),
            'horario'      => $aula->horario,
            'semestre'     => $aula->semestre,
            'professor_id' => (int) $aula->professor_id,
            'presentes'    => isset($aula->presentes) ? (int) $aula->presentes : null,
        ];
    }
}
