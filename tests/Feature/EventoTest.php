<?php

namespace Tests\Feature;

use App\Models\ProfessorModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EventoTest extends TestCase
{
    use RefreshDatabase;

    private function criarProfessor(): ProfessorModel
    {
        return ProfessorModel::create([
            'cpf' => '12345678901',
            'nome' => 'Professor Teste',
            'email' => 'professor' . uniqid() . '@example.com',
            'password' => Hash::make('password123'),
            'role' => 'professor',
        ]);
    }

    public function test_checkin_form_is_accessible_without_login(): void
    {
        $response = $this->get('/evento/checkin?token=teste123');

        $response->assertStatus(200);
    }

    public function test_professor_can_access_event_dashboard(): void
    {
        $professor = $this->criarProfessor();

        $this->actingAs($professor, 'professores');

        $response = $this->get('/professor/evento/presenca');

        $response->assertStatus(200);
    }

    public function test_checkin_requires_name(): void
    {
        $response = $this->postJson('/evento/checkin/process', [
            'email' => 'teste@example.com',
            'token' => 'teste123',
        ]);

        $response->assertStatus(422);
    }

    public function test_checkin_requires_valid_email(): void
    {
        $response = $this->postJson('/evento/checkin/process', [
            'name' => 'João da Silva',
            'email' => 'email-invalido',
            'token' => 'teste123',
        ]);

        $response->assertStatus(422);
    }

    public function test_checkin_requires_token(): void
    {
        $response = $this->postJson('/evento/checkin/process', [
            'name' => 'João da Silva',
            'email' => 'teste@example.com',
        ]);

        $response->assertStatus(422);
    }

    public function test_honeypot_blocks_bot(): void
    {
        $response = $this->postJson('/evento/checkin/process', [
            'name' => 'João da Silva',
            'email' => 'teste@example.com',
            'token' => 'teste123',
            'hp_field' => 'bot',
        ]);

        $response->assertStatus(422);
    }

    public function test_participant_can_check_in(): void
    {
        $response = $this->postJson('/evento/checkin/process', [
            'name' => 'João da Silva',
            'email' => 'teste@example.com',
            'token' => 'teste123',
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $checkins = Cache::get('evento_checkin_teste123');

        $this->assertCount(1, $checkins);
        $this->assertSame('João da Silva', $checkins[0]['name']);
        $this->assertSame('teste@example.com', $checkins[0]['email']);
    }

    public function test_duplicate_email_is_rejected(): void
    {
        Cache::put('evento_checkin_teste123', [
            [
                'name' => 'João da Silva',
                'email' => 'teste@example.com',
                'time' => now()->toISOString(),
            ],
        ], now()->addHours(8));

        $response = $this->postJson('/evento/checkin/process', [
            'name' => 'Outro Nome',
            'email' => 'TESTE@EXAMPLE.COM',
            'token' => 'teste123',
        ]);

        $response
            ->assertStatus(409)
            ->assertJson([
                'error' => 'E-mail já registrado nesta palestra.',
            ]);
    }

    public function test_get_checkins_returns_participants(): void
    {
        $professor = $this->criarProfessor();

        Cache::put('evento_checkin_teste123', [
            [
                'name' => 'João da Silva',
                'email' => 'joao@example.com',
                'time' => now()->toISOString(),
            ],
        ], now()->addHours(8));

        $this->actingAs($professor, 'professores');

        $response = $this->getJson('/professor/evento/checkins/teste123');

        $response
            ->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment([
                'name' => 'João da Silva',
                'email' => 'joao@example.com',
            ]);
    }

    public function test_encerrar_sessao_removes_checkins(): void
    {
        $professor = $this->criarProfessor();

        Cache::put('evento_checkin_teste123', [
            [
                'name' => 'João da Silva',
                'email' => 'joao@example.com',
                'time' => now()->toISOString(),
            ],
        ], now()->addHours(8));

        Cache::put(
            "professor_evento_{$professor->id}",
            'teste123',
            now()->addHours(8)
        );

        $this->actingAs($professor, 'professores');

        $response = $this->postJson('/professor/evento/encerrar', [
            'token' => 'teste123',
            'participantes' => [
                ['name' => 'João da Silva'],
            ],
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Sessão encerrada.',
                'total' => 1,
            ]);

        $this->assertNull(Cache::get('evento_checkin_teste123'));
        $this->assertNull(
            Cache::get("professor_evento_{$professor->id}")
        );
    }
}