<?php

namespace App\Http\Controllers;

use App\Support\ExportadorPresencas;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;

/**
 * Exportação das presenças em planilha Excel (.xlsx), com os mesmos filtros
 * das telas de relatório do professor e da Central de Presenças do Master.
 */
class ExportacaoController extends BaseController
{
    public function professor(Request $request)
    {
        $professor = Auth::guard('professores')->user();

        return ExportadorPresencas::baixar(
            GerenciarMateriaController::filtrarRelatorioProfessor($request, $professor),
        );
    }

    public function master(Request $request)
    {
        return ExportadorPresencas::baixar(
            DashboardController::filtrarPresencasMaster($request),
            'central-presencas',
        );
    }
}
