<?php

namespace Tests\Feature;

use App\Models\AlunoModel;
use App\Models\Materia;
use App\Models\ProfessorModel;
use App\Models\Presenca;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Testa a geração e a leitura dos QR Codes de presença.
 *
 * Cobre o fluxo completo:
 * 1. Professor autentica → gera QR Code (código de aula no cache).
 * 2. Aluno autentica → confirma presença via URL do QR Code.
 * 3. Sistema bloqueia duplo registro (idempotência por codigo_aula).
 * 4. Sistema recusa código expirado/inválido.
 * 5. Sistema recusa aluno não matriculado na matéria.
 */
class QrCodePresencaTest extends TestCase
{
    use RefreshDatabase;

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Cria um professor com uma matéria vinculada e retorna ambos.
     */
    private function criarProfessorComMateria(): array
    {
        $professor = ProfessorModel::factory()->create([
            'password' => bcrypt('senha123'),
        ]);
        $materia = Materia::factory()->create();
        $materia->professores()->attach($professor->id);

        return [$professor, $materia];
    }

    /**
     * Cria um aluno matriculado na matéria informada.
     */
    private function criarAlunoMatriculado(Materia $materia): AlunoModel
    {
        $aluno = AlunoModel::factory()->create([
            'password' => bcrypt('senha123'),
        ]);
        $materia->alunos()->attach($aluno->id);

        return $aluno;
    }

    /**
     * Gera e persiste um código de aula no cache, simulando o que
     * PresencaController::gerarQr() faz, e retorna o código.
     */
    private function gerarCodigoNoCache(ProfessorModel $professor, Materia $materia): string
    {
        $codigoAula   = $materia->id . '-' . now()->timestamp . '-' . Str::random(4);
        $expiraEm     = now()->addHours(2);
        $cacheKey     = 'aula_materia_' . $materia->id . '_' . now()->format('Y-m-d');

        Cache::put($cacheKey, [
            'codigo'      => $codigoAula,
            'expira_em'   => $expiraEm->timestamp,
            'professor_id' => $professor->id,
        ], $expiraEm);

        return $codigoAula;
    }

    // ─── Testes ───────────────────────────────────────────────────────────────

    /**
     * Um professor autenticado consegue acessar a página de geração de QR Code.
     */
    public function test_professor_pode_acessar_pagina_de_presenca(): void
    {
        $this->withoutVite();

        [$professor, $materia] = $this->criarProfessorComMateria();

        $response = $this
            ->actingAs($professor, 'professores')
            ->get(route('professor.presenca.index'));

        $response->assertStatus(200);
    }

    /**
     * Um aluno autenticado e matriculado na matéria confirma presença com sucesso.
     */
    public function test_aluno_confirma_presenca_com_qr_valido(): void
    {
        $this->withoutVite();

        [$professor, $materia] = $this->criarProfessorComMateria();
        $aluno                 = $this->criarAlunoMatriculado($materia);
        $codigoAula            = $this->gerarCodigoNoCache($professor, $materia);

        $response = $this
            ->actingAs($aluno, 'alunos')
            ->get(route('presenca.confirmar', $codigoAula));

        $response->assertStatus(200);

        $this->assertDatabaseHas('presencas', [
            'aluno_id'    => $aluno->id,
            'materia_id'  => $materia->id,
            'codigo_aula' => $codigoAula,
        ]);
    }

    /**
     * O sistema bloqueia duplo registro do mesmo aluno no mesmo código de aula.
     * (idempotência: campo codigo_aula + aluno_id é chave de unicidade lógica)
     */
    public function test_sistema_bloqueia_duplo_registro_de_presenca(): void
    {
        $this->withoutVite();

        [$professor, $materia] = $this->criarProfessorComMateria();
        $aluno                 = $this->criarAlunoMatriculado($materia);
        $codigoAula            = $this->gerarCodigoNoCache($professor, $materia);

        // Primeiro acesso — registra presença
        $this->actingAs($aluno, 'alunos')
            ->get(route('presenca.confirmar', $codigoAula));

        // Segundo acesso — deve cair na view de "já registrado"
        $response = $this
            ->actingAs($aluno, 'alunos')
            ->get(route('presenca.confirmar', $codigoAula));

        $response->assertStatus(200);

        // Garante que existe exatamente 1 registro (não duplicou)
        $this->assertEquals(
            1,
            Presenca::where('aluno_id', $aluno->id)
                ->where('codigo_aula', $codigoAula)
                ->count()
        );
    }

    /**
     * O sistema recusa um código de aula expirado ou inválido.
     */
    public function test_sistema_recusa_qr_code_invalido(): void
    {
        $this->withoutVite();

        [$professor, $materia] = $this->criarProfessorComMateria();
        $aluno                 = $this->criarAlunoMatriculado($materia);

        // Usamos um código que nunca foi armazenado no cache
        $codigoFalso = $materia->id . '-' . now()->subHours(3)->timestamp . '-XXXX';

        $response = $this
            ->actingAs($aluno, 'alunos')
            ->get(route('presenca.confirmar', $codigoFalso));

        // Deve retornar a view de erro (não a de sucesso)
        $response->assertStatus(200);

        $this->assertDatabaseMissing('presencas', [
            'aluno_id'    => $aluno->id,
            'codigo_aula' => $codigoFalso,
        ]);
    }

    /**
     * O sistema recusa presença de aluno não matriculado na matéria.
     */
    public function test_sistema_recusa_aluno_nao_matriculado(): void
    {
        $this->withoutVite();

        [$professor, $materia] = $this->criarProfessorComMateria();
        $codigoAula            = $this->gerarCodigoNoCache($professor, $materia);

        // Aluno criado mas NÃO matriculado na matéria
        $alunoForaDeMatricula = AlunoModel::factory()->create([
            'password' => bcrypt('senha123'),
        ]);

        $response = $this
            ->actingAs($alunoForaDeMatricula, 'alunos')
            ->get(route('presenca.confirmar', $codigoAula));

        $response->assertStatus(200);

        $this->assertDatabaseMissing('presencas', [
            'aluno_id'    => $alunoForaDeMatricula->id,
            'codigo_aula' => $codigoAula,
        ]);
    }

    /**
     * Um aluno não autenticado ao escanear o QR Code é redirecionado para login.
     */
    public function test_aluno_nao_autenticado_e_redirecionado_para_login(): void
    {
        [$professor, $materia] = $this->criarProfessorComMateria();
        $codigoAula            = $this->gerarCodigoNoCache($professor, $materia);

        $response = $this->get(route('presenca.confirmar', $codigoAula));

        $response->assertRedirect(route('login.aluno.form'));
    }
}
