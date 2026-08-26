<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class HoneypotTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkin_works_when_honeypot_is_empty(): void
    {
        $response = $this->postJson('/evento/checkin/process', [
            'name' => 'João da Silva',
            'email' => 'joao@example.com',
            'token' => 'teste-honeypot-empty',
            'hp_field' => '',
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $checkins = Cache::get('evento_checkin_teste-honeypot-empty');

        $this->assertCount(1, $checkins);
        $this->assertSame('João da Silva', $checkins[0]['name']);
        $this->assertSame('joao@example.com', $checkins[0]['email']);
    }

    public function test_checkin_is_blocked_when_honeypot_is_filled(): void
    {
        $response = $this->postJson('/evento/checkin/process', [
            'name' => 'Bot Teste',
            'email' => 'bot@example.com',
            'token' => 'teste-honeypot-filled',
            'hp_field' => 'bot',
        ]);

        $response->assertStatus(422);

        $checkins = Cache::get('evento_checkin_teste-honeypot-filled');

        $this->assertNull($checkins);
    }
}
