<?php

namespace App\Support;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Login que passou pela senha e aguarda o segundo fator.
 *
 * Nada é autenticado (nem o cookie de "lembrar-me" é emitido) até o código
 * ser conferido: só o guard, o id e o destino ficam na sessão, por 5 minutos.
 */
class LoginPendente
{
    private const CHAVE = '2fa.pendente';
    private const GUARDS = ['alunos', 'professores', 'masters'];

    public static function iniciar(Request $request, string $guard, int $id, bool $lembrar, string $destino): void
    {
        $request->session()->regenerate();
        $request->session()->put(self::CHAVE, [
            'guard'   => $guard,
            'id'      => $id,
            'lembrar' => $lembrar,
            'destino' => $destino,
            'expira'  => now()->addSeconds(config('seguranca.2fa_desafio_ttl', 300))->getTimestamp(),
        ]);
    }

    /** @return array{guard: string, id: int, lembrar: bool, destino: string, expira: int}|null */
    public static function atual(Request $request): ?array
    {
        $pendente = $request->session()->get(self::CHAVE);

        if (! is_array($pendente) || ! in_array($pendente['guard'] ?? null, self::GUARDS, true)) {
            return null;
        }

        if (now()->getTimestamp() > (int) $pendente['expira']) {
            self::cancelar($request);

            return null;
        }

        return $pendente;
    }

    public static function usuario(array $pendente)
    {
        return Auth::guard($pendente['guard'])->getProvider()->retrieveById($pendente['id']);
    }

    public static function concluir(Request $request, array $pendente): RedirectResponse
    {
        $usuario = self::usuario($pendente);

        foreach (self::GUARDS as $guard) {
            if ($guard !== $pendente['guard']) {
                Auth::guard($guard)->logout();
            }
        }

        Auth::guard($pendente['guard'])->login($usuario, (bool) $pendente['lembrar']);
        self::cancelar($request);
        $request->session()->regenerate();
        $request->session()->put('login_time', time());

        return redirect()->to($pendente['destino']);
    }

    public static function cancelar(Request $request): void
    {
        $request->session()->forget(self::CHAVE);
    }
}
