<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware CheckRole
 *
 * Valida que o usuário autenticado possui o papel (role) exigido pela rota.
 *
 * O sistema possui 3 guards isolados: 'alunos', 'professores' e 'masters'.
 * Este middleware impede que um aluno autenticado acesse rotas de professor
 * mesmo que manipule cookies ou tokens — é uma segunda camada de verificação
 * além da autenticação pelo guard.
 *
 * Uso nas rotas: ->middleware('role:professor')
 */
class CheckRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // Mapeia cada role para seu guard isolado no Laravel.
        // Guards separados impedem escalação de privilégio entre perfis.
        $guardMap = [
            'professor' => 'professores',
            'aluno' => 'alunos',
            'master' => 'masters',
        ];

        $guard = $guardMap[$role] ?? null;

        if (!$guard || !Auth::guard($guard)->check()) {
            return $this->handleUnauthorizedResponse($request);
        }

        $user = Auth::guard($guard)->user();

        if ($user->role !== $role) {
            return $this->handleUnauthorizedResponse($request);
        }

        return $next($request);
    }

    private function handleUnauthorizedResponse(Request $request): Response
    {
        if (Auth::guard('professores')->check()) {
            return redirect()->route('dashboard.professor');
        }
        if (Auth::guard('alunos')->check()) {
            return redirect()->route('dashboard.aluno');
        }
        if (Auth::guard('masters')->check()) {
            return redirect()->route('dashboard.master');
        }

        return redirect()->route('login_form');
    }
}
