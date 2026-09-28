<?php

namespace Tests\Feature;

use App\Models\AlunoModel;
use App\Models\Materia;
use App\Models\Presenca;
use App\Models\ProfessorModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HistoricoAlunoTest extends TestCase
{
    use RefreshDatabase;

    private function chamada(Materia $materia, ProfessorModel $prof, AlunoModel $aluno, string $codigo, string $data): void
    {
        Presenca::create([
            'aluno_id' => $aluno->id, 'professor_id' => $prof->id, 'materia_id' => $materia->id,
            'data_aula' => $data, 'semestre' => '2/2026', 'horario' => 'N', 'codigo_aula' => $codigo,
        ]);
    }

    public function test_painel_mostra_presencas_e_faltas_por_data(): void
    {
        $prof = ProfessorModel::factory()->create();
        [$aluno, $colega] = AlunoModel::factory()->count(2)->create();
        $calculo = Materia::factory()->create(['nome' => 'Cálculo']);
        $fisica = Materia::factory()->create(['nome' => 'Física']);
        $aluno->materias()->attach([$calculo->id, $fisica->id]);

        // Cálculo: 2 aulas, o aluno foi só à primeira
        $this->chamada($calculo, $prof, $aluno, 'A1', '2026-09-01');
        $this->chamada($calculo, $prof, $colega, 'A1', '2026-09-01');
        $this->chamada($calculo, $prof, $colega, 'A2', '2026-09-08');
        // Física usa o mesmo código "A2", e o aluno esteve presente nela
        $this->chamada($fisica, $prof, $aluno, 'A2', '2026-09-09');

        $response = $this->actingAs($aluno, 'alunos')->get('/dashboard/aluno')->assertOk();

        $materias = $response->viewData('aluno')->materias->keyBy('nome');
        $histCalculo = $materias['Cálculo']->historico->map(fn ($a) => [$a['data']->format('Y-m-d'), $a['presente']])->all();

        $this->assertSame([['2026-09-08', false], ['2026-09-01', true]], $histCalculo);
        $this->assertTrue($materias['Física']->historico->first()['presente']);
        $response->assertSee('Ver histórico de aulas (2)');
    }

    public function test_central_do_master_mostra_as_mesmas_faltas_do_aluno(): void
    {
        $prof = ProfessorModel::factory()->create();
        $aluno = AlunoModel::factory()->create();
        $materia = Materia::factory()->create(['total_aulas' => 40]);
        $aluno->materias()->attach($materia->id);
        // 3 aulas dadas, o aluno foi a todas: 0 faltas (e não 40 − 3 = 37)
        foreach (['A1' => '2026-09-01', 'A2' => '2026-09-08', 'A3' => '2026-09-15'] as $codigo => $data) {
            $this->chamada($materia, $prof, $aluno, $codigo, $data);
        }

        $this->actingAs(\App\Models\UsuarioMaster::factory()->create(), 'masters');

        $this->getJson('/dashboard/master/search/presencas')
            ->assertOk()
            ->assertJsonPath('0.faltas', 0)
            ->assertJsonPath('0.limite_faltas', 10);

        $this->get('/dashboard/master/presenca')->assertOk()->assertSee('de 10 permitidas');
    }
}
