<?php

namespace Tests\Unit;

use App\Support\HostsConfiaveis;
use PHPUnit\Framework\TestCase;

class HostsConfiaveisTest extends TestCase
{
    private function aceita(string $host): bool
    {
        foreach (HostsConfiaveis::padroes() as $padrao) {
            if (preg_match('{' . $padrao . '}i', $host)) {
                return true;
            }
        }

        return false;
    }

    protected function setUp(): void
    {
        parent::setUp();
        foreach (['APP_URL', 'TRUSTED_HOSTS', 'VERCEL_URL', 'VERCEL_BRANCH_URL', 'VERCEL_PROJECT_PRODUCTION_URL'] as $v) {
            putenv($v);
            unset($_ENV[$v], $_SERVER[$v]);
        }
    }

    public function test_aceita_os_dominios_do_projeto_e_localhost(): void
    {
        $this->assertTrue($this->aceita('smart-attendance-nine-alpha.vercel.app'));
        $this->assertTrue($this->aceita('SMART-ATTENDANCE-NINE-ALPHA.vercel.app'), 'sem diferenciar maiúsculas');
        $this->assertTrue($this->aceita('localhost'));
    }

    public function test_recusa_host_de_atacante_e_variacoes(): void
    {
        $this->assertFalse($this->aceita('evil.com'));
        $this->assertFalse($this->aceita('evil.vercel.app'), 'sem curinga em *.vercel.app');
        $this->assertFalse($this->aceita('smart-attendance-nine-alpha.vercel.app.evil.com'));
        $this->assertFalse($this->aceita('xsmart-attendance-nine-alpha.vercel.app'));
        $this->assertFalse($this->aceita('smart-attendance-nine-alphaXvercel.app'), 'ponto é literal');
    }

    public function test_trusted_hosts_e_app_url_e_vercel_url_entram_na_lista(): void
    {
        putenv('TRUSTED_HOSTS=chamada.escola.edu.br, outro.escola.edu.br');
        putenv('APP_URL=https://app.exemplo.com.br');
        putenv('VERCEL_URL=smart-attendance-abc123-jooaoleonardis-projects.vercel.app');

        $this->assertTrue($this->aceita('chamada.escola.edu.br'));
        $this->assertTrue($this->aceita('outro.escola.edu.br'));
        $this->assertTrue($this->aceita('app.exemplo.com.br'));
        $this->assertTrue($this->aceita('smart-attendance-abc123-jooaoleonardis-projects.vercel.app'));
        $this->assertFalse($this->aceita('escola.edu.br'));
    }
}
