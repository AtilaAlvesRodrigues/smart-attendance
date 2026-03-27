<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\AlunoModel;

/**
 * AlunoSeeder — uses AlunoModel::create() to ensure
 * AES-256 encryption and Blind Index generation are applied
 * to all seeded data, exactly as in production.
 */
class AlunoSeeder extends Seeder
{
    public function run(): void
    {
        $alunos = [
            [
                'ra'       => '100000000',
                'cpf'      => '00011122233',
                'nome'     => 'Aluno Teste',
                'email'    => 'aluno.teste@site.com',
                'password' => 'aluno123',
                'role'     => 'aluno',
            ],
            [
                'ra'       => '200000000',
                'cpf'      => '44455566677',
                'nome'     => 'Aluna Mariana',
                'email'    => 'mariana@aluno.com',
                'password' => 'aluno123',
                'role'     => 'aluno',
            ],
            [
                'ra'       => '300000000',
                'cpf'      => '11111111111',
                'nome'     => 'Lucas Silva',
                'email'    => 'lucas@aluno.com',
                'password' => 'aluno123',
                'role'     => 'aluno',
            ],
            [
                'ra'       => '400000000',
                'cpf'      => '22222222222',
                'nome'     => 'Beatriz Costa',
                'email'    => 'beatriz@aluno.com',
                'password' => 'aluno123',
                'role'     => 'aluno',
            ],
            [
                'ra'       => '500000000',
                'cpf'      => '33333333333',
                'nome'     => 'Gabriel Santos',
                'email'    => 'gabriel@aluno.com',
                'password' => 'aluno123',
                'role'     => 'aluno',
            ],
            [
                'ra'       => '600000000',
                'cpf'      => '44444444444',
                'nome'     => 'Julia Souza',
                'email'    => 'julia@aluno.com',
                'password' => 'aluno123',
                'role'     => 'aluno',
            ],
            [
                'ra'       => '700000000',
                'cpf'      => '55555555555',
                'nome'     => 'Rafael Lima',
                'email'    => 'rafael@aluno.com',
                'password' => 'aluno123',
                'role'     => 'aluno',
            ],
        ];

        foreach ($alunos as $data) {
            AlunoModel::create($data);
        }
    }
}
