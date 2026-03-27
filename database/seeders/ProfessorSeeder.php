<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProfessorModel;

/**
 * ProfessorSeeder — uses ProfessorModel::create() so that
 * AES-256 encryption, Blind Index generation, and password
 * hashing (via the 'hashed' cast) are all applied automatically.
 */
class ProfessorSeeder extends Seeder
{
    public function run(): void
    {
        $professores = [
            [
                'cpf'      => '12233344445',
                'nome'     => 'Professor Teste',
                'email'    => 'professor@teste.com',
                'password' => 'professor123',
                'role'     => 'professor',
            ],
            [
                'cpf'      => '55566677788',
                'nome'     => 'Profa. Maria',
                'email'    => 'maria@teste.com',
                'password' => 'professor123',
                'role'     => 'professor',
            ],
            [
                'cpf'      => '99988877766',
                'nome'     => 'Prof. Carlos',
                'email'    => 'carlos@teste.com',
                'password' => 'professor123',
                'role'     => 'professor',
            ],
            [
                'cpf'      => '11122233344',
                'nome'     => 'Profa. Ana',
                'email'    => 'ana@teste.com',
                'password' => 'professor123',
                'role'     => 'professor',
            ],
            [
                'cpf'      => '55544433322',
                'nome'     => 'Prof. Pedro',
                'email'    => 'pedro@teste.com',
                'password' => 'professor123',
                'role'     => 'professor',
            ],
        ];

        foreach ($professores as $data) {
            ProfessorModel::create($data);
        }
    }
}
