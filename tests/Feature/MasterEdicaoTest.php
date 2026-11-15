<?php

namespace Tests\Feature;

use App\Models\AlunoModel;
use App\Models\Materia;
use App\Models\ProfessorModel;
use App\Models\UsuarioMaster;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterEdicaoTest extends TestCase
{
    use RefreshDatabase;

    private UsuarioMaster $master;

    protected function setUp(): void
    {
        parent::setUp();
        $this->master = UsuarioMaster::factory()->create();
    }

    public function test_tela_de_edicao_mostra_dados_atuais(): void
    {
        $aluno = AlunoModel::factory()->create(['nome' => 'Carla Mendes', 'ra' => '20251234']);

        $this->actingAs($this->master, 'masters')
            ->get("/dashboard/master/aluno/{$aluno->id}/editar")
            ->assertOk()
            ->assertSee('Carla Mendes')
            ->assertSee('20251234');
    }

    public function test_editar_aluno_atualiza_blind_index(): void
    {
        $aluno = AlunoModel::factory()->create(['email' => 'antigo@teste.com']);

        $this->actingAs($this->master, 'masters')
            ->put("/dashboard/master/aluno/{$aluno->id}", [
                'nome' => 'Nome Novo', 'email' => 'novo@teste.com', 'cpf' => $aluno->cpf, 'ra' => $aluno->ra,
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertSame('Nome Novo', $aluno->fresh()->nome);
        $this->assertTrue(AlunoModel::where('email_search', AlunoModel::generateBlindIndex('novo@teste.com'))->exists());
        $this->assertFalse(AlunoModel::where('email_search', AlunoModel::generateBlindIndex('antigo@teste.com'))->exists());
    }

    public function test_editar_aluno_email_duplicado(): void
    {
        AlunoModel::factory()->create(['email' => 'ocupado@teste.com']);
        $aluno = AlunoModel::factory()->create();

        $this->actingAs($this->master, 'masters')
            ->put("/dashboard/master/aluno/{$aluno->id}", [
                'nome' => $aluno->nome, 'email' => 'OCUPADO@teste.com', 'cpf' => $aluno->cpf, 'ra' => $aluno->ra,
            ])
            ->assertSessionHasErrors('email');

        $this->assertNotSame('ocupado@teste.com', $aluno->fresh()->email);
    }

    public function test_manter_o_proprio_email_nao_conta_como_duplicado(): void
    {
        $aluno = AlunoModel::factory()->create(['email' => 'meu@teste.com']);

        $this->actingAs($this->master, 'masters')
            ->put("/dashboard/master/aluno/{$aluno->id}", [
                'nome' => 'Outro Nome', 'email' => 'meu@teste.com', 'cpf' => $aluno->cpf, 'ra' => $aluno->ra,
            ])
            ->assertSessionHasNoErrors();
    }

    public function test_editar_nao_altera_role_nem_senha(): void
    {
        $professor = ProfessorModel::factory()->create(['cpf' => '52998224725']);
        $senha = $professor->password;

        $this->actingAs($this->master, 'masters')
            ->put("/dashboard/master/professor/{$professor->id}", [
                'nome' => 'Prof Editado', 'email' => 'prof.editado@teste.com', 'cpf' => '52998224725',
                'role' => 'master', 'password' => 'hackeada',
            ])
            ->assertSessionHasNoErrors();

        $professor->refresh();
        $this->assertSame('Prof Editado', $professor->nome);
        $this->assertSame('professor', $professor->role);
        $this->assertSame($senha, $professor->password);
    }

    public function test_editar_professor_cpf_invalido(): void
    {
        $professor = ProfessorModel::factory()->create();

        $this->actingAs($this->master, 'masters')
            ->put("/dashboard/master/professor/{$professor->id}", ['nome' => 'X', 'email' => 'x@teste.com', 'cpf' => '111'])
            ->assertSessionHasErrors('cpf');
    }

    public function test_editar_materia_nome_duplicado(): void
    {
        Materia::factory()->create(['nome' => 'Cálculo III']);
        $materia = Materia::factory()->create();

        $this->actingAs($this->master, 'masters')
            ->put("/dashboard/master/materia/{$materia->id}", ['nome' => 'Cálculo III', 'sala' => '10', 'carga_horaria' => 60, 'total_aulas' => 30])
            ->assertSessionHasErrors('nome');
    }

    public function test_editar_materia_total_aulas(): void
    {
        $materia = Materia::factory()->create(['total_aulas' => 20]);

        $this->actingAs($this->master, 'masters')
            ->put("/dashboard/master/materia/{$materia->id}", ['nome' => $materia->nome, 'sala' => 'B2', 'carga_horaria' => 80, 'total_aulas' => 36])
            ->assertSessionHasNoErrors();

        $this->assertSame(36, (int) $materia->fresh()->total_aulas);
        $this->assertSame('B2', $materia->fresh()->sala);
    }

    public function test_excluir_aluno_soft_delete(): void
    {
        $aluno = AlunoModel::factory()->create();

        $this->actingAs($this->master, 'masters')
            ->delete("/dashboard/master/aluno/{$aluno->id}")
            ->assertRedirect(route('master.alunos'));

        $this->assertSoftDeleted('alunos', ['id' => $aluno->id]);
    }

    public function test_excluir_materia_soft_delete(): void
    {
        $materia = Materia::factory()->create();

        $this->actingAs($this->master, 'masters')
            ->delete("/dashboard/master/materia/{$materia->id}")
            ->assertRedirect(route('master.materias'));

        $this->assertSoftDeleted('materias', ['id' => $materia->id]);
    }

    public function test_professor_nao_edita(): void
    {
        $professor = ProfessorModel::factory()->create();
        $aluno = AlunoModel::factory()->create(['nome' => 'Intocado']);

        $this->actingAs($professor, 'professores')
            ->put("/dashboard/master/aluno/{$aluno->id}", ['nome' => 'Hack', 'email' => 'h@h.com', 'cpf' => '1', 'ra' => '1']);
        $this->actingAs($professor, 'professores')->delete("/dashboard/master/aluno/{$aluno->id}");

        $this->assertSame('Intocado', $aluno->fresh()->nome);
        $this->assertNotSoftDeleted('alunos', ['id' => $aluno->id]);
    }

    public function test_tipo_invalido_404(): void
    {
        $this->actingAs($this->master, 'masters')
            ->get('/dashboard/master/usuario/1/editar')
            ->assertStatus(404);
    }

    public function test_registro_inexistente_404(): void
    {
        $this->actingAs($this->master, 'masters')
            ->get('/dashboard/master/aluno/99999/editar')
            ->assertStatus(404);
    }
}
