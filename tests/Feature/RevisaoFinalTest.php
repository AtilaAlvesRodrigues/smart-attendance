<?php

namespace Tests\Feature;

use App\Models\Justificativa;
use App\Models\Materia;
use App\Models\Presenca;
use App\Models\ProfessorModel;
use App\Models\UsuarioMaster;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use OpenSpout\Reader\XLSX\Reader;
use Tests\Concerns\CriaCenarioAcademico;
use Tests\TestCase;

/**
 * Achados da revisão final da branch (cada teste reproduz um deles).
 */
class RevisaoFinalTest extends TestCase
{
    use RefreshDatabase, CriaCenarioAcademico;

    private function justificativa($aluno, $materia, string $codigo): Justificativa
    {
        return Justificativa::create([
            'aluno_id' => $aluno->id, 'materia_id' => $materia->id, 'codigo_aula' => $codigo,
            'data_aula' => '2026-11-10', 'motivo' => 'Consulta médica no horário.', 'status' => Justificativa::STATUS_PENDENTE,
        ]);
    }

    // I1 — telas não quebram depois de excluir aluno ou matéria

    public function test_telas_funcionam_apos_excluir_aluno_com_justificativa(): void
    {
        [$professor, $materia] = $this->professorComMateria();
        $presente = $this->alunoMatriculado($materia);
        $faltou = $this->alunoMatriculado($materia);
        $codigo = $this->registrarAula($materia, $professor, [$presente]);
        $this->justificativa($faltou, $materia, $codigo);

        $faltou->delete();
        $presente->delete();

        $this->actingAs($professor, 'professores')->get('/professor/justificativas')->assertOk();
        $this->actingAs($professor, 'professores')->get('/professor/gerenciar/relatorios')->assertOk();
    }

    public function test_tela_do_aluno_funciona_apos_excluir_materia(): void
    {
        [$professor, $materia] = $this->professorComMateria();
        $presente = $this->alunoMatriculado($materia);
        $faltou = $this->alunoMatriculado($materia);
        $codigo = $this->registrarAula($materia, $professor, [$presente]);
        $this->justificativa($faltou, $materia, $codigo);

        $materia->delete();

        $this->actingAs($faltou, 'alunos')->get('/aluno/justificativas')->assertOk();
    }

    // I2 — o mesmo código de aula em matérias diferentes (dados antigos) é permitido

    public function test_mesmo_codigo_em_materias_diferentes_nao_conflita(): void
    {
        [$p1, $m1] = $this->professorComMateria();
        [$p2, $m2] = $this->professorComMateria();
        $aluno = $this->alunoMatriculado($m1);
        DB::table('aluno_materia')->insert(['aluno_id' => $aluno->id, 'materia_id' => $m2->id]);

        $this->registrarAula($m1, $p1, [$aluno], '2026-11-10', 'GEN0');
        $this->registrarAula($m2, $p2, [$aluno], '2026-11-10', 'GEN0');

        $this->assertSame(2, Presenca::where('aluno_id', $aluno->id)->where('codigo_aula', 'GEN0')->count());
    }

    public function test_justificativa_com_mesmo_codigo_em_outra_materia(): void
    {
        [$p1, $m1] = $this->professorComMateria();
        [$p2, $m2] = $this->professorComMateria();
        $aluno = $this->alunoMatriculado($m1);
        DB::table('aluno_materia')->insert(['aluno_id' => $aluno->id, 'materia_id' => $m2->id]);
        $this->registrarAula($m1, $p1, [$this->alunoMatriculado($m1)], '2026-11-10', 'GEN0');
        $this->registrarAula($m2, $p2, [$this->alunoMatriculado($m2)], '2026-11-10', 'GEN0');
        $this->justificativa($aluno, $m1, 'GEN0');

        $this->actingAs($aluno, 'alunos')->post('/aluno/justificativas', [
            'materia_id' => $m2->id, 'codigo_aula' => 'GEN0', 'motivo' => 'Consulta médica no horário.',
        ])->assertSessionHasNoErrors();

        $this->assertSame(2, Justificativa::where('aluno_id', $aluno->id)->count());
    }

    // I3 — exportação com empate de horário não repete nem perde linhas

