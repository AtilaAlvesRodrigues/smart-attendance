<?php

namespace Tests\Feature;

use App\Models\UsuarioMaster;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CriaCenarioAcademico;
use Tests\TestCase;

class GraficosTest extends TestCase
{
    use RefreshDatabase, CriaCenarioAcademico;

    public function test_professor_ve_graficos_da_sua_materia(): void
    {
        [$professor, $materia] = $this->professorComMateria();
        $a = $this->alunoMatriculado($materia);
        $this->registrarAula($materia, $professor, [$a]);

        $this->actingAs($professor, 'professores')
            ->get("/professor/gerenciar/{$materia->id}/graficos")
            ->assertOk()
            ->assertSee('js/vendor/chart.umd.js', false);

        $this->actingAs($professor, 'professores')
            ->getJson("/professor/gerenciar/{$materia->id}/graficos.json")
            ->assertOk()
            ->assertJsonStructure(['frequencia_por_aula', 'situacoes', 'em_risco', 'frequencia_media']);
    }

    public function test_json_professor_outra_materia_403(): void
    {
        [, $materia] = $this->professorComMateria();
        [$outro] = $this->professorComMateria();

        $this->actingAs($outro, 'professores')
            ->getJson("/professor/gerenciar/{$materia->id}/graficos.json")
            ->assertStatus(403);
    }

    public function test_json_master_lista_turmas(): void
    {
        [$professor, $materia] = $this->professorComMateria();
        $a = $this->alunoMatriculado($materia);
        $this->registrarAula($materia, $professor, [$a]);
        $master = UsuarioMaster::factory()->create();

        $this->actingAs($master, 'masters')
            ->getJson('/dashboard/master/graficos.json')
            ->assertOk()
            ->assertJsonFragment(['materia' => $materia->nome, 'frequencia_media' => 100]);

        $this->actingAs($master, 'masters')->get('/dashboard/master/graficos')->assertOk();
    }

    public function test_aluno_nao_acessa_graficos_do_master(): void
    {
        [, $materia] = $this->professorComMateria();
        $aluno = $this->alunoMatriculado($materia);

        $resposta = $this->actingAs($aluno, 'alunos')->getJson('/dashboard/master/graficos.json');

        $this->assertContains($resposta->status(), [302, 401, 403]);
        $this->assertStringNotContainsString($materia->nome, $resposta->getContent());
    }
}
