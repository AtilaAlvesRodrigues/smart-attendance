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
        Schema::create('materias', function (Blueprint $table) {
            $table->id(); // PK
            $table->string('nome')->unique();
            $table->string('sala');
            $table->integer('carga_horaria');
            
            // Novos campos de horário por período
            $table->string('horario_matutino')->nullable();
            $table->string('horario_vespertino')->nullable();
            $table->string('horario_noturno')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materias');
    }
};
