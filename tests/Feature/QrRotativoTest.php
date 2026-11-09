<?php

namespace Tests\Feature;

use App\Models\AlunoModel;
use App\Models\Materia;
use App\Models\ProfessorModel;
use App\Support\QrToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * QR Code rotativo: o token da URL muda a cada 30 s, então uma foto ou print
 * do QR Code deixa de registrar presença em no máximo 60 s.
 */
class QrRotativoTest extends TestCase
{
    use RefreshDatabase;

    private function aulaAberta(): array
    {
        $professor = ProfessorModel::factory()->create();
        $aluno = AlunoModel::factory()->create();
        $materia = Materia::factory()->create();
        $professor->materias()->attach($materia->id);
        $aluno->materias()->attach($materia->id);

        $codigo = $this->actingAs($professor, 'professores')
            ->get("/professor/presenca/gerar/{$materia->id}")
            ->assertOk()
            ->viewData('codigo_aula');

        return [$professor, $aluno, $materia, $codigo];
    }

    public function test_token_atual_e_valido_e_o_adulterado_nao(): void
    {
        $token = QrToken::gerar('1-1700000000-ABCD');

        $this->assertTrue(QrToken::validar('1-1700000000-ABCD', $token));
        $this->assertFalse(QrToken::validar('1-1700000000-ABCE', $token), 'token de outra aula');
        $this->assertFalse(QrToken::validar('1-1700000000-ABCD', null));
        $this->assertFalse(QrToken::validar('1-1700000000-ABCD', 'lixo'));
        // troca o último caractere por um DIFERENTE (com '0' fixo, 1 em cada 16 assinaturas não mudaria nada)
        $ultimo = substr($token, -1);
        $this->assertFalse(QrToken::validar('1-1700000000-ABCD', substr($token, 0, -1) . ($ultimo === '0' ? '1' : '0')), 'assinatura alterada');
    }

    public function test_token_vale_no_slot_atual_e_no_anterior_mas_nao_depois(): void
    {
        $t0 = 1_800_000_000 - (1_800_000_000 % QrToken::STEP); // início de um slot
        $token = QrToken::gerar('AULA', $t0 + 5);

        $this->assertTrue(QrToken::validar('AULA', $token, $t0 + 29), 'mesmo slot');
        $this->assertTrue(QrToken::validar('AULA', $token, $t0 + 30 + 10), 'slot seguinte (tolerância)');
        $this->assertFalse(QrToken::validar('AULA', $token, $t0 + 60 + 1), 'dois slots depois já expirou');
        $this->assertFalse(QrToken::validar('AULA', $token, $t0 - 31), 'token do futuro não vale');
    }

    public function test_segundos_ate_proximo_fica_entre_1_e_30(): void
    {
        foreach ([0, 1, 29, 30, 59, 1_800_000_017] as $agora) {
            $s = QrToken::segundosAteProximo($agora);
            $this->assertGreaterThanOrEqual(1, $s);
            $this->assertLessThanOrEqual(QrToken::STEP, $s);
        }
    }

    public function test_url_do_qr_inclui_token_valido(): void
    {
        [$professor, , $materia, $codigo] = $this->aulaAberta();

        $presencaUrl = $this->actingAs($professor, 'professores')
            ->get("/professor/presenca/gerar/{$materia->id}")
            ->viewData('presencaUrl');

        $this->assertStringContainsString("/presenca/confirmar/{$codigo}?t=", $presencaUrl);
        parse_str(parse_url($presencaUrl, PHP_URL_QUERY), $query);
        $this->assertTrue(QrToken::validar($codigo, $query['t']));
    }

    public function test_aluno_com_token_atual_registra_presenca(): void
    {
        [, $aluno, , $codigo] = $this->aulaAberta();

        $this->actingAs($aluno, 'alunos')
            ->get("/presenca/confirmar/{$codigo}?t=" . QrToken::gerar($codigo))
            ->assertViewIs('aluno.presenca.sucesso');

        $this->assertDatabaseHas('presencas', ['aluno_id' => $aluno->id, 'codigo_aula' => $codigo]);
    }

    public function test_aluno_sem_token_ou_com_token_velho_nao_registra_presenca(): void
    {
        [, $aluno, , $codigo] = $this->aulaAberta();

        $this->actingAs($aluno, 'alunos')
            ->get("/presenca/confirmar/{$codigo}")
            ->assertViewIs('aluno.presenca.erro');

        $velho = QrToken::gerar($codigo, time() - 3 * QrToken::STEP);
        $this->get("/presenca/confirmar/{$codigo}?t={$velho}")
            ->assertViewIs('aluno.presenca.erro');

        $this->assertDatabaseCount('presencas', 0);
    }

    public function test_token_atravessa_o_login_do_aluno(): void
    {
        [, , , $codigo] = $this->aulaAberta();
        auth('professores')->logout();

        $token = QrToken::gerar($codigo);
        $this->get("/presenca/confirmar/{$codigo}?t={$token}")->assertRedirect('/login/aluno');

        $this->assertSame($codigo, session('pending_attendance_code'));
        $this->assertSame($token, session('pending_attendance_token'));
    }

    public function test_endpoint_de_token_entrega_url_nova_so_ao_dono_da_chamada(): void
    {
        [$professor, , , $codigo] = $this->aulaAberta();

        $json = $this->actingAs($professor, 'professores')
            ->getJson("/professor/presenca/token/{$codigo}")
            ->assertOk()
            ->json();

        $this->assertStringContainsString("/presenca/confirmar/{$codigo}?t=", $json['url']);
        $this->assertGreaterThanOrEqual(1, $json['renova_em']);
        $this->assertLessThanOrEqual(QrToken::STEP, $json['renova_em']);

        parse_str(parse_url($json['url'], PHP_URL_QUERY), $query);
        $this->assertTrue(QrToken::validar($codigo, $query['t']));

        $outro = ProfessorModel::factory()->create();
        $this->actingAs($outro, 'professores')
            ->getJson("/professor/presenca/token/{$codigo}")
            ->assertNotFound();
    }

    public function test_endpoint_de_token_exige_login_de_professor(): void
    {
        $this->getJson('/professor/presenca/token/1-1700000000-ABCD')->assertUnauthorized();
    }
}
