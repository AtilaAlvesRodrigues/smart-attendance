<?php

namespace Tests\Feature;

use App\Models\Justificativa;
use App\Models\Presenca;
use App\Models\ProfessorModel;
use App\Support\EstatisticasTurma;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use PragmaRX\Google2FA\Google2FA;
use Tests\Concerns\CriaCenarioAcademico;
use Tests\TestCase;

/**
 * Os ajustes menores listados como pendentes no relatório da entrega.
 */
class PendenciasMenoresTest extends TestCase
{
    use RefreshDatabase, CriaCenarioAcademico;

    // --- Login não apaga o token de primeiro acesso antes do código do 2FA

    public function test_senha_certa_sem_codigo_nao_apaga_o_token_de_primeiro_acesso(): void
    {
        $professor = ProfessorModel::factory()->create([
            'email' => 'token@teste.com', 'password' => Hash::make('senha-forte-123'), 'remember_token' => 'token-primeiro-acesso',
        ]);
        $professor->forceFill(['two_factor_secret' => (new Google2FA())->generateSecretKey(), 'two_factor_confirmed_at' => now()])->save();

        $this->post('/login/professor', ['cpf_email' => 'token@teste.com', 'password' => 'senha-forte-123'])
            ->assertRedirect(route('dois-fatores.desafio'));

        $this->assertSame('token-primeiro-acesso', $professor->fresh()->remember_token);
    }

    public function test_token_de_primeiro_acesso_e_apagado_depois_do_codigo(): void
    {
        $g2fa = new Google2FA();
        $segredo = $g2fa->generateSecretKey();
        $professor = ProfessorModel::factory()->create([
            'email' => 'token2@teste.com', 'password' => Hash::make('senha-forte-123'), 'remember_token' => 'token-primeiro-acesso',
        ]);
        $professor->forceFill(['two_factor_secret' => $segredo, 'two_factor_confirmed_at' => now()])->save();

        $this->post('/login/professor', ['cpf_email' => 'token2@teste.com', 'password' => 'senha-forte-123']);
        $this->post('/2fa/desafio', ['codigo' => $g2fa->getCurrentOtp($segredo)])->assertRedirect(route('dashboard.professor'));

        $this->assertNotSame('token-primeiro-acesso', $professor->fresh()->remember_token);
    }

    // --- Desmarcar uma presença abonada atualiza a justificativa

    public function test_desmarcar_presenca_abonada_revoga_a_justificativa(): void
    {
        Mail::fake();
        [$professor, $materia] = $this->professorComMateria();
        $presente = $this->alunoMatriculado($materia);
        $faltou = $this->alunoMatriculado($materia);
        $codigo = $this->registrarAula($materia, $professor, [$presente]);
        $j = Justificativa::create([
            'aluno_id' => $faltou->id, 'materia_id' => $materia->id, 'codigo_aula' => $codigo,
            'data_aula' => '2026-11-10', 'motivo' => 'Consulta médica no horário.', 'status' => Justificativa::STATUS_PENDENTE,
        ]);
        $this->actingAs($professor, 'professores')->post("/professor/justificativas/{$j->id}/decidir", ['decisao' => 'aprovar']);
        $this->assertSame(Justificativa::STATUS_APROVADA, $j->fresh()->status);

        $this->actingAs($professor, 'professores')
            ->post("/professor/gerenciar/{$materia->id}/chamadas/{$codigo}/presenca", ['aluno_id' => $faltou->id, 'presente' => '0', 'motivo' => 'Atestado era de outra data'])
            ->assertRedirect();

        $j->refresh();
        $this->assertSame(Justificativa::STATUS_RECUSADA, $j->status);
        $this->assertStringContainsString('Atestado era de outra data', $j->comentario_professor);
        $this->assertFalse(Presenca::where('aluno_id', $faltou->id)->where('codigo_aula', $codigo)->exists());
    }

    // --- Contagem de presentes só com quem está matriculado

    public function test_presentes_por_aula_ignoram_aluno_desmatriculado(): void
    {
        [$professor, $materia] = $this->professorComMateria();
        $fica = $this->alunoMatriculado($materia);
        $saiu = $this->alunoMatriculado($materia);
        $outro = $this->alunoMatriculado($materia);
        $codigo = $this->registrarAula($materia, $professor, [$fica, $saiu]);
        DB::table('aluno_materia')->where('aluno_id', $saiu->id)->delete();

        // 2 matriculados (fica, outro); 1 deles presente → 50%, não 100%
        $this->assertSame(50, EstatisticasTurma::daMateria($materia)['frequencia_por_aula'][0]['percentual']);

        $this->actingAs($professor, 'professores')
            ->get("/professor/gerenciar/{$materia->id}/chamadas/{$codigo}")
            ->assertSee('1 de 2 presentes');

        $this->actingAs($professor, 'professores')
            ->get("/professor/gerenciar/{$materia->id}/chamadas")
            ->assertSee('1 de 2 presentes');
    }

    // --- Tela de justificativas do aluno sem consultas repetidas por matéria

    public function test_tela_de_justificativas_nao_cresce_em_consultas_por_materia(): void
    {
        $contar = function (int $materias): int {
            DB::table('aluno_materia')->delete();
            $aluno = \App\Models\AlunoModel::factory()->create(['remember_token' => null]);
            for ($i = 0; $i < $materias; $i++) {
                [$p, $m] = $this->professorComMateria();
                DB::table('aluno_materia')->insert(['aluno_id' => $aluno->id, 'materia_id' => $m->id]);
                $this->registrarAula($m, $p, [$this->alunoMatriculado($m)]);
            }

            DB::flushQueryLog();
            DB::enableQueryLog();
            $this->actingAs($aluno, 'alunos')->get('/aluno/justificativas')->assertOk();
            $n = count(DB::getQueryLog());
            DB::disableQueryLog();

            return $n;
        };

        $this->assertSame($contar(2), $contar(6), 'O número de consultas não pode depender do número de matérias.');
    }
}
