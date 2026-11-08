<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

/**
 * Injects security-oriented HTTP response headers.
 * Protects against Clickjacking, MIME sniffing, and XSS (via Content-Security-Policy).
 *
 * A CSP gera um nonce novo a cada requisição. Todo <script> e <style> em bloco das
 * views precisa de `@nonce` (veja a diretiva em AppServiceProvider); um bloco injetado
 * por um atacante não tem o nonce e o navegador se recusa a executá-lo.
 *
 * Débito conhecido: `script-src-attr` e `style-src-attr` ainda liberam 'unsafe-inline'
 * porque as views têm atributos `onclick=` e `style=`. Ver docs/falhas-de-seguranca.md.
 */
class SecurityHeaders
{
    public const NONCE_BINDING = 'csp.nonce';

    public function handle(Request $request, Closure $next): Response
    {
        $nonce = base64_encode(random_bytes(16));
        app()->instance(self::NONCE_BINDING, $nonce);
        Vite::useCspNonce($nonce); // as tags geradas por @vite também recebem o nonce

        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $response->headers->set('Content-Security-Policy', self::politica($nonce, $request->isSecure()));

        return $response;
    }

    /** Valor do nonce da requisição atual (vazio fora de uma requisição web). */
    public static function nonce(): string
    {
        return app()->bound(self::NONCE_BINDING) ? app(self::NONCE_BINDING) : '';
    }

    public static function politica(string $nonce, bool $https = false): string
    {
        $diretivas = [
            "default-src 'self'",
            // Scripts: só os do próprio site, os com nonce e a CDN dos QR Codes/PDF.
            "script-src 'self' 'nonce-{$nonce}' https://cdnjs.cloudflare.com",
            // Atributos onclick=... das telas antigas (débito: migrar para addEventListener).
            "script-src-attr 'unsafe-inline'",
            "style-src 'self' 'nonce-{$nonce}' https://fonts.googleapis.com",
            // Atributos style="..." das telas antigas (débito: migrar para classes CSS).
            "style-src-attr 'unsafe-inline'",
            "font-src 'self' https://fonts.gstatic.com",
            "img-src 'self' data:",
            "connect-src 'self'",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'none'",
        ];

        if ($https) {
            $diretivas[] = 'upgrade-insecure-requests';
        }

        return implode('; ', $diretivas) . ';';
    }
}
