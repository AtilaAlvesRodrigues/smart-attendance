<?php

namespace Tests\Feature;

use App\Models\AlunoModel;
use App\Models\Materia;
use App\Models\Presenca;
use App\Models\ProfessorModel;
use App\Models\UsuarioMaster;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class IntegracaoTabelasTest extends TestCase
{
    use RefreshDatabase;

    public function test_fluxo_completo_de_integracao_entre_as_tabelas(): void
    {
        $professor = ProfessorModel::factory()->create();
        $aluno = AlunoModel::factory()->create();
        $materia = Materia::factory()->create();

        $professor->materias()->attach($materia->id);
        $aluno->materias()->attach($materia->id);

        $this->assertDatabaseHas('materia_professor', [
            'professor_id' => $professor->id,
            'materia_id' => $materia->id,
        ]);

        $this->assertDatabaseHas('aluno_materia', [
            'aluno_id' => $aluno->id,
            'materia_id' => $materia->id,
        ]);

        $presenca = Presenca::create([
            'aluno_id' => $aluno->id,
            'professor_id' => $professor->id,
            'materia_id' => $materia->id,
            'data_aula' => now()->toDateString(),
            'semestre' => (now()->month <= 6 ? '1/' : '2/') . now()->year,
            'horario' => 'N',
            'codigo_aula' => 'TESTE-INTEGRACAO-001',
        ]);

        $this->assertDatabaseHas('presencas', [
            'id' => $presenca->id,
            'aluno_id' => $aluno->id,
            'professor_id' => $professor->id,
            'materia_id' => $materia->id,
            'codigo_aula' => 'TESTE-INTEGRACAO-001',
        ]);

        $presencaConsultada = Presenca::with([
            'aluno',
            'professor',
            'materia',
        ])->findOrFail($presenca->id);

        $this->assertEquals($aluno->id, $presencaConsultada->aluno->id);
        $this->assertEquals($professor->id, $presencaConsultada->professor->id);
        $this->assertEquals($materia->id, $presencaConsultada->materia->id);

        $this->assertEquals(
            'TESTE-INTEGRACAO-001',
            $presencaConsultada->codigo_aula
        );
    }

    public function test_atualizacao_de_nota_e_refletida_no_banco(): void
    {
        $professor = ProfessorModel::factory()->create();
        $aluno = AlunoModel::factory()->create();
        $materia = Materia::factory()->create();

        $professor->materias()->attach($materia->id);
        $aluno->materias()->attach($materia->id);

        DB::table('aluno_materia')
            ->where('aluno_id', $aluno->id)
            ->where('materia_id', $materia->id)
            ->update([
                'prova1' => 8.5,
                'updated_at' => now(),
            ]);

        $this->assertDatabaseHas('aluno_materia', [
            'aluno_id' => $aluno->id,
            'materia_id' => $materia->id,
            'prova1' => 8.5,
        ]);
    }

    public function test_controller_salva_nota_no_banco(): void
    {
        $professor = ProfessorModel::factory()->create();
        $aluno = AlunoModel::factory()->create();
        $materia = Materia::factory()->create();

        $professor->materias()->attach($materia->id);
        $aluno->materias()->attach($materia->id);

        $this->actingAs($professor, 'professores');

        $response = $this->postJson(
            "/professor/gerenciar/{$materia->id}/notas",
            [
                'aluno_ra' => $aluno->ra,
                'campo' => 'prova1',
                'valor' => 9,
            ]
        );

        $response->assertStatus(200);

        $this->assertDatabaseHas('aluno_materia', [
            'aluno_id' => $aluno->id,
            'materia_id' => $materia->id,
            'prova1' => 9,
        ]);
    }

    public function test_dados_do_banco_sao_recuperados_e_apresentados_na_tela_de_gerenciamento(): void
    {
        $professor = ProfessorModel::factory()->create();
        $aluno = AlunoModel::factory()->create();
        $materia = Materia::factory()->create([
            'total_aulas' => 40,
        ]);

        $professor->materias()->attach($materia->id);
        $aluno->materias()->attach($materia->id);

        $presenca = Presenca::create([
            'aluno_id' => $aluno->id,
            'professor_id' => $professor->id,
            'materia_id' => $materia->id,
            'data_aula' => now()->toDateString(),
            'semestre' => (now()->month <= 6 ? '1/' : '2/') . now()->year,
            'horario' => 'N',
            'codigo_aula' => 'TESTE-TELA-001',
        ]);

        $this->actingAs($professor, 'professores');

        $response = $this->get(
            "/professor/gerenciar/{$materia->id}"
        );

        $response->assertStatus(200);

        $response->assertViewIs('professor.gerenciar.materia');

        $response->assertViewHas('materia', function ($materiaDaView) use ($materia) {
            return $materiaDaView->id === $materia->id;
        });

        $response->assertViewHas('alunos', function ($alunos) use ($aluno) {
            return $alunos->contains('id', $aluno->id);
        });

        $response->assertViewHas('presencasPorAluno', function ($presencasPorAluno) use ($aluno) {
            return $presencasPorAluno[$aluno->id] === 1;
        });

        $response->assertViewHas('aulasRealizadas', 1);

        $this->assertDatabaseHas('presencas', [
            'id' => $presenca->id,
            'aluno_id' => $aluno->id,
            'materia_id' => $materia->id,
            'codigo_aula' => 'TESTE-TELA-001',
        ]);
    }

    public function test_cadastro_de_aluno_pela_aplicacao_e_refletido_no_banco(): void
    {
        $master = UsuarioMaster::factory()->create();

        $this->actingAs($master, 'masters');

        $response = $this->postJson('/dashboard/master/cadastrar/aluno', [
            'nome' => 'Aluno Integração',
            'email' => 'aluno.integracao@teste.com',
            'cpf' => '12345678901',
            'ra' => '12345678',
        ]);

        $response->assertStatus(200);

        $response->assertJson([
            'success' => true,
        ]);

        $aluno = AlunoModel::where(
            'email_search',
            AlunoModel::generateBlindIndex('aluno.integracao@teste.com')
        )->first();

        $this->assertNotNull($aluno);

        $this->assertDatabaseHas('alunos', [
            'id' => $aluno->id,
        ]);

        $this->assertEquals('Aluno Integração', $aluno->nome);
        $this->assertEquals('aluno.integracao@teste.com', $aluno->email);
        $this->assertEquals('12345678901', $aluno->cpf);
        $this->assertEquals('12345678', $aluno->ra);
    }
}