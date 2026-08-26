<?php

namespace Tests\Feature;

use App\Models\AlunoModel;
use App\Models\ProfessorModel;
use App\Models\UsuarioMaster;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlindIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_blind_index_is_deterministic(): void
    {
        $value = 'aluno@teste.com';

        $hash1 = AlunoModel::generateBlindIndex($value);
        $hash2 = AlunoModel::generateBlindIndex($value);

        $this->assertSame($hash1, $hash2);
    }

    public function test_blind_index_is_case_insensitive_and_trims_spaces(): void
    {
        $hash1 = AlunoModel::generateBlindIndex('  Aluno@Teste.com  ');
        $hash2 = AlunoModel::generateBlindIndex('aluno@teste.com');

        $this->assertSame($hash1, $hash2);
    }

    public function test_different_values_generate_different_blind_indexes(): void
    {
        $hash1 = AlunoModel::generateBlindIndex('aluno1@teste.com');
        $hash2 = AlunoModel::generateBlindIndex('aluno2@teste.com');

        $this->assertNotSame($hash1, $hash2);
    }

    public function test_empty_value_returns_null(): void
    {
        $this->assertNull(AlunoModel::generateBlindIndex(''));
        $this->assertNull(AlunoModel::generateBlindIndex(null));
    }

    public function test_blind_index_is_stored_when_aluno_is_created(): void
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

        $this->assertNotNull($aluno->email_search);
        $this->assertNotNull($aluno->cpf_search);
        $this->assertNotNull($aluno->ra_search);
        $this->assertNotNull($aluno->nome_search);

        $this->assertSame(
            AlunoModel::generateBlindIndex('aluno@teste.com'),
            $aluno->email_search
        );

        $this->assertSame(
            AlunoModel::generateBlindIndex('12345678900'),
            $aluno->cpf_search
        );

        $this->assertSame(
            AlunoModel::generateBlindIndex('123456'),
            $aluno->ra_search
        );

        $this->assertSame(
            AlunoModel::generateBlindIndex('Aluno Teste'),
            $aluno->nome_search
        );
    }

    public function test_blind_index_is_stored_for_professor(): void
    {
        $professor = ProfessorModel::create([
            'cpf' => '98765432100',
            'nome' => 'Professor Teste',
            'email' => 'professor@teste.com',
            'password' => '12345678',
            'role' => 'professor',
        ]);

        $professor->refresh();

        $this->assertSame(
            ProfessorModel::generateBlindIndex('professor@teste.com'),
            $professor->email_search
        );

        $this->assertSame(
            ProfessorModel::generateBlindIndex('98765432100'),
            $professor->cpf_search
        );

        $this->assertSame(
            ProfessorModel::generateBlindIndex('Professor Teste'),
            $professor->nome_search
        );
    }

    public function test_blind_index_is_stored_for_master(): void
    {
        $master = UsuarioMaster::create([
            'nome' => 'Master Teste',
            'email' => 'master@teste.com',
            'password' => '12345678',
            'role' => 'master',
        ]);

        $master->refresh();

        $this->assertSame(
            UsuarioMaster::generateBlindIndex('master@teste.com'),
            $master->email_search
        );

        $this->assertSame(
            UsuarioMaster::generateBlindIndex('Master Teste'),
            $master->nome_search
        );
    }
}