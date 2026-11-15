<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origem de cada presença: 'qr' (aluno escaneou), 'correcao' (professor marcou
 * manualmente) ou 'justificativa' (falta abonada). Abonos e correções entram
 * como presenças para que o SituacaoAcademica continue sendo a única regra.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('presencas', function (Blueprint $table) {
            $table->string('origem', 20)->default('qr');
        });
    }

    public function down(): void
    {
        Schema::table('presencas', function (Blueprint $table) {
            $table->dropColumn('origem');
        });
    }
};