    public function test_exportacao_com_mais_de_500_linhas_no_mesmo_segundo(): void
    {
        $this->freezeSecond();
        [$professor, $materia] = $this->professorComMateria();
        $alunos = \App\Models\AlunoModel::factory()->count(30)->create(['remember_token' => null]);
        foreach ($alunos as $a) {
            DB::table('aluno_materia')->insert(['aluno_id' => $a->id, 'materia_id' => $materia->id]);
        }
        for ($aula = 1; $aula <= 18; $aula++) {
            $this->registrarAula($materia, $professor, $alunos->all(), sprintf('2026-10-%02d', $aula), "A{$aula}");
        }

        $arquivo = tempnam(sys_get_temp_dir(), 'xlsx');
        file_put_contents($arquivo, $this->actingAs($professor, 'professores')->get('/professor/gerenciar/relatorios/exportar')->streamedContent());
        $reader = new Reader();
        $reader->open($arquivo);
        $linhas = [];
        foreach ($reader->getSheetIterator() as $aba) {
            if ($aba->getName() === 'Presenças') {
                foreach ($aba->getRowIterator() as $l) {
                    $linhas[] = $l->toArray();
                }
            }
        }
        $reader->close();
        unlink($arquivo);

        $this->assertCount(541, $linhas); // cabeçalho + 540
        $pares = array_map(fn ($l) => $l[1] . '|' . $l[3], array_slice($linhas, 1));
        $this->assertCount(540, array_unique($pares), 'Cada presença deve aparecer exatamente uma vez.');
    }

    // I4 — limite do desafio 2FA por usuário, não por IP

    public function test_desafio_2fa_nao_trava_varios_usuarios_na_mesma_rede(): void
    {
        $g2fa = new \PragmaRX\Google2FA\Google2FA();

        for ($i = 1; $i <= 6; $i++) {
            $prof = ProfessorModel::factory()->create(['email' => "p{$i}@rede.test", 'password' => Hash::make('senha-forte-1'), 'remember_token' => null]);
            $segredo = $g2fa->generateSecretKey();
            $prof->forceFill(['two_factor_secret' => $segredo, 'two_factor_confirmed_at' => now()])->save();

            $this->post('/login/professor', ['cpf_email' => "p{$i}@rede.test", 'password' => 'senha-forte-1']);
            $this->post('/2fa/desafio', ['codigo' => $g2fa->getCurrentOtp($segredo)])
                ->assertRedirect(route('dashboard.professor'));
            auth('professores')->logout();
        }
    }

    public function test_desafio_2fa_limita_tentativas_do_mesmo_usuario(): void
    {
        $prof = ProfessorModel::factory()->create(['email' => 'alvo@rede.test', 'password' => Hash::make('senha-forte-1'), 'remember_token' => null]);
        $prof->forceFill(['two_factor_secret' => (new \PragmaRX\Google2FA\Google2FA())->generateSecretKey(), 'two_factor_confirmed_at' => now()])->save();
        $this->post('/login/professor', ['cpf_email' => 'alvo@rede.test', 'password' => 'senha-forte-1']);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/2fa/desafio', ['codigo' => '000000']);
        }

        $this->post('/2fa/desafio', ['codigo' => '000000'])->assertStatus(429);
    }

    // Spec, objetivo 5 — Master vê a distribuição de situações

    public function test_graficos_do_master_trazem_distribuicao_de_situacoes(): void
    {
        [$professor, $materia] = $this->professorComMateria();
        $this->registrarAula($materia, $professor, [$this->alunoMatriculado($materia)]);
        $master = UsuarioMaster::factory()->create();

        $this->actingAs($master, 'masters')->getJson('/dashboard/master/graficos.json')
            ->assertOk()
            ->assertJsonPath('situacoes.regular', 1);

        $this->actingAs($master, 'masters')->get('/dashboard/master/graficos')
            ->assertSee('grafico-situacoes', false);
    }

    // Upload num preview da Vercel (APP_ENV diferente de production, disco somente leitura)

    public function test_anexo_desligado_na_vercel_sem_supabase_storage(): void
    {
        $_SERVER['VERCEL'] = '1';
        try {
            $this->assertFalse(Justificativa::anexoHabilitado());
        } finally {
            unset($_SERVER['VERCEL']);
        }

        $this->assertTrue(Justificativa::anexoHabilitado());
    }
}
