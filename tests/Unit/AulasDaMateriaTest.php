<?php

namespace Tests\Unit;

use App\Support\AulasDaMateria;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CriaCenarioAcademico;
use Tests\TestCase;

class AulasDaMateriaTest extends TestCase
{
    use RefreshDatabase, CriaCenarioAcademico;

    public function test_lista_uma_linha_por_aula_com_total_de_presentes(): void
    {
        [$professor, $materia] = $this->professorComMateria();
        $a1 = $this->alunoMatriculado($materia);
        $a2 = $this->alunoMatriculado($materia);
        $this->registrarAula($materia, $professor, [$a1, $a2], '2026-11-03');
        $this->registrarAula($materia, $professor, [$a1], '2026-11-10');

        $aulas = AulasDaMateria::listar($materia->id);

        $this->assertCount(2, $aulas);
        $this->assertSame('2026-11-10', $aulas[0]->data_aula);
        $this->assertSame(1, $aulas[0]->presentes);
        $this->assertSame(2, $aulas[1]->presentes);
    }

    public function test_faltas_do_aluno_exclui_aulas_com_presenca(): void
    {
        [$professor, $materia] = $this->professorComMateria();
        $a1 = $this->alunoMatriculado($materia);
        $a2 = $this->alunoMatriculado($materia);
        $this->registrarAula($materia, $professor, [$a1, $a2], '2026-11-03');
        $faltou = $this->registrarAula($materia, $professor, [$a1], '2026-11-10');

        $faltas = AulasDaMateria::faltasDoAluno($materia->id, $a2->id);

        $this->assertCount(1, $faltas);
        $this->assertSame($faltou, $faltas[0]->codigo_aula);
        $this->assertCount(0, AulasDaMateria::faltasDoAluno($materia->id, $a1->id));
    }

    public function test_aula_retorna_null_para_codigo_de_outra_materia(): void
    {
        [$professor, $materia] = $this->professorComMateria();
        [$p2, $outra] = $this->professorComMateria();
        $a = $this->alunoMatriculado($outra);
        $codigo = $this->registrarAula($outra, $p2, [$a]);

        $this->assertNull(AulasDaMateria::aula($materia->id, $codigo));
        $this->assertNotNull(AulasDaMateria::aula($outra->id, $codigo));
    }
}
