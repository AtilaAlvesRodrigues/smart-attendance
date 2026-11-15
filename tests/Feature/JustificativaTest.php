<?php

namespace Tests\Feature;

use App\Mail\JustificativaDecididaMail;
use App\Models\Justificativa;
use App\Models\Presenca;
use App\Models\PresencaAjuste;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CriaCenarioAcademico;
use Tests\TestCase;

class JustificativaTest extends TestCase
{
    use RefreshDatabase, CriaCenarioAcademico;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('justificativas');
        Mail::fake();
    }

    /** Professor, matéria, um aluno presente e um que faltou à aula. */
    private function cenario(): array
    {
        [$professor, $materia] = $this->professorComMateria();
        $presente = $this->alunoMatriculado($materia);
        $faltou = $this->alunoMatriculado($materia);
        $codigo = $this->registrarAula($materia, $professor, [$presente], '2026-11-05');

        return [$professor, $materia, $presente, $faltou, $codigo];
    }

    private function enviar($aluno, $materia, string $codigo, array $extra = [])
    {
        return $this->actingAs($aluno, 'alunos')->post('/aluno/justificativas', array_merge([
            'materia_id'  => $materia->id,
            'codigo_aula' => $codigo,
            'motivo'      => 'Consulta médica no horário da aula.',
        ], $extra));
    }

    private function criarPendente($faltou, $materia, string $codigo, ?string $anexo = null): Justificativa
    {
        return Justificativa::create([
            'aluno_id'    => $faltou->id,
            'materia_id'  => $materia->id,
            'codigo_aula' => $codigo,
            'data_aula'   => '2026-11-05',
            'motivo'      => 'Consulta médica no horário da aula.',
            'anexo_path'  => $anexo,
            'anexo_nome'  => $anexo ? 'atestado.pdf' : null,
            'anexo_mime'  => $anexo ? 'application/pdf' : null,
            'status'      => Justificativa::STATUS_PENDENTE,
        ]);
    }

    public function test_aluno_envia_justificativa_com_anexo(): void
    {
        [, $materia, , $faltou, $codigo] = $this->cenario();

        $this->enviar($faltou, $materia, $codigo, [
            'anexo' => UploadedFile::fake()->create('atestado.pdf', 300, 'application/pdf'),
        ])->assertRedirect()->assertSessionHasNoErrors();

        $j = Justificativa::first();
        $this->assertNotNull($j);
        $this->assertSame(Justificativa::STATUS_PENDENTE, $j->status);
        $this->assertSame('atestado.pdf', $j->anexo_nome);
        $this->assertDoesNotMatchRegularExpression('/atestado/', $j->anexo_path, 'O arquivo deve ser salvo com nome gerado, não o original.');
        Storage::disk('justificativas')->assertExists($j->anexo_path);
    }

    public function test_aluno_ve_faltas_para_justificar(): void
    {
        [, $materia, , $faltou] = $this->cenario();

        $this->actingAs($faltou, 'alunos')
            ->get('/aluno/justificativas')
            ->assertOk()
            ->assertSee($materia->nome)
            ->assertSee('05/11/2026');
    }

    public function test_nao_justifica_aula_com_presenca(): void
    {
        [, $materia, $presente, , $codigo] = $this->cenario();

        $this->enviar($presente, $materia, $codigo)->assertSessionHasErrors('codigo_aula');
        $this->assertSame(0, Justificativa::count());
    }

    public function test_nao_justifica_aula_de_materia_em_que_nao_esta_matriculado(): void
    {
        [, $materia, , , $codigo] = $this->cenario();
        [, $outra] = $this->professorComMateria();
        $deFora = $this->alunoMatriculado($outra);

        $this->enviar($deFora, $materia, $codigo)->assertSessionHasErrors();
        $this->assertSame(0, Justificativa::count());
    }

    public function test_nao_justifica_duas_vezes(): void
    {
        [, $materia, , $faltou, $codigo] = $this->cenario();

        $this->enviar($faltou, $materia, $codigo)->assertSessionHasNoErrors();
        $this->enviar($faltou, $materia, $codigo)->assertSessionHasErrors('codigo_aula');
        $this->assertSame(1, Justificativa::count());
    }

    public function test_anexo_tipo_invalido_422(): void
    {
        [, $materia, , $faltou, $codigo] = $this->cenario();

        $this->enviar($faltou, $materia, $codigo, [
            'anexo' => UploadedFile::fake()->create('virus.exe', 10, 'application/x-msdownload'),
        ])->assertSessionHasErrors('anexo');

        $this->assertSame(0, Justificativa::count());
    }

    public function test_anexo_maior_que_5mb_recusado(): void
    {
        [, $materia, , $faltou, $codigo] = $this->cenario();

        $this->enviar($faltou, $materia, $codigo, [
            'anexo' => UploadedFile::fake()->create('atestado.pdf', 6000, 'application/pdf'),
        ])->assertSessionHasErrors('anexo');
    }

    public function test_professor_aprova_cria_presenca_justificada(): void
    {
        [$professor, $materia, , $faltou, $codigo] = $this->cenario();
        $j = $this->criarPendente($faltou, $materia, $codigo);

        $this->actingAs($professor, 'professores')
            ->post("/professor/justificativas/{$j->id}/decidir", ['decisao' => 'aprovar', 'comentario' => 'Atestado ok'])
            ->assertRedirect();

        $j->refresh();
        $this->assertSame(Justificativa::STATUS_APROVADA, $j->status);
        $this->assertSame($professor->id, $j->decidida_por);
        $this->assertNotNull($j->decidida_em);

        $presenca = Presenca::where('aluno_id', $faltou->id)->where('codigo_aula', $codigo)->first();
        $this->assertSame(Presenca::ORIGEM_JUSTIFICATIVA, $presenca->origem);
        $this->assertDatabaseHas('presenca_ajustes', ['aluno_id' => $faltou->id, 'acao' => PresencaAjuste::ACAO_ABONOU]);

        Mail::assertSent(JustificativaDecididaMail::class, fn ($m) => $m->hasTo($faltou->email));
    }

    public function test_professor_recusa_nao_cria_presenca(): void
    {
        [$professor, $materia, , $faltou, $codigo] = $this->cenario();
        $j = $this->criarPendente($faltou, $materia, $codigo);

        $this->actingAs($professor, 'professores')
            ->post("/professor/justificativas/{$j->id}/decidir", ['decisao' => 'recusar', 'comentario' => 'Sem documento'])
            ->assertRedirect();

        $this->assertSame(Justificativa::STATUS_RECUSADA, $j->fresh()->status);
        $this->assertFalse(Presenca::where('aluno_id', $faltou->id)->exists());
        Mail::assertSent(JustificativaDecididaMail::class);
    }

    public function test_professor_outra_materia_403(): void
    {
        [, $materia, , $faltou, $codigo] = $this->cenario();
        [$outro] = $this->professorComMateria();
        Storage::disk('justificativas')->put('x/arquivo.pdf', 'pdf');
        $j = $this->criarPendente($faltou, $materia, $codigo, 'x/arquivo.pdf');

        $this->actingAs($outro, 'professores')
            ->post("/professor/justificativas/{$j->id}/decidir", ['decisao' => 'aprovar'])
            ->assertStatus(403);

        $this->actingAs($outro, 'professores')
            ->get("/professor/justificativas/{$j->id}/anexo")
            ->assertStatus(403);

        $this->assertSame(Justificativa::STATUS_PENDENTE, $j->fresh()->status);
    }

    public function test_professor_ve_somente_pendentes_das_suas_materias(): void
    {
        [$professor, $materia, , $faltou, $codigo] = $this->cenario();
        [$outro, $outraMateria] = $this->professorComMateria();
        $alunoOutro = $this->alunoMatriculado($outraMateria, ['nome' => 'Aluno De Outra Turma']);
        $codigoOutro = $this->registrarAula($outraMateria, $outro, [$this->alunoMatriculado($outraMateria)]);
        $this->criarPendente($faltou, $materia, $codigo);
        $this->criarPendente($alunoOutro, $outraMateria, $codigoOutro);

        $this->actingAs($professor, 'professores')
            ->get('/professor/justificativas')
            ->assertOk()
            ->assertSee($faltou->nome)
            ->assertDontSee('Aluno De Outra Turma');
    }

    public function test_outro_aluno_nao_baixa_anexo_403(): void
    {
        [, $materia, $presente, $faltou, $codigo] = $this->cenario();
        Storage::disk('justificativas')->put('x/arquivo.pdf', 'pdf');
        $j = $this->criarPendente($faltou, $materia, $codigo, 'x/arquivo.pdf');

        $this->actingAs($presente, 'alunos')
            ->get("/aluno/justificativas/{$j->id}/anexo")
            ->assertStatus(403);

        $this->actingAs($faltou, 'alunos')
            ->get("/aluno/justificativas/{$j->id}/anexo")
            ->assertOk();
    }

    public function test_decidir_duas_vezes_422(): void
    {
        [$professor, $materia, , $faltou, $codigo] = $this->cenario();
        $j = $this->criarPendente($faltou, $materia, $codigo);

        $this->actingAs($professor, 'professores')
            ->post("/professor/justificativas/{$j->id}/decidir", ['decisao' => 'recusar']);

        $this->actingAs($professor, 'professores')
            ->postJson("/professor/justificativas/{$j->id}/decidir", ['decisao' => 'aprovar'])
            ->assertStatus(422);

        $this->assertSame(Justificativa::STATUS_RECUSADA, $j->fresh()->status);
        $this->assertFalse(Presenca::where('aluno_id', $faltou->id)->exists());
    }

    public function test_falha_no_email_da_decisao_nao_grava_endereco_no_log(): void
    {
        [$professor, $materia, , $faltou, $codigo] = $this->cenario();
        $j = $this->criarPendente($faltou, $materia, $codigo);
        \Illuminate\Support\Facades\Log::spy();
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('550 recipient ' . $faltou->email . ' rejected'));

        $this->actingAs($professor, 'professores')
            ->post("/professor/justificativas/{$j->id}/decidir", ['decisao' => 'recusar'])
            ->assertRedirect();

        \Illuminate\Support\Facades\Log::shouldHaveReceived('warning')->withArgs(
            fn ($msg, $ctx) => ! str_contains(json_encode($ctx), $faltou->email)
        );
    }
}
