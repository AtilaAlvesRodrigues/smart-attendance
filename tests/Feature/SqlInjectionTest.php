<?php

namespace Tests\Feature;

use App\Models\AlunoModel;
use App\Models\Materia;
use App\Models\ProfessorModel;
use App\Models\UsuarioMaster;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SqlInjectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_aluno_login_is_protected_against_sql_injection(): void
    {
        AlunoModel::create([
            'ra' => '123456',
            'cpf' => '12345678900',
            'nome' => 'Aluno Teste',
            'email' => 'aluno@teste.com',
            'password' => Hash::make('12345678'),
            'role' => 'aluno',
        ]);

        $response = $this->post('/login/aluno', [
            'ra_email_cpf' => "' OR 1=1 --",
            'password' => '12345678',
        ]);

        $response->assertRedirect('/login/aluno');
        $this->assertGuest('alunos');
    }

    public function test_professor_login_is_protected_against_sql_injection(): void
    {
        ProfessorModel::create([
            'cpf' => '12345678900',
            'nome' => 'Professor Teste',
            'email' => 'professor@teste.com',
            'password' => Hash::make('12345678'),
            'role' => 'professor',
        ]);

        $response = $this->post('/login/professor', [
            'cpf_email' => "' OR 1=1 --",
            'password' => '12345678',
        ]);

        $response->assertRedirect('/login/professor');
        $this->assertGuest('professores');
    }

    public function test_master_search_alunos_is_protected_against_sql_injection(): void
    {
        $master = UsuarioMaster::create([
            'nome' => 'Master Teste',
            'email' => 'master@teste.com',
            'password' => Hash::make('12345678'),
            'role' => 'master',
        ]);

        AlunoModel::create([
            'ra' => '123456',
            'cpf' => '12345678900',
            'nome' => 'Aluno Teste',
            'email' => 'aluno@teste.com',
            'password' => Hash::make('12345678'),
            'role' => 'aluno',
        ]);

        AlunoModel::create([
            'ra' => '654321',
            'cpf' => '98765432100',
            'nome' => 'Outro Aluno',
            'email' => 'outro@teste.com',
            'password' => Hash::make('12345678'),
            'role' => 'aluno',
        ]);

        $this->actingAs($master, 'masters');

        $normalResponse = $this->getJson(
            '/dashboard/master/search/alunos?q=' . urlencode('Aluno Teste')
        );

        $normalResponse->assertStatus(200);

        $this->assertCount(1, $normalResponse->json());

        $injectionResponse = $this->getJson(
            '/dashboard/master/search/alunos?q=' . urlencode("' OR 1=1 --")
        );

        $injectionResponse->assertStatus(200);

        $this->assertLessThan(
            2,
            count($injectionResponse->json())
        );
    }

    public function test_master_search_professores_is_protected_against_sql_injection(): void
    {
        $master = UsuarioMaster::create([
            'nome' => 'Master Teste',
            'email' => 'master@teste.com',
            'password' => Hash::make('12345678'),
            'role' => 'master',
        ]);

        ProfessorModel::create([
            'cpf' => '12345678900',
            'nome' => 'Professor Teste',
            'email' => 'professor@teste.com',
            'password' => Hash::make('12345678'),
            'role' => 'professor',
        ]);

        ProfessorModel::create([
            'cpf' => '98765432100',
            'nome' => 'Outro Professor',
            'email' => 'outro@teste.com',
            'password' => Hash::make('12345678'),
            'role' => 'professor',
        ]);

        $this->actingAs($master, 'masters');

        $normalResponse = $this->getJson(
            '/dashboard/master/search/professores?q=' . urlencode('Professor Teste')
        );

        $normalResponse->assertStatus(200);

        $this->assertCount(1, $normalResponse->json());

        $injectionResponse = $this->getJson(
            '/dashboard/master/search/professores?q=' . urlencode("' OR 1=1 --")
        );

        $injectionResponse->assertStatus(200);

        $this->assertLessThan(
            2,
            count($injectionResponse->json())
        );
    }

    public function test_master_search_materias_is_protected_against_sql_injection(): void
    {
        $master = UsuarioMaster::create([
            'nome' => 'Master Teste',
            'email' => 'master@teste.com',
            'password' => Hash::make('12345678'),
            'role' => 'master',
        ]);

        Materia::create([
            'nome' => 'Matemática',
            'sala' => '101',
            'carga_horaria' => 80,
        ]);

        Materia::create([
            'nome' => 'Programação',
            'sala' => '102',
            'carga_horaria' => 80,
        ]);

        $this->actingAs($master, 'masters');

        $normalResponse = $this->getJson(
            '/dashboard/master/search/materias?q=' . urlencode('Matemática')
        );

        $normalResponse->assertStatus(200);

        $this->assertCount(1, $normalResponse->json());

        $injectionResponse = $this->getJson(
            '/dashboard/master/search/materias?q=' . urlencode("' OR 1=1 --")
        );

        $injectionResponse->assertStatus(200);

        $this->assertLessThan(
            2,
            count($injectionResponse->json())
        );
    }
}