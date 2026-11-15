<?php

namespace Tests\Feature;

use App\Models\AlunoModel;
use App\Models\ProfessorModel;
use App\Models\UsuarioMaster;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class DoisFatoresTest extends TestCase
{
    use RefreshDatabase;

    private Google2FA $g2fa;

    protected function setUp(): void
    {
        parent::setUp();
        $this->g2fa = new Google2FA();
    }

    private function professorCom2fa(string $senha = 'senha-forte-123'): array
    {
        $professor = ProfessorModel::factory()->create([
            'email'          => 'prof2fa@teste.com',
            'password'       => Hash::make($senha),
            'remember_token' => null,
        ]);
        $segredo = $this->g2fa->generateSecretKey();
        $professor->forceFill(['two_factor_secret' => $segredo, 'two_factor_confirmed_at' => now()])->save();
        $codigos = $professor->gerarCodigosRecuperacao();

        return [$professor, $segredo, $codigos];
    }

    private function loginProfessor(array $extra = [])
    {
        return $this->post('/login/professor', array_merge(['cpf_email' => 'prof2fa@teste.com', 'password' => 'senha-forte-123'], $extra));
    }

    private function cookiesDeLembrar($resposta): array
    {
        return array_filter($resposta->headers->getCookies(), fn ($c) => str_starts_with($c->getName(), 'remember_'));
    }

    public function test_login_com_2fa_pede_codigo(): void
    {
        $this->professorCom2fa();

        $this->loginProfessor()->assertRedirect(route('dois-fatores.desafio'));
        $this->assertFalse(auth('professores')->check());
    }

    public function test_login_sem_2fa_continua_direto(): void
    {
        ProfessorModel::factory()->create(['email' => 'sem2fa@teste.com', 'password' => Hash::make('x12345678'), 'remember_token' => null]);

        $this->post('/login/professor', ['cpf_email' => 'sem2fa@teste.com', 'password' => 'x12345678'])
            ->assertRedirect(route('dashboard.professor'));
    }

    public function test_codigo_correto_conclui_login(): void
    {
        [$professor, $segredo] = $this->professorCom2fa();
        $this->loginProfessor();

        $this->post('/2fa/desafio', ['codigo' => $this->g2fa->getCurrentOtp($segredo)])
            ->assertRedirect(route('dashboard.professor'));

        $this->assertTrue(auth('professores')->check());
        $this->assertSame($professor->id, auth('professores')->id());
    }

    public function test_codigo_errado_nao_loga(): void
    {
        $this->professorCom2fa();
        $this->loginProfessor();

        $this->post('/2fa/desafio', ['codigo' => '000000'])->assertSessionHasErrors('codigo');
        $this->assertFalse(auth('professores')->check());
    }

    public function test_codigo_recuperacao_funciona_uma_vez(): void
    {
        [, , $codigos] = $this->professorCom2fa();

        $this->loginProfessor();
        $this->post('/2fa/desafio', ['codigo' => $codigos[0]])->assertRedirect(route('dashboard.professor'));

        auth('professores')->logout();
        $this->loginProfessor();
        $this->post('/2fa/desafio', ['codigo' => $codigos[0]])->assertSessionHasErrors('codigo');
        $this->assertFalse(auth('professores')->check());
    }

    public function test_lembrar_me_so_apos_codigo_2fa(): void
    {
        [, $segredo] = $this->professorCom2fa();

        $resposta = $this->loginProfessor(['remember' => 'on']);
        $this->assertCount(0, $this->cookiesDeLembrar($resposta), 'O cookie de lembrar não pode sair antes do segundo fator.');

        $resposta = $this->post('/2fa/desafio', ['codigo' => $this->g2fa->getCurrentOtp($segredo)]);
        $this->assertCount(1, $this->cookiesDeLembrar($resposta));
    }

    public function test_desafio_expira_apos_5_minutos(): void
    {
        [, $segredo] = $this->professorCom2fa();
        $this->loginProfessor();

        $this->travel(6)->minutes();

        $this->post('/2fa/desafio', ['codigo' => $this->g2fa->getCurrentOtp($segredo)])
            ->assertRedirect(route('login_form'));
        $this->assertFalse(auth('professores')->check());
    }

    public function test_desafio_sem_login_pendente_volta_ao_login(): void
    {
        $this->get('/2fa/desafio')->assertRedirect(route('login_form'));
    }

    public function test_aluno_com_2fa_tambem_passa_pelo_desafio(): void
    {
        $aluno = AlunoModel::factory()->create(['ra' => '20260001', 'password' => Hash::make('senha-aluno-1'), 'remember_token' => null]);
        $segredo = $this->g2fa->generateSecretKey();
        $aluno->forceFill(['two_factor_secret' => $segredo, 'two_factor_confirmed_at' => now()])->save();

        $this->post('/login/aluno', ['ra_email_cpf' => '20260001', 'password' => 'senha-aluno-1'])
            ->assertRedirect(route('dois-fatores.desafio'));

        $this->post('/2fa/desafio', ['codigo' => $this->g2fa->getCurrentOtp($segredo)])
            ->assertRedirect(route('dashboard.aluno'));
    }

    public function test_master_sem_2fa_redirecionado_quando_obrigatorio(): void
    {
        config(['seguranca.2fa_obrigatorio_master' => true]);
        $master = UsuarioMaster::factory()->create();

        $this->actingAs($master, 'masters')->get('/dashboard/master')
            ->assertRedirect(route('dois-fatores.configurar'));
    }

    public function test_master_com_2fa_acessa_painel_quando_obrigatorio(): void
    {
        config(['seguranca.2fa_obrigatorio_master' => true]);
        $master = UsuarioMaster::factory()->create();
        $master->forceFill(['two_factor_secret' => $this->g2fa->generateSecretKey(), 'two_factor_confirmed_at' => now()])->save();

        $this->actingAs($master, 'masters')->get('/dashboard/master')->assertOk();
    }

    public function test_configurar_exige_codigo_valido_para_ativar(): void
    {
        $professor = ProfessorModel::factory()->create(['remember_token' => null, 'password' => Hash::make('senha-atual-1')]);
        $this->actingAs($professor, 'professores');

        $this->get('/2fa/configurar')->assertOk()->assertSee('otpauth://', false);
        $segredo = session('2fa.segredo_novo');
        $this->assertNotEmpty($segredo);

        $this->post('/2fa/confirmar', ['codigo' => '123456', 'senha' => 'senha-atual-1'])->assertSessionHasErrors('codigo');
        $this->assertFalse($professor->fresh()->temDoisFatores());

        $this->post('/2fa/confirmar', ['codigo' => $this->g2fa->getCurrentOtp($segredo), 'senha' => 'senha-atual-1'])
            ->assertRedirect(route('dois-fatores.configurar'))
            ->assertSessionHas('codigos_recuperacao');

        $this->assertTrue($professor->fresh()->temDoisFatores());
        $this->assertCount(8, session('codigos_recuperacao'));
    }

    public function test_desativar_exige_senha(): void
    {
        [$professor] = $this->professorCom2fa();
        $this->actingAs($professor, 'professores');

        $this->post('/2fa/desativar', ['senha' => 'errada'])->assertSessionHasErrors('senha');
        $this->assertTrue($professor->fresh()->temDoisFatores());

        $this->post('/2fa/desativar', ['senha' => 'senha-forte-123'])->assertRedirect();
        $this->assertFalse($professor->fresh()->temDoisFatores());
    }

    public function test_master_reseta_2fa_de_professor(): void
    {
        [$professor] = $this->professorCom2fa();
        $master = UsuarioMaster::factory()->create();

        $this->actingAs($master, 'masters')
            ->post("/dashboard/master/professor/{$professor->id}/resetar-2fa")
            ->assertRedirect();

        $this->assertFalse($professor->fresh()->temDoisFatores());
    }

    public function test_professor_nao_reseta_2fa_de_outro(): void
    {
        [$alvo] = $this->professorCom2fa();
        $outro = ProfessorModel::factory()->create();

        $this->actingAs($outro, 'professores')->post("/dashboard/master/professor/{$alvo->id}/resetar-2fa");

        $this->assertTrue($alvo->fresh()->temDoisFatores());
    }

    public function test_segredo_criptografado_no_banco(): void
    {
        [$professor, $segredo] = $this->professorCom2fa();

        $bruto = DB::table('professores')->where('id', $professor->id)->value('two_factor_secret');
        $this->assertNotSame($segredo, $bruto);
        $this->assertStringNotContainsString($segredo, $bruto);
        $this->assertArrayNotHasKey('two_factor_secret', $professor->fresh()->toArray());
    }

    public function test_redefinir_senha_por_token_nao_pula_o_2fa(): void
    {
        [$professor, $segredo] = $this->professorCom2fa();
        $professor->forceFill(['remember_token' => 'token-recuperacao-123456'])->save();

        // Token do e-mail de recuperação usado no campo de senha
        $this->post('/login/professor', ['cpf_email' => 'prof2fa@teste.com', 'password' => 'token-recuperacao-123456'])
            ->assertRedirect(route('criar-senha.show'));

        $this->post('/criar-senha', ['senha' => 'nova-senha-123', 'senha_confirmacao' => 'nova-senha-123'])
            ->assertRedirect(route('dois-fatores.desafio'));
        $this->assertFalse(auth('professores')->check());

        $this->post('/2fa/desafio', ['codigo' => $this->g2fa->getCurrentOtp($segredo)])
            ->assertRedirect(route('dashboard.professor'));
        $this->assertTrue(auth('professores')->check());
    }

    public function test_mesmo_codigo_nao_vale_duas_vezes(): void
    {
        [, $segredo] = $this->professorCom2fa();
        $codigo = $this->g2fa->getCurrentOtp($segredo);

        $this->loginProfessor();
        $this->post('/2fa/desafio', ['codigo' => $codigo])->assertRedirect(route('dashboard.professor'));

        auth('professores')->logout();
        $this->loginProfessor();
        $this->post('/2fa/desafio', ['codigo' => $codigo])->assertSessionHasErrors('codigo');
        $this->assertFalse(auth('professores')->check());
    }

    public function test_codigo_nao_vale_duas_vezes_em_requisicoes_simultaneas(): void
    {
        [$professor, $segredo, $codigos] = $this->professorCom2fa();
        $codigo = $this->g2fa->getCurrentOtp($segredo);

        // Duas requisições carregaram o usuário antes de qualquer uma gravar
        $req1 = ProfessorModel::find($professor->id);
        $req2 = ProfessorModel::find($professor->id);

        $this->assertTrue($req1->verificarCodigoDoisFatores($codigo));
        $this->assertFalse($req2->verificarCodigoDoisFatores($codigo));

        $this->assertTrue($req1->verificarCodigoDoisFatores($codigos[1]));
        $this->assertFalse($req2->verificarCodigoDoisFatores($codigos[1]));
    }

    public function test_ativar_2fa_exige_a_senha_atual(): void
    {
        $professor = ProfessorModel::factory()->create(['remember_token' => null, 'password' => Hash::make('senha-atual-1')]);
        $this->actingAs($professor, 'professores');
        $this->get('/2fa/configurar');
        $codigo = $this->g2fa->getCurrentOtp(session('2fa.segredo_novo'));

        $this->post('/2fa/confirmar', ['codigo' => $codigo])->assertSessionHasErrors('senha');
        $this->post('/2fa/confirmar', ['codigo' => $codigo, 'senha' => 'errada'])->assertSessionHasErrors('senha');

        $this->assertFalse($professor->fresh()->temDoisFatores());
    }

    public function test_confirmar_nao_substitui_2fa_ja_ativo(): void
    {
        [$professor, $segredoOriginal] = $this->professorCom2fa();
        $this->actingAs($professor, 'professores');
        $outro = $this->g2fa->generateSecretKey();
        session(['2fa.segredo_novo' => $outro]);

        $this->post('/2fa/confirmar', ['codigo' => $this->g2fa->getCurrentOtp($outro), 'senha' => 'senha-forte-123'])
            ->assertStatus(409);

        $this->assertSame($segredoOriginal, $professor->fresh()->two_factor_secret);
    }
}
