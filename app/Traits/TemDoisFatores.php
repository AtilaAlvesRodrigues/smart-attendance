<?php

namespace App\Traits;

use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

/**
 * Autenticação em dois fatores por app autenticador (TOTP, RFC 6238).
 *
 * - two_factor_secret: segredo Base32, criptografado no banco.
 * - two_factor_recovery_codes: hashes SHA-256 dos 8 códigos de uso único.
 * - two_factor_confirmed_at: o 2FA só vale depois que o usuário confirma um
 *   código, para ninguém ficar trancado por ter escaneado errado.
 */
trait TemDoisFatores
{
    public function initializeTemDoisFatores(): void
    {
        $this->mergeCasts([
            'two_factor_secret'         => 'encrypted',
            'two_factor_recovery_codes' => 'encrypted:array',
            'two_factor_confirmed_at'   => 'datetime',
        ]);
        $this->hidden = array_values(array_unique(array_merge($this->hidden, [
            'two_factor_secret',
            'two_factor_recovery_codes',
        ])));
    }

    public function temDoisFatores(): bool
    {
        return ! empty($this->two_factor_secret) && $this->two_factor_confirmed_at !== null;
    }

    public static function novoSegredoDoisFatores(): string
    {
        return (new Google2FA())->generateSecretKey(32);
    }

    /** Ativa o 2FA com o segredo mostrado na configuração, se o código bater. */
    public function confirmarDoisFatores(string $segredo, string $codigo): bool
    {
        if (! self::codigoTotpValido($segredo, $codigo)) {
            return false;
        }

        $this->forceFill([
            'two_factor_secret'       => $segredo,
            'two_factor_confirmed_at' => now(),
        ])->save();

        return true;
    }

    /** Código do app (janela de ±30 s) ou código de recuperação, que é consumido. */
    public function verificarCodigoDoisFatores(string $codigo): bool
    {
        if (! $this->temDoisFatores()) {
            return false;
        }

        $codigo = trim($codigo);

        if (preg_match('/^\d{6}$/', str_replace(' ', '', $codigo))) {
            return self::codigoTotpValido($this->two_factor_secret, $codigo);
        }

        return $this->consumirCodigoRecuperacao($codigo);
    }

    /** @return list<string> códigos em texto puro, mostrados uma única vez */
    public function gerarCodigosRecuperacao(): array
    {
        $codigos = collect(range(1, 8))
            ->map(fn () => Str::upper(Str::random(5) . '-' . Str::random(5)))
            ->all();

        $this->forceFill([
            'two_factor_recovery_codes' => array_map(fn ($c) => hash('sha256', $c), $codigos),
        ])->save();

        return $codigos;
    }

    public function desativarDoisFatores(): void
    {
        $this->forceFill([
            'two_factor_secret'         => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at'   => null,
        ])->save();
    }

    public function codigosRecuperacaoRestantes(): int
    {
        return count($this->two_factor_recovery_codes ?? []);
    }

    private function consumirCodigoRecuperacao(string $codigo): bool
    {
        $hash = hash('sha256', Str::upper($codigo));
        $restantes = $this->two_factor_recovery_codes ?? [];

        foreach ($restantes as $i => $guardado) {
            if (hash_equals($guardado, $hash)) {
                unset($restantes[$i]);
                $this->forceFill(['two_factor_recovery_codes' => array_values($restantes)])->save();

                return true;
            }
        }

        return false;
    }

    private static function codigoTotpValido(string $segredo, string $codigo): bool
    {
        $codigo = str_replace(' ', '', $codigo);

        return preg_match('/^\d{6}$/', $codigo) === 1
            && (new Google2FA())->verifyKey($segredo, $codigo, 1);
    }
}
