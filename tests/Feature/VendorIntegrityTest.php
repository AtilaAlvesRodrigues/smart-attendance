<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * As bibliotecas em public/js/vendor são cópias dos arquivos da cdnjs. Se alguém trocar
 * ou editar um deles (inclusive por engano), este teste falha: o hash tem de ser o publicado.
 */
class VendorIntegrityTest extends TestCase
{
    private const SHA512 = [
        'qrcode.min.js' => 'CNgIRecGo7nphbeZ04Sc13ka07paqdeTu0WR1IM4kNcpmBAUSHSQX0FslNhTDadL4O5SAGapGt4FodqL8My0mA==',
        'jspdf.umd.min.js' => 'qZvrmS2ekKPF2mSznTQsxqPgnpkI4DNTlrdUmTzrDgektczlKNRRhy5X5AAOnx5S09ydFYWWNSfcEqDTTHgtNA==',
        'jspdf.plugin.autotable.min.js' => '03CCNkeosDFN2zCCu4vLpu3pJfZcrL48F3yB8k87ejT+OVMwco7IH3FW02vtbGhdncS6gyYZ/duYaC/K62xQPQ==',
    ];

    public function test_bibliotecas_locais_batem_com_o_hash_publicado(): void
    {
        foreach (self::SHA512 as $arquivo => $esperado) {
            $caminho = public_path("js/vendor/{$arquivo}");
            $this->assertFileExists($caminho);
            $this->assertSame($esperado, base64_encode(hash_file('sha512', $caminho, true)), "{$arquivo} foi alterado");
        }
    }

    public function test_nenhuma_view_carrega_script_de_cdn(): void
    {
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(resource_path('views'))) as $f) {
            if ($f->isFile() && str_ends_with($f->getFilename(), '.blade.php') && ! str_contains($f->getPathname(), '/emails/')) {
                $this->assertDoesNotMatchRegularExpression('#<script[^>]+src="https?://#i', file_get_contents($f->getPathname()), $f->getPathname());
            }
        }
    }

    public function test_csp_nao_libera_cdn_de_scripts(): void
    {
        $csp = $this->get('/login')->headers->get('Content-Security-Policy');
        $this->assertStringNotContainsString('cdnjs.cloudflare.com', $csp);
    }
}
