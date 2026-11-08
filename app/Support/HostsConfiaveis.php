<?php

namespace App\Support;

/**
 * Domínios que podem aparecer no cabeçalho Host (e em X-Forwarded-Host).
 *
 * Sem isso, quem escolhe o Host é o cliente, e o Laravel gera URLs com ele: o e-mail de
 * recuperação de senha podia levar um link para o domínio de um atacante (host header
 * poisoning). Com a lista, um Host desconhecido recebe 400.
 *
 * A lista é exata (nada de curinga *.vercel.app, que qualquer pessoa consegue usar com
 * um projeto próprio). Para incluir um domínio novo, como um domínio próprio da escola,
 * use a variável TRUSTED_HOSTS (separada por vírgulas) na Vercel.
 */
class HostsConfiaveis
{
    /** Endereços do projeto na Vercel (produção). */
    private const PADRAO = [
        'smart-attendance-nine-alpha.vercel.app',
        'smart-attendance-jooaoleonardis-projects.vercel.app',
        'localhost',
        '127.0.0.1',
    ];

    /** Variáveis que a própria Vercel preenche com o domínio de cada deploy (preview inclusive). */
    private const VARIAVEIS_VERCEL = ['VERCEL_URL', 'VERCEL_BRANCH_URL', 'VERCEL_PROJECT_PRODUCTION_URL'];

    /** @return array<int, string> padrões (regex ancoradas) aceitos pelo Symfony */
    public static function padroes(): array
    {
        $hosts = self::PADRAO;

        if ($appUrl = parse_url((string) env('APP_URL'), PHP_URL_HOST)) {
            $hosts[] = $appUrl;
        }

        foreach (self::VARIAVEIS_VERCEL as $variavel) {
            if ($valor = env($variavel)) {
                $hosts[] = $valor;
            }
        }

        foreach (explode(',', (string) env('TRUSTED_HOSTS')) as $extra) {
            $hosts[] = $extra;
        }

        $hosts = array_unique(array_filter(array_map(fn ($h) => strtolower(trim((string) $h)), $hosts)));

        return array_map(fn ($h) => '^' . preg_quote($h, '/') . '$', array_values($hosts));
    }
}
