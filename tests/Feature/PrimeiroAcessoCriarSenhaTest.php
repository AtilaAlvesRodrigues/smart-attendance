<?php

namespace Tests\Feature;

use App\Models\AlunoModel;
use App\Models\ProfessorModel;
use App\Models\UsuarioMaster;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Testa o fluxo de criação de senha no primeiro acesso.
 *
 * Cobre os seguintes cenários:
 * 1. A página de "criar senha" exige uma sessão válida (pending_password_creation).
 * 2. Aluno/Professor/Master conseguem definir uma nova senha com sucesso.
 * 3. Token já utilizado (remember_token = null) é recusado.
 * 4. Senhas divergentes são rejeitadas pela validação.
 * 5. Senha curta demais é rejeitada pela validação.
 *
 * Este fluxo usa a sessão (pending_password_creation + pending_password_role)
 * como mecanismo de autenticação temporária, protegido pelo middleware 'primeiro-acesso'.
 */
class PrimeiroAcessoCriarSenhaTest extends TestCase
{
    use RefreshDatabase;

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Cria um aluno sem senha (remember_token definido, password null)
     * — estado que representa um "primeiro acesso pendente".
     */
    private function criarAlunoPendenteDeAtivacao(): AlunoModel
    {
        return AlunoModel::factory()->create([
            'password'       => null,
            'remember_token' => 'token-ativacao-aleatorio',
        ]);
    }

    /**
     * Cria um professor sem senha (primeiro acesso pendente).
     */
    private function criarProfessorPendenteDeAtivacao(): ProfessorModel
    {
        return ProfessorModel::factory()->create([
            'password'       => null,
            'remember_token' => 'token-ativacao-aleatorio',
        ]);
    }

    /**
     * Retorna os cabeçalhos de sessão que simulam um usuário redirecionado para
     * a tela de "criar senha" após o primeiro acesso detectado pelo LoginController.
     */
    private function sessaoComPendencia(int $userId, string $role): array
    {
        return [
            'pending_password_creation' => $userId,
            'pending_password_role'     => $role,
        ];
    }

    // ─── Testes ───────────────────────────────────────────────────────────────

    /**
     * A rota GET /criar-senha redireciona usuários sem sessão pendente válida.
     */
    public function test_pagina_criar_senha_exige_sessao_pendente(): void
    {
        $response = $this->get(route('criar-senha.show'));

        // Sem sessão pendente, o middleware 'primeiro-acesso' deve bloquear o acesso
        $response->assertRedirect();
    }

    /**
     * A rota GET /criar-senha é acessível quando a sessão pendente existe.
     */
    public function test_pagina_criar_senha_carrega_com_sessao_valida(): void
    {
        $this->withoutVite();

        $aluno = $this->criarAlunoPendenteDeAtivacao();

        $response = $this
            ->withSession($this->sessaoComPendencia($aluno->id, 'aluno'))
            ->get(route('criar-senha.show'));

        $response->assertStatus(200);
    }

    /**
     * Um aluno consegue definir sua senha no primeiro acesso com sucesso.
     * Após o POST bem-sucedido, é redirecionado ao seu dashboard.
     */
    public function test_aluno_cria_senha_com_sucesso_no_primeiro_acesso(): void
    {
        $aluno = $this->criarAlunoPendenteDeAtivacao();

        $response = $this
            ->withSession($this->sessaoComPendencia($aluno->id, 'aluno'))
            ->post(route('criar-senha.store'), [
                'senha'             => 'NovaSenha@2026',
                'senha_confirmacao' => 'NovaSenha@2026',
            ]);

        // Após definir a senha, é redirecionado ao dashboard do aluno
        $response->assertRedirect(route('dashboard.aluno'));

        // O remember_token deve ser apagado (token de ativação consumido)
        $aluno->refresh();
        $this->assertNull($aluno->remember_token);

        // A senha deve ter sido salva (campo 'password' não deve ser mais null)
        $this->assertNotNull($aluno->password);
    }

    /**
     * Um professor consegue definir sua senha no primeiro acesso.
     */
    public function test_professor_cria_senha_com_sucesso_no_primeiro_acesso(): void
    {
        $professor = $this->criarProfessorPendenteDeAtivacao();

        $response = $this
            ->withSession($this->sessaoComPendencia($professor->id, 'professor'))
            ->post(route('criar-senha.store'), [
                'senha'             => 'ProfSenha@2026',
                'senha_confirmacao' => 'ProfSenha@2026',
            ]);

        $response->assertRedirect(route('dashboard.professor'));

        $professor->refresh();
        $this->assertNull($professor->remember_token);
    }

    /**
     * A tentativa de usar um token já consumido (remember_token = null) é recusada.
     * Isso garante que o link de "primeiro acesso" é de uso único.
     */
    public function test_token_ja_utilizado_e_recusado(): void
    {
        // Aluno com remember_token = null: token já consumido anteriormente
        $aluno = AlunoModel::factory()->create([
            'password'       => bcrypt('senhaExistente'),
            'remember_token' => null,
        ]);

        $response = $this
            ->withSession($this->sessaoComPendencia($aluno->id, 'aluno'))
            ->post(route('criar-senha.store'), [
                'senha'             => 'TentarNovamente@2026',
                'senha_confirmacao' => 'TentarNovamente@2026',
            ]);

        // Deve redirecionar para a tela de login com mensagem de erro
        $response->assertRedirect(route('login_form'));
    }

    /**
     * Senhas que não coincidem são rejeitadas pela validação do Laravel.
     */
    public function test_senhas_divergentes_sao_rejeitadas(): void
    {
        $aluno = $this->criarAlunoPendenteDeAtivacao();

        $response = $this
            ->withSession($this->sessaoComPendencia($aluno->id, 'aluno'))
            ->post(route('criar-senha.store'), [
                'senha'             => 'SenhaCorreta@2026',
                'senha_confirmacao' => 'SenhaDiferente@2026',
            ]);

        $response->assertSessionHasErrors('senha');
    }

    /**
     * Uma senha com menos de 8 caracteres é rejeitada pela validação.
     */
    public function test_senha_curta_e_rejeitada(): void
    {
        $aluno = $this->criarAlunoPendenteDeAtivacao();

        $response = $this
            ->withSession($this->sessaoComPendencia($aluno->id, 'aluno'))
            ->post(route('criar-senha.store'), [
                'senha'             => '1234',
                'senha_confirmacao' => '1234',
            ]);

        $response->assertSessionHasErrors('senha');
    }

    /**
     * Uma sessão com role inválida redireciona para login com erro.
     */
    public function test_role_invalida_na_sessao_redireciona_para_login(): void
    {
        $aluno = $this->criarAlunoPendenteDeAtivacao();

        $response = $this
            ->withSession([
                'pending_password_creation' => $aluno->id,
                'pending_password_role'     => 'role_inexistente',
            ])
            ->post(route('criar-senha.store'), [
                'senha'             => 'SenhaValida@2026',
                'senha_confirmacao' => 'SenhaValida@2026',
            ]);

        $response->assertRedirect(route('login_form'));
    }
}
