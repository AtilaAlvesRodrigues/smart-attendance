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
        Schema::create('usuario_masters', function (Blueprint $table) {
            $table->id();
            $table->text('nome');
            $table->string('nome_search')->index();
            $table->text('email')->nullable();
            $table->string('email_search')->unique();
            $table->string('password');
            $table->string('role')->default('master');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuario_masters');
    }
};
