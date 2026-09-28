<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configurarLimitesDeTentativas();
    }

    /**
     * Limites de tentativas por PESSOA (usuário ou e-mail + IP), com um teto
     * bem mais alto por IP.
     *
     * Limitar só por IP travava o uso real: numa instituição, todos os celulares
     * do Wi-Fi saem pelo mesmo IP, então uma turma inteira fazendo login para
     * registrar presença (ou o público de uma palestra fazendo check-in)
     * esbarrava no limite depois das primeiras pessoas.
     */
    private function configurarLimitesDeTentativas(): void
    {
        $chave = fn (Request $request, string ...$campos) => Str::lower(trim(implode('|', array_map(
            fn ($campo) => (string) $request->input($campo, ''),
            $campos,
        )))) . '|' . $request->ip();

        // Login: protege cada conta contra tentativa e erro de senha
        RateLimiter::for('login', fn (Request $request) => [
            Limit::perMinutes(3, 10)->by('login|' . $chave($request, 'ra_email_cpf', 'cpf_email')),
            Limit::perMinute(300)->by('login-ip|' . $request->ip()),
        ]);

        // Criação de senha: só quem já entrou com o token chega aqui
        RateLimiter::for('criar-senha', fn (Request $request) => [
            Limit::perMinutes(3, 10)->by('criar-senha|' . $request->session()->getId()),
            Limit::perMinute(300)->by('criar-senha-ip|' . $request->ip()),
        ]);

        RateLimiter::for('recuperar-senha', fn (Request $request) => [
            Limit::perMinutes(10, 3)->by('recuperar|' . $chave($request, 'email')),
            Limit::perMinutes(10, 100)->by('recuperar-ip|' . $request->ip()),
        ]);

        RateLimiter::for('solicitar-acesso', fn (Request $request) => [
            Limit::perMinutes(10, 5)->by('solicitar|' . $chave($request, 'email')),
            Limit::perMinutes(10, 100)->by('solicitar-ip|' . $request->ip()),
        ]);

        // Check-in público de eventos: muitos participantes na mesma rede
        RateLimiter::for('evento-checkin', fn (Request $request) => [
            Limit::perMinute(5)->by('evento|' . $chave($request, 'token', 'email')),
            Limit::perMinute(300)->by('evento-ip|' . $request->ip()),
        ]);
        RateLimiter::for('evento-formulario', fn (Request $request) => Limit::perMinute(300)->by($request->ip()));
    }
}
