<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CsrfTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkin_request_without_csrf_token_is_blocked(): void
    {
        $this->withMiddleware();

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
        $this->withMiddleware();

        $this->markTestSkipped(
            'O fluxo de primeiro acesso precisa ser configurado antes do teste CSRF.'
        );
    }
}