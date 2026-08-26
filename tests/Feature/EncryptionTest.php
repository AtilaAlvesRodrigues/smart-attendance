<?php

namespace Tests\Feature;

use App\Models\AlunoModel;
use App\Models\ProfessorModel;
use App\Models\UsuarioMaster;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EncryptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_aluno_sensitive_data_is_encrypted_in_database(): void
    {
        $email = 'aluno@teste.com';
        $cpf = '12345678900';
        $ra = '123456';
        $nome = 'Aluno Teste';

        $aluno = AlunoModel::create([
            'ra' => $ra,
            'cpf' => $cpf,
            'nome' => $nome,
            'email' => $email,
            'password' => '12345678',
            'role' => 'aluno',
        ]);

        $raw = DB::table('alunos')
            ->where('id', $aluno->id)
            ->first();

        $this->assertNotSame($email, $raw->email);
        $this->assertNotSame($cpf, $raw->cpf);
        $this->assertNotSame($ra, $raw->ra);
        $this->assertNotSame($nome, $raw->nome);
    }

    public function test_aluno_sensitive_data_is_decrypted_by_model(): void
    {
        $aluno = AlunoModel::create([
            'ra' => '123456',
            'cpf' => '12345678900',
            'nome' => 'Aluno Teste',
            'email' => 'aluno@teste.com',
            'password' => '12345678',
            'role' => 'aluno',
        ]);

        $aluno->refresh();

        $this->assertSame('aluno@teste.com', $aluno->email);
        $this->assertSame('12345678900', $aluno->cpf);
        $this->assertSame('123456', $aluno->ra);
        $this->assertSame('Aluno Teste', $aluno->nome);
    }

    public function test_professor_sensitive_data_is_encrypted_in_database(): void
    {
        $email = 'professor@teste.com';
        $cpf = '98765432100';
        $nome = 'Professor Teste';

        $professor = ProfessorModel::create([
            'cpf' => $cpf,
            'nome' => $nome,
            'email' => $email,
            'password' => '12345678',
            'role' => 'professor',
        ]);

        $raw = DB::table('professores')
            ->where('id', $professor->id)
            ->first();

        $this->assertNotSame($email, $raw->email);
        $this->assertNotSame($cpf, $raw->cpf);
        $this->assertNotSame($nome, $raw->nome);
    }

    public function test_professor_sensitive_data_is_decrypted_by_model(): void
    {
        $professor = ProfessorModel::create([
            'cpf' => '98765432100',
            'nome' => 'Professor Teste',
            'email' => 'professor@teste.com',
            'password' => '12345678',
            'role' => 'professor',
        ]);

        $professor->refresh();

        $this->assertSame('professor@teste.com', $professor->email);
        $this->assertSame('98765432100', $professor->cpf);
        $this->assertSame('Professor Teste', $professor->nome);
    }

    public function test_master_sensitive_data_is_encrypted_in_database(): void
    {
        $email = 'master@teste.com';
        $nome = 'Master Teste';

        $master = UsuarioMaster::create([
            'nome' => $nome,
            'email' => $email,
            'password' => '12345678',
            'role' => 'master',
        ]);

        $raw = DB::table('usuario_masters')
            ->where('id', $master->id)
            ->first();

        $this->assertNotSame($email, $raw->email);
        $this->assertNotSame($nome, $raw->nome);
    }

    public function test_master_sensitive_data_is_decrypted_by_model(): void
    {
        $master = UsuarioMaster::create([
            'nome' => 'Master Teste',
            'email' => 'master@teste.com',
            'password' => '12345678',
            'role' => 'master',
        ]);

        $master->refresh();

        $this->assertSame('master@teste.com', $master->email);
        $this->assertSame('Master Teste', $master->nome);
    }
}