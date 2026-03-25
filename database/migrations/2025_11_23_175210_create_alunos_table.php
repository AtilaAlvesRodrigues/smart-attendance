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
        Schema::create('alunos', function (Blueprint $table) {
            $table->id(); // PK Interna
            $table->text('ra')->nullable(); // RA Criptografado
            $table->string('ra_search')->unique(); // Blind Index para RA
            $table->text('cpf')->nullable(); // CPF Criptografado
            $table->string('cpf_search')->unique(); // Blind Index para CPF
            $table->text('nome'); // Nome Criptografado
            $table->string('nome_search')->index(); // Blind Index para Nome
            $table->text('email')->nullable(); // Email Criptografado
            $table->string('email_search')->unique(); // Blind Index para Email
            $table->string('password'); // SENHA para login
            $table->string('role')->default('aluno');
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alunos');
    }
};
