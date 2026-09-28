<?php

namespace Tests\Feature;

use Tests\TestCase;

class CronManterBancoTest extends TestCase
{
    public function test_sem_segredo_configurado_a_rota_nao_existe(): void
    {
        config(['services.cron.secret' => null]);
        $this->get('/cron/manter-banco-ativo')->assertNotFound();
    }

    public function test_segredo_errado_e_recusado(): void
    {
        config(['services.cron.secret' => 'certo']);
        $this->get('/cron/manter-banco-ativo', ['Authorization' => 'Bearer errado'])->assertNotFound();
    }

    public function test_segredo_certo_consulta_o_banco(): void
    {
        config(['services.cron.secret' => 'certo']);
        $this->get('/cron/manter-banco-ativo', ['Authorization' => 'Bearer certo'])->assertNoContent();
    }
}
