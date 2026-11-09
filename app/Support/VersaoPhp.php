<?php

namespace App\Support;

/**
 * Diz se uma versão do PHP já tem a correção das CVE-2026-109618 e CVE-2026-109619
 * (execução remota de código no interpretador, CVSS 9.2, divulgadas em 21/10/2026).
 *
 * Primeira versão corrigida de cada série: 8.2.35, 8.3.36, 8.4.27 e 8.5.12.
 * Veja SEC-11 em docs/falhas-de-seguranca.md.
 */
class VersaoPhp
{
    /** Série => primeira versão com a correção. */
    public const CORRIGIDAS = [
        '8.2' => '8.2.35',
        '8.3' => '8.3.36',
        '8.4' => '8.4.27',
        '8.5' => '8.5.12',
    ];

    /** Série da versão, por exemplo "8.4" para "8.4.14". */
    public static function serie(string $versao): string
    {
        return implode('.', array_slice(explode('.', $versao), 0, 2));
    }

    /**
     * true  = versão corrigida;
     * false = série conhecida e ainda sem a correção, ou série antiga (8.1 e anteriores);
     * null  = série mais nova que as conhecidas: não dá para afirmar.
     */
    public static function corrigida(string $versao): ?bool
    {
        $serie = self::serie($versao);

        if (isset(self::CORRIGIDAS[$serie])) {
            return version_compare($versao, self::CORRIGIDAS[$serie], '>=');
        }

        return version_compare($serie, '8.2', '<') ? false : null;
    }

    /** @return array<string, mixed> */
    public static function relatorio(string $versao = PHP_VERSION): array
    {
        $serie = self::serie($versao);

        return [
            'php_versao' => $versao,
            'serie' => $serie,
            'corrigida_a_partir_de' => self::CORRIGIDAS[$serie] ?? null,
            'cves_corrigidas' => self::corrigida($versao),
            'sapi' => PHP_SAPI,
            'memory_limit' => ini_get('memory_limit'),
            'laravel' => app()->version(),
            'ambiente' => app()->environment(),
            'debug' => (bool) config('app.debug'),
        ];
    }
}
