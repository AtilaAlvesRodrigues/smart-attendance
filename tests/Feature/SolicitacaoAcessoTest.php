<?php

namespace Tests\Feature;

use App\Mail\PrimeiroAcessoMail;
use App\Models\AlunoModel;
use App\Models\SolicitacaoAcesso;
use App\Models\UsuarioMaster;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SolicitacaoAcessoTest extends TestCase
{
    use RefreshDatabase;

    private function dadosAluno(array $extra = []): array
    {
        return array_merge([
            'nome'  => 'Maria Souza',
            'email' => 'maria@example.com',
            'cpf'   => '11122233344',
            'ra'    => '202600001',
        ], $extra);
    }

    public function test_formulario_publico_e_exibido(): void
    {
        $this->get('/solicitar-acesso/aluno')->assertOk();
        $this->get('/solicitar-acesso/professor')->assertOk();
    }

    public function test_tipo_invalido_retorna_404(): void
    {
        $this->get('/solicitar-acesso/master')->assertNotFound();
    }

    public function test_aluno_envia_solicitacao_pendente(): void
    {
        $this->post('/solicitar-acesso/aluno', $this->dadosAluno())
            ->assertRedirect()
            ->assertSessionHas('success');

        $solicitacao = SolicitacaoAcesso::first();
        $this->assertNotNull($solicitacao);
        $this->assertSame('pendente', $solicitacao->status);
        $this->assertSame('maria@example.com', $solicitacao->email);
    }

    public function test_solicitacao_duplicada_e_recusada(): void
    {
        $this->post('/solicitar-acesso/aluno', $this->dadosAluno());
        $this->post('/solicitar-acesso/aluno', $this->dadosAluno())
            ->assertSessionHasErrors('email');

        $this->assertSame(1, SolicitacaoAcesso::count());
    }

    public function test_verificar_email_informa_pendencia(): void
    {
        $this->post('/solicitar-acesso/aluno', $this->dadosAluno());

        $this->postJson('/solicitar-acesso/aluno/verificar-email', ['email' => 'maria@example.com'])
            ->assertOk()
            ->assertJson(['status' => 'pendente']);
    }

    public function test_master_aprova_solicitacao_e_cria_aluno(): void
    {
        Mail::fake();
        $this->post('/solicitar-acesso/aluno', $this->dadosAluno());
        $solicitacao = SolicitacaoAcesso::first();

        $master = UsuarioMaster::factory()->create();
        $this->actingAs($master, 'masters');

        $this->get('/dashboard/master/solicitacoes')->assertOk();

        $this->post("/dashboard/master/solicitacoes/{$solicitacao->id}/aprovar")
            ->assertSessionHas('success');

        $this->assertSame('aprovado', $solicitacao->fresh()->status);
        $this->assertTrue(
            AlunoModel::where('email_search', AlunoModel::generateBlindIndex('maria@example.com'))->exists()
        );
        Mail::assertSent(PrimeiroAcessoMail::class);
    }

    public function test_master_rejeita_solicitacao(): void
    {
        $this->post('/solicitar-acesso/aluno', $this->dadosAluno());
        $solicitacao = SolicitacaoAcesso::first();

        $this->actingAs(UsuarioMaster::factory()->create(), 'masters');

        $this->post("/dashboard/master/solicitacoes/{$solicitacao->id}/rejeitar", ['motivo' => 'Dados incompletos'])
            ->assertSessionHas('success');

        $this->assertSame('rejeitado', $solicitacao->fresh()->status);
        $this->assertSame(0, AlunoModel::count());
    }

    public function test_aluno_nao_acessa_painel_de_solicitacoes(): void
    {
        $aluno = AlunoModel::factory()->create();
        $this->actingAs($aluno, 'alunos');

        $this->get('/dashboard/master/solicitacoes')->assertStatus(302);
    }
}
