<?php

namespace App\Console\Commands;

use App\Support\AvisosFrequencia;
use Illuminate\Console\Command;

class AvisarFrequencia extends Command
{
    protected $signature = 'frequencia:avisar';

    protected $description = 'Avisa por e-mail os alunos em Atenção por faltas ou frequência (no máximo 1 aviso por matéria por semana)';

    public function handle(): int
    {
        $enviados = AvisosFrequencia::enviar();
        $this->info("Avisos de baixa frequência enviados: {$enviados}");

        return self::SUCCESS;
    }
}
