<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Master sem 2FA confirmado não entra no painel: vai para a configuração.
 * Controlado por config('seguranca.2fa_obrigatorio_master').
 */
class ExigirDoisFatores
{
    public function handle(Request $request, Closure $next): Response
    {
        $master = Auth::guard('masters')->user();

        if ($master && config('seguranca.2fa_obrigatorio_master') && ! $master->temDoisFatores()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Ative a autenticação em dois fatores para continuar.'], 403);
            }

            return redirect()->route('dois-fatores.configurar')
                ->with('status', 'Para acessar o painel do Master, ative a autenticação em dois fatores.');
        }

        return $next($request);
    }
}
