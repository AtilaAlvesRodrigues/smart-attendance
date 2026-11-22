<?php

namespace Tests\Feature;

use App\Mail\AvisoFrequenciaMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\CriaCenarioAcademico;
use Tests\TestCase;

/**
 * Issue #10: aviso por e-mail ao aluno cuja matéria entra em Atenção por
 * frequência, no máximo uma vez por matéria por semana.
 */
class AvisoFrequenciaTest extends TestCase
{
    use RefreshDatabase, CriaCenarioAcademico;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        config(['services.cron.secret' => 'segredo-do-cron-de-teste']);
    }

    /**
     * Matéria com 8 aulas previstas (limite de 2 faltas) e 4 aulas dadas:
     * $faltoso falta em 2 (50% de frequência, atenção), $assiduo vai a todas.
     */
    private function turma(): array
    {
        [$professor, $materia] = $this->professorComMateria(totalAulas: 8);
        $assiduo = $this->alunoMatriculado($materia, ['nome' => 'Ana Assídua']);
        $faltoso = $this->alunoMatriculado($materia, ['nome' => 'Bruno Faltoso', 'email' => 'bruno@aluno.test']);
        $this->registrarAula($materia, $professor, [$assiduo, $faltoso], '2026-11-02');
        $this->registrarAula($materia, $professor, [$assiduo, $faltoso], '2026-11-03');
        $this->registrarAula($materia, $professor, [$assiduo], '2026-11-04');
        $this->registrarAula($materia, $professor, [$assiduo], '2026-11-05');

        return [$professor, $materia, $assiduo, $faltoso];
    }

    private function rodar(): void
    {
        $this->artisan('frequencia:avisar')->assertSuccessful();
    }

    public function test_avisa_o_aluno_em_atencao_por_frequencia(): void
    {
        [, $materia, , $faltoso] = $this->turma();

        $this->rodar();

        Mail::assertSent(AvisoFrequenciaMail::class, 1);
        Mail::assertSent(AvisoFrequenciaMail::class, function (AvisoFrequenciaMail $m) use ($faltoso, $materia) {
            $html = $m->render();

            return $m->hasTo('bruno@aluno.test')
                && str_contains($html, $materia->nome)
                && str_contains($html, $m->situacao->explicacao())
                && $m->aluno->is($faltoso);
        });
        $this->assertDatabaseHas('avisos_frequencia', ['aluno_id' => $faltoso->id, 'materia_id' => $materia->id]);
    }

    public function test_nao_avisa_quem_esta_em_dia(): void
    {
        [, , $assiduo] = $this->turma();

        $this->rodar();

        Mail::assertNotSent(AvisoFrequenciaMail::class, fn ($m) => $m->aluno->is($assiduo));
    }

    public function test_nao_avisa_atencao_so_por_media(): void
    {
        [$professor, $materia] = $this->professorComMateria(totalAulas: 40);
        $aluno = $this->alunoMatriculado($materia);
        $this->registrarAula($materia, $professor, [$aluno]);
        DB::table('aluno_materia')->where('aluno_id', $aluno->id)->update(['prova1' => 2.0]);

        $this->rodar();

        Mail::assertNothingSent();
    }

    public function test_no_maximo_um_aviso_por_materia_por_semana(): void
    {
        $this->turma();

        $this->rodar();
        $this->rodar();
        Mail::assertSent(AvisoFrequenciaMail::class, 1);

        $this->travel(6)->days();
        $this->rodar();
        Mail::assertSent(AvisoFrequenciaMail::class, 1);

        $this->travel(2)->days();
        $this->rodar();
        Mail::assertSent(AvisoFrequenciaMail::class, 2);
    }

    public function test_um_aviso_para_cada_materia_em_atencao(): void
    {
        [$professor, $materia, , $faltoso] = $this->turma();
        [$professor2, $outra] = $this->professorComMateria(totalAulas: 8);
        DB::table('aluno_materia')->insert(['aluno_id' => $faltoso->id, 'materia_id' => $outra->id]);
        $colega = $this->alunoMatriculado($outra);
        // Na outra matéria, o mesmo aluno vai a 2 de 4 aulas: também em atenção (não reprovado)
        $this->registrarAula($outra, $professor2, [$colega, $faltoso], '2026-11-02');
        $this->registrarAula($outra, $professor2, [$colega, $faltoso], '2026-11-03');
        $this->registrarAula($outra, $professor2, [$colega], '2026-11-04');
        $this->registrarAula($outra, $professor2, [$colega], '2026-11-05');

        $this->rodar();

        Mail::assertSent(AvisoFrequenciaMail::class, fn ($m) => $m->aluno->is($faltoso) && $m->materia->is($materia));
        Mail::assertSent(AvisoFrequenciaMail::class, fn ($m) => $m->aluno->is($faltoso) && $m->materia->is($outra));
    }

    public function test_aluno_excluido_nao_recebe(): void
    {
        [, , , $faltoso] = $this->turma();
        $faltoso->delete();

        $this->rodar();

        Mail::assertNothingSent();
    }

    public function test_falha_no_envio_nao_registra_e_continua_os_outros(): void
    {
        [$professor, $materia] = $this->professorComMateria(totalAulas: 8);
        $bruno = $this->alunoMatriculado($materia, ['email' => 'bruno@aluno.test']);
        $carla = $this->alunoMatriculado($materia, ['email' => 'carla@aluno.test']);
        $sempre = $this->alunoMatriculado($materia);
        // Bruno e Carla vão às 2 primeiras de 4 aulas: os dois em atenção
        $this->registrarAula($materia, $professor, [$sempre, $bruno, $carla], '2026-11-02');
        $this->registrarAula($materia, $professor, [$sempre, $bruno, $carla], '2026-11-03');
        $this->registrarAula($materia, $professor, [$sempre], '2026-11-04');
        $this->registrarAula($materia, $professor, [$sempre], '2026-11-05');

        $entregue = \Mockery::mock();
        $entregue->shouldReceive('send')->once();
        Mail::shouldReceive('to')->with('bruno@aluno.test')->andThrow(new \RuntimeException('SMTP fora do ar'));
        Mail::shouldReceive('to')->with('carla@aluno.test')->andReturn($entregue);

        $this->artisan('frequencia:avisar')->assertSuccessful();

        $this->assertDatabaseMissing('avisos_frequencia', ['aluno_id' => $bruno->id]);
        $this->assertDatabaseHas('avisos_frequencia', ['aluno_id' => $carla->id, 'materia_id' => $materia->id]);
    }

    public function test_rota_do_cron_exige_o_segredo(): void
    {
        $this->turma();

        $this->get('/cron/avisos-frequencia')->assertNotFound();
        $this->get('/cron/avisos-frequencia', ['Authorization' => 'Bearer errado'])->assertNotFound();
        Mail::assertNothingSent();

        $this->get('/cron/avisos-frequencia', ['Authorization' => 'Bearer segredo-do-cron-de-teste'])
            ->assertOk()
            ->assertJson(['avisos' => 1]);
        Mail::assertSent(AvisoFrequenciaMail::class, 1);
    }
}
