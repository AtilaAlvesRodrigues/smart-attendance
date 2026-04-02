<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware PrimeiroAcessoMiddleware
 *
 * Protege as rotas de criação de senha (/criar-senha) para que só sejam acessíveis
 * por usuários que chegaram via link de primeiro acesso (e-mail enviado pelo sistema).
 *
 * A sessão 'pending_password_creation' é criada pelos controllers de login quando
 * detectam que o remember_token do usuário ainda é o token de ativação original.
 * Sem essa sessão, a rota /criar-senha redireciona para o login, impedindo
 * que qualquer pessoa acesse a criação de senha diretamente pela URL.
 */
class PrimeiroAcessoMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!session()->has('pending_password_creation')) {
            return redirect()->route('login_form');
        }

        return $next($request);
    }
}
