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

    public function test_turma_inteira_na_mesma_rede_consegue_fazer_login(): void
    {
        // 40 alunos saindo pelo mesmo IP (Wi-Fi da instituição): nenhum pode ser barrado
        $alunos = \App\Models\AlunoModel::factory()->count(40)->create();

        foreach ($alunos as $aluno) {
            $this->post('/login/aluno', ['ra_email_cpf' => $aluno->email, 'password' => 'senha123'])
                ->assertRedirect()
                ->assertSessionHasNoErrors();
            $this->post('/logout');
        }
    }

    public function test_publico_de_evento_na_mesma_rede_consegue_fazer_check_in(): void
    {
        for ($i = 1; $i <= 30; $i++) {
            $status = $this->post('/evento/checkin/process', [
                'name' => "Participante {$i}", 'email' => "p{$i}@exemplo.com", 'token' => 'palestra', 'hp_field' => '',
            ])->status();
            $this->assertNotSame(429, $status, "Participante {$i} foi barrado pelo limite");
        }
    }

    public function test_limite_continua_valendo_para_a_mesma_conta_de_outra_forma_de_escrita(): void
    {
        // Mesmo usuário com letras maiúsculas e espaços conta como o mesmo
        for ($i = 0; $i < 10; $i++) {
            $this->post('/login/aluno', ['ra_email_cpf' => $i % 2 ? ' Alvo@Teste.com ' : 'alvo@teste.com', 'password' => 'errada']);
        }
        $this->post('/login/aluno', ['ra_email_cpf' => 'ALVO@teste.com', 'password' => 'errada'])->assertStatus(429);

        // ...mas outra conta, no mesmo IP, segue livre
        $this->post('/login/aluno', ['ra_email_cpf' => 'outra@teste.com', 'password' => 'errada'])
            ->assertSessionHasErrors('ra_email_cpf');
    }
}
