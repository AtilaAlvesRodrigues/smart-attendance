<?php

namespace Tests\Unit;

use PDO;
use Tests\TestCase;

/**
 * Em produção, a Vercel usa o pooler do Supabase em modo transação (porta 6543):
 * a vaga no pool fica ocupada só durante cada consulta, e não a requisição
 * inteira (no modo sessão, o 16º acesso simultâneo recebia erro 500). Nesse
 * modo, a conexão do servidor muda entre transações, então os prepared
 * statements precisam ser emulados no PHP (DB_EMULATE_PREPARES=true).
 */
class ConexaoPoolerTest extends TestCase
{
    private function opcoesPgsql(): array
    {
        return (require base_path('config/database.php'))['connections']['pgsql']['options'] ?? [];
    }

    public function test_emula_prepared_statements_quando_ligado(): void
    {
        putenv('DB_EMULATE_PREPARES=true');
        $_ENV['DB_EMULATE_PREPARES'] = 'true';
        try {
            $this->assertTrue($this->opcoesPgsql()[PDO::ATTR_EMULATE_PREPARES] ?? false);
        } finally {
            putenv('DB_EMULATE_PREPARES');
            unset($_ENV['DB_EMULATE_PREPARES']);
        }
    }

    public function test_sem_emulacao_por_padrao(): void
    {
        $antes = getenv('DB_EMULATE_PREPARES');
        putenv('DB_EMULATE_PREPARES');
        unset($_ENV['DB_EMULATE_PREPARES'], $_SERVER['DB_EMULATE_PREPARES']);
        try {
            $this->assertArrayNotHasKey(PDO::ATTR_EMULATE_PREPARES, $this->opcoesPgsql());
        } finally {
            if ($antes !== false) {
                putenv("DB_EMULATE_PREPARES={$antes}");
                $_ENV['DB_EMULATE_PREPARES'] = $antes;
            }
        }
    }
}
