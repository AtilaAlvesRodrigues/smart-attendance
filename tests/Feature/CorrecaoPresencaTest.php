<?php

namespace Tests\Feature;

use App\Models\Presenca;
use App\Models\PresencaAjuste;
use App\Support\SituacaoAcademica;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CriaCenarioAcademico;
use Tests\TestCase;

class CorrecaoPresencaTest extends TestCase
{
    use RefreshDatabase, CriaCenarioAcademico;

    private function url($materia, string $codigo): string
    {
        return "/professor/gerenciar/{$materia->id}/chamadas/{$codigo}/presenca";
    }

    public function test_professor_marca_presenca_com_motivo(): void
    {
        [$professor, $materia] = $this->professorComMateria();
        $presente = $this->alunoMatriculado($materia);
        $faltou = $this->alunoMatriculado($materia);
        $codigo = $this->registrarAula($materia, $professor, [$presente]);

        $this->actingAs($professor, 'professores')
            ->post($this->url($materia, $codigo), ['aluno_id' => $faltou->id, 'presente' => '1', 'motivo' => 'Celular sem bateria'])
            ->assertRedirect();

        $presenca = Presenca::where('aluno_id', $faltou->id)->where('codigo_aula', $codigo)->first();
        $this->assertNotNull($presenca);
        $this->assertSame(Presenca::ORIGEM_CORRECAO, $presenca->origem);
        $this->assertSame('2026-11-10', \Carbon\Carbon::parse($presenca->data_aula)->toDateString());

        $ajuste = PresencaAjuste::first();
        $this->assertSame('marcou', $ajuste->acao);
        $this->assertSame('Celular sem bateria', $ajuste->motivo);
        $this->assertSame('professor', $ajuste->autor_tipo);
        $this->assertSame($professor->id, $ajuste->autor_id);
    }

    public function test_professor_desmarca_presenca(): void
    {
        [$professor, $materia] = $this->professorComMateria();
        $a1 = $this->alunoMatriculado($materia);
        $a2 = $this->alunoMatriculado($materia);
        $codigo = $this->registrarAula($materia, $professor, [$a1, $a2]);
        $presencaId = Presenca::where('aluno_id', $a2->id)->value('id');

        $this->actingAs($professor, 'professores')
            ->post($this->url($materia, $codigo), ['aluno_id' => $a2->id, 'presente' => '0', 'motivo' => 'Saiu antes da chamada'])
            ->assertRedirect();

        $this->assertDatabaseMissing('presencas', ['id' => $presencaId]);
        $this->assertDatabaseHas('presenca_ajustes', ['presenca_id' => $presencaId, 'acao' => 'desmarcou', 'aluno_id' => $a2->id]);
    }

    public function test_motivo_obrigatorio(): void
    {
        [$professor, $materia] = $this->professorComMateria();
        $aluno = $this->alunoMatriculado($materia);
        $codigo = $this->registrarAula($materia, $professor, [$aluno]);

        $this->actingAs($professor, 'professores')
            ->postJson($this->url($materia, $codigo), ['aluno_id' => $aluno->id, 'presente' => '0'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['motivo']);
    }

    public function test_professor_de_outra_materia_recebe_403(): void
    {
        [$professor, $materia] = $this->professorComMateria();
        [$outro] = $this->professorComMateria();
        $aluno = $this->alunoMatriculado($materia);
        $codigo = $this->registrarAula($materia, $professor, [$aluno]);

        $this->actingAs($outro, 'professores')
            ->post($this->url($materia, $codigo), ['aluno_id' => $aluno->id, 'presente' => '0', 'motivo' => 'x'])
            ->assertStatus(403);

        $this->actingAs($outro, 'professores')
            ->get("/professor/gerenciar/{$materia->id}/chamadas")
            ->assertStatus(403);

        $this->assertSame(1, Presenca::count());
    }

    public function test_aluno_nao_matriculado_422(): void
    {
        [$professor, $materia] = $this->professorComMateria();
        [, $outraMateria] = $this->professorComMateria();
        $aluno = $this->alunoMatriculado($materia);
        $deFora = $this->alunoMatriculado($outraMateria);
        $codigo = $this->registrarAula($materia, $professor, [$aluno]);

        $this->actingAs($professor, 'professores')
            ->postJson($this->url($materia, $codigo), ['aluno_id' => $deFora->id, 'presente' => '1', 'motivo' => 'teste'])
            ->assertStatus(422);
    }

    public function test_aula_inexistente_404(): void
    {
        [$professor, $materia] = $this->professorComMateria();
        $aluno = $this->alunoMatriculado($materia);

        $this->actingAs($professor, 'professores')
            ->post($this->url($materia, 'codigo-que-nao-existe'), ['aluno_id' => $aluno->id, 'presente' => '1', 'motivo' => 'teste'])
            ->assertStatus(404);
    }

    public function test_correcao_altera_situacao(): void
    {
        [$professor, $materia] = $this->professorComMateria();
        $presente = $this->alunoMatriculado($materia);
        $faltou = $this->alunoMatriculado($materia);
        $codigo = $this->registrarAula($materia, $professor, [$presente]);

        $this->assertSame(1, SituacaoAcademica::doAluno($materia->id, $faltou->id, 40)->faltas);

        $this->actingAs($professor, 'professores')
            ->post($this->url($materia, $codigo), ['aluno_id' => $faltou->id, 'presente' => '1', 'motivo' => 'Erro no QR']);

        $this->assertSame(0, SituacaoAcademica::doAluno($materia->id, $faltou->id, 40)->faltas);
    }

    public function test_tela_de_chamadas_lista_aulas_e_alunos(): void
    {
        [$professor, $materia] = $this->professorComMateria();
        $aluno = $this->alunoMatriculado($materia, ['nome' => 'Beatriz Lima']);
        $codigo = $this->registrarAula($materia, $professor, [$aluno], '2026-11-03');

        $this->actingAs($professor, 'professores')
            ->get("/professor/gerenciar/{$materia->id}/chamadas")
            ->assertOk()
            ->assertSee('03/11/2026');

        $this->actingAs($professor, 'professores')
            ->get("/professor/gerenciar/{$materia->id}/chamadas/{$codigo}")
            ->assertOk()
            ->assertSee('Beatriz Lima');
    }

    public function test_nao_remove_a_ultima_presenca_da_aula(): void
    {
        [$professor, $materia] = $this->professorComMateria();
        $unico = $this->alunoMatriculado($materia);
        $outro = $this->alunoMatriculado($materia);
        $codigo = $this->registrarAula($materia, $professor, [$unico]);

        $this->actingAs($professor, 'professores')
            ->postJson($this->url($materia, $codigo), ['aluno_id' => $unico->id, 'presente' => '0', 'motivo' => 'Engano'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['presente']);

        $this->assertSame(1, Presenca::where('codigo_aula', $codigo)->count());
        $this->assertSame(1, SituacaoAcademica::doAluno($materia->id, $outro->id, 40)->aulasRealizadas);
    }

    public function test_banco_impede_presenca_duplicada_na_mesma_aula(): void
    {
        [$professor, $materia] = $this->professorComMateria();
        $aluno = $this->alunoMatriculado($materia);
        $codigo = $this->registrarAula($materia, $professor, [$aluno]);

        $this->expectException(\Illuminate\Database\UniqueConstraintViolationException::class);
        $this->registrarAula($materia, $professor, [$aluno], '2026-11-10', $codigo);
    }
}
