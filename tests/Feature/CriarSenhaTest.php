<?php

namespace Tests\Feature;

use App\Models\AlunoModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CriarSenhaTest extends TestCase
{
    use RefreshDatabase;

    public function test_criar_senha_page_is_accessible(): void
    {
        $response = $this->withSession([
            'pending_password_creation' => 1,
            'pending_password_role' => 'aluno',
        ])->get('/criar-senha');

        $response->assertStatus(200);
    }

    public function test_criar_senha_requires_password(): void
    {
        $response = $this->withSession([
            'pending_password_creation' => 1,
            'pending_password_role' => 'aluno',
        ])->post('/criar-senha', [
            'senha_confirmacao' => '12345678',
        ]);

        $response->assertSessionHasErrors('senha');
    }

    public function test_criar_senha_requires_confirmation(): void
    {
        $response = $this->withSession([
            'pending_password_creation' => 1,
            'pending_password_role' => 'aluno',
        ])->post('/criar-senha', [
            'senha' => '12345678',
        ]);

        $response->assertSessionHasErrors('senha_confirmacao');
    }

    public function test_criar_senha_requires_minimum_eight_characters(): void
    {
        $response = $this->withSession([
            'pending_password_creation' => 1,
            'pending_password_role' => 'aluno',
        ])->post('/criar-senha', [
            'senha' => '1234567',
            'senha_confirmacao' => '1234567',
        ]);

        $response->assertSessionHasErrors('senha');
    }

    public function test_criar_senha_requires_matching_confirmation(): void
    {
        $response = $this->withSession([
            'pending_password_creation' => 1,
            'pending_password_role' => 'aluno',
        ])->post('/criar-senha', [
            'senha' => '12345678',
            'senha_confirmacao' => '87654321',
        ]);

        $response->assertSessionHasErrors('senha');
    }

    public function test_criar_senha_rejects_invalid_session(): void
    {
        $response = $this->withSession([
            'pending_password_creation' => 999999,
            'pending_password_role' => 'aluno',
        ])->post('/criar-senha', [
            'senha' => '12345678',
            'senha_confirmacao' => '12345678',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('erro');
    }

    public function test_aluno_can_create_password_on_first_access(): void
    {
        $aluno = AlunoModel::create([
            'ra' => '123456',
            'cpf' => '12345678900',
            'nome' => 'Aluno Teste',
            'email' => 'aluno@teste.com',
            'password' => 'senha-antiga',
            'role' => 'aluno',
            'remember_token' => 'TOKEN123',
        ]);

        $response = $this->withSession([
            'pending_password_creation' => $aluno->id,
            'pending_password_role' => 'aluno',
        ])->post('/criar-senha', [
            'senha' => 'novasenha123',
            'senha_confirmacao' => 'novasenha123',
        ]);

        $response->assertRedirect('/dashboard/aluno');

        $aluno->refresh();

        $this->assertTrue(
            Hash::check('novasenha123', $aluno->password)
        );

        $this->assertNull($aluno->remember_token);
    }
}