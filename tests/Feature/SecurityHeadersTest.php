<?php

namespace Tests\Feature;

use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    public function test_security_headers_are_present(): void
    {
        $response = $this->get('/evento/checkin?token=teste123');

        $response->assertStatus(200);

        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader(
            'Referrer-Policy',
            'strict-origin-when-cross-origin'
        );
        $response->assertHeader(
            'Permissions-Policy',
            'camera=(), microphone=(), geolocation=()'
        );
        $response->assertHeader('Content-Security-Policy');
    }

    public function test_csp_nao_permite_script_inline_sem_nonce(): void
    {
        $csp = $this->get('/evento/checkin?token=teste123')->headers->get('Content-Security-Policy');

        preg_match("/(?:^|; )script-src ([^;]+)/", $csp, $script);
        preg_match("/(?:^|; )style-src ([^;]+)/", $csp, $style);

        $this->assertStringNotContainsString("'unsafe-inline'", $script[1], 'script-src não pode liberar inline');
        $this->assertStringNotContainsString("'unsafe-inline'", $style[1], 'style-src (blocos) não pode liberar inline');
        $this->assertStringNotContainsString('fonts.googleapis.com', $script[1], 'fonte não é origem de script');
        $this->assertMatchesRegularExpression("/'nonce-[A-Za-z0-9+\/=]{20,}'/", $script[1]);

        foreach (["object-src 'none'", "base-uri 'self'", "form-action 'self'", "frame-ancestors 'none'", "default-src 'self'"] as $diretiva) {
            $this->assertStringContainsString($diretiva, $csp);
        }
    }

    public function test_nonce_muda_a_cada_requisicao(): void
    {
        $nonce = function (): string {
            $this->refreshApplication();
            $csp = $this->get('/evento/checkin?token=teste123')->headers->get('Content-Security-Policy');
            preg_match("/'nonce-([^']+)'/", $csp, $m);

            return $m[1];
        };

        $this->assertNotSame($nonce(), $nonce());
    }

    public function test_todo_script_e_style_em_bloco_da_pagina_leva_o_nonce_do_cabecalho(): void
    {
        foreach (['/evento/checkin?token=teste123', '/login/aluno', '/login/professor', '/pagina-que-nao-existe'] as $url) {
            $resposta = $this->get($url);
            $csp = $resposta->headers->get('Content-Security-Policy');
            $this->assertNotNull($csp, "sem CSP em {$url}");

            preg_match("/'nonce-([^']+)'/", $csp, $m);
            $html = $resposta->getContent();

            preg_match_all('/<(script|style)\b([^>]*)>/i', $html, $tags, PREG_SET_ORDER);
            foreach ($tags as $tag) {
                if (stripos($tag[2], ' src=') !== false) {
                    continue; // scripts externos são liberados por origem
                }
                $this->assertStringContainsString("nonce=\"{$m[1]}\"", $tag[2], "<{$tag[1]}> sem nonce em {$url}");
            }
        }
    }

    public function test_pagina_404_tambem_recebe_os_cabecalhos(): void
    {
        $this->get('/pagina-que-nao-existe')
            ->assertNotFound()
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('Content-Security-Policy');
    }
}
