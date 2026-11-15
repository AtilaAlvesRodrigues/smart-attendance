<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;
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
            'two_factor_ultimo_timestep'=> 'integer',
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
        $timestep = self::timestepAceito($segredo, $codigo, null);

        if ($timestep === null) {
            return false;
        }

        $this->forceFill([
            'two_factor_secret'          => $segredo,
            'two_factor_confirmed_at'    => now(),
            'two_factor_ultimo_timestep' => $timestep,
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

        // Lê e grava sob lock da linha: duas requisições simultâneas com o
        // mesmo código não podem passar as duas (cada código vale uma vez)
        return DB::transaction(function () use ($codigo) {
            $atual = static::whereKey($this->getKey())->lockForUpdate()->first();

            if (! $atual || ! $atual->temDoisFatores()) {
                return false;
            }

            $aceito = preg_match('/^\d{6}$/', str_replace(' ', '', $codigo))
                ? $atual->consumirCodigoTotp($codigo)
                : $atual->consumirCodigoRecuperacao($codigo);

            if ($aceito) {
                $this->setRawAttributes($atual->getAttributes(), true);
            }

            return $aceito;
        });
    }

    /** Só aceita código de uma janela posterior à do último aceito (sem reuso). */
    private function consumirCodigoTotp(string $codigo): bool
    {
        $timestep = self::timestepAceito($this->two_factor_secret, $codigo, $this->two_factor_ultimo_timestep);

        if ($timestep === null) {
            return false;
        }

        $this->forceFill(['two_factor_ultimo_timestep' => $timestep])->save();

        return true;
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
            'two_factor_ultimo_timestep'=> null,
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

    /**
     * Timestep (janela de 30 s) do código, se ele for válido (±1 janela) e mais
     * novo que $ultimo. Null se inválido ou já usado.
     */
    private static function timestepAceito(string $segredo, string $codigo, ?int $ultimo): ?int
    {
        $codigo = str_replace(' ', '', $codigo);

        if (preg_match('/^\d{6}$/', $codigo) !== 1) {
            return null;
        }

        $resultado = (new Google2FA())->verifyKeyNewer($segredo, $codigo, $ultimo ?? 0, 1);

        return is_int($resultado) ? $resultado : null;
    }
}
