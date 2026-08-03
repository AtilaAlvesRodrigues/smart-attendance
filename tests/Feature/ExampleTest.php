<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase; // <-- Importação necessária
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase; // <-- Trait que roda as migrations automaticamente

    /**
     * Testa se a rota raiz redireciona para o formulário de login.
     */
    public function test_root_route_redirects_to_login(): void
    {
        $response = $this->get('/');

        // Verifica se deu 302 e se o destino é a rota 'login_form'
        $response->assertRedirect(route('login_form'));
    }

    /**
     * Testa se a página de login carrega com sucesso (Status 200).
     */
    public function test_login_form_loads_successfully(): void
    {
        // Ignora o Vite para evitar o erro de manifest not found
        $this->withoutVite();

        $response = $this->get('/login');

        // Como essa é a página final, ela sim deve retornar 200
        $response->assertStatus(200);
    }
}