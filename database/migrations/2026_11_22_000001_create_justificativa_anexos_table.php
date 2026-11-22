<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Atestados guardados no próprio banco (JUSTIFICATIVAS_DISK=banco).
 *
 * Na Vercel o disco é somente leitura; guardar o arquivo aqui evita depender
 * de um serviço externo. O conteúdo vai criptografado com a APP_KEY (texto
 * base64 do Laravel), igual no PostgreSQL e no SQLite dos testes. Atestados
 * têm no máximo 4 MB.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('justificativa_anexos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('justificativa_id')->unique()->constrained('justificativas')->cascadeOnDelete();
            $table->longText('conteudo'); // criptografado (Crypt::encryptString)
            $table->unsignedInteger('tamanho'); // bytes do arquivo original
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('justificativa_anexos');
    }
};
