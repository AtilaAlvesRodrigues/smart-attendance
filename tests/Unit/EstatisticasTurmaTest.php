<?php

namespace Tests\Unit;

use App\Support\EstatisticasTurma;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CriaCenarioAcademico;
use Tests\TestCase;

class EstatisticasTurmaTest extends TestCase
{
    use RefreshDatabase, CriaCenarioAcademico;

    public function test_estatisticas_da_materia(): void
    {
        // 4 aulas previstas → limite de 1 falta
        [$professor, $materia] = $this->professorComMateria(totalAulas: 4);
        $assidua = $this->alunoMatriculado($materia, ['nome' => 'Ana Assídua']);
        $faltosa = $this->alunoMatriculado($materia, ['nome' => 'Bia Faltosa']);
        $this->registrarAula($materia, $professor, [$assidua, $faltosa], '2026-11-03');
        $this->registrarAula($materia, $professor, [$assidua], '2026-11-04');
        $this->registrarAula($materia, $professor, [$assidua], '2026-11-05');

        $e = EstatisticasTurma::daMateria($materia);

        $this->assertSame([
            ['data' => '2026-11-03', 'percentual' => 100],
            ['data' => '2026-11-04', 'percentual' => 50],
            ['data' => '2026-11-05', 'percentual' => 50],
        ], $e['frequencia_por_aula']);

        $this->assertSame(1, $e['situacoes']['reprovado_falta']);
        $this->assertSame(1, $e['situacoes']['regular']);
        $this->assertSame(67, $e['frequencia_media']); // (100 + 33) / 2

        $this->assertCount(1, $e['em_risco']);
        $this->assertSame('Bia Faltosa', $e['em_risco'][0]['nome']);
        $this->assertSame(2, $e['em_risco'][0]['faltas']);
        $this->assertSame(1, $e['em_risco'][0]['limite']);
    }

    public function test_materia_sem_aulas_nao_quebra(): void
    {
        [, $materia] = $this->professorComMateria();
        $this->alunoMatriculado($materia);

        $e = EstatisticasTurma::daMateria($materia);

        $this->assertSame([], $e['frequencia_por_aula']);
        $this->assertSame([], $e['em_risco']);
        $this->assertSame(100, $e['frequencia_media']);
    }

    public function test_geral_lista_cada_turma(): void
    {
        [$p1, $m1] = $this->professorComMateria();
        [$p2, $m2] = $this->professorComMateria();
        $a = $this->alunoMatriculado($m1);
        $b = $this->alunoMatriculado($m1);
        $c = $this->alunoMatriculado($m2);
        $this->registrarAula($m1, $p1, [$a]);
        $this->registrarAula($m2, $p2, [$c]);

        $geral = collect(EstatisticasTurma::geral())->keyBy('materia_id');

        $this->assertSame(50, $geral[$m1->id]['frequencia_media']);
        $this->assertSame(2, $geral[$m1->id]['alunos']);
        $this->assertSame(100, $geral[$m2->id]['frequencia_media']);
    }
}
