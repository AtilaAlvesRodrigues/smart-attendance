<?php

namespace Tests\Feature;

use App\Models\AlunoModel;
use App\Models\ProfessorModel;
use App\Models\UsuarioMaster;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_requires_authentication(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect();
    }

    public function test_aluno_dashboard_requires_authentication(): void
    {
        $response = $this->get('/dashboard/aluno');

        $response->assertRedirect();
    }

    public function test_professor_dashboard_requires_authentication(): void
    {
        $response = $this->get('/professor/dashboard');

        $response->assertRedirect();
    }

    public function test_master_dashboard_requires_authentication(): void
    {
        $response = $this->get('/dashboard/master');

        $response->assertRedirect();
    }

    public function test_aluno_can_access_aluno_dashboard(): void
    {
        $aluno = AlunoModel::create([
            'ra' => '123456',
            'cpf' => '12345678900',
            'nome' => 'Aluno Teste',
            'email' => 'aluno@dashboard.com',
            'password' => 'senha123456',
            'role' => 'aluno',
        ]);

        $this->actingAs($aluno, 'alunos');

        $response = $this->get('/dashboard/aluno');

        $response->assertStatus(200);
    }

    public function test_professor_can_access_professor_dashboard(): void
    {
        $professor = ProfessorModel::create([
            'cpf' => '98765432100',
            'nome' => 'Professor Teste',
            'email' => 'professor@dashboard.com',
            'password' => 'senha123456',
            'role' => 'professor',
        ]);

        $this->actingAs($professor, 'professores');

        $response = $this->get('/professor/dashboard');

        $response->assertStatus(200);
    }

    public function test_master_can_access_master_dashboard(): void
    {
        $master = UsuarioMaster::create([
            'nome' => 'Master Teste',
            'email' => 'master@dashboard.com',
            'password' => 'senha123456',
            'role' => 'master',
        ]);

        $this->actingAs($master, 'masters');

        $response = $this->get('/dashboard/master');

        $response->assertStatus(200);
    }
}