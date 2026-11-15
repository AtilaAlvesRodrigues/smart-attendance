<?php

namespace Tests\Concerns;

use App\Models\AlunoModel;
use App\Models\Materia;
use App\Models\Presenca;
use App\Models\ProfessorModel;
use Illuminate\Support\Facades\DB;

/**
 * Monta professor, matéria, alunos matriculados e chamadas para os testes
 * das funcionalidades de presença (correção, justificativa, gráficos, exportação).
 */
trait CriaCenarioAcademico
{
    protected function professorComMateria(int $totalAulas = 40): array
    {
        $professor = ProfessorModel::factory()->create(['remember_token' => null]);
        $materia = Materia::factory()->create(['total_aulas' => $totalAulas]);
        DB::table('materia_professor')->insert(['professor_id' => $professor->id, 'materia_id' => $materia->id]);

        return [$professor, $materia];
    }

    protected function alunoMatriculado(Materia $materia, array $atributos = []): AlunoModel
    {
        $aluno = AlunoModel::factory()->create(array_merge(['remember_token' => null], $atributos));
        DB::table('aluno_materia')->insert(['aluno_id' => $aluno->id, 'materia_id' => $materia->id]);

        return $aluno;
    }

    /**
     * Registra uma chamada (aula) com os alunos presentes. Uma aula só "existe"
     * quando há ao menos uma presença com o código dela.
     *
     * @param  array<int, AlunoModel>  $presentes
     */
    protected function registrarAula(Materia $materia, ProfessorModel $professor, array $presentes, string $data = '2026-11-10', ?string $codigo = null): string
    {
        $codigo ??= $materia->id . '-' . strtotime($data) . '-' . substr(md5(uniqid('', true)), 0, 4);

        foreach ($presentes as $aluno) {
            Presenca::create([
                'aluno_id'     => $aluno->id,
                'professor_id' => $professor->id,
                'materia_id'   => $materia->id,
                'data_aula'    => $data,
                'semestre'     => '2/2026',
                'horario'      => 'N',
                'codigo_aula'  => $codigo,
            ]);
        }

        return $codigo;
    }
}
