<?php

namespace Tests\Feature;

use App\Models\AlunoModel;
use App\Models\ProfessorModel;
use App\Models\UsuarioMaster;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_aluno_cannot_access_professor_dashboard(): void
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

        $response = $this->get('/professor/dashboard');

        $this->assertTrue(
            in_array($response->status(), [302, 403])
        );
    }

    public function test_aluno_cannot_access_master_dashboard(): void
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

        $response = $this->get('/dashboard/master');

        $this->assertTrue(
            in_array($response->status(), [302, 403])
        );
    }

    public function test_professor_cannot_access_aluno_dashboard(): void
    {
        $professor = ProfessorModel::create([
            'cpf' => '12345678900',
            'nome' => 'Professor Teste',
            'email' => 'professor@teste.com',
            'password' => Hash::make('12345678'),
            'role' => 'professor',
        ]);

        $this->actingAs($professor, 'professores');

        $response = $this->get('/dashboard/aluno');

        $this->assertTrue(
            in_array($response->status(), [302, 403])
        );
    }

    public function test_professor_cannot_access_master_dashboard(): void
    {
        $professor = ProfessorModel::create([
            'cpf' => '12345678900',
            'nome' => 'Professor Teste',
            'email' => 'professor@teste.com',
            'password' => Hash::make('12345678'),
            'role' => 'professor',
        ]);

        $this->actingAs($professor, 'professores');

        $response = $this->get('/dashboard/master');

        $this->assertTrue(
            in_array($response->status(), [302, 403])
        );
    }

    public function test_master_cannot_access_aluno_dashboard(): void
    {
        $master = UsuarioMaster::create([
            'nome' => 'Master Teste',
            'email' => 'master@teste.com',
            'password' => Hash::make('12345678'),
            'role' => 'master',
        ]);

        $this->actingAs($master, 'masters');

        $response = $this->get('/dashboard/aluno');

        $this->assertTrue(
            in_array($response->status(), [302, 403])
        );
    }

    public function test_master_cannot_access_professor_dashboard(): void
    {
        $master = UsuarioMaster::create([
            'nome' => 'Master Teste',
            'email' => 'master@teste.com',
            'password' => Hash::make('12345678'),
            'role' => 'master',
        ]);

        $this->actingAs($master, 'masters');

        $response = $this->get('/professor/dashboard');

        $this->assertTrue(
            in_array($response->status(), [302, 403])
        );
    }

    public function test_aluno_can_access_only_aluno_dashboard(): void
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

        $response = $this->get('/dashboard/aluno');

        $response->assertStatus(200);
    }

    public function test_professor_can_access_only_professor_dashboard(): void
    {
        $professor = ProfessorModel::create([
            'cpf' => '12345678900',
            'nome' => 'Professor Teste',
            'email' => 'professor@teste.com',
            'password' => Hash::make('12345678'),
            'role' => 'professor',
        ]);

        $this->actingAs($professor, 'professores');

        $response = $this->get('/professor/dashboard');

        $response->assertStatus(200);
    }

    public function test_master_can_access_only_master_dashboard(): void
    {
        $master = UsuarioMaster::create([
            'nome' => 'Master Teste',
            'email' => 'master@teste.com',
            'password' => Hash::make('12345678'),
            'role' => 'master',
        ]);

        $this->actingAs($master, 'masters');

        $response = $this->get('/dashboard/master');

        $response->assertStatus(200);
    }
}