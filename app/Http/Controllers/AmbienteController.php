<?php

namespace App\Http\Controllers;

use App\Support\VersaoPhp;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

/**
 * Informações do ambiente de execução, só para o Master.
 * Existe para responder "qual PHP está rodando em produção?" (SEC-11). Não expõe
 * segredos, variáveis de ambiente nem caminhos: só versões e flags.
 */
class AmbienteController extends Controller
{
    public function php(): JsonResponse
    {
        return response()->json(VersaoPhp::relatorio())->header('Cache-Control', 'no-store');
    }
}
