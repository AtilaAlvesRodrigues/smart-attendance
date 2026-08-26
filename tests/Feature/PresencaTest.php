<?php

namespace Tests\Feature;

use App\Models\AlunoModel;
use App\Models\Materia;
use App\Models\ProfessorModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PresencaTest extends TestCase
{
    use RefreshDatabase;

    public function test_presenca_page_requires_professor_authentication(): void
    {
        $response = $this->get('/professor/presenca');

        $response->assertRedirect('/login');
    }

    public function test_aluno_confirmar_presenca_requires_login(): void
    {
        $response = $this->get('/presenca/confirmar/1-1234567890-ABCD');

        $response->assertRedirect('/login/aluno');

        $this->assertEquals(
            '1-1234567890-ABCD',
            session('pending_attendance_code')
        );
    }

    public function test_professor_can_access_presenca_page(): void
    {
        $professor = ProfessorModel::create([
            'cpf' => '12345678900',
            'nome' => 'Professor Teste',
            'email' => 'professor@teste.com',
            'password' => Hash::make('12345678'),
            'role' => 'professor',
        ]);

        $this->actingAs($professor, 'professores');

        $response = $this->get('/professor/presenca');

        $response->assertStatus(200);
    }

    public function test_professor_cannot_generate_qr_for_unrelated_subject(): void
    {
        $professor = ProfessorModel::create([
            'cpf' => '12345678900',
            'nome' => 'Professor Teste',
            'email' => 'professor@teste.com',
            'password' => Hash::make('12345678'),
            'role' => 'professor',
        ]);

        $materia = Materia::create([
            'nome' => 'Matemática',
            'sala' => '101',
            'carga_horaria' => 80,
        ]);

        $this->actingAs($professor, 'professores');

        $response = $this->get("/professor/presenca/gerar/{$materia->id}");

        $response->assertStatus(403);
    }

    public function test_invalid_attendance_code_shows_error(): void
    {
        $aluno = AlunoModel::create([
            'ra' => '123456',
            'cpf' => '12345678900',
            'nome' => 'Aluno Teste',
            'email' => 'aluno@teste.com',
            'password' => Hash::make('12345678'),
            'role' => 'aluno',
        ]);

        $this->actingAs($aluno, 'alunos');

        $response = $this->get('/presenca/confirmar/codigo-invalido');

        $response->assertStatus(200);
        $response->assertViewIs('aluno.presenca.erro');
    }

    public function test_get_presencas_returns_json(): void
    {
        $professor = ProfessorModel::create([
            'cpf' => '98765432100',
            'nome' => 'Professor Presença',
            'email' => 'presenca@teste.com',
            'password' => Hash::make('12345678'),
            'role' => 'professor',
        ]);

        $this->actingAs($professor, 'professores');

        $response = $this->get('/professor/presenca/check/codigo-teste');

        $response->assertStatus(200);
        $response->assertJson([]);
    }

    public function test_aluno_can_confirm_valid_attendance(): void
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
        ]);

        $professor->materias()->attach($materia->id);
        $aluno->materias()->attach($materia->id);

        $timestamp = now()->timestamp;
        $codigo = $materia->id . '-' . $timestamp . '-ABCD';

        Cache::put(
            'aula_materia_' . $materia->id . '_' . now()->format('Y-m-d'),
            [
                'codigo' => $codigo,
                'expira_em' => now()->addHours(2)->timestamp,
                'professor_id' => $professor->id,
            ],
            now()->addHours(2)
        );

        $this->actingAs($aluno, 'alunos');

        $response = $this->get("/presenca/confirmar/{$codigo}");

        $response->assertStatus(200);
        $response->assertViewIs('aluno.presenca.sucesso');

        $this->assertDatabaseHas('presencas', [
            'aluno_id' => $aluno->id,
            'professor_id' => $professor->id,
            'materia_id' => $materia->id,
            'codigo_aula' => $codigo,
        ]);
    }

    public function test_expired_attendance_code_is_rejected(): void
    {
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
        ]);

        $aluno->materias()->attach($materia->id);

        $timestamp = now()->timestamp;
        $codigo = $materia->id . '-' . $timestamp . '-ABCD';

        $this->actingAs($aluno, 'alunos');

        $response = $this->get("/presenca/confirmar/{$codigo}");

        $response->assertStatus(200);
        $response->assertViewIs('aluno.presenca.erro');

        $this->assertDatabaseMissing('presencas', [
            'aluno_id' => $aluno->id,
            'codigo_aula' => $codigo,
        ]);
    }

    public function test_aluno_cannot_register_same_attendance_twice(): void
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
        ]);

        $professor->materias()->attach($materia->id);
        $aluno->materias()->attach($materia->id);

        $timestamp = now()->timestamp;
        $codigo = $materia->id . '-' . $timestamp . '-ABCD';

        Cache::put(
            'aula_materia_' . $materia->id . '_' . now()->format('Y-m-d'),
            [
                'codigo' => $codigo,
                'expira_em' => now()->addHours(2)->timestamp,
                'professor_id' => $professor->id,
            ],
            now()->addHours(2)
        );

        $this->actingAs($aluno, 'alunos');

        $response = $this->get("/presenca/confirmar/{$codigo}");

        $response->assertViewIs('aluno.presenca.sucesso');

        $response = $this->get("/presenca/confirmar/{$codigo}");

        $response->assertStatus(200);
        $response->assertViewIs('aluno.presenca.ja_registrado');

        $this->assertDatabaseCount('presencas', 1);

        $this->assertDatabaseHas('presencas', [
            'aluno_id' => $aluno->id,
            'codigo_aula' => $codigo,
        ]);
    }
}