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
        Schema::create('professores', function (Blueprint $table) {
            $table->id(); 
            $table->text('cpf')->nullable(); 
            $table->string('cpf_search')->unique(); 
            $table->text('nome'); 
            $table->string('nome_search')->index(); 
            $table->text('email')->nullable(); 
            $table->string('email_search')->unique(); 
            $table->string('password'); 
            $table->string('role')->default('professor');
            $table->timestamp('email_verified_at')->nullable();
            $table->text('remember_token')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('professores');
    }
};
