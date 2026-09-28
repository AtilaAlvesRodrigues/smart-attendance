<?php

namespace Tests\Feature;

use App\Models\AlunoModel;
use App\Models\Materia;
use App\Models\ProfessorModel;
use App\Models\UsuarioMaster;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MasterTurmaTest extends TestCase
{
    use RefreshDatabase;

    private function master(): UsuarioMaster
    {
        return UsuarioMaster::factory()->create();
    }

    public function test_master_ve_tela_da_turma(): void
    {
        $materia = Materia::factory()->create(['nome' => 'Algoritmos']);
        ProfessorModel::factory()->create(['nome' => 'Profa. Ana']);
        AlunoModel::factory()->create(['nome' => 'Bruno Lima']);

        $this->actingAs($this->master(), 'masters')
            ->get("/dashboard/master/materias/{$materia->id}/turma")
            ->assertOk()
            ->assertSee('Algoritmos')
            ->assertSee('Profa. Ana')
            ->assertSee('Bruno Lima');
    }

    public function test_professor_e_aluno_nao_acessam_a_turma(): void
    {
        $materia = Materia::factory()->create();

        $this->actingAs(ProfessorModel::factory()->create(), 'professores')
            ->get("/dashboard/master/materias/{$materia->id}/turma")
            ->assertRedirect();

        $this->actingAs(AlunoModel::factory()->create(), 'alunos')
            ->put("/dashboard/master/materias/{$materia->id}/turma", ['alunos' => []])
            ->assertRedirect();
    }

    public function test_master_define_professores_e_alunos(): void
    {
        $materia = Materia::factory()->create();
        $professor = ProfessorModel::factory()->create();
        [$a1, $a2] = AlunoModel::factory()->count(2)->create();

        $this->actingAs($this->master(), 'masters')
            ->put("/dashboard/master/materias/{$materia->id}/turma", [
                'professores' => [$professor->id],
                'alunos' => [$a1->id, $a2->id],
            ])
            ->assertRedirect("/dashboard/master/materias/{$materia->id}/turma")
            ->assertSessionHas('success');

        $this->assertEqualsCanonicalizing([$professor->id], $materia->professores()->pluck('professores.id')->all());
        $this->assertEqualsCanonicalizing([$a1->id, $a2->id], $materia->alunos()->pluck('alunos.id')->all());
    }

    public function test_quem_continua_matriculado_mantem_as_notas(): void
    {
        $materia = Materia::factory()->create();
        [$fica, $sai] = AlunoModel::factory()->count(2)->create();
        $materia->alunos()->attach($fica->id, ['prova1' => 8.5]);
        $materia->alunos()->attach($sai->id, ['prova1' => 6.0]);

        $this->actingAs($this->master(), 'masters')
            ->put("/dashboard/master/materias/{$materia->id}/turma", ['alunos' => [$fica->id]]);

        $this->assertEquals(8.5, DB::table('aluno_materia')->where('aluno_id', $fica->id)->value('prova1'));
        $this->assertFalse(DB::table('aluno_materia')->where('aluno_id', $sai->id)->exists());
    }

    public function test_ids_inexistentes_sao_recusados(): void
    {
        $materia = Materia::factory()->create();

        $this->actingAs($this->master(), 'masters')
            ->put("/dashboard/master/materias/{$materia->id}/turma", ['alunos' => [99999]])
            ->assertSessionHasErrors('alunos.0');

        $this->assertSame(0, $materia->alunos()->count());
    }

    public function test_aluno_novo_matriculado_pela_turma_consegue_registrar_presenca(): void
    {
        $materia = Materia::factory()->create();
        $professor = ProfessorModel::factory()->create();
        $aluno = AlunoModel::factory()->create();

        $this->actingAs($this->master(), 'masters')
            ->put("/dashboard/master/materias/{$materia->id}/turma", [
                'professores' => [$professor->id],
                'alunos' => [$aluno->id],
            ]);

        // O professor recém-vinculado abre a chamada...
        $codigo = $this->actingAs($professor, 'professores')
            ->get("/professor/presenca/gerar/{$materia->id}")
            ->assertOk()
            ->viewData('codigo_aula');

        // ...e o aluno recém-matriculado confirma a presença.
        $this->actingAs($aluno, 'alunos')
            ->get("/presenca/confirmar/{$codigo}")
            ->assertViewIs('aluno.presenca.sucesso');
    }

    public function test_lista_de_materias_tem_atalho_para_a_turma(): void
    {
        $materia = Materia::factory()->create();

        $this->actingAs($this->master(), 'masters')
            ->get('/dashboard/master/materias')
            ->assertOk()
            ->assertSee(route('master.turma', $materia), false)
            ->assertSee('Gerenciar turma');
    }
}
