<?php

namespace Tests\Feature;

use App\Mail\EmailTesteMail;
use App\Models\ProfessorModel;
use App\Models\UsuarioMaster;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\TestCase;

class MasterEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_master_envia_email_teste(): void
    {
        Mail::fake();
        $master = UsuarioMaster::factory()->create(['email' => 'master@instituicao.edu.br']);

        $this->actingAs($master, 'masters')
            ->post('/dashboard/master/email/teste')
            ->assertRedirect()
            ->assertSessionHas('email_status');

        Mail::assertSent(EmailTesteMail::class, fn ($m) => $m->hasTo('master@instituicao.edu.br'));
    }

    public function test_professor_nao_acessa_email_teste(): void
    {
        Mail::fake();
        $professor = ProfessorModel::factory()->create();

        $this->actingAs($professor, 'professores')->post('/dashboard/master/email/teste');

        Mail::assertNothingSent();
    }

    public function test_falha_smtp_mostra_erro_sem_senha(): void
    {
        config(['mail.mailers.smtp.password' => 'senha-super-secreta']);
        $master = UsuarioMaster::factory()->create();

        Mail::shouldReceive('to')->andThrow(new TransportException('535 Authentication failed for senha-super-secreta'));

        $resposta = $this->actingAs($master, 'masters')
            ->from('/dashboard/master')
            ->post('/dashboard/master/email/teste');

        $resposta->assertRedirect('/dashboard/master')->assertSessionHas('email_erro');
        $this->assertStringNotContainsString('senha-super-secreta', session('email_erro'));
        $this->assertStringContainsString('535', session('email_erro'));
    }

    public function test_painel_master_mostra_configuracao_sem_credenciais(): void
    {
        config(['mail.default' => 'smtp', 'mail.mailers.smtp.host' => 'smtp.office365.com', 'mail.mailers.smtp.port' => 587,
            'mail.mailers.smtp.username' => 'usuario-secreto@inst.edu.br', 'mail.mailers.smtp.password' => 'senha-super-secreta']);
        $master = UsuarioMaster::factory()->create();

        $this->actingAs($master, 'masters')->get('/dashboard/master')
            ->assertOk()
            ->assertSee('smtp.office365.com')
            ->assertDontSee('senha-super-secreta')
            ->assertDontSee('usuario-secreto@inst.edu.br');
    }
}
