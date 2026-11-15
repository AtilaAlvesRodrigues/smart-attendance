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

        $request->validate([
            'materia_id'  => 'nullable|integer',
            'data_inicio' => 'nullable|date_format:Y-m-d',
            'data_fim'    => 'nullable|date_format:Y-m-d',
        ]);

        $permitidas = $professor->materias()->pluck('materias.id')->all();

        abort_if(
            $request->filled('materia_id') && ! in_array((int) $request->materia_id, $permitidas, true),
            403,
            'Você não tem permissão para exportar esta matéria.',
        );

        return ExportadorPresencas::baixar(
            GerenciarMateriaController::filtrarRelatorioProfessor($request, $professor),
            materiasPermitidas: $permitidas,
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
