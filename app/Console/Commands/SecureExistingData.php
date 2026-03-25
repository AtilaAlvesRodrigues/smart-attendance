<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SecureExistingData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'secure:data';
    protected $description = 'Criptografa dados em texto plano e gera Blind Indexes para registros existentes.';

    public function handle()
    {
        $this->info('Iniciando criptografia de dados legados (Apenas Sensíveis não-ID)...');

        // 1. Alunos
        $this->info('Processando Alunos...');
        \Illuminate\Support\Facades\DB::table('alunos')->get()->each(function ($raw) {
            if (strpos($raw->email, 'eyJpdiI6') === false) {
                \Illuminate\Support\Facades\DB::table('alunos')
                    ->where('ra', $raw->ra)
                    ->update([
                        'nome' => \Illuminate\Support\Facades\Crypt::encryptString($raw->nome),
                        'email' => \Illuminate\Support\Facades\Crypt::encryptString($raw->email),
                        // CPF e RA permanecem plain-text por serem IDs/Chaves
                        'email_search' => \App\Models\AlunoModel::generateBlindIndex($raw->email),
                        'cpf_search' => \App\Models\AlunoModel::generateBlindIndex($raw->cpf),
                        'ra_search' => \App\Models\AlunoModel::generateBlindIndex($raw->ra),
                    ]);
            }
        });

        // 2. Professores
        $this->info('Processando Professores...');
        \Illuminate\Support\Facades\DB::table('professores')->get()->each(function ($raw) {
            if (strpos($raw->email, 'eyJpdiI6') === false) {
                \Illuminate\Support\Facades\DB::table('professores')
                    ->where('cpf', $raw->cpf)
                    ->update([
                        'nome' => \Illuminate\Support\Facades\Crypt::encryptString($raw->nome),
                        'email' => \Illuminate\Support\Facades\Crypt::encryptString($raw->email),
                        'email_search' => \App\Models\ProfessorModel::generateBlindIndex($raw->email),
                        'cpf_search' => \App\Models\ProfessorModel::generateBlindIndex($raw->cpf),
                    ]);
            }
        });

        // 3. Masters
        $this->info('Processando Masters...');
        \Illuminate\Support\Facades\DB::table('usuario_masters')->get()->each(function ($raw) {
            if (strpos($raw->email, 'eyJpdiI6') === false) {
                \Illuminate\Support\Facades\DB::table('usuario_masters')
                    ->where('id', $raw->id)
                    ->update([
                        'nome' => \Illuminate\Support\Facades\Crypt::encryptString($raw->nome),
                        'email' => \Illuminate\Support\Facades\Crypt::encryptString($raw->email),
                        'email_search' => \App\Models\UsuarioMaster::generateBlindIndex($raw->email),
                    ]);
            }
        });

        $this->info('✅ Todos os dados legados foram protegidos com sucesso!');
    }
}
