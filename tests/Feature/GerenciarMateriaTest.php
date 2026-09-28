<?php

namespace Tests\Feature;

use App\Models\Materia;
use App\Models\ProfessorModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class GerenciarMateriaTest extends TestCase
{
    use RefreshDatabase;

    private function criarProfessor(): ProfessorModel
    {
        $professor = new ProfessorModel();

        $professor->cpf = '12345678900';
        $professor->nome = 'Professor Teste';
        $professor->email = 'professor@teste.com';
        $professor->password = Hash::make('password123');
        $professor->role = 'professor';

        $professor->save();

        return $professor;
    }

    private function criarMateria(string $nome = 'Matemática'): Materia
    {
        return Materia::create([
            'nome' => $nome,
            'sala' => '101',
            'carga_horaria' => 80,
            'total_aulas' => 40,
        ]);
    }

    public function test_gerenciar_materia_requires_professor_authentication(): void
    {
        $response = $this->get('/professor/gerenciar');

        $response->assertRedirect('/login');
    }

    public function test_professor_can_access_gerenciar_materia(): void
    {
        $professor = $this->criarProfessor();
        $materia = $this->criarMateria();

        DB::table('materia_professor')->insert([
            'professor_id' => $professor->id,
            'materia_id' => $materia->id,
        ]);

        $this->actingAs($professor, 'professores');

        $response = $this->get('/professor/gerenciar');

        $response->assertStatus(200);
    }

    public function test_professor_cannot_access_unrelated_subject(): void
    {
        $professor = $this->criarProfessor();
        $materia = $this->criarMateria();

        $this->actingAs($professor, 'professores');

        $response = $this->get("/professor/gerenciar/{$materia->id}");

        $response->assertStatus(403);
    }

    public function test_professor_can_access_subject_details(): void
    {
        $professor = $this->criarProfessor();
        $materia = $this->criarMateria();

        DB::table('materia_professor')->insert([
            'professor_id' => $professor->id,
            'materia_id' => $materia->id,
        ]);

        $this->actingAs($professor, 'professores');

        $response = $this->get("/professor/gerenciar/{$materia->id}");

        $response->assertStatus(200);
    }

    public function test_professor_cannot_save_grade_in_unrelated_subject(): void
    {
        $professor = $this->criarProfessor();
        $materia = $this->criarMateria();

        $this->actingAs($professor, 'professores');

        $response = $this->postJson(
            "/professor/gerenciar/{$materia->id}/notas",
            [
                'aluno_ra' => '123456',
                'campo' => 'prova1',
                'valor' => 8,
            ]
        );

        $response->assertStatus(403);
    }

    public function test_salvar_notas_requires_valid_fields(): void
    {
        $professor = $this->criarProfessor();
        $materia = $this->criarMateria();

        DB::table('materia_professor')->insert([
            'professor_id' => $professor->id,
            'materia_id' => $materia->id,
        ]);

        $this->actingAs($professor, 'professores');

        $response = $this->postJson(
            "/professor/gerenciar/{$materia->id}/notas",
            []
        );

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'aluno_ra',
            'campo',
        ]);
    }

    public function test_salvar_notas_rejects_value_above_ten(): void
    {
        $professor = $this->criarProfessor();
        $materia = $this->criarMateria();

        DB::table('materia_professor')->insert([
            'professor_id' => $professor->id,
            'materia_id' => $materia->id,
        ]);

        $this->actingAs($professor, 'professores');

        $response = $this->postJson(
            "/professor/gerenciar/{$materia->id}/notas",
            [
                'aluno_ra' => '123456',
                'campo' => 'prova1',
                'valor' => 11,
            ]
        );

        $response->assertStatus(422);

        $response->assertJsonValidationErrors('valor');
    }

    public function test_salvar_notas_rejects_negative_value(): void
    {
        $professor = $this->criarProfessor();
        $materia = $this->criarMateria();

        DB::table('materia_professor')->insert([
            'professor_id' => $professor->id,
            'materia_id' => $materia->id,
        ]);

        $this->actingAs($professor, 'professores');

        $response = $this->postJson(
            "/professor/gerenciar/{$materia->id}/notas",
            [
                'aluno_ra' => '123456',
                'campo' => 'prova1',
                'valor' => -1,
            ]
        );

        $response->assertStatus(422);

        $response->assertJsonValidationErrors('valor');
    }

    public function test_salvar_notas_rejects_invalid_grade_field(): void
    {
        $professor = $this->criarProfessor();
        $materia = $this->criarMateria();

        DB::table('materia_professor')->insert([
            'professor_id' => $professor->id,
            'materia_id' => $materia->id,
        ]);

        $this->actingAs($professor, 'professores');

        $response = $this->postJson(
            "/professor/gerenciar/{$materia->id}/notas",
            [
                'aluno_ra' => '123456',
                'campo' => 'nota_inventada',
                'valor' => 8,
            ]
        );

        $response->assertStatus(422);

        $response->assertJsonValidationErrors('campo');
    }

    public function test_relatorio_filtra_por_data_e_ignora_data_invalida(): void
    {
        $professor = \App\Models\ProfessorModel::factory()->create();
        $aluno = \App\Models\AlunoModel::factory()->create(['nome' => 'Aluno Filtro']);
        $materia = \App\Models\Materia::factory()->create();
        $professor->materias()->attach($materia->id);

        $presenca = \App\Models\Presenca::create([
            'aluno_id' => $aluno->id, 'professor_id' => $professor->id, 'materia_id' => $materia->id,
            'data_aula' => '2026-09-01', 'semestre' => '2/2026', 'horario' => 'N', 'codigo_aula' => 'R1',
        ]);
        $presenca->forceFill(['created_at' => '2026-09-01 20:00:00'])->save();

        $this->actingAs($professor, 'professores');

        $this->get('/professor/gerenciar/relatorios?data_inicio=2026-09-01&data_fim=2026-09-01')
            ->assertOk()->assertSee('Aluno Filtro');
        $this->get('/professor/gerenciar/relatorios?data_inicio=2026-09-02')
            ->assertOk()->assertDontSee('Aluno Filtro');
        // Formato errado não quebra a página: o filtro é ignorado
        $this->get('/professor/gerenciar/relatorios?data_inicio=01/09/2026&data_fim=abc')
            ->assertOk()->assertSee('Aluno Filtro');
    }
}
