<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('presencas', function (Blueprint $table) {
            $table->id();
            
            // Relacionamentos usando ID interno (PK)
            $table->foreignId('aluno_id')->constrained('alunos')->onDelete('cascade');
            $table->foreignId('professor_id')->constrained('professores')->onDelete('cascade');

            // Relacionamento com Matéria
            $table->foreignId('materia_id')->constrained('materias')->onDelete('cascade');

            // Dados da Aula
            $table->date('data_aula');
            $table->string('semestre', 20)->nullable(); // Ex: 2026/1
            $table->string('horario', 5)->nullable(); // M, V, N
            $table->string('codigo_aula')->index(); // Para agrupar todos os alunos da mesma chamada
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presencas');
    }
};
