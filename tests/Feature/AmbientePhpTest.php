<?php

namespace Tests\Feature;

use App\Models\AlunoModel;
use App\Models\ProfessorModel;
use App\Models\UsuarioMaster;
use App\Support\VersaoPhp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AmbientePhpTest extends TestCase
{
    use RefreshDatabase;

    public function test_versoes_corrigidas_e_vulneraveis_de_cada_serie(): void
    {
        foreach (['8.2.35', '8.2.40', '8.3.36', '8.4.27', '8.4.30', '8.5.12', '8.5.20'] as $v) {
            $this->assertTrue(VersaoPhp::corrigida($v), "{$v} deveria estar corrigida");
        }
        foreach (['8.2.34', '8.3.35', '8.3.30', '8.4.26', '8.4.14', '8.5.10', '8.5.11', '8.1.30', '7.4.33'] as $v) {
            $this->assertFalse(VersaoPhp::corrigida($v), "{$v} deveria estar vulnerável");
        }
    }

    public function test_serie_mais_nova_que_as_conhecidas_nao_afirma_nada(): void
    {
        $this->assertNull(VersaoPhp::corrigida('8.6.0'));
        $this->assertNull(VersaoPhp::corrigida('9.0.1'));
    }

    public function test_master_ve_a_versao_sem_segredos(): void
    {
        $master = UsuarioMaster::factory()->create();

        $resposta = $this->actingAs($master, 'masters')->getJson('/dashboard/master/ambiente/php');

        $resposta->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertJsonPath('php_versao', PHP_VERSION)
            ->assertJsonPath('serie', VersaoPhp::serie(PHP_VERSION))
            ->assertJsonStructure(['php_versao', 'serie', 'corrigida_a_partir_de', 'cves_corrigidas', 'sapi', 'memory_limit', 'laravel', 'ambiente', 'debug']);

        $corpo = $resposta->getContent();
        $this->assertStringNotContainsString(config('app.key'), $corpo);
        $this->assertStringNotContainsString(base_path(), $corpo);
    }

    public function test_aluno_e_professor_nao_acessam(): void
    {
        // Barrados pelo guard `masters` (401) ou pelo middleware de papel (403): o que importa é negar e não vazar a versão.
        foreach ([[AlunoModel::factory()->create(), 'alunos'], [ProfessorModel::factory()->create(), 'professores']] as [$usuario, $guard]) {
            $resposta = $this->actingAs($usuario, $guard)->getJson('/dashboard/master/ambiente/php');

            $this->assertContains($resposta->getStatusCode(), [401, 403], "{$guard} não deveria acessar");
            $this->assertStringNotContainsString('php_versao', $resposta->getContent());
        }
    }

    public function test_visitante_sem_login_nao_acessa(): void
    {
        $this->getJson('/dashboard/master/ambiente/php')->assertUnauthorized();
    }
}
