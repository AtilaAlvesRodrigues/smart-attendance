<?php

namespace App\Support;

/**
 * Token rotativo do QR Code da chamada.
 *
 * O QR Code leva `?t={slot}.{assinatura}` na URL. O slot é o tempo dividido em
 * janelas de STEP segundos e a assinatura é um HMAC-SHA256 de (código da aula + slot)
 * com a chave da aplicação. Não precisa de banco nem de cache: professor e
 * servidor chegam ao mesmo valor só com a APP_KEY e o relógio do servidor.
 *
 * Uma foto do QR Code deixa de valer em, no máximo, 2 × STEP segundos
 * (o slot atual e o anterior são aceitos, para cobrir a latência de quem escaneia
 * no fim da janela).
 */
class QrToken
{
    /** Duração de cada janela, em segundos. */
    public const STEP = 30;

    /** Quantas janelas anteriores à atual ainda são aceitas. */
    public const TOLERANCIA = 1;

    public static function gerar(string $codigoAula, ?int $agora = null): string
    {
        $slot = self::slot($agora);

        return $slot . '.' . self::assinatura($codigoAula, $slot);
    }

    public static function validar(string $codigoAula, ?string $token, ?int $agora = null): bool
    {
        if (! is_string($token) || ! preg_match('/^(\d{1,12})\.([a-f0-9]{32})$/', $token, $m)) {
            return false;
        }

        $slotToken = (int) $m[1];
        $slotAtual = self::slot($agora);

        if ($slotToken > $slotAtual || $slotAtual - $slotToken > self::TOLERANCIA) {
            return false;
        }

        return hash_equals(self::assinatura($codigoAula, $slotToken), $m[2]);
    }

    /** Segundos até o token atual deixar de ser o mais novo (quando o QR deve ser trocado). */
    public static function segundosAteProximo(?int $agora = null): int
    {
        $agora ??= time();

        return self::STEP - ($agora % self::STEP);
    }

    private static function slot(?int $agora): int
    {
        return intdiv($agora ?? time(), self::STEP);
    }

    private static function assinatura(string $codigoAula, int $slot): string
    {
        return substr(hash_hmac('sha256', $codigoAula . '|' . $slot, (string) config('app.key')), 0, 32);
    }
}
