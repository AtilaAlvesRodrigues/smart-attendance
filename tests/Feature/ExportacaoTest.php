<?php

namespace Tests\Feature;

use App\Models\UsuarioMaster;
use Illuminate\Foundation\Testing\RefreshDatabase;
use OpenSpout\Reader\XLSX\Reader;
use Tests\Concerns\CriaCenarioAcademico;
use Tests\TestCase;

class ExportacaoTest extends TestCase
{
    use RefreshDatabase, CriaCenarioAcademico;

    private const XLSX = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

    /** @return array<string, list<list<mixed>>> linhas de cada aba */
    private function lerPlanilha($resposta): array
    {
        $arquivo = tempnam(sys_get_temp_dir(), 'xlsx');
        file_put_contents($arquivo, $resposta->streamedContent());

        $reader = new Reader();
        $reader->open($arquivo);
        $abas = [];
        foreach ($reader->getSheetIterator() as $aba) {
            foreach ($aba->getRowIterator() as $linha) {
                $abas[$aba->getName()][] = $linha->toArray();
            }
        }
        $reader->close();
        unlink($arquivo);

        return $abas;
    }

    public function test_professor_exporta_xlsx(): void
    {
        [$professor, $materia] = $this->professorComMateria();
        $a1 = $this->alunoMatriculado($materia, ['nome' => 'Ana Souza', 'ra' => '11112222']);
        $a2 = $this->alunoMatriculado($materia, ['nome' => 'Bruno Lima']);
        $this->registrarAula($materia, $professor, [$a1, $a2], '2026-11-03');

        $resposta = $this->actingAs($professor, 'professores')->get('/professor/gerenciar/relatorios/exportar');

        $resposta->assertOk();
        $this->assertSame(self::XLSX, $resposta->headers->get('Content-Type'));
        $this->assertStringContainsString('presencas-', $resposta->headers->get('Content-Disposition'));

        $abas = $this->lerPlanilha($resposta);
        $this->assertSame(['Aluno', 'RA', 'Matéria', 'Data da aula', 'Horário', 'Origem', 'Registrado em'], $abas['Presenças'][0]);
        $this->assertCount(3, $abas['Presenças']);
        $this->assertContains('Ana Souza', array_column($abas['Presenças'], 0));
        $this->assertContains('11112222', array_column($abas['Presenças'], 1));
        $this->assertSame('03/11/2026', $abas['Presenças'][1][3]);

        $this->assertSame(['Aluno', 'RA', 'Matéria', 'Presenças', 'Faltas', 'Limite de faltas', 'Frequência (%)', 'Situação'], $abas['Resumo'][0]);
        $this->assertCount(3, $abas['Resumo']);
    }

    public function test_exportacao_respeita_filtro_materia(): void
    {
        [$professor, $m1] = $this->professorComMateria();
        $m2 = \App\Models\Materia::factory()->create();
        \Illuminate\Support\Facades\DB::table('materia_professor')->insert(['professor_id' => $professor->id, 'materia_id' => $m2->id]);
        $this->registrarAula($m1, $professor, [$this->alunoMatriculado($m1)]);
        $this->registrarAula($m2, $professor, [$this->alunoMatriculado($m2), $this->alunoMatriculado($m2)]);

        $abas = $this->lerPlanilha($this->actingAs($professor, 'professores')
            ->get("/professor/gerenciar/relatorios/exportar?materia_id={$m2->id}"));

        $this->assertCount(3, $abas['Presenças']);
        $this->assertSame([$m2->nome, $m2->nome], array_slice(array_column($abas['Presenças'], 2), 1));
    }

    public function test_exportacao_sem_cpf_email(): void
    {
        [$professor, $materia] = $this->professorComMateria();
        $aluno = $this->alunoMatriculado($materia, ['email' => 'privado@aluno.com', 'cpf' => '987.654.321-00']);
        $this->registrarAula($materia, $professor, [$aluno]);

        $abas = $this->lerPlanilha($this->actingAs($professor, 'professores')->get('/professor/gerenciar/relatorios/exportar'));
        $tudo = json_encode($abas);

        $this->assertStringNotContainsString('privado@aluno.com', $tudo);
        $this->assertStringNotContainsString('987.654.321-00', $tudo);
    }

    public function test_exportar_vazio(): void
    {
        [$professor] = $this->professorComMateria();

        $resposta = $this->actingAs($professor, 'professores')->get('/professor/gerenciar/relatorios/exportar');

        $resposta->assertOk();
        $abas = $this->lerPlanilha($resposta);
        $this->assertCount(1, $abas['Presenças']);
    }

    public function test_professor_nao_exporta_presencas_de_outro(): void
    {
        [$professor, $materia] = $this->professorComMateria();
        [$outro, $outraMateria] = $this->professorComMateria();
        $this->registrarAula($outraMateria, $outro, [$this->alunoMatriculado($outraMateria, ['nome' => 'Aluno Alheio'])]);

        $abas = $this->lerPlanilha($this->actingAs($professor, 'professores')
            ->get("/professor/gerenciar/relatorios/exportar?materia_id={$outraMateria->id}"));

        $this->assertStringNotContainsString('Aluno Alheio', json_encode($abas));
    }

    public function test_master_exporta_central_de_presencas(): void
    {
        [$professor, $materia] = $this->professorComMateria();
        $this->registrarAula($materia, $professor, [$this->alunoMatriculado($materia, ['nome' => 'Carla Dias'])]);
        $master = UsuarioMaster::factory()->create();

        $resposta = $this->actingAs($master, 'masters')->get('/dashboard/master/presenca/exportar');

        $resposta->assertOk();
        $this->assertSame(self::XLSX, $resposta->headers->get('Content-Type'));
        $this->assertContains('Carla Dias', array_column($this->lerPlanilha($resposta)['Presenças'], 0));
    }

    public function test_aluno_nao_exporta(): void
    {
        [, $materia] = $this->professorComMateria();
        $aluno = $this->alunoMatriculado($materia);

        $this->actingAs($aluno, 'alunos')->get('/dashboard/master/presenca/exportar')->assertRedirect();
        $this->actingAs($aluno, 'alunos')->get('/professor/gerenciar/relatorios/exportar')->assertRedirect();
    }
}
