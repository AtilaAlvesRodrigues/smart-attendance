<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

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
