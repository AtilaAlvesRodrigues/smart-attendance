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
            $mensagem = self::semCredenciais($e->getMessage());
            // No log, só a classe: o texto do servidor pode trazer endereços de e-mail
            Log::warning('Falha no e-mail de teste', ['excecao' => $e::class]);

            return back()->with('email_erro', 'Falha no envio: ' . mb_substr($mensagem, 0, 300));
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
     * Remove da mensagem do servidor qualquer credencial de e-mail: usuário,
     * senha e URL de cada mailer configurado, e o trecho "usuario:senha@" de
     * qualquer URL (ex.: MAIL_URL=smtp://usuario:senha@host:587).
     */
    private static function semCredenciais(string $mensagem): string
    {
        $mensagem = preg_replace('#([a-z][a-z0-9+.\-]*://)[^\s/@]+@#i', '$1***@', $mensagem) ?? '';

        $segredos = [config('mail.url')];
        foreach ((array) config('mail.mailers', []) as $mailer) {
            foreach (['username', 'password', 'url', 'secret', 'token', 'key'] as $campo) {
                $segredos[] = is_array($mailer) ? ($mailer[$campo] ?? null) : null;
            }
        }

        foreach (array_filter($segredos, fn ($s) => is_string($s) && strlen($s) >= 3) as $segredo) {
            $mensagem = str_replace($segredo, '***', $mensagem);
        }

        return $mensagem;
    }
}
