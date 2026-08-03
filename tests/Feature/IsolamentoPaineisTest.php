<?php

namespace Tests\Feature;

use App\Models\AlunoModel;
use App\Models\Materia;
use App\Models\ProfessorModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Testa o isolamento dos painéis por guard de autenticação.
 *
 * Garante que:
 * - Um aluno autenticado NUNCA acessa rotas exclusivas de professor.
 * - Um professor autenticado NUNCA acessa rotas exclusivas de aluno.
 * - Usuários sem autenticação são barrados em todas as rotas protegidas.
 *
 * Este é o teste mais crítico de segurança do sistema: ele valida que
 * o isolamento entre guards ('alunos' vs 'professores') funciona corretamente
 * e que o middleware 'role' reforça o controle de acesso em cada painel.
 */
class IsolamentoPaineisTest extends TestCase
{
    use RefreshDatabase;

    // ─── Rotas protegidas por painel ──────────────────────────────────────────

    /**
     * Rotas exclusivas do painel do professor.
     * Um aluno nunca deve acessar nenhuma delas.
     */
    private array $rotasProfessor = [
        'dashboard.professor',
        'professor.presenca.index',
        'professor.gerenciar.index',
        'professor.relatorios',
    ];

    /**
     * Rotas exclusivas do painel do aluno.
     * Um professor nunca deve acessar nenhuma delas.
     */
    private array $rotasAluno = [
        'dashboard.aluno',
    ];

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function criarAluno(): AlunoModel
    {
        return AlunoModel::factory()->create(['password' => bcrypt('senha123')]);
    }

    private function criarProfessor(): ProfessorModel
    {
        return ProfessorModel::factory()->create(['password' => bcrypt('senha123')]);
    }

    // ─── Testes: Aluno tentando acessar painel do professor ───────────────────

    /**
     * Um aluno autenticado (guard 'alunos') não consegue acessar
     * nenhuma rota do painel do professor.
     *
     * @dataProvider rotasDoProfessor
     */
    public function test_aluno_nao_acessa_rotas_do_professor(string $nomeRota): void
    {
        $aluno = $this->criarAluno();

        $response = $this
            ->actingAs($aluno, 'alunos')
            ->get(route($nomeRota));

        // Deve ser negado: 403 (Forbidden) ou redirecionamento para login/dashboard do aluno
        $this->assertNotEquals(
            200,
            $response->status(),
            "Aluno conseguiu acessar a rota '{$nomeRota}' indevidamente (esperado: não-200)."
        );
    }

    public static function rotasDoProfessor(): array
    {
        return [
            'dashboard do professor'      => ['dashboard.professor'],
            'índice de presença'           => ['professor.presenca.index'],
            'gerenciar matérias'          => ['professor.gerenciar.index'],
            'relatórios'                  => ['professor.relatorios'],
        ];
    }

    // ─── Testes: Professor tentando acessar painel do aluno ───────────────────

    /**
     * Um professor autenticado (guard 'professores') não consegue acessar
     * nenhuma rota do painel do aluno.
     */
    public function test_professor_nao_acessa_dashboard_do_aluno(): void
    {
        $professor = $this->criarProfessor();

        $response = $this
            ->actingAs($professor, 'professores')
            ->get(route('dashboard.aluno'));

        $this->assertNotEquals(
            200,
            $response->status(),
            "Professor conseguiu acessar o dashboard do aluno indevidamente."
        );
    }

    // ─── Testes: Usuário não autenticado ──────────────────────────────────────

    /**
     * Um visitante não autenticado é redirecionado para login
     * ao tentar acessar o dashboard do professor.
     */
    public function test_visitante_e_redirecionado_ao_tentar_acessar_painel_do_professor(): void
    {
        $response = $this->get(route('dashboard.professor'));

        $response->assertRedirect();
    }

    /**
     * Um visitante não autenticado é redirecionado para login
     * ao tentar acessar o dashboard do aluno.
     */
    public function test_visitante_e_redirecionado_ao_tentar_acessar_painel_do_aluno(): void
    {
        $response = $this->get(route('dashboard.aluno'));

        $response->assertRedirect();
    }

    // ─── Testes: Acesso legítimo ───────────────────────────────────────────────

    /**
     * Um professor autenticado consegue acessar seu próprio dashboard.
     */
    public function test_professor_autentico_acessa_seu_dashboard(): void
    {
        $this->withoutVite();

        $professor = $this->criarProfessor();

        $response = $this
            ->actingAs($professor, 'professores')
            ->get(route('dashboard.professor'));

        $response->assertStatus(200);
    }

    /**
     * Um aluno autenticado consegue acessar seu próprio dashboard.
     */
    public function test_aluno_autentico_acessa_seu_dashboard(): void
    {
        $this->withoutVite();

        $aluno = $this->criarAluno();

        $response = $this
            ->actingAs($aluno, 'alunos')
            ->get(route('dashboard.aluno'));

        $response->assertStatus(200);
    }
}
