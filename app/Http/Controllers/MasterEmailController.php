<?php

namespace App\Http\Controllers;

use App\Mail\EmailTesteMail;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Envio de e-mail de teste pelo Master, para confirmar que o SMTP da
 * instituição está configurado. Mostra o erro do servidor sem expor
 * usuário nem senha.
 */
class MasterEmailController extends BaseController
{
    public function teste()
    {
        $master = Auth::guard('masters')->user();

        try {
            Mail::to($master->email)->send(new EmailTesteMail($master->nome ?? 'Administrador'));
        } catch (\Throwable $e) {
            // No log e na tela, nunca o texto do servidor: ele pode trazer credenciais
            // ou endereços, e nenhum filtro cobre todos os formatos.
            Log::warning('Falha no e-mail de teste', ['excecao' => $e::class]);

            return back()->with('email_erro', self::mensagemDeFalha($e));
        }

        return back()->with('email_status', 'E-mail de teste enviado para ' . $master->email . '. Confira a caixa de entrada (e o spam).');
    }

    /** Configuração exibida no painel: transporte, servidor e porta. Nunca usuário/senha. */
    public static function resumoConfiguracao(): array
    {
        $mailer = config('mail.default');

        return [
            'mailer'    => $mailer,
            'host'      => $mailer === 'smtp' ? config('mail.mailers.smtp.host') : null,
            'porta'     => $mailer === 'smtp' ? config('mail.mailers.smtp.port') : null,
            'remetente' => config('mail.from.address'),
            'real'      => ! in_array($mailer, ['log', 'array'], true),
        ];
    }

    /**
     * Mensagem segura para a tela: o tipo do erro, o código SMTP (ex.: 535) e
     * uma dica do que conferir. O texto do servidor não é repetido.
     */
    private static function mensagemDeFalha(\Throwable $e): string
    {
        preg_match('/\b([45]\d\d)\b/', $e->getMessage(), $m);
        $codigo = $m[1] ?? null;

        $dica = match (true) {
            $codigo === '535', $codigo === '534' => 'o servidor recusou o usuário ou a senha (MAIL_USERNAME / MAIL_PASSWORD).',
            $codigo !== null && $codigo[0] === '5' => 'o servidor recusou a mensagem; confira o remetente (MAIL_FROM_ADDRESS) e as permissões da conta.',
            $codigo !== null => 'o servidor está temporariamente indisponível; tente de novo em alguns minutos.',
            default => 'não foi possível conectar; confira MAIL_HOST, MAIL_PORT e MAIL_SCHEME.',
        };

        return 'Falha no envio' . ($codigo ? " (código SMTP {$codigo})" : '') . ': ' . $dica;
    }
}
