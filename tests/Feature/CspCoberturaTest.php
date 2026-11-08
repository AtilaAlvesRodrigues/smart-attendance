<?php

namespace Tests\Feature;

use App\Models\AlunoModel;
use App\Models\Materia;
use App\Models\ProfessorModel;
use App\Models\UsuarioMaster;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Garante que NENHUMA tela do sistema tenha <script> ou <style> em bloco sem nonce.
 * Um bloco sem nonce é bloqueado pelo navegador (tela sem estilo ou botão morto),
 * e o PHPUnit comum não percebe isso.
 */
class CspCoberturaTest extends TestCase
{
    use RefreshDatabase;

    private function assertBlocosComNonce(string $url, $resposta): void
    {
        $resposta->assertOk();

        $csp = $resposta->headers->get('Content-Security-Policy');
        $this->assertNotNull($csp, "sem CSP em {$url}");
        preg_match("/'nonce-([^']+)'/", $csp, $m);

        preg_match_all('/<(script|style)\b([^>]*)>/i', $resposta->getContent(), $tags, PREG_SET_ORDER);
        foreach ($tags as $tag) {
            if (stripos($tag[2], ' src=') !== false) {
                continue;
            }
            $this->assertStringContainsString("nonce=\"{$m[1]}\"", $tag[2], "<{$tag[1]}> sem nonce em {$url}");
        }
    }

    public function test_paginas_publicas(): void
    {
        foreach (['/login', '/login/aluno', '/login/professor', '/evento/checkin?token=x', '/aluno/demonstracao', '/aluno/saiba-mais', '/professor/demonstracao', '/professor/saiba-mais', '/esqueci-senha/aluno', '/esqueci-senha/professor', '/solicitar-acesso/aluno', '/solicitar-acesso/professor'] as $url) {
            $this->assertBlocosComNonce($url, $this->get($url));
        }
    }

    public function test_paginas_do_aluno(): void
    {
        $aluno = AlunoModel::factory()->create();
        $aluno->materias()->attach(Materia::factory()->create()->id);

        foreach (['/dashboard/aluno'] as $url) {
            $this->assertBlocosComNonce($url, $this->actingAs($aluno, 'alunos')->get($url));
        }
    }

    public function test_paginas_do_professor(): void
    {
        $professor = ProfessorModel::factory()->create();
        $materia = Materia::factory()->create();
        $professor->materias()->attach($materia->id);

        $urls = ['/professor/dashboard', '/professor/presenca', "/professor/presenca/gerar/{$materia->id}", '/professor/gerenciar', '/professor/gerenciar/relatorios', '/professor/evento/presenca'];
        foreach ($urls as $url) {
            $this->assertBlocosComNonce($url, $this->actingAs($professor, 'professores')->get($url));
        }
    }

    public function test_paginas_do_master(): void
    {
        $master = UsuarioMaster::factory()->create();

        $urls = ['/dashboard/master', '/dashboard/master/alunos', '/dashboard/master/professores', '/dashboard/master/materias', '/dashboard/master/cadastrar', '/dashboard/master/presenca', '/dashboard/master/solicitacoes', '/dashboard/master/relatorio-seguranca'];
        foreach ($urls as $url) {
            $this->assertBlocosComNonce($url, $this->actingAs($master, 'masters')->get($url));
        }
    }
}
