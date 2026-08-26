<?php

namespace Tests\Feature;

use App\Models\AlunoModel;
use App\Models\Materia;
use App\Models\ProfessorModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ColumnInjectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_professor_cannot_inject_arbitrary_column_into_grade_update(): void
    {
        $professor = ProfessorModel::create([
            'cpf' => '12345678900',
            'nome' => 'Professor Teste',
            'email' => 'professor@teste.com',
            'password' => Hash::make('12345678'),
            'role' => 'professor',
        ]);

        $aluno = AlunoModel::create([
            'ra' => '123456',
            'cpf' => '98765432100',
            'nome' => 'Aluno Teste',
            'email' => 'aluno@teste.com',
            'password' => Hash::make('12345678'),
            'role' => 'aluno',
        ]);

        $materia = Materia::create([
            'nome' => 'Matemática',
            'sala' => '101',
            'carga_horaria' => 80,
            'total_aulas' => 20,
        ]);

        $professor->materias()->attach($materia->id);

        DB::table('aluno_materia')->insert([
            'aluno_id' => $aluno->id,
            'materia_id' => $materia->id,
            'prova1' => 5,
            'trabalho1' => 6,
            'trabalho2' => 7,
            'prova2' => 8,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($professor, 'professores');

        $response = $this->postJson(
            "/professor/gerenciar/{$materia->id}/notas",
            [
                'aluno_ra' => '123456',
                'campo' => 'password',
                'valor' => 10,
            ]
        );

        $response->assertStatus(422);

        $notas = DB::table('aluno_materia')
            ->where('aluno_id', $aluno->id)
            ->where('materia_id', $materia->id)
            ->first();

        $this->assertSame(5.0, (float) $notas->prova1);
        $this->assertSame(6.0, (float) $notas->trabalho1);
        $this->assertSame(7.0, (float) $notas->trabalho2);
        $this->assertSame(8.0, (float) $notas->prova2);
    }

    public function test_only_allowed_grade_columns_can_be_updated(): void
    {
        $professor = ProfessorModel::create([
            'cpf' => '11111111111',
            'nome' => 'Professor Notas',
            'email' => 'notas@teste.com',
            'password' => Hash::make('12345678'),
            'role' => 'professor',
        ]);

        $aluno = AlunoModel::create([
            'ra' => '654321',
            'cpf' => '22222222222',
            'nome' => 'Aluno Notas',
            'email' => 'notas.aluno@teste.com',
            'password' => Hash::make('12345678'),
            'role' => 'aluno',
        ]);

        $materia = Materia::create([
            'nome' => 'Programação',
            'sala' => '102',
            'carga_horaria' => 80,
            'total_aulas' => 20,
        ]);

        $professor->materias()->attach($materia->id);

        DB::table('aluno_materia')->insert([
            'aluno_id' => $aluno->id,
            'materia_id' => $materia->id,
            'prova1' => 4,
            'trabalho1' => 5,
            'trabalho2' => 6,
            'prova2' => 7,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($professor, 'professores');

        $response = $this->postJson(
            "/professor/gerenciar/{$materia->id}/notas",
            [
                'aluno_ra' => '654321',
                'campo' => 'prova1',
                'valor' => 9,
            ]
        );

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $notas = DB::table('aluno_materia')
            ->where('aluno_id', $aluno->id)
            ->where('materia_id', $materia->id)
            ->first();

        $this->assertSame(9.0, (float) $notas->prova1);
        $this->assertSame(5.0, (float) $notas->trabalho1);
        $this->assertSame(6.0, (float) $notas->trabalho2);
        $this->assertSame(7.0, (float) $notas->prova2);
    }
}
