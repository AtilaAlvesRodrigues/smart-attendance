<?php

namespace Tests\Unit;

use App\Support\SituacaoAcademica;
use PHPUnit\Framework\TestCase;

class SituacaoAcademicaTest extends TestCase
{
    public function test_meio_do_semestre_sem_faltas_nao_reprova(): void
    {
        // 15 de 40 aulas dadas e o aluno foi a todas: não pode aparecer como reprovado
        $s = new SituacaoAcademica(aulasRealizadas: 15, presencas: 15, aulasPrevistas: 40);

        $this->assertSame(0, $s->faltas);
        $this->assertSame(10, $s->limiteFaltas);
        $this->assertSame(100, $s->frequencia);
        $this->assertSame('regular', $s->status);
    }

    public function test_reprova_por_falta_so_quando_passa_do_limite(): void
    {
        $noLimite = new SituacaoAcademica(aulasRealizadas: 20, presencas: 10, aulasPrevistas: 40);
        $passou = new SituacaoAcademica(aulasRealizadas: 21, presencas: 10, aulasPrevistas: 40);

        $this->assertSame(10, $noLimite->faltas);
        $this->assertNotSame('reprovado_falta', $noLimite->status);
        $this->assertSame('reprovado_falta', $passou->status);
    }

    public function test_perto_do_limite_gera_atencao(): void
    {
        $s = new SituacaoAcademica(aulasRealizadas: 12, presencas: 6, aulasPrevistas: 30);

        $this->assertSame(6, $s->faltas);
        $this->assertSame(7, $s->limiteFaltas);
        $this->assertSame('atencao', $s->status);
        $this->assertStringContainsString('1 falta', $s->explicacao());
    }

    public function test_aprovacao_so_com_todas_as_notas(): void
    {
        $parcial = new SituacaoAcademica(10, 10, 40, [9.0, null, null, null]);
        $final = new SituacaoAcademica(10, 10, 40, [6.0, 5.0, 4.0, 7.0]);
        $reprovado = new SituacaoAcademica(10, 10, 40, [3.0, 4.0, 5.0, 4.0]);

        $this->assertSame('regular', $parcial->status);
        $this->assertSame(9.0, $parcial->media);
        $this->assertSame('aprovado', $final->status);
        $this->assertSame(5.5, $final->media);
        $this->assertSame('reprovado', $reprovado->status);
    }

    public function test_media_parcial_baixa_gera_atencao(): void
    {
        $s = new SituacaoAcademica(10, 10, 40, [4.5, 4.0, null, null]);

        $this->assertSame('atencao', $s->status);
        $this->assertStringContainsString('abaixo de 5,0', $s->explicacao());
    }

    public function test_sem_aulas_realizadas_frequencia_cheia(): void
    {
        $s = new SituacaoAcademica(0, 0, 30);

        $this->assertSame(100, $s->frequencia);
        $this->assertSame(0, $s->faltas);
    }

    public function test_frequencia_baixa_gera_atencao_mesmo_longe_do_limite(): void
    {
        $s = new SituacaoAcademica(aulasRealizadas: 5, presencas: 0, aulasPrevistas: 30);

        $this->assertSame(5, $s->faltas);
        $this->assertSame(0, $s->frequencia);
        $this->assertSame('atencao', $s->status);
        $this->assertStringContainsString('abaixo dos 75%', $s->explicacao());
    }
}
