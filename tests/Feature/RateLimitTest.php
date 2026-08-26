<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_aluno_login_is_rate_limited_after_too_many_attempts(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $response = $this->post('/login/aluno', [
                'ra_email_cpf' => 'usuario-inexistente@teste.com',
                'password' => 'senha-invalida',
            ]);

            $response->assertSessionHasErrors('ra_email_cpf');
        }

        $response = $this->post('/login/aluno', [
            'ra_email_cpf' => 'usuario-inexistente@teste.com',
            'password' => 'senha-invalida',
        ]);

        $response->assertStatus(429);
    }

    public function test_professor_login_is_rate_limited_after_too_many_attempts(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $response = $this->post('/login/professor', [
                'cpf_email' => 'usuario-inexistente@teste.com',
                'password' => 'senha-invalida',
            ]);

            $response->assertSessionHasErrors('cpf_email');
        }

        $response = $this->post('/login/professor', [
            'cpf_email' => 'usuario-inexistente@teste.com',
            'password' => 'senha-invalida',
        ]);

        $response->assertStatus(429);
    }

    public function test_password_recovery_is_rate_limited(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $response = $this->post('/esqueci-senha/aluno', [
                'email' => 'naoexiste@teste.com',
            ]);

            $response->assertRedirect();
        }

        $response = $this->post('/esqueci-senha/aluno', [
            'email' => 'naoexiste@teste.com',
        ]);

        $response->assertStatus(429);
    }

    public function test_event_checkin_processing_is_rate_limited(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $response = $this->post('/evento/checkin/process', [
                'codigo' => 'codigo-invalido',
            ]);

            $this->assertNotSame(429, $response->status());
        }

        $response = $this->post('/evento/checkin/process', [
            'codigo' => 'codigo-invalido',
        ]);

        $this->assertSame(429, $response->status());
    }
}