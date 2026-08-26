<?php

namespace Tests\Feature;

use App\Models\AlunoModel;
use App\Models\ProfessorModel;
use App\Models\UsuarioMaster;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class XssTest extends TestCase
{
    use RefreshDatabase;

    public function test_aluno_name_is_escaped_when_displayed(): void
    {
        $aluno = AlunoModel::create([
            'ra' => '123456',
            'cpf' => '12345678900',
            'nome' => '<script>alert("XSS")</script>',
            'email' => 'aluno@teste.com',
            'password' => Hash::make('12345678'),
            'role' => 'aluno',
        ]);

        $this->actingAs($aluno, 'alunos');

        $response = $this->get('/dashboard/aluno');

        $response->assertStatus(200);

        $response->assertDontSee('<script>alert("XSS")</script>', false);
    }

    public function test_professor_name_is_escaped_when_displayed(): void
    {
        $professor = ProfessorModel::create([
            'cpf' => '98765432100',
            'nome' => '<script>alert("XSS")</script>',
            'email' => 'professor@teste.com',
            'password' => Hash::make('12345678'),
            'role' => 'professor',
        ]);

        $this->actingAs($professor, 'professores');

        $response = $this->get('/professor/dashboard');

        $response->assertStatus(200);

        $response->assertDontSee('<script>alert("XSS")</script>', false);
    }

    public function test_master_name_is_escaped_when_displayed(): void
    {
        $master = UsuarioMaster::create([
            'nome' => '<script>alert("XSS")</script>',
            'email' => 'master@teste.com',
            'password' => Hash::make('12345678'),
            'role' => 'master',
        ]);

        $this->actingAs($master, 'masters');

        $response = $this->get('/dashboard/master');

        $response->assertStatus(200);

        $response->assertDontSee('<script>alert("XSS")</script>', false);
    }
}