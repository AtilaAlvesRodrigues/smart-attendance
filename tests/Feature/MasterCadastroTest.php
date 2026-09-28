<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use App\Models\AlunoModel;
use App\Models\ProfessorModel;
use App\Models\Materia;
use App\Models\UsuarioMaster;
use App\Mail\PrimeiroAcessoMail;

class MasterCadastroTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();

        $master = UsuarioMaster::factory()->create();
        
        $this->actingAs($master, 'masters');
    }


    public function test_cadastrar_aluno_requer_nome(): void
    {
        $response = $this->postJson('/dashboard/master/cadastrar/aluno', [
            'email' => 'aluno@example.com',
            'cpf' => '12345678901',
            'ra' => '123456',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nome']);
    }

    public function test_cadastrar_aluno_requer_email(): void
    {
        $response = $this->postJson('/dashboard/master/cadastrar/aluno', [
            'nome' => 'João da Silva',
            'cpf' => '12345678901',
            'ra' => '123456',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_cadastrar_aluno_requer_cpf(): void
    {
        $response = $this->postJson('/dashboard/master/cadastrar/aluno', [
            'nome' => 'João da Silva',
            'email' => 'aluno@example.com',
            'ra' => '123456',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['cpf']);
    }

    public function test_cadastrar_aluno_requer_ra(): void
    {
        $response = $this->postJson('/dashboard/master/cadastrar/aluno', [
            'nome' => 'João da Silva',
            'email' => 'aluno@example.com',
            'cpf' => '12345678901',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ra']);
    }

    public function test_cadastrar_aluno_rejeita_email_invalido(): void
    {
        $response = $this->postJson('/dashboard/master/cadastrar/aluno', [
            'nome' => 'João da Silva',
            'email' => 'email-invalido',
            'cpf' => '12345678901',
            'ra' => '123456',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_cadastrar_aluno_funciona(): void
    {
        $response = $this->postJson('/dashboard/master/cadastrar/aluno', [
            'nome' => 'João da Silva',
            'email' => 'joao@example.com',
            'cpf' => '12345678901',
            'ra' => '123456',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('alunos', [
            'nome' => 'João da Silva',
        ]);

        Mail::assertSent(PrimeiroAcessoMail::class);
    }

    public function test_cadastrar_aluno_rejeita_email_duplicado(): void
    {
        $this->postJson('/dashboard/master/cadastrar/aluno', [
            'nome' => 'Primeiro Aluno',
            'email' => 'joao@example.com',
            'cpf' => '12345678901',
            'ra' => '123456',
        ]);

        $response = $this->postJson('/dashboard/master/cadastrar/aluno', [
            'nome' => 'Segundo Aluno',
            'email' => 'joao@example.com',
            'cpf' => '98765432100',
            'ra' => '654321',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_cadastrar_aluno_rejeita_cpf_duplicado(): void
    {
        $this->postJson('/dashboard/master/cadastrar/aluno', [
            'nome' => 'Primeiro Aluno',
            'email' => 'primeiro@example.com',
            'cpf' => '12345678901',
            'ra' => '123456',
        ]);

        $response = $this->postJson('/dashboard/master/cadastrar/aluno', [
            'nome' => 'Segundo Aluno',
            'email' => 'segundo@example.com',
            'cpf' => '12345678901',
            'ra' => '654321',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['cpf']);
    }

    public function test_cadastrar_aluno_rejeita_ra_duplicado(): void
    {
        $this->postJson('/dashboard/master/cadastrar/aluno', [
            'nome' => 'Primeiro Aluno',
            'email' => 'primeiro@example.com',
            'cpf' => '12345678901',
            'ra' => '123456',
        ]);

        $response = $this->postJson('/dashboard/master/cadastrar/aluno', [
            'nome' => 'Segundo Aluno',
            'email' => 'segundo@example.com',
            'cpf' => '98765432100',
            'ra' => '123456',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ra']);
    }


    public function test_cadastrar_professor_requer_nome(): void
    {
        $response = $this->postJson('/dashboard/master/cadastrar/professor', [
            'email' => 'professor@example.com',
            'cpf' => '12345678901',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nome']);
    }

    public function test_cadastrar_professor_requer_email(): void
    {
        $response = $this->postJson('/dashboard/master/cadastrar/professor', [
            'nome' => 'Professor Teste',
            'cpf' => '12345678901',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_cadastrar_professor_requer_cpf(): void
    {
        $response = $this->postJson('/dashboard/master/cadastrar/professor', [
            'nome' => 'Professor Teste',
            'email' => 'professor@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['cpf']);
    }

    public function test_cadastrar_professor_rejeita_cpf_invalido(): void
    {
        $response = $this->postJson('/dashboard/master/cadastrar/professor', [
            'nome' => 'Professor Teste',
            'email' => 'professor@example.com',
            'cpf' => '11111111111',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['cpf']);
    }

    public function test_cadastrar_professor_funciona(): void
    {
        $response = $this->postJson('/dashboard/master/cadastrar/professor', [
            'nome' => 'Professor Teste',
            'email' => 'professor@example.com',
            'cpf' => '12345678901',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('professores', [
            'nome' => 'Professor Teste',
        ]);

        Mail::assertSent(PrimeiroAcessoMail::class);
    }

    public function test_cadastrar_professor_rejeita_email_duplicado(): void
    {
        $this->postJson('/dashboard/master/cadastrar/professor', [
            'nome' => 'Professor 1',
            'email' => 'professor@example.com',
            'cpf' => '12345678901',
        ]);

        $response = $this->postJson('/dashboard/master/cadastrar/professor', [
            'nome' => 'Professor 2',
            'email' => 'professor@example.com',
            'cpf' => '98765432100',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }


    public function test_cadastrar_materia_requer_nome(): void
    {
        $response = $this->postJson('/dashboard/master/cadastrar/materia', [
            'sala' => '101',
            'carga_horaria' => 60,
            'total_aulas' => 30,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nome']);
    }

    public function test_cadastrar_materia_rejeita_carga_horaria_invalida(): void
    {
        $response = $this->postJson('/dashboard/master/cadastrar/materia', [
            'nome' => 'Matemática',
            'sala' => '101',
            'carga_horaria' => 0,
            'total_aulas' => 30,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['carga_horaria']);
    }

    public function test_cadastrar_materia_rejeita_total_aulas_invalido(): void
    {
        $response = $this->postJson('/dashboard/master/cadastrar/materia', [
            'nome' => 'Matemática',
            'sala' => '101',
            'carga_horaria' => 60,
            'total_aulas' => 0,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['total_aulas']);
    }

    public function test_cadastrar_materia_funciona(): void
    {
        $response = $this->postJson('/dashboard/master/cadastrar/materia', [
            'nome' => 'Matemática',
            'sala' => '101',
            'carga_horaria' => 60,
            'total_aulas' => 30,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('materias', [
            'nome' => 'Matemática',
            'sala' => '101',
            'carga_horaria' => 60,
            'total_aulas' => 30,
        ]);
    }

    public function test_cadastrar_materia_rejeita_nome_duplicado(): void
    {
        Materia::create([
            'nome' => 'Matemática',
            'sala' => '101',
            'carga_horaria' => 60,
            'total_aulas' => 30,
        ]);

        $response = $this->postJson('/dashboard/master/cadastrar/materia', [
            'nome' => 'Matemática',
            'sala' => '102',
            'carga_horaria' => 80,
            'total_aulas' => 40,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nome']);
    }

    public function test_sem_envio_de_email_master_recebe_o_token_para_entregar(): void
    {
        config(['mail.default' => 'log']);

        $response = $this->postJson('/dashboard/master/cadastrar/aluno', [
            'nome' => 'Carla Dias',
            'email' => 'carla@example.com',
            'cpf' => '98765432109',
            'ra' => '654321',
        ]);

        $response->assertOk()->assertJsonStructure(['token_provisorio', 'proximo_passo']);
        $token = $response->json('token_provisorio');

        // O token devolvido é o mesmo que permite o primeiro acesso
        $aluno = \App\Models\AlunoModel::where('email_search', \App\Models\AlunoModel::generateBlindIndex('carla@example.com'))->first();
        $this->assertSame($token, $aluno->remember_token);
        Mail::assertNothingSent();
    }

    public function test_cadastrar_materia_aponta_para_a_turma(): void
    {
        $response = $this->postJson('/dashboard/master/cadastrar/materia', [
            'nome' => 'Estruturas de Dados',
            'sala' => 'Lab 3',
            'carga_horaria' => 60,
            'total_aulas' => 30,
        ]);

        $materia = \App\Models\Materia::where('nome', 'Estruturas de Dados')->first();
        $response->assertOk()->assertJson(['turma_url' => route('master.turma', $materia)]);
    }
}
