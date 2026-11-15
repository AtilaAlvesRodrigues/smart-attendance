<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Justificativas de falta enviadas pelo aluno e decididas pelo professor.
 * Uma por falta (aluno + matéria + codigo_aula). O atestado fica no disco
 * 'justificativas', nunca em pasta pública.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('justificativas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aluno_id')->constrained('alunos')->cascadeOnDelete();
            $table->foreignId('materia_id')->constrained('materias')->cascadeOnDelete();
            $table->string('codigo_aula');
            $table->date('data_aula');
            $table->text('motivo');
            $table->string('anexo_path')->nullable();
            $table->string('anexo_nome')->nullable();
            $table->string('anexo_mime', 100)->nullable();
            $table->string('status', 20)->default('pendente'); // pendente | aprovada | recusada
            $table->text('comentario_professor')->nullable();
            $table->unsignedBigInteger('decidida_por')->nullable(); // professores.id
            $table->timestamp('decidida_em')->nullable();
            $table->timestamps();

            $table->unique(['aluno_id', 'materia_id', 'codigo_aula']);
            $table->index(['materia_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('justificativas');
    }
};
