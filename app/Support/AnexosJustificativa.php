<?php

namespace App\Support;

use App\Models\Justificativa;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

/**
 * Onde ficam os atestados das justificativas.
 *
 * - JUSTIFICATIVAS_DISK=banco: tabela justificativa_anexos (base64). Funciona
 *   na Vercel sem serviço externo.
 * - local / s3: disco "justificativas" do Laravel (pasta privada ou Supabase Storage).
 *
 * A leitura não depende do modo atual: anexo_path = 'banco' indica o banco;
 * qualquer outro valor é um caminho no disco.
 */
class AnexosJustificativa
{
    public const MARCADOR_BANCO = 'banco';

    /** Tamanho máximo, em KB. A Vercel recusa requisições acima de 4,5 MB. */
    public const LIMITE_KB = 4096;

    public static function noBanco(): bool
    {
        return (bool) config('filesystems.justificativas_no_banco');
    }

    /**
     * Grava o arquivo no disco antes de existir a justificativa (modo disco).
     * No modo banco não faz nada aqui: o conteúdo é gravado por guardarNoBanco(),
     * na mesma transação da justificativa.
     *
     * @return array{path: string, nome: string, mime: ?string}
     */
    public static function prepararNoDisco(UploadedFile $arquivo): array
    {
        $path = self::noBanco()
            ? self::MARCADOR_BANCO
            : Storage::disk(Justificativa::DISCO)->putFile(now()->format('Y/m'), $arquivo);

        return [
            'path' => $path,
            'nome' => mb_substr($arquivo->getClientOriginalName(), 0, 200),
            'mime' => $arquivo->getMimeType(),
        ];
    }

    public static function guardarNoBanco(Justificativa $justificativa, UploadedFile $arquivo): void
    {
        if ($justificativa->anexo_path !== self::MARCADOR_BANCO) {
            return;
        }

        DB::table('justificativa_anexos')->insert([
            'justificativa_id' => $justificativa->id,
            'conteudo'         => base64_encode((string) file_get_contents($arquivo->getRealPath())),
            'tamanho'          => (int) $arquivo->getSize(),
            'created_at'       => now(),
        ]);
    }

    /** Desfaz o arquivo gravado no disco quando a justificativa não chegou a ser criada. */
    public static function descartar(?string $path): void
    {
        if ($path && $path !== self::MARCADOR_BANCO) {
            Storage::disk(Justificativa::DISCO)->delete($path);
        }
    }

    public static function resposta(Justificativa $justificativa): Response
    {
        abort_unless($justificativa->anexo_path, 404);

        $nome = $justificativa->anexo_nome ?: 'atestado';
        $cabecalhos = [
            'Content-Type'           => $justificativa->anexo_mime ?: 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
        ];

        if ($justificativa->anexo_path === self::MARCADOR_BANCO) {
            $conteudo = DB::table('justificativa_anexos')
                ->where('justificativa_id', $justificativa->id)
                ->value('conteudo');
            abort_unless($conteudo, 404);

            return response()->streamDownload(function () use ($conteudo) {
                echo base64_decode($conteudo);
            }, $nome, $cabecalhos);
        }

        $disco = Storage::disk(Justificativa::DISCO);
        abort_unless($disco->exists($justificativa->anexo_path), 404);

        return $disco->download($justificativa->anexo_path, $nome, $cabecalhos);
    }
}
