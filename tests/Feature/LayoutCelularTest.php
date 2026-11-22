<?php

namespace Tests\Feature;

use App\Models\UsuarioMaster;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CriaCenarioAcademico;
use Tests\TestCase;

/**
 * Ajustes do layout no celular: o que o CSS precisa encontrar no HTML para a
 * barra de baixo, a busca e as tabelas funcionarem numa tela de 360 px.
 */
class LayoutCelularTest extends TestCase
{
    use RefreshDatabase, CriaCenarioAcademico;

    public function test_barra_do_professor_tem_nomes_curtos_para_o_celular(): void
    {
        [$professor] = $this->professorComMateria();

        $html = $this->actingAs($professor, 'professores')->get('/professor/gerenciar')->assertOk()->getContent();

        // Nome completo continua no computador; o curto aparece só na barra de baixo
        $this->assertStringContainsString('<span class="pal-sidebar-label-longo">Presença / QR</span>', $html);
        $this->assertStringContainsString('<span class="pal-sidebar-label-curto" aria-hidden="true">QR Code</span>', $html);
        $this->assertStringContainsString('aria-label="Segurança da conta"', $html);
        $this->assertStringContainsString('aria-current="page"', $html);
    }

    public function test_busca_do_master_nao_fica_escondida_no_celular(): void
    {
        $master = UsuarioMaster::factory()->create();

        foreach (['alunos', 'professores', 'materias'] as $tela) {
            $html = $this->actingAs($master, 'masters')->get("/dashboard/master/{$tela}")->assertOk()->getContent();

            $this->assertStringContainsString("id=\"search-{$tela}\"", $html);
            $this->assertStringNotContainsString('hidden md:block', $html, "A busca de {$tela} não pode sumir no celular.");
            $this->assertStringContainsString('rc-tabela-cartoes', $html, "A tabela de {$tela} precisa virar cartões no celular.");
        }
    }

    public function test_layout_carrega_o_script_das_tabelas_em_cartoes(): void
    {
        [$professor, $materia] = $this->professorComMateria();
        $aluno = $this->alunoMatriculado($materia);
        $codigo = $this->registrarAula($materia, $professor, [$aluno]);

        $html = $this->actingAs($professor, 'professores')
            ->get("/professor/gerenciar/{$materia->id}/chamadas/{$codigo}")
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('js/tabela-cartoes.js', $html);
        $this->assertStringContainsString('rc-table rc-tabela-cartoes', $html);
        $this->assertFileExists(public_path('js/tabela-cartoes.js'));
    }
}
