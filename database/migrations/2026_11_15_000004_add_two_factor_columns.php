<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Autenticação em dois fatores (TOTP) para alunos, professores e masters.
 * Segredo e códigos de recuperação são gravados criptografados (cast encrypted).
 */
return new class extends Migration
{
    private array $tabelas = ['alunos', 'professores', 'usuario_masters'];

    public function up(): void
    {
        foreach ($this->tabelas as $tabela) {
            Schema::table($tabela, function (Blueprint $table) {
                $table->text('two_factor_secret')->nullable();
                $table->text('two_factor_recovery_codes')->nullable();
                $table->timestamp('two_factor_confirmed_at')->nullable();
                // Janela (timestep de 30 s) do último código aceito: impede reusar o mesmo código
                $table->unsignedBigInteger('two_factor_ultimo_timestep')->nullable();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tabelas as $tabela) {
            Schema::table($tabela, function (Blueprint $table) {
                $table->dropColumn(['two_factor_secret', 'two_factor_recovery_codes', 'two_factor_confirmed_at', 'two_factor_ultimo_timestep']);
            });
        }
    }
};
