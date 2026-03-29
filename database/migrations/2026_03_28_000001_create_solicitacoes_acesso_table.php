<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitacoes_acesso', function (Blueprint $table) {
            $table->id();
            $table->string('tipo');                             // 'aluno' ou 'professor'
            $table->text('nome');                              // criptografado via cast
            $table->string('nome_search')->index();            // blind index
            $table->text('email');                             // criptografado via cast
            $table->string('email_search')->index();           // blind index (não unique — permite re-solicitação)
            $table->text('cpf')->nullable();                   // criptografado, nullable
            $table->string('cpf_search')->nullable()->index(); // blind index, nullable
            $table->text('ra')->nullable();                    // criptografado, só para alunos
            $table->string('ra_search')->nullable()->index();  // blind index, nullable
            $table->string('status')->default('pendente');     // pendente | aprovado | rejeitado
            $table->text('motivo_rejeicao')->nullable();       // justificativa do Master
            $table->foreignId('aprovado_por')->nullable()
                  ->constrained('usuario_masters')
                  ->onDelete('set null');                      // rastreabilidade — quem aprovou/rejeitou
            $table->timestamps();

            // Índices compostos para queries do painel Master
            $table->index(['status', 'tipo']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitacoes_acesso');
    }
};
