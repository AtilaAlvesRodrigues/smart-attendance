<?php

namespace Tests\Feature;

use App\Models\Justificativa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CriaCenarioAcademico;
use Tests\TestCase;

/**
 * Atestados guardados no próprio banco (JUSTIFICATIVAS_DISK=banco), para
 * funcionar na Vercel sem depender de um serviço de arquivos externo.
 */
class AnexoNoBancoTest extends TestCase
{
    use RefreshDatabase, CriaCenarioAcademico;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Storage::fake('justificativas');
        config(['filesystems.justificativas_no_banco' => true]);
    }

    private function cenario(): array
    {
        [$professor, $materia] = $this->professorComMateria();
        $presente = $this->alunoMatriculado($materia);
        $faltou = $this->alunoMatriculado($materia);
        $codigo = $this->registrarAula($materia, $professor, [$presente], '2026-11-05');

        return [$professor, $materia, $presente, $faltou, $codigo];
    }

    private function enviar($aluno, $materia, string $codigo, ?UploadedFile $anexo = null)
    {
        return $this->actingAs($aluno, 'alunos')->post('/aluno/justificativas', array_filter([
            'materia_id'  => $materia->id,
            'codigo_aula' => $codigo,
            'motivo'      => 'Consulta médica no horário da aula.',
            'anexo'       => $anexo,
        ]));
    }

    public function test_atestado_fica_no_banco_e_nao_no_disco(): void
    {
        [, $materia, , $faltou, $codigo] = $this->cenario();
        $conteudo = "%PDF-1.4\n" . random_bytes(2048);

        $this->enviar($faltou, $materia, $codigo, UploadedFile::fake()->createWithContent('atestado.pdf', $conteudo))
            ->assertSessionHasNoErrors();

        $j = Justificativa::first();
        $this->assertSame('atestado.pdf', $j->anexo_nome);
        $this->assertSame(1, DB::table('justificativa_anexos')->where('justificativa_id', $j->id)->count());
        $this->assertSame([], Storage::disk('justificativas')->allFiles());
    }

    public function test_aluno_e_professor_baixam_o_mesmo_conteudo(): void
    {
        [$professor, $materia, $presente, $faltou, $codigo] = $this->cenario();
        $conteudo = "%PDF-1.4\n" . random_bytes(4096);
        $this->enviar($faltou, $materia, $codigo, UploadedFile::fake()->createWithContent('atestado.pdf', $conteudo));
        $j = Justificativa::first();

        $doAluno = $this->actingAs($faltou, 'alunos')->get("/aluno/justificativas/{$j->id}/anexo");
        $doAluno->assertOk();
        $this->assertSame($conteudo, $doAluno->streamedContent());
        $this->assertStringContainsString('atestado.pdf', $doAluno->headers->get('Content-Disposition'));
        $this->assertSame('nosniff', $doAluno->headers->get('X-Content-Type-Options'));

        $doProfessor = $this->actingAs($professor, 'professores')->get("/professor/justificativas/{$j->id}/anexo");
        $this->assertSame($conteudo, $doProfessor->streamedContent());

        $this->actingAs($presente, 'alunos')->get("/aluno/justificativas/{$j->id}/anexo")->assertStatus(403);
    }

    public function test_anexo_habilitado_na_vercel_quando_guardado_no_banco(): void
    {
        $_SERVER['VERCEL'] = '1';
        try {
            $this->assertTrue(Justificativa::anexoHabilitado());
        } finally {
            unset($_SERVER['VERCEL']);
        }
    }

    public function test_anexo_acima_de_4mb_recusado(): void
    {
        [, $materia, , $faltou, $codigo] = $this->cenario();

        // A Vercel recusa requisições acima de 4,5 MB: o limite do atestado é 4 MB
        $this->enviar($faltou, $materia, $codigo, UploadedFile::fake()->create('atestado.pdf', 4200, 'application/pdf'))
            ->assertSessionHasErrors('anexo');

        $this->assertSame(0, Justificativa::count());
    }

    public function test_envio_simultaneo_nao_da_erro_nem_deixa_anexo_solto(): void
    {
        [, $materia, , $faltou, $codigo] = $this->cenario();

        // Simula o segundo clique gravando a mesma justificativa logo antes deste insert
        Justificativa::creating(function (Justificativa $j) {
            Justificativa::withoutEvents(fn () => Justificativa::query()->insert([
                'aluno_id' => $j->aluno_id, 'materia_id' => $j->materia_id, 'codigo_aula' => $j->codigo_aula,
                'data_aula' => $j->data_aula, 'motivo' => 'outra aba', 'status' => 'pendente',
                'created_at' => now(), 'updated_at' => now(),
            ]));
        });

        $this->enviar($faltou, $materia, $codigo, UploadedFile::fake()->create('atestado.pdf', 100, 'application/pdf'))
            ->assertStatus(302)
            ->assertSessionHasErrors('codigo_aula');

        // A linha "da outra aba" foi gravada dentro desta transação e é desfeita junto;
        // numa corrida real ela já estaria confirmada. O que importa: sem 500 e sem anexo solto.
        $this->assertSame(0, DB::table('justificativa_anexos')->count());
    }

    public function test_envio_simultaneo_no_disco_nao_deixa_arquivo_solto(): void
    {
        config(['filesystems.justificativas_no_banco' => false]);
        [, $materia, , $faltou, $codigo] = $this->cenario();

        Justificativa::creating(function (Justificativa $j) {
            Justificativa::withoutEvents(fn () => Justificativa::query()->insert([
                'aluno_id' => $j->aluno_id, 'materia_id' => $j->materia_id, 'codigo_aula' => $j->codigo_aula,
                'data_aula' => $j->data_aula, 'motivo' => 'outra aba', 'status' => 'pendente',
                'created_at' => now(), 'updated_at' => now(),
            ]));
        });

        $this->enviar($faltou, $materia, $codigo, UploadedFile::fake()->create('atestado.pdf', 100, 'application/pdf'))
            ->assertSessionHasErrors('codigo_aula');

        $this->assertSame([], Storage::disk('justificativas')->allFiles());
    }
}
