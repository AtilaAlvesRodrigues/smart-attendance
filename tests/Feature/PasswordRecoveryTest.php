<?php

namespace Tests\Feature;

use App\Models\AlunoModel;
use App\Models\ProfessorModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PasswordRecoveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_recovery_does_not_reveal_if_aluno_email_exists(): void
    {
        Mail::fake();

        AlunoModel::create([
            'ra' => '123456',
            'cpf' => '12345678900',
            'nome' => 'Aluno Teste',
            'email' => 'aluno@teste.com',
            'password' => Hash::make('12345678'),
            'role' => 'aluno',
        ]);

        $existingResponse = $this->post('/esqueci-senha/aluno', [
            'email' => 'aluno@teste.com',
        ]);

        $missingResponse = $this->post('/esqueci-senha/aluno', [
            'email' => 'naoexiste@teste.com',
        ]);

        $existingResponse->assertSessionHas(
            'success',
            'Se este e-mail estiver cadastrado, você receberá as instruções em breve.'
        );

        $missingResponse->assertSessionHas(
            'success',
            'Se este e-mail estiver cadastrado, você receberá as instruções em breve.'
        );

        Mail::assertSentCount(1);
    }

    public function test_password_recovery_does_not_reveal_if_professor_email_exists(): void
    {
        Mail::fake();

        ProfessorModel::create([
            'cpf' => '12345678900',
            'nome' => 'Professor Teste',
            'email' => 'professor@teste.com',
            'password' => Hash::make('12345678'),
            'role' => 'professor',
        ]);

        $existingResponse = $this->post('/esqueci-senha/professor', [
            'email' => 'professor@teste.com',
        ]);

        $missingResponse = $this->post('/esqueci-senha/professor', [
            'email' => 'naoexiste@teste.com',
        ]);

        $existingResponse->assertSessionHas(
            'success',
            'Se este e-mail estiver cadastrado, você receberá as instruções em breve.'
        );

        $missingResponse->assertSessionHas(
            'success',
            'Se este e-mail estiver cadastrado, você receberá as instruções em breve.'
        );

        Mail::assertSentCount(1);
    }
}