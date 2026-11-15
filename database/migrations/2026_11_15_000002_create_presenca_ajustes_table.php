<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Histórico de toda presença alterada fora do QR Code (correção do professor
 * ou abono por justificativa): quem fez, quando, o quê e por quê.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presenca_ajustes', function (Blueprint $table) {
            $table->id();
            // Sem FK: a presença pode ter sido apagada (ação "desmarcou")
            $table->unsignedBigInteger('presenca_id')->nullable();
            $table->foreignId('materia_id')->constrained('materias')->cascadeOnDelete();
            $table->foreignId('aluno_id')->constrained('alunos')->cascadeOnDelete();
            $table->string('codigo_aula');
            $table->string('acao', 20); // marcou | desmarcou | abonou
            $table->text('motivo');
            $table->string('autor_tipo', 20); // professor | master
            $table->unsignedBigInteger('autor_id');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['materia_id', 'codigo_aula']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presenca_ajustes');
    }
};
