<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Um aluno tem no máximo uma presença por aula (codigo_aula).
 *
 * Até aqui isso era garantido só pela aplicação, e duas requisições ao mesmo
 * tempo podiam gravar a mesma presença duas vezes, reduzindo as faltas.
 * Antes de criar o índice, remove as cópias exatas (mesmo aluno e mesma aula),
 * mantendo o registro mais antigo: as cópias não carregam informação própria.
 */
return new class extends Migration
{
    public function up(): void
    {
        $duplicadas = DB::table('presencas')
            ->select('aluno_id', 'codigo_aula', DB::raw('MIN(id) as manter'))
            ->groupBy('aluno_id', 'codigo_aula')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicadas as $d) {
            DB::table('presencas')
                ->where('aluno_id', $d->aluno_id)
                ->where('codigo_aula', $d->codigo_aula)
                ->where('id', '!=', $d->manter)
                ->delete();
        }

        Schema::table('presencas', function (Blueprint $table) {
            $table->unique(['aluno_id', 'codigo_aula'], 'presencas_aluno_aula_unique');
        });
    }

    public function down(): void
    {
        Schema::table('presencas', function (Blueprint $table) {
            $table->dropUnique('presencas_aluno_aula_unique');
        });
    }
};
