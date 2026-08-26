<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\ProfessorModel;
use App\Models\UsuarioMaster;
use App\Models\AlunoModel;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_accessible(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_aluno_login_page_is_accessible(): void
    {
        $response = $this->get('/login/aluno');

        $response->assertStatus(200);
    }

    public function test_professor_login_page_is_accessible(): void
    {
        $response = $this->get('/login/professor');

        $response->assertStatus(200);
    }

    public function test_login_redirects_to_aluno_login(): void
    {
        $response = $this->post('/login');

        $response->assertRedirect('/login/aluno');
    }


    public function test_aluno_login_requires_access_field(): void
    {
        $response = $this->post('/login/aluno', [
            'password' => '123456',
        ]);

        $response->assertSessionHasErrors('ra_email_cpf');
    }

    public function test_aluno_login_requires_password(): void
    {
        $response = $this->post('/login/aluno', [
            'ra_email_cpf' => '123456',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_aluno_login_requires_both_fields(): void
    {
        $response = $this->post('/login/aluno', []);

        $response->assertSessionHasErrors([
            'ra_email_cpf',
            'password',
        ]);
    }

    public function test_aluno_login_rejects_invalid_credentials(): void
    {
        $response = $this->post('/login/aluno', [
            'ra_email_cpf' => 'aluno-inexistente',
            'password' => 'senha-incorreta',
        ]);

        $response->assertRedirect('/login/aluno');
        $response->assertSessionHasErrors('ra_email_cpf');
    }

    public function test_aluno_login_rejects_wrong_password(): void
    {
        $response = $this->post('/login/aluno', [
            'ra_email_cpf' => 'aluno-inexistente',
            'password' => 'senha-errada',
        ]);

        $response->assertRedirect('/login/aluno');
        $response->assertSessionHasErrors('ra_email_cpf');
    }

    public function test_aluno_can_login_with_valid_credentials(): void
    {
        $aluno = AlunoModel::create([
            'ra' => '123456',
            'cpf' => '12345678900',
            'nome' => 'Aluno Teste',
            'email' => 'aluno@teste.com',
            'password' => 'senha123',
            'role' => 'aluno',
        ]);

        $response = $this->post('/login/aluno', [
            'ra_email_cpf' => '123456',
            'password' => 'senha123',
        ]);

        $response->assertRedirect('/dashboard/aluno');

        $this->assertAuthenticatedAs(
            $aluno,
            'alunos'
        );
    }


    public function test_professor_login_requires_cpf_or_email(): void
    {
        $response = $this->post('/login/professor', [
            'password' => '123456',
        ]);

        $response->assertSessionHasErrors('cpf_email');
    }

    public function test_professor_login_requires_password(): void
    {
        $response = $this->post('/login/professor', [
            'cpf_email' => '12345678900',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_professor_login_requires_both_fields(): void
    {
        $response = $this->post('/login/professor', []);

        $response->assertSessionHasErrors([
            'cpf_email',
            'password',
        ]);
    }

    public function test_professor_login_rejects_invalid_credentials(): void
    {
        $response = $this->post('/login/professor', [
            'cpf_email' => 'professor-inexistente',
            'password' => 'senha-incorreta',
        ]);

        $response->assertRedirect('/login/professor');
        $response->assertSessionHasErrors('cpf_email');
    }

    public function test_professor_login_rejects_wrong_password(): void
    {
        $response = $this->post('/login/professor', [
            'cpf_email' => 'professor-inexistente',
            'password' => 'senha-errada',
        ]);

        $response->assertRedirect('/login/professor');
        $response->assertSessionHasErrors('cpf_email');
    }

    public function test_professor_can_login_with_valid_credentials(): void
    {
        $professor = ProfessorModel::create([
            'cpf' => '12345678900',
            'nome' => 'Professor Teste',
            'email' => 'professor@teste.com',
            'password' => 'senha123',
            'role' => 'professor',
        ]);

        $response = $this->post('/login/professor', [
            'cpf_email' => '12345678900',
            'password' => 'senha123',
        ]);

        $response->assertRedirect('/professor/dashboard');

        $this->assertAuthenticatedAs(
            $professor,
            'professores'
        );
    }


    public function test_master_login_rejects_invalid_credentials(): void
    {
        $response = $this->post('/login/professor', [
            'cpf_email' => 'master-inexistente@email.com',
            'password' => 'senha-incorreta',
        ]);

        $response->assertRedirect('/login/professor');
        $response->assertSessionHasErrors('cpf_email');
    }

    public function test_master_login_rejects_wrong_password(): void
    {
        $master = UsuarioMaster::create([
            'nome' => 'Master Teste',
            'email' => 'master@teste.com',
            'password' => 'senha-correta',
            'role' => 'master',
        ]);

        $response = $this->post('/login/professor', [
            'cpf_email' => 'master@teste.com',
            'password' => 'senha-errada',
        ]);

        $response->assertRedirect('/login/professor');
        $response->assertSessionHasErrors('cpf_email');
    }

    public function test_master_can_login_with_valid_credentials(): void
    {
        $master = UsuarioMaster::create([
            'nome' => 'Master Teste',
            'email' => 'master@teste.com',
            'password' => 'senha-correta',
            'role' => 'master',
        ]);

        $response = $this->post('/login/professor', [
            'cpf_email' => 'master@teste.com',
            'password' => 'senha-correta',
        ]);

        $response->assertRedirect('/dashboard/master');

        $this->assertAuthenticatedAs(
            $master,
            'masters'
        );
    }
}