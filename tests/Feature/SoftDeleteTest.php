<?php

namespace Tests\Feature;

use App\Models\AlunoModel;
use App\Models\Materia;
use App\Models\ProfessorModel;
use App\Models\UsuarioMaster;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SoftDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_aluno_uses_soft_delete(): void
    {
        $aluno = AlunoModel::create([
            'ra' => '123456',
            'cpf' => '12345678900',
            'nome' => 'Aluno Teste',
            'email' => 'aluno@teste.com',
            'password' => Hash::make('12345678'),
            'role' => 'aluno',
        ]);

        $aluno->delete();

        $this->assertSoftDeleted('alunos', [
            'id' => $aluno->id,
        ]);

        $this->assertDatabaseHas('alunos', [
            'id' => $aluno->id,
        ]);
    }

    public function test_professor_uses_soft_delete(): void
    {
        $professor = ProfessorModel::create([
            'cpf' => '12345678900',
            'nome' => 'Professor Teste',
            'email' => 'professor@teste.com',
            'password' => Hash::make('12345678'),
            'role' => 'professor',
        ]);

        $professor->delete();

        $this->assertSoftDeleted('professores', [
            'id' => $professor->id,
        ]);

        $this->assertDatabaseHas('professores', [
            'id' => $professor->id,
        ]);
    }

    public function test_materia_uses_soft_delete(): void
    {
        $materia = Materia::create([
            'nome' => 'Matemática',
            'sala' => '101',
            'carga_horaria' => 80,
        ]);

        $materia->delete();

        $this->assertSoftDeleted('materias', [
            'id' => $materia->id,
        ]);

        $this->assertDatabaseHas('materias', [
            'id' => $materia->id,
        ]);
    }

    public function test_master_uses_soft_delete(): void
    {
        $master = UsuarioMaster::create([
            'nome' => 'Master Teste',
            'email' => 'master@teste.com',
            'password' => Hash::make('12345678'),
            'role' => 'master',
        ]);

        $master->delete();

        $this->assertSoftDeleted('usuario_masters', [
            'id' => $master->id,
        ]);

        $this->assertDatabaseHas('usuario_masters', [
            'id' => $master->id,
        ]);
    }
}