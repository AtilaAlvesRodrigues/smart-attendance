<?php

namespace Tests\Feature;

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CsrfTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // O Laravel ignora a verificação CSRF durante testes automatizados.
        // Substituímos o middleware por uma versão que sempre valida o token.
        $this->app->bind(ValidateCsrfToken::class, fn ($app) => new class($app, $app['encrypter']) extends ValidateCsrfToken {
            protected function runningUnitTests()
            {
                return false;
            }
        });
    }

    public function test_checkin_request_without_csrf_token_is_blocked(): void
    {
        $response = $this->post('/evento/checkin/process', [
            'name' => 'João da Silva',
            'email' => 'csrf@example.com',
            'token' => 'teste-csrf',
            'hp_field' => '',
        ]);

        $response->assertStatus(419);
    }

    public function test_password_creation_without_csrf_token_is_blocked(): void
    {
        $response = $this->post('/criar-senha', [
            'password' => 'NovaSenha@123',
            'password_confirmation' => 'NovaSenha@123',
        ]);

        $response->assertStatus(419);
    }

    public function test_login_without_csrf_token_is_blocked(): void
    {
        $response = $this->post('/login/aluno', [
            'login' => 'aluno.teste@site.com',
            'password' => 'senha123',
        ]);

        $response->assertStatus(419);
    }
}
