<?php

namespace Tests\Feature;

use App\Models\AlunoModel;
use App\Models\ProfessorModel;
use App\Models\UsuarioMaster;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UnauthorizedAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_aluno_cannot_access_master_alunos_page(): void
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

        $response = $this->get('/dashboard/master/alunos');

        $this->assertNotSame(200, $response->status());
    }

    public function test_aluno_cannot_access_master_professores_page(): void
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

        $response = $this->get('/dashboard/master/professores');

        $this->assertNotSame(200, $response->status());
    }

    public function test_professor_cannot_access_master_alunos_page(): void
    {
        $professor = ProfessorModel::create([
            'cpf' => '12345678900',
            'nome' => 'Professor Teste',
            'email' => 'professor@teste.com',
            'password' => Hash::make('12345678'),
            'role' => 'professor',
        ]);

        $this->actingAs($professor, 'professores');

        $response = $this->get('/dashboard/master/alunos');

        $this->assertNotSame(200, $response->status());
    }

    public function test_professor_cannot_access_master_professores_page(): void
    {
        $professor = ProfessorModel::create([
            'cpf' => '12345678900',
            'nome' => 'Professor Teste',
            'email' => 'professor@teste.com',
            'password' => Hash::make('12345678'),
            'role' => 'professor',
        ]);

        $this->actingAs($professor, 'professores');

        $response = $this->get('/dashboard/master/professores');

        $this->assertNotSame(200, $response->status());
    }

    public function test_master_cannot_access_professor_management(): void
    {
        $master = UsuarioMaster::create([
            'nome' => 'Master Teste',
            'email' => 'master@teste.com',
            'password' => Hash::make('12345678'),
            'role' => 'master',
        ]);

        $this->actingAs($master, 'masters');

        $response = $this->get('/professor/gerenciar');

        $this->assertNotSame(200, $response->status());
    }

    public function test_aluno_cannot_access_professor_management(): void
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

        $response = $this->get('/professor/gerenciar');

        $this->assertNotSame(200, $response->status());
    }

    public function test_aluno_cannot_access_professor_presence_page(): void
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

        $response = $this->get('/professor/presenca');

        $this->assertNotSame(200, $response->status());
    }

    public function test_master_cannot_access_professor_presence_page(): void
    {
        $master = UsuarioMaster::create([
            'nome' => 'Master Teste',
            'email' => 'master@teste.com',
            'email' => 'master@teste.com',
            'password' => Hash::make('12345678'),
            'role' => 'master',
        ]);

        $this->actingAs($master, 'masters');

        $response = $this->get('/professor/presenca');

        $this->assertNotSame(200, $response->status());
    }

    public function test_aluno_cannot_access_master_search(): void
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

        $response = $this->getJson(
            '/dashboard/master/search/alunos?q=teste'
        );

        $this->assertNotSame(200, $response->status());
    }

    public function test_professor_cannot_access_master_search(): void
    {
        $professor = ProfessorModel::create([
            'cpf' => '12345678900',
            'nome' => 'Professor Teste',
            'email' => 'professor@teste.com',
            'password' => Hash::make('12345678'),
            'role' => 'professor',
        ]);

        $this->actingAs($professor, 'professores');

        $response = $this->getJson(
            '/dashboard/master/search/alunos?q=teste'
        );

        $this->assertNotSame(200, $response->status());
    }
}