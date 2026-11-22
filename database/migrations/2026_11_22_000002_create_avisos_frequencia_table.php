<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Avisos de baixa frequência já enviados (issue #10): garante no máximo um
 * aviso por aluno e matéria a cada 7 dias.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('avisos_frequencia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aluno_id')->constrained('alunos')->cascadeOnDelete();
            $table->foreignId('materia_id')->constrained('materias')->cascadeOnDelete();
            $table->unsignedInteger('faltas');
            $table->unsignedTinyInteger('frequencia');
            $table->timestamp('enviado_em');

            $table->index(['aluno_id', 'materia_id', 'enviado_em']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('avisos_frequencia');
    }
};
